<?php

declare(strict_types=1);

namespace Pcd\Repository;

use PDO;

final class UsuarioRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, mixed>|null */
    public function findByCodigo(string $codigo): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, codigo, nombre_display, perfil, presbiterio_id, password_hash, activo, auth_version
             FROM usuario WHERE codigo = ? LIMIT 1',
        );
        $statement->execute([$codigo]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, codigo, nombre_display, perfil, presbiterio_id, password_hash, activo, auth_version
             FROM usuario WHERE id = ? LIMIT 1',
        );
        $statement->execute([$id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }
}
