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

    public function failedCountByCode(string $codigo, string $since): int
    {
        $statement = $this->pdo->prepare(
            "SELECT COUNT(*) AS total FROM login_evento
             WHERE codigo_intentado = ? AND resultado = 'FALLIDO' AND created_at >= ?",
        );
        $statement->execute([$codigo, $since]);

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
