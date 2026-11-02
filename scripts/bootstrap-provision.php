#!/usr/bin/env php
<?php

declare(strict_types=1);

use Pcd\Config\Env;
use Pcd\Database\Db;
use Pcd\Provisioning\Plan;
use Pcd\Provisioning\Provisioner;

/**
 * Aprovisionamiento de cuentas PCD.
 *
 * UAT (pruebas/aceptación):
 *   php scripts/bootstrap-provision.php --mode=uat
 *   Requiere PCD_DB_* y PCD_UAT_DIGITADOR_PASSWORD, PCD_ADMIN_PASSWORD,
 *   PCD_REPORTES_PASSWORD. La clave común de DIGITADOR es SOLO UAT.
 *
 * Primer ADMIN productivo (procedimiento separado):
 *   PCD_ENV=production php scripts/bootstrap-provision.php --mode=admin-prod
 *   Requiere PCD_ADMIN_PASSWORD provista por canal seguro, fuera de Git.
 *   --rotate cambia la clave del ADMIN y sube auth_version (revoca sesiones).
 *
 * Nunca imprime contraseñas ni hashes. No ejecuta DDL ni modifica el esquema.
 */

$root = dirname(__DIR__);
$autoload = $root . '/backend/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "ERROR: falta backend/vendor/autoload.php. Ejecute: (cd backend && composer install)\n");
    exit(2);
}

require $autoload;
Env::load($root . '/backend/.env');

$options = getopt('', ['mode:', 'dry-run', 'rotate', 'help']);

if (isset($options['help'])) {
    usage();
    exit(0);
}

$mode = (string) ($options['mode'] ?? 'uat');
$dryRun = array_key_exists('dry-run', $options);
$rotate = array_key_exists('rotate', $options);

try {
    $exitCode = match ($mode) {
        'uat' => runUat($dryRun),
        'admin-prod' => runAdminProd($dryRun, $rotate),
        default => throw new BootstrapError(sprintf('Modo no válido: %s (use uat o admin-prod).', $mode), 2),
    };
} catch (BootstrapError $error) {
    fwrite(STDERR, 'ERROR: ' . $error->getMessage() . PHP_EOL);
    exit($error->exitCode);
} catch (Throwable $error) {
    fwrite(STDERR, 'ERROR: ' . $error->getMessage() . PHP_EOL);
    exit(3);
}

exit($exitCode);

function runUat(bool $dryRun): int
{
    $environment = strtolower((string) Env::get('PCD_ENV', 'local'));

    if (!in_array($environment, ['local', 'uat', 'testing', 'development'], true)) {
        throw new BootstrapError(
            'El modo uat usa contraseña común de DIGITADOR y no puede ejecutarse en producción. '
            . 'Use --mode=admin-prod y credenciales individuales.',
            2,
        );
    }

    $passwords = [
        'DIGITADOR' => requireEnv('PCD_UAT_DIGITADOR_PASSWORD'),
        'ADMIN' => requireEnv('PCD_ADMIN_PASSWORD'),
        'REPORTES' => requireEnv('PCD_REPORTES_PASSWORD'),
    ];

    if ($dryRun) {
        $plan = Plan::build(fixedPresbiterios());
        printPlan($plan);
        fwrite(STDOUT, PHP_EOL . 'DRY-RUN: plan válido; no se conecta a BD ni se escribe nada.' . PHP_EOL);
        return 0;
    }

    assertArgon2id();

    $db = dbFromEnv();
    $presbiterios = $db->fetchAll(
        "SELECT id, codigo FROM organizacion
         WHERE tipo = 'PRESBITERIO' AND activo = 1 AND codigo IS NOT NULL
         ORDER BY codigo",
    );

    if ($presbiterios === []) {
        throw new BootstrapError('No hay presbiterios activos. Ejecute 002_catalogos_calidad.sql antes del aprovisionamiento.', 3);
    }

    $plan = Plan::build($presbiterios);
    $provisioner = new Provisioner($db->pdo());
    $summary = $provisioner->run($plan, $passwords);

    printPlan($plan);
    fwrite(STDOUT, sprintf(
        PHP_EOL . 'Aprovisionamiento UAT: creados=%d, existentes=%d, conflictos=%d, total=%d.' . PHP_EOL
        . 'Las contraseñas provienen del entorno y no se registran.' . PHP_EOL,
        $summary['created'],
        $summary['existing'],
        count($summary['conflicts']),
        $summary['total'],
    ));

    return printConflicts($summary['conflicts']);
}

function runAdminProd(bool $dryRun, bool $rotate): int
{
    $environment = strtolower((string) Env::get('PCD_ENV', ''));

    if ($environment !== 'production') {
        throw new BootstrapError(
            'El modo admin-prod exige PCD_ENV=production. Para pruebas use --mode=uat.',
            2,
        );
    }

    $adminPassword = requireEnv('PCD_ADMIN_PASSWORD');

    if ($dryRun) {
        printPlan(Plan::adminOnly());
        fwrite(STDOUT, PHP_EOL . 'DRY-RUN: solo se aprovisionaría el ADMIN nacional.' . PHP_EOL);
        return 0;
    }

    assertArgon2id();

    $db = dbFromEnv();
    $provisioner = new Provisioner($db->pdo());

    if ($rotate) {
        $provisioner->rotateAdminPassword($adminPassword);
        fwrite(STDOUT, 'ADMIN-NAC rotado; auth_version incrementado y sesiones previas revocadas.' . PHP_EOL);
        return 0;
    }

    $summary = $provisioner->run(Plan::adminOnly(), ['ADMIN' => $adminPassword]);
    printPlan(Plan::adminOnly());
    fwrite(STDOUT, sprintf(
        PHP_EOL . 'ADMIN productivo: creados=%d, existentes=%d, conflictos=%d.' . PHP_EOL
        . 'La contraseña proviene del entorno y no se registra.' . PHP_EOL,
        $summary['created'],
        $summary['existing'],
        count($summary['conflicts']),
    ));

    return printConflicts($summary['conflicts']);
}

/**
 * Informa divergencias sin corregirlas. Devuelve 5 si existe al menos una.
 *
 * @param list<array{codigo:string, conflicts:list<array{campo:string, esperado:string|null, actual:string|null}>}> $conflicts
 */
function printConflicts(array $conflicts): int
{
    if ($conflicts === []) {
        return 0;
    }

    fwrite(STDERR, PHP_EOL . 'CONFLICTOS detectados (no se modificó ninguna cuenta existente):' . PHP_EOL);

    foreach ($conflicts as $userConflict) {
        foreach ($userConflict['conflicts'] as $conflict) {
            fwrite(STDERR, sprintf(
                '  %s: %s esperado=%s actual=%s' . PHP_EOL,
                $userConflict['codigo'],
                $conflict['campo'],
                $conflict['esperado'] ?? '(nulo)',
                $conflict['actual'] ?? '(nulo)',
            ));
        }
    }

    fwrite(STDERR, 'Revise la cuenta y resuelva el conflicto por un procedimiento autorizado.' . PHP_EOL);

    return 5;
}

/** @return list<array{id:int|null, codigo:string}> */
function fixedPresbiterios(): array
{
    return [
        ['id' => null, 'codigo' => 'LIM'],
        ['id' => null, 'codigo' => 'TRU'],
        ['id' => null, 'codigo' => 'CHI'],
        ['id' => null, 'codigo' => 'IQU'],
    ];
}

/**
 * @param list<array{codigo:string, nombre_display:string, perfil:string, presbiterio_id:int|null, presbiterio_codigo:string|null}> $plan
 */
function printPlan(array $plan): void
{
    fwrite(STDOUT, sprintf("%-18s %-12s %s\n", 'CÓDIGO', 'PERFIL', 'PRESBITERIO'));
    fwrite(STDOUT, str_repeat('-', 48) . PHP_EOL);

    foreach ($plan as $user) {
        if ($user['presbiterio_id'] !== null) {
            $scope = sprintf('%s (id=%d)', (string) $user['presbiterio_codigo'], $user['presbiterio_id']);
        } elseif ($user['presbiterio_codigo'] !== null) {
            $scope = $user['presbiterio_codigo'] . ' (id pendiente)';
        } else {
            $scope = '(nacional)';
        }

        fwrite(STDOUT, sprintf(
            "%-18s %-12s %s\n",
            $user['codigo'],
            $user['perfil'],
            $scope,
        ));
    }
}

function requireEnv(string $key): string
{
    try {
        return Env::require($key);
    } catch (RuntimeException) {
        throw new BootstrapError(sprintf('Falta la variable de entorno obligatoria %s.', $key), 2);
    }
}

function dbFromEnv(): Db
{
    try {
        return Db::fromEnv();
    } catch (RuntimeException $exception) {
        throw new BootstrapError($exception->getMessage(), 2);
    }
}

function assertArgon2id(): void
{
    if (!defined('PASSWORD_ARGON2ID')) {
        throw new BootstrapError(
            'PHP no ofrece Argon2id (PASSWORD_ARGON2ID). No se aprovisiona con otro algoritmo; habilite Argon2id en el hosting.',
            4,
        );
    }
}

function usage(): void
{
    fwrite(STDOUT, <<<'TXT'
Uso: php scripts/bootstrap-provision.php [opciones]

Opciones:
  --mode=uat          Cuentas UAT: ADMIN, REPORTES y 6 DIGITADORES por presbiterio.
  --mode=admin-prod   Solo el primer ADMIN productivo (exige PCD_ENV=production).
  --dry-run           Muestra el plan sin conectar a BD ni escribir.
  --rotate            Con admin-prod: rota la clave del ADMIN y revoca sesiones.
  --help              Esta ayuda.

Variables de entorno:
  PCD_DB_HOST, PCD_DB_PORT, PCD_DB_NAME, PCD_DB_USER, PCD_DB_PASSWORD
  PCD_UAT_DIGITADOR_PASSWORD   (solo UAT; nunca en Git)
  PCD_ADMIN_PASSWORD
  PCD_REPORTES_PASSWORD

Seguridad:
  - No se imprimen contraseñas ni hashes.
  - La clave común de DIGITADOR es exclusivamente para UAT.
  - Antes de producción: credenciales individuales, rotación y revocación.
TXT);
    fwrite(STDOUT, PHP_EOL);
}

final class BootstrapError extends RuntimeException
{
    public function __construct(string $message, public readonly int $exitCode = 2)
    {
        parent::__construct($message);
    }
}
