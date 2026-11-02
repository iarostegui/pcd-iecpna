<?php

declare(strict_types=1);

namespace Pcd\Security;

use Pcd\Config\Env;
use RuntimeException;

/**
 * Argon2id es el algoritmo exigido en producción. Para desarrollo/CI sin
 * soporte Argon2id se permite bcrypt costo 12 solo con
 * PCD_ALLOW_BCRYPT_FALLBACK=true y PCD_ENV != production (CA-114).
 */
final class PasswordHasher
{
    private static ?string $dummy = null;

    public static function algorithm(): string
    {
        return defined('PASSWORD_ARGON2ID') ? 'argon2id' : 'bcrypt';
    }

    public static function assertAvailable(): void
    {
        if (defined('PASSWORD_ARGON2ID')) {
            return;
        }

        if (self::fallbackAllowed()) {
            return;
        }

        throw new RuntimeException(
            'PHP no ofrece Argon2id (PASSWORD_ARGON2ID). Habilite Argon2id o, solo en desarrollo/CI, '
            . 'PCD_ALLOW_BCRYPT_FALLBACK=true. No se aprovisiona silenciosamente con un algoritmo débil.',
        );
    }

    public static function hash(string $password): string
    {
        self::assertAvailable();

        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID);
        }

        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Hash ficticio con el mismo algoritmo para reducir diferencias de tiempo
     * cuando el código de usuario no existe. No se usa para autenticar.
     */
    public static function dummyHash(): string
    {
        if (self::$dummy === null) {
            self::$dummy = self::hash('pcd-dummy-password-not-used-' . bin2hex(random_bytes(8)));
        }

        return self::$dummy;
    }

    public static function isStrongHash(string $hash): bool
    {
        if (str_starts_with($hash, '$argon2id$') || str_starts_with($hash, '$argon2i$')) {
            return true;
        }

        if (str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2a$')) {
            return preg_match('/^\$2[ay]\$(1[2-9]|[2-9][0-9])\$/', $hash) === 1;
        }

        return false;
    }

    public static function fallbackAllowed(): bool
    {
        return Env::bool('PCD_ALLOW_BCRYPT_FALLBACK', false)
            && strtolower((string) Env::get('PCD_ENV', 'local')) !== 'production';
    }
}
