<?php

declare(strict_types=1);

namespace Pcd\Provisioning;

use PDO;
use RuntimeException;
use Throwable;

/**
 * Aprovisionamiento transaccional e idempotente. Nunca imprime ni registra
 * contraseñas; solo devuelve conteos.
 */
final class Provisioner
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param list<array{codigo:string, nombre_display:string, perfil:string, presbiterio_id:int|null}> $users
     * @param array<string, string> $passwords
     * @return array{created:int, existing:int, total:int}
     */
    public function run(array $users, array $passwords): array
    {
        if (!defined('PASSWORD_ARGON2ID')) {
            throw new RuntimeException(
                'PHP no ofrece Argon2id (PASSWORD_ARGON2ID). No se aprovisiona con un algoritmo distinto; instale soporte Argon2id.',
            );
        }

        $select = $this->pdo->prepare('SELECT id FROM usuario WHERE codigo = ?');
        $insert = $this->pdo->prepare(
            'INSERT INTO usuario (codigo, nombre_display, perfil, presbiterio_id, password_hash, activo, auth_version)
             VALUES (?, ?, ?, ?, ?, 1, 1)',
        );
        $update = $this->pdo->prepare(
            'UPDATE usuario SET nombre_display = ?, perfil = ?, presbiterio_id = ?, activo = 1 WHERE id = ?',
        );

        $created = 0;
        $existing = 0;

        $this->pdo->beginTransaction();

        try {
            foreach ($users as $user) {
                $select->execute([$user['codigo']]);
                $row = $select->fetch();

                if ($row !== false) {
                    $update->execute([
                        $user['nombre_display'],
                        $user['perfil'],
                        $user['presbiterio_id'],
                        (int) $row['id'],
                    ]);
                    $existing++;
                    continue;
                }

                $password = Plan::passwordFor($user, $passwords);
                $insert->execute([
                    $user['codigo'],
                    $user['nombre_display'],
                    $user['perfil'],
                    $user['presbiterio_id'],
                    password_hash($password, PASSWORD_ARGON2ID),
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

        return ['created' => $created, 'existing' => $existing, 'total' => count($users)];
    }

    /**
     * Rotación del ADMIN productivo: cambia hash y sube auth_version para
     * revocar sesiones previas. Nunca registra la contraseña.
     */
    public function rotateAdminPassword(string $password): void
    {
        if (!defined('PASSWORD_ARGON2ID')) {
            throw new RuntimeException('PHP no ofrece Argon2id (PASSWORD_ARGON2ID).');
        }

        $statement = $this->pdo->prepare(
            'UPDATE usuario SET password_hash = ?, auth_version = auth_version + 1 WHERE codigo = ? AND perfil = ?',
        );
        $statement->execute([password_hash($password, PASSWORD_ARGON2ID), 'ADMIN-NAC', 'ADMIN']);

        if ($statement->rowCount() === 0) {
            throw new RuntimeException('No existe ADMIN-NAC; cree el ADMIN productivo antes de rotar.');
        }
    }
}
