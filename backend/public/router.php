<?php

declare(strict_types=1);

/**
 * Router para el servidor embebido de desarrollo:
 *   php -S 127.0.0.1:8080 -t backend/public backend/public/router.php
 * En producción cPanel se usa .htaccess y el DocumentRoot apunta a public/.
 */
$path = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$file = __DIR__ . $path;

if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
