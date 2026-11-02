<?php

declare(strict_types=1);

namespace Pcd\Controller;

use Pcd\Http\Request;
use Pcd\Http\Response;

/**
 * Rutas del contrato OpenAPI que pertenecen a iteraciones funcionales.
 * P01 no implementa login, sesión ni catálogos: responde 501 uniforme
 * para que el frontend y el runner distingan NOT_IMPLEMENTED.
 */
final class PlaceholderController
{
    /** @param array<string, string> $params */
    public static function notImplemented(Request $request, array $params = []): void
    {
        Response::error(
            $request,
            'not_implemented',
            'Funcionalidad no implementada en P01; corresponde a una iteración funcional (I01–I05).',
            501,
        );
    }
}
