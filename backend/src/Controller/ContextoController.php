<?php

declare(strict_types=1);

namespace Pcd\Controller;

use Pcd\Http\Middleware\AuthMiddleware;
use Pcd\Http\Request;
use Pcd\Http\Response;
use Pcd\Security\Csrf;
use Pcd\Support\Ip;
use Pcd\Support\Validation;

final class ContextoController
{
    public static function presbiterio(Request $request): void
    {
        $context = AuthMiddleware::requireSessionAndCsrf($request);
        $payload = $request->json();
        $presbiterioId = Validation::requiredInt($payload, 'presbiterio_id');

        $updated = AuthMiddleware::service()->changePresbiterio(
            $context,
            $presbiterioId,
            $request->requestId,
            Ip::fromGlobals(),
        );

        Response::json($request, 200, $updated->toAuthMe(Csrf::token()));
    }

    public static function sede(Request $request): void
    {
        $context = AuthMiddleware::requireSessionAndCsrf($request);

        if ($context->perfil === 'REPORTES') {
            Response::error($request, 'forbidden', 'El perfil REPORTES es de solo lectura.', 403);

            return;
        }

        $payload = $request->json();
        $sedeId = Validation::requiredInt($payload, 'sede_id');
        $updated = AuthMiddleware::service()->changeSede($context, $sedeId);

        Response::json($request, 200, $updated->toAuthMe(Csrf::token()));
    }
}
