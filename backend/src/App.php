<?php

declare(strict_types=1);

namespace Pcd;

use Pcd\Config\Env;
use Pcd\Controller\HealthController;
use Pcd\Controller\PlaceholderController;
use Pcd\Http\ErrorHandler;
use Pcd\Http\HttpException;
use Pcd\Http\Middleware\CorsMiddleware;
use Pcd\Http\Middleware\CsrfMiddleware;
use Pcd\Http\Request;
use Pcd\Http\Response;
use Pcd\Http\Router;
use Throwable;

/**
 * Composición del andamiaje: request -> CORS -> validación JSON -> router -> CSRF -> handler.
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

            if (($match['csrf'] ?? false) === true) {
                CsrfMiddleware::enforce($request);
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
        $router->add('GET', '/health', [HealthController::class, 'show']);
        $router->add('POST', '/auth/login', [PlaceholderController::class, 'notImplemented']);
        $router->add('GET', '/auth/me', [PlaceholderController::class, 'notImplemented']);
        $router->add('POST', '/auth/logout', [PlaceholderController::class, 'notImplemented'], true);
        $router->add('GET', '/presbiterios', [PlaceholderController::class, 'notImplemented']);

        return $router;
    }
}
