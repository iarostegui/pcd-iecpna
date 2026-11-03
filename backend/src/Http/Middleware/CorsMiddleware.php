<?php

declare(strict_types=1);

namespace Pcd\Http\Middleware;

use Pcd\Config\Env;
use Pcd\Http\Request;
use Pcd\Http\Response;

/**
 * CORS restringido por entorno. El frontend de desarrollo usa
 * http://localhost:5173; producción declara su origen exacto.
 */
final class CorsMiddleware
{
    public static function handle(Request $request): bool
    {
        $origin = $request->header('origin');
        $allowed = self::allowedOrigins();

        if ($origin !== null && in_array($origin, $allowed, true)) {
            $headers = [
                'Access-Control-Allow-Origin' => $origin,
                'Access-Control-Allow-Credentials' => 'true',
                'Vary' => 'Origin',
            ];
            Response::addDefaultHeaders($headers);

            if ($request->method === 'OPTIONS') {
                Response::empty($request, 204, array_merge($headers, [
                    'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
                    'Access-Control-Allow-Headers' => 'Content-Type, X-CSRF-Token, X-Request-Id, Idempotency-Key',
                    'Access-Control-Max-Age' => '600',
                ]));

                return true;
            }

            return false;
        }

        if ($request->method === 'OPTIONS') {
            Response::error($request, 'cors_origin_denied', 'Origen no permitido.', 403);

            return true;
        }

        return false;
    }

    /** @return list<string> */
    public static function allowedOrigins(): array
    {
        $raw = Env::get('PCD_ALLOWED_ORIGINS', 'http://localhost:5173') ?? '';

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}
