<?php

declare(strict_types=1);

namespace Pcd\Security;

/**
 * Token CSRF por sesión de servidor. Se recupera mediante GET /auth/me y se
 * envía en X-CSRF-Token para escrituras autenticadas. Nunca se persiste en
 * localStorage ni se registra en logs.
 */
final class Csrf
{
    public static function startSession(): void
    {
        PhpSession::start();
    }

    public static function token(): string
    {
        self::startSession();

        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === '') {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function rotateToken(): string
    {
        self::startSession();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        return $_SESSION['csrf_token'];
    }

    public static function validate(?string $provided): bool
    {
        self::startSession();
        $expected = $_SESSION['csrf_token'] ?? null;

        return self::matches(is_string($expected) ? $expected : null, $provided);
    }

    public static function matches(?string $expected, ?string $provided): bool
    {
        if ($expected === null || $provided === null || $expected === '' || $provided === '') {
            return false;
        }

        return hash_equals($expected, $provided);
    }

    public static function reset(): void
    {
        self::startSession();
        unset($_SESSION['csrf_token']);
    }
}
