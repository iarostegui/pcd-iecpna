<?php

declare(strict_types=1);

namespace Pcd\Security;

use Pcd\Config\Env;

/**
 * Preparación de CSRF: token por sesión, cookie HttpOnly/SameSite y
 * comparación en tiempo constante. La emisión del token al cliente
 * corresponde al flujo de autenticación (I01).
 */
final class Csrf
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $name = Env::get('PCD_SESSION_NAME', '__Host-pcd_sid') ?? '__Host-pcd_sid';
        $https = strtolower((string) ($_SERVER['HTTPS'] ?? '')) ;
        $secure = ($https !== '' && $https !== 'off') || Env::get('PCD_ENV') === 'production';

        session_name($name);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    public static function token(): string
    {
        self::startSession();

        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === '') {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

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
