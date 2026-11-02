<?php

declare(strict_types=1);

namespace Pcd\Security;

use Pcd\Config\Env;

/**
 * Sesión PHP de servidor. La cookie solo transporta el identificador; el
 * estado autoritativo de autenticación vive en la tabla sesion y el token
 * CSRF en el almacenamiento de sesiones del servidor.
 */
final class PhpSession
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $name = (string) (Env::get('PCD_SESSION_NAME', '__Host-pcd_sid') ?? '__Host-pcd_sid');
        $secure = self::secure();

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

    public static function id(): string
    {
        self::start();

        return (string) session_id();
    }

    public static function regenerate(): void
    {
        self::start();

        if (!headers_sent()) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?: '/',
                'domain' => $params['domain'] ?? '',
                'secure' => (bool) ($params['secure'] ?? false),
                'httponly' => (bool) ($params['httponly'] ?? true),
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();
    }

    public static function secure(): bool
    {
        $environment = strtolower((string) Env::get('PCD_ENV', 'local'));

        if (Env::bool('PCD_COOKIE_SECURE', false)) {
            return true;
        }

        return $environment === 'production' || self::isHttps();
    }

    public static function isHttps(): bool
    {
        $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));

        if ($https !== '' && $https !== 'off') {
            return true;
        }

        return (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
    }
}
