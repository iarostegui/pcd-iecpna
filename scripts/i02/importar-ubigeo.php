#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * I02 — importador seguro de CSV de procedencia documentada (INEI o fuente usuario).
 * Preparación técnica; no incorpora datos ficticios al catálogo nacional.
 * Invocación: php scripts/i02/importar-ubigeo.php
 *  --file=/ruta/datos.csv --sha256=<64 hex> --fuente-tipo=usuario --referencia=H-UBIGEO-2026-11-04 [--apply]\n *  --file=/ruta/inei.csv --sha256=<64 hex> --fuente-tipo=inei --fuente-url=https://... --corte=YYYY-MM-DD [--apply]
 *
 * CSV UTF-8 con encabezado exacto: codigo,tipo,padre_codigo,nombre
 * Código de 2/4/6 dígitos, jerarquía referencial y nombre preservado.
 * Sin --apply solo valida archivo; jamás descarga directamente ni supone oficialidad.
 */
$root = dirname(__DIR__, 2);
$autoload = $root . '/backend/vendor/autoload.php';
if (!is_file($autoload)) { fwrite(STDERR, "Instale dependencias Composer primero.\n"); exit(2); }
require $autoload;

use Pcd\Database\Db;

$options = getopt('', ['file:', 'sha256:', 'fuente-tipo:', 'referencia:', 'fuente-url:', 'corte:', 'apply', 'help']);
if (isset($options['help'])) {
    echo "Uso: php scripts/i02/importar-ubigeo.php --file=... --sha256=... --fuente-tipo=usuario --referencia=H-UBIGEO-2026-11-04 [--apply]\n";
    echo "O bien: --fuente-tipo=inei --fuente-url=https://... --corte=YYYY-MM-DD [--apply]\n";
    exit(0);
}
try {
    $file = (string) ($options['file'] ?? '');
    $sha = strtolower((string) ($options['sha256'] ?? ''));
    $fuenteTipo = (string) ($options['fuente-tipo'] ?? 'inei');
    $referencia = (string) ($options['referencia'] ?? '');
    $url = (string) ($options['fuente-url'] ?? '');
    $corte = (string) ($options['corte'] ?? '');
    $apply = array_key_exists('apply', $options);
    if (!is_file($file) || !is_readable($file)) throw new RuntimeException('No existe CSV legible.');
    if (!preg_match('/^[a-f0-9]{64}$/D', $sha) || !hash_equals($sha, hash_file('sha256', $file))) {
        throw new RuntimeException('SHA256 faltante o distinto del archivo. Sin importación.');
    }
    if (!in_array($fuenteTipo, ['inei', 'usuario'], true)) {
        throw new RuntimeException('fuente-tipo debe ser inei o usuario.');
    }
    if ($fuenteTipo === 'inei') {
        if (!filter_var($url, FILTER_VALIDATE_URL) || !str_starts_with($url, 'https://')) {
            throw new RuntimeException('Registre URL HTTPS real de la fuente INEI.');
        }
        if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $corte) !== 1
            || !DateTimeImmutable::createFromFormat('!Y-m-d', $corte)
            || DateTimeImmutable::createFromFormat('!Y-m-d', $corte)->format('Y-m-d') !== $corte) {
            throw new RuntimeException('Indique corte YYYY-MM-DD verificable.');
        }
    } else {
        // Un archivo recibido y aprobado para digitación no es por ello fuente oficial INEI.
        // Referencia fija a la ficha de procedencia: no aceptar metadatos sin respaldo.
        if ($referencia !== 'H-UBIGEO-2026-11-04') {
            throw new RuntimeException('Referencia de usuario no registrada en documentación.');
        }
        if ($sha !== '8a3abc99898d2927d926536e4f31120b324e08e571111414846c6d2508cb650b') {
            throw new RuntimeException('El archivo no coincide con CSV normalizado recibido H-UBIGEO.');
        }
        if ($url !== '' || $corte !== '') {
            throw new RuntimeException('No atribuir URL o corte INEI a un CSV recibido del usuario.');
        }
    }
    if ($apply && strtolower((string) getenv('PCD_ENV')) === 'production') {
        throw new RuntimeException('Importación productiva bloqueada sin GO institucional H-40.');
    }

    $input = fopen($file, 'rb');
    if ($input === false) throw new RuntimeException('No pudo abrirse CSV.');
    try {
        $columns = fgetcsv($input, escape: '');
        if ($columns !== ['codigo', 'tipo', 'padre_codigo', 'nombre']) {
            throw new RuntimeException('Encabezado CSV inválido: codigo,tipo,padre_codigo,nombre.');
        }
        $rows = [];
        $levelCount = ['DEPARTAMENTO' => 0, 'PROVINCIA' => 0, 'DISTRITO' => 0];
        while (($row = fgetcsv($input, escape: '')) !== false) {
            if ($row === [null]) continue;
            if (count($row) !== 4) throw new RuntimeException('Fila con cantidad incorrecta de columnas.');
            [$codigo, $tipo, $padre, $nombre] = $row;
            $codigo = trim($codigo); $tipo = strtoupper(trim($tipo));
            $padre = trim($padre); $nombre = trim($nombre);
            $length = match ($tipo) {
                'DEPARTAMENTO' => 2, 'PROVINCIA' => 4, 'DISTRITO' => 6,
                default => throw new RuntimeException('Tipo de ubigeo inválido.'),
            };
            if (preg_match('/^[0-9]{' . $length . '}$/D', $codigo) !== 1) {
                throw new RuntimeException('Código inválido: formato de longitud de nivel.');
            }
            $esperado = $length === 2 ? '' : substr($codigo, 0, $length - 2);
            if ($padre !== $esperado || $nombre === '' || mb_strlen($nombre, 'UTF-8') > 160) {
                throw new RuntimeException('Padre/nombre inválido para código: ' . $codigo);
            }
            if (isset($rows[$codigo])) throw new RuntimeException('Código ubigeo duplicado: ' . $codigo);
            $rows[$codigo] = ['codigo' => $codigo, 'tipo' => $tipo,
                'padre_codigo' => $padre === '' ? null : $padre, 'nombre' => $nombre];
            $levelCount[$tipo]++;
        }
    } finally {
        fclose($input);
    }
    foreach ($levelCount as $tipo => $count) {
        if ($count === 0) throw new RuntimeException('El CSV no contiene el nivel: ' . $tipo);
    }
    foreach ($rows as $row) {
        if ($row['padre_codigo'] === null) continue;
        $parent = $rows[$row['padre_codigo']] ?? null;
        $expected = $row['tipo'] === 'PROVINCIA' ? 'DEPARTAMENTO' : 'PROVINCIA';
        if ($parent === null || $parent['tipo'] !== $expected) {
            throw new RuntimeException('Padre faltante/inválido para código ' . $row['codigo']);
        }
    }
    echo 'VALIDACION_OK: registros=' . count($rows) . ' niveles=' . json_encode($levelCount)
        . ' fuente=' . $fuenteTipo . ' corte=' . ($corte !== '' ? $corte : 'NO_ACREDITADO') . ' sha256=' . $sha . '\n';
    if (!$apply) {
        echo "DRY_RUN — sin escritura. La fuente usuario no acredita corte INEI oficial; no se ha efectuado escritura.\n";
        exit(0);
    }

    // La carga real NO constituye revisión humana de autenticidad de la fuente.
    uksort($rows, static fn (string $a, string $b): int => strlen($a) <=> strlen($b) ?: strcmp($a, $b));
    $pdo = Db::fromEnv()->pdo();
    $pdo->beginTransaction();
    $created = 0; $equal = 0;
    try {
        $lookup = $pdo->prepare('SELECT tipo, padre_codigo, nombre FROM ubigeo WHERE codigo = ? FOR UPDATE');
        $insert = $pdo->prepare('INSERT INTO ubigeo (codigo,tipo,padre_codigo,nombre) VALUES (?,?,?,?)');
        foreach ($rows as $row) {
            $lookup->execute([$row['codigo']]);
            $existing = $lookup->fetch(PDO::FETCH_ASSOC);
            if ($existing !== false) {
                if ($existing['tipo'] !== $row['tipo'] || $existing['padre_codigo'] !== $row['padre_codigo']
                    || $existing['nombre'] !== $row['nombre']) {
                    throw new RuntimeException('Conflicto con ubigeo existente ' . $row['codigo'] . '; no sobrescribir automáticamente.');
                }
                $equal++;
                continue;
            }
            $insert->execute([$row['codigo'], $row['tipo'], $row['padre_codigo'], $row['nombre']]);
            $created++;
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
    echo "IMPORTACION_OK creados=$created existentes_iguales=$equal\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
