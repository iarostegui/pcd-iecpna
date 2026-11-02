<?php

declare(strict_types=1);

namespace Pcd\Repository;

use PDO;

final class LoginEventoRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param array{usuario_id:?int, presbiterio_id:?int, codigo:?string, resultado:string, ip:?string, user_agent:?string} $data
     */
    public function record(array $data): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO login_evento (usuario_id, presbiterio_id, codigo_intentado, resultado, ip, user_agent)
             VALUES (?, ?, ?, ?, INET6_ATON(?), ?)',
        );
        $statement->execute([
            $data['usuario_id'],
            $data['presbiterio_id'],
            $data['codigo'],
            $data['resultado'],
            $data['ip'],
            $data['user_agent'],
        ]);
    }

    /**
     * Cubo de intentos por cuenta y presbiterio.
     *
     * La cuenta se identifica por usuario_id cuando la cuenta existe; si el
     * código no corresponde a una cuenta, se usa codigo_intentado. El
     * presbiterio forma parte de la clave para que intentos fallidos en un
     * contexto no bloqueen el acceso legítimo en otro (aislamiento CA-59/O01).
     */
    public function failedCountByAccount(
        ?int $usuarioId,
        string $codigo,
        int $presbiterioId,
        string $since,
    ): int {
        if ($usuarioId !== null) {
            $statement = $this->pdo->prepare(
                "SELECT COUNT(*) AS total FROM login_evento
                 WHERE usuario_id = ? AND presbiterio_id = ?
                   AND resultado = 'FALLIDO' AND created_at >= ?",
            );
            $statement->execute([$usuarioId, $presbiterioId, $since]);
        } else {
            $statement = $this->pdo->prepare(
                "SELECT COUNT(*) AS total FROM login_evento
                 WHERE usuario_id IS NULL AND codigo_intentado = ? AND presbiterio_id = ?
                   AND resultado = 'FALLIDO' AND created_at >= ?",
            );
            $statement->execute([$codigo, $presbiterioId, $since]);
        }

        return (int) $statement->fetch()['total'];
    }

    public function failedCountByIp(?string $ip, string $since): int
    {
        if ($ip === null) {
            return 0;
        }

        $statement = $this->pdo->prepare(
            "SELECT COUNT(*) AS total FROM login_evento
             WHERE ip = INET6_ATON(?) AND resultado = 'FALLIDO' AND created_at >= ?",
        );
        $statement->execute([$ip, $since]);

        return (int) $statement->fetch()['total'];
    }
}
