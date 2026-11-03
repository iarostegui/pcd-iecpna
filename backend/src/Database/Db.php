<?php

declare(strict_types=1);

namespace Pcd\Database;

use Pcd\Config\Env;
use PDO;
use PDOException;
use Throwable;

/**
 * PDO con utf8mb4, prepares reales y transacciones explícitas.
 * No hay ORM ni constructor de SQL dinámico.
 */
final class Db
{
    private ?PDO $pdo = null;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $name,
        private readonly string $user,
        private readonly string $password,
        private readonly string $charset = 'utf8mb4',
    ) {
    }

    public static function fromEnv(): self
    {
        return new self(
            Env::require('PCD_DB_HOST'),
            Env::int('PCD_DB_PORT', 3306),
            Env::require('PCD_DB_NAME'),
            Env::require('PCD_DB_USER'),
            Env::get('PCD_DB_PASSWORD', '') ?? '',
            Env::get('PCD_DB_CHARSET', 'utf8mb4') ?? 'utf8mb4',
        );
    }

    public static function buildDsn(string $host, int $port, string $name, string $charset = 'utf8mb4'): string
    {
        return sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $name, $charset);
    }

    public function dsn(): string
    {
        return self::buildDsn($this->host, $this->port, $this->name, $this->charset);
    }

    public function pdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        $this->pdo = new PDO($this->dsn(), $this->user, $this->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_TIMEOUT => 3,
        ]);

        // Contrato V4.1/CA-122: timestamps del servidor coherentes en UTC.
        // El offset '+00:00' no requiere tablas de zonas horarias en cPanel.
        $this->pdo->exec("SET time_zone = '+00:00'");

        return $this->pdo;
    }

    public function ping(): bool
    {
        try {
            $this->pdo()->query('SELECT 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /** @param array<int|string, mixed> $params
     *  @return list<array<string, mixed>> */
    public function fetchAll(string $sql, array $params = []): array
    {
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    /** @param array<int|string, mixed> $params
     *  @return array<string, mixed>|null */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** @param array<int|string, mixed> $params */
    public function execute(string $sql, array $params = []): int
    {
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($params);

        return $statement->rowCount();
    }

    /**
     * @template T
     * @param callable(PDO):T $work
     * @return T
     */
    public function transaction(callable $work): mixed
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $result = $work($pdo);
            $pdo->commit();

            return $result;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function close(): void
    {
        $this->pdo = null;
    }

    public function isConnected(): bool
    {
        return $this->pdo instanceof PDO;
    }

    public function assertConnected(): void
    {
        if (!$this->isConnected()) {
            throw new PDOException('Sin conexión PDO.');
        }
    }
}
