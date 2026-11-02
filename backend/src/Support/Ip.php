<?php

declare(strict_types=1);

namespace Pcd\Support;

final class Ip
{
    /**
     * No se confía en X-Forwarded-For: el hosting/cPanel debe configurar el
     * proxy si algún día se requiere. Para I01 basta REMOTE_ADDR.
     */
    public static function fromGlobals(): ?string
    {
        $value = $_SERVER['REMOTE_ADDR'] ?? null;

        if (!is_string($value) || filter_var($value, FILTER_VALIDATE_IP) === false) {
            return null;
        }

        return $value;
    }

    public static function userAgent(): ?string
    {
        $value = $_SERVER['HTTP_USER_AGENT'] ?? null;

        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return mb_substr(trim($value), 0, 255, 'UTF-8');
    }
}
