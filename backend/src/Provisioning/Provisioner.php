<?php

declare(strict_types=1);

namespace Pcd\Provisioning;

use Pcd\Security\PasswordHasher;
use PDO;
use RuntimeException;
use Throwable;

/**
 * Aprovisionamiento transaccional e idempotente:
 * - la primera ejecución crea las cuentas del plan;
 * - las existentes NO se actualizan (ni clave, ni perfil, ni presbiterio, ni activo);
 * - cualquier divergencia respecto del plan se informa como conflicto, sin corregirse;
 * - la rotación del ADMIN productivo es un procedimiento explícito separado.
 */
final class Provisioner
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param list<array{codigo:string, nombre_display:string, perfil:string, presbiterio_id:int|null}> $users
     * @param array<string, string> $passwords
     * @return array{created:int, existing:int, conflicts:list<array{codigo:string, conflicts:list<array{campo:string, esperado:string|null, actual:string|null}>}>, total:int}
     */
    public function run(array $users, array $passwords): array
    {
        PasswordHasher::assertAvailable();

        $select = $this->pdo->prepare(
            'SELECT id, nombre_display, perfil, presbiterio_id, activo FROM usuario WHERE codigo = ?',
        );
        $insert = $this->pdo->prepare(
            'INSERT INTO usuario (codigo, nombre_display, perfil, presbiterio_id, password_hash, activo, auth_version)
             VALUES (?, ?, ?, ?, ?, 1, 1)',
        );

        $created = 0;
        $existing = 0;
        $conflicts = [];

        $this->pdo->beginTransaction();

        try {
            foreach ($users as $user) {
                $select->execute([$user['codigo']]);
                $row = $select->fetch();

                if ($row !== false) {
                    $existing++;
                    $userConflicts = self::detectConflicts($user, $row);

                    if ($userConflicts !== []) {
                        $conflicts[] = ['codigo' => $user['codigo'], 'conflicts' => $userConflicts];
                    }

                    continue;
                }

                $password = Plan::passwordFor($user, $passwords);
                $insert->execute([
                    $user['codigo'],
                    $user['nombre_display'],
                    $user['perfil'],
                    $user['presbiterio_id'],
                    PasswordHasher::hash($password),
                ]);
                $created++;
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }

        return [
            'created' => $created,
            'existing' => $existing,
            'conflicts' => $conflicts,
            'total' => count($users),
        ];
    }

    /**
     * Detecta divergencias relevantes sin modificar la fila existente.
     * `activo=0` se informa (no se reactiva) y la clave nunca se compara.
     *
     * @param array{codigo:string, nombre_display:string, perfil:string, presbiterio_id:int|null} $expected
     * @param array<string, mixed> $existing
     * @return list<array{campo:string, esperado:string|null, actual:string|null}>
     */
    public static function detectConflicts(array $expected, array $existing): array
    {
        $conflicts = [];

        foreach (['perfil', 'presbiterio_id'] as $field) {
            $expectedValue = $expected[$field] ?? null;
            $actualValue = $existing[$field] ?? null;

            if ((string) ($expectedValue ?? '') !== (string) ($actualValue ?? '')) {
                $conflicts[] = [
                    'campo' => $field,
                    'esperado' => $expectedValue === null ? null : (string) $expectedValue,
                    'actual' => $actualValue === null ? null : (string) $actualValue,
                ];
            }
        }

        $active = (int) ($existing['activo'] ?? 0);

        if ($active !== 1) {
            $conflicts[] = [
                'campo' => 'activo',
                'esperado' => '1',
                'actual' => (string) $active,
            ];
        }

        return $conflicts;
    }

    /**
     * Rotación del ADMIN productivo: cambia hash y sube auth_version para
     * revocar sesiones previas. Procedimiento explícito, nunca automático.
     */
    public function rotateAdminPassword(string $password): void
    {
        PasswordHasher::assertAvailable();

        $statement = $this->pdo->prepare(
            'UPDATE usuario SET password_hash = ?, auth_version = auth_version + 1 WHERE codigo = ? AND perfil = ?',
        );
        $statement->execute([PasswordHasher::hash($password), 'ADMIN-NAC', 'ADMIN']);

        if ($statement->rowCount() === 0) {
            throw new RuntimeException('No existe ADMIN-NAC; cree el ADMIN productivo antes de rotar.');
        }
    }
}
