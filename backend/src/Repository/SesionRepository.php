<?php

declare(strict_types=1);

namespace Pcd\Repository;

use PDO;

final class SesionRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param array{usuario_id:int, presbiterio_id:int, sede_id:?int, hash:string, ip:?string, user_agent:?string, fecha_login:string, last_seen:string, auth_version:int} $data
     */
    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO sesion
                (usuario_id, presbiterio_id, sede_id, session_ref_hash, ip_login, user_agent,
                 fecha_login, last_seen, auth_version_al_ingreso)
             VALUES (?, ?, ?, ?, INET6_ATON(?), ?, ?, ?, ?)',
        );

        $statement->execute([
            $data['usuario_id'],
            $data['presbiterio_id'],
            $data['sede_id'],
            $data['hash'],
            $data['ip'],
            $data['user_agent'],
            $data['fecha_login'],
            $data['last_seen'],
            $data['auth_version'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    public function findByHash(string $hash): ?array
    {
        return $this->findOneBy('s.session_ref_hash = ?', [$hash]);
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        return $this->findOneBy('s.id = ?', [$id]);
    }

    /**
     * @param array<int, mixed> $params
     * @return array<string, mixed>|null
     */
    private function findOneBy(string $condition, array $params): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT s.id AS sesion_id, s.usuario_id, s.presbiterio_id, s.sede_id,
                    s.fecha_login, s.fecha_logout, s.last_seen, s.revocada_en,
                    s.auth_version_al_ingreso, s.geo_estado, s.geo_capturada_en,
                    u.codigo AS usuario_codigo, u.nombre_display AS usuario_nombre,
                    u.perfil, u.presbiterio_id AS usuario_presbiterio_id,
                    u.activo AS usuario_activo, u.auth_version AS usuario_auth_version,
                    p.nombre AS presbiterio_nombre,
                    sd.nombre AS sede_nombre
             FROM sesion s
             JOIN usuario u ON u.id = s.usuario_id
             JOIN organizacion p ON p.id = s.presbiterio_id
             LEFT JOIN organizacion sd ON sd.id = s.sede_id
             WHERE " . $condition . '
             LIMIT 1',
        );
        $statement->execute($params);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function touch(int $sessionId, string $now, string $threshold): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE sesion SET last_seen = ?
             WHERE id = ? AND (last_seen IS NULL OR last_seen < ?)',
        );
        $statement->execute([$now, $sessionId, $threshold]);
    }

    public function updatePresbiterio(int $sessionId, int $presbiterioId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE sesion SET presbiterio_id = ?, sede_id = NULL WHERE id = ?',
        );
        $statement->execute([$presbiterioId, $sessionId]);
    }

    public function updateSede(int $sessionId, int $sedeId): void
    {
        $statement = $this->pdo->prepare('UPDATE sesion SET sede_id = ? WHERE id = ?');
        $statement->execute([$sedeId, $sessionId]);
    }

    public function markLogout(int $sessionId, string $now): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE sesion SET fecha_logout = ?, revocada_en = ? WHERE id = ?',
        );
        $statement->execute([$now, $now, $sessionId]);
    }

    public function markRevoked(int $sessionId, string $now): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE sesion SET revocada_en = ? WHERE id = ? AND revocada_en IS NULL',
        );
        $statement->execute([$now, $sessionId]);
    }

    public function findIdByHash(string $hash): ?int
    {
        $statement = $this->pdo->prepare('SELECT id FROM sesion WHERE session_ref_hash = ? LIMIT 1');
        $statement->execute([$hash]);
        $row = $statement->fetch();
        $id = $row === false ? null : (int) $row['id'];

        return $id;
    }

    public function markRevokedByHash(string $hash, string $now): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE sesion SET revocada_en = ? WHERE session_ref_hash = ? AND revocada_en IS NULL',
        );
        $statement->execute([$now, $hash]);
    }

    /** @return array<string, mixed>|null */
    public function location(int $sessionId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT geo_estado, geo_latitud, geo_longitud, geo_precision_m, geo_capturada_en
             FROM sesion WHERE id = ? LIMIT 1',
        );
        $statement->execute([$sessionId]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Captura condicional y atómica: solo la primera solicitud que encuentre
     * geo_estado NULL modifica la fila. Devuelve true si esta solicitud ganó.
     */
    public function storeLocationIfEmpty(
        int $sessionId,
        string $estado,
        ?float $latitud,
        ?float $longitud,
        ?float $precision,
        string $receivedAt,
    ): bool {
        $statement = $this->pdo->prepare(
            'UPDATE sesion
             SET geo_estado = ?, geo_latitud = ?, geo_longitud = ?, geo_precision_m = ?, geo_capturada_en = ?
             WHERE id = ? AND geo_estado IS NULL',
        );
        $statement->execute([$estado, $latitud, $longitud, $precision, $receivedAt, $sessionId]);

        return $statement->rowCount() === 1;
    }
}
