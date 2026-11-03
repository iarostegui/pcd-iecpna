<?php

declare(strict_types=1);

namespace Pcd\Repository;

use Pcd\Support\Json;
use PDO;

final class AuditoriaRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Registra una operación de contexto (no alta de ficha). Solo se envían
     * las claves efectivamente modificadas, sin PII.
     *
     * @param array<string, mixed>|null $antes
     * @param array<string, mixed>|null $despues
     */
    public function record(
        string $requestId,
        int $sessionId,
        int $usuarioId,
        string $actorCodigo,
        string $actorPerfil,
        string $accion,
        string $recursoTipo,
        ?int $recursoId,
        ?string $ip,
        ?array $antes,
        ?array $despues,
    ): void {
        $statement = $this->pdo->prepare(
            'INSERT INTO auditoria_evento
                (request_id, sesion_id, usuario_id, actor_codigo, actor_perfil, accion,
                 recurso_tipo, recurso_id, ip, antes_json, despues_json)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, INET6_ATON(?), ?, ?)',
        );
        $statement->execute([
            $requestId,
            $sessionId,
            $usuarioId,
            $actorCodigo,
            $actorPerfil,
            $accion,
            $recursoTipo,
            $recursoId,
            $ip,
            $antes === null ? null : Json::encode($antes),
            $despues === null ? null : Json::encode($despues),
        ]);
    }
}
