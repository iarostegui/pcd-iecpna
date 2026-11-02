<?php

declare(strict_types=1);

namespace Pcd;

use Pcd\Config\Env;
use Pcd\Controller\AuthController;
use Pcd\Controller\ContextoController;
use Pcd\Controller\HealthController;
use Pcd\Controller\OrganizacionController;
use Pcd\Controller\PlaceholderController;
use Pcd\Http\ErrorHandler;
use Pcd\Http\HttpException;
use Pcd\Http\Middleware\CorsMiddleware;
use Pcd\Http\Request;
use Pcd\Http\Response;
use Pcd\Http\Router;
use Throwable;

/**
 * Composición: request -> CORS -> validación JSON -> router -> handler.
 * Autenticación y CSRF se aplican dentro de cada handler de escritura para
 * garantizar 401 antes de 403 y autorización por perfil después.
 */
final class App
{
    public static function run(): void
    {
        $request = Request::fromGlobals();
        ErrorHandler::register($request);

        try {
            $request = self::stripBasePath($request);

            if (CorsMiddleware::handle($request)) {
                return;
            }

            self::validateJsonBody($request);

            $match = self::router()->resolve($request);
            $status = $match['status'] ?? 404;

            if ($status === 404) {
                throw new HttpException(404, 'not_found', 'Recurso no encontrado.');
            }

            if ($status === 405) {
                Response::error(
                    $request,
                    'method_not_allowed',
                    'Método no permitido.',
                    405,
                    null,
                    ['Allow' => implode(', ', $match['allow'] ?? [])],
                );

                return;
            }

            $handler = $match['handler'];
            $handler($request, $match['params'] ?? []);
        } catch (Throwable $exception) {
            ErrorHandler::handle($exception, $request);
        }
    }

    private static function stripBasePath(Request $request): Request
    {
        $base = rtrim((string) Env::get('PCD_API_BASE_PATH', '/api/v1'), '/');

        if ($base === '') {
            return $request;
        }

        if ($request->path === $base) {
            return $request->withPath('/');
        }

        if (!str_starts_with($request->path, $base . '/')) {
            throw new HttpException(404, 'not_found', 'Recurso no encontrado.');
        }

        return $request->withPath(substr($request->path, strlen($base)));
    }

    private static function validateJsonBody(Request $request): void
    {
        if (!in_array($request->method, ['POST', 'PUT', 'PATCH'], true)) {
            return;
        }

        if (trim($request->body) === '') {
            return;
        }

        $contentType = strtolower($request->header('content-type', '') ?? '');

        if (str_contains($contentType, 'application/json')) {
            $request->json();
        }
    }

    private static function router(): Router
    {
        $router = new Router();
        $router->add('GET', '/health', [HealthController::class, 'liveness']);
        $router->add('GET', '/ready', [HealthController::class, 'readiness']);

        $router->add('POST', '/auth/login', [AuthController::class, 'login']);
        $router->add('GET', '/auth/me', [AuthController::class, 'me']);
        $router->add('POST', '/auth/logout', [AuthController::class, 'logout']);
        $router->add('POST', '/auth/ubicacion', [AuthController::class, 'location']);

        $router->add('GET', '/presbiterios', [OrganizacionController::class, 'presbiterios']);
        $router->add('GET', '/sedes', [OrganizacionController::class, 'sedes']);
        $router->add('POST', '/sedes', [OrganizacionController::class, 'createSede']);

        $router->add('PUT', '/contexto/presbiterio', [ContextoController::class, 'presbiterio']);
        $router->add('PUT', '/contexto/sede', [ContextoController::class, 'sede']);

        // I02–I06 permanecen fuera de alcance y responden 501.
        $router->add('GET', '/catalogos', [PlaceholderController::class, 'notImplemented']);
        $router->add('GET', '/ubigeos', [PlaceholderController::class, 'notImplemented']);

        return $router;
    }
}
