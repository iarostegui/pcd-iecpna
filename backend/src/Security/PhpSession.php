<?php

declare(strict_types=1);

namespace Pcd\Security;

use Pcd\Config\Env;
use RuntimeException;

/**
 * Sesión PHP de servidor. La cookie solo transporta el identificador; el
 * estado autoritativo de autenticación vive en la tabla sesion y el token
 * CSRF en el almacenamiento de sesiones del servidor.
 *
 * La detección HTTPS no confía en X-Forwarded-Proto/Forwarded: un cliente
 * podría falsificarlos. cPanel debe declarar PCD_ENV=production o
 * PCD_COOKIE_SECURE=true cuando TLS termina en un proxy confiable.
 */
final class PhpSession
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $name = self::cookieName();
        $options = self::cookieOptions();

        if (str_starts_with($name, '__Host-') && $options['secure'] !== true) {
            throw new RuntimeException(
                'La cookie __Host- requiere Secure. Configure PCD_ENV=production o PCD_COOKIE_SECURE=true sobre HTTPS.',
            );
        }

        session_name($name);
        session_set_cookie_params($options);
        session_start();
    }

    public static function cookieName(): string
    {
        return (string) (Env::get('PCD_SESSION_NAME', '__Host-pcd_sid') ?? '__Host-pcd_sid');
    }

    /**
     * @return array{lifetime:int, path:string, domain:string, secure:bool, httponly:bool, samesite:string}
     */
    public static function cookieOptions(): array
    {
        return [
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => self::secure(),
            'httponly' => true,
            'samesite' => 'Lax',
        ];
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

    /**
     * Solo señales del servidor web, nunca cabeceras de proxy del cliente.
     */
    public static function isHttps(): bool
    {
        $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));

        if ($https !== '' && $https !== 'off') {
            return true;
        }

        return (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
    }
}
