<?php

declare(strict_types=1);

namespace Pcd\Http\Middleware;

use Pcd\Config\Env;
use Pcd\Http\HttpException;
use Pcd\Http\Request;
use Pcd\Security\Csrf;

/**
 * CSRF solo en rutas marcadas (escrituras autenticadas). El login pre-sesión
 * queda fuera hasta que I01 implemente la emisión del token.
 */
final class CsrfMiddleware
{
    public static function enforce(Request $request): void
    {
        if (!Env::bool('PCD_CSRF_ENABLED', true)) {
            return;
        }

        if (!Csrf::validate($request->header('x-csrf-token'))) {
            throw new HttpException(403, 'csrf_invalid', 'Token CSRF ausente o inválido.');
        }
    }
}
