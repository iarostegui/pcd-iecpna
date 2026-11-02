<?php

declare(strict_types=1);

namespace Pcd\Repository;

use PDO;

final class IdempotenciaRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, mixed>|null */
    public function find(int $usuarioId, string $clave): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, usuario_id, clave, payload_hash, response_status, response_json
             FROM idempotencia WHERE usuario_id = ? AND clave = ? LIMIT 1',
        );
        $statement->execute([$usuarioId, $clave]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function store(int $usuarioId, string $clave, string $payloadHash, int $status, string $responseJson): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO idempotencia (usuario_id, clave, payload_hash, response_status, response_json)
             VALUES (?, ?, ?, ?, ?)',
        );
        $statement->execute([$usuarioId, $clave, $payloadHash, $status, $responseJson]);
    }
}
