<?php

declare(strict_types=1);

namespace Pcd\Http;

use ErrorException;
use Throwable;

/**
 * Manejo centralizado: toda excepción se transforma en sobre JSON de error.
 * El detalle técnico se registra en error_log, nunca en la respuesta HTTP.
 */
final class ErrorHandler
{
    public static function register(Request $request): void
    {
        set_exception_handler(static function (Throwable $exception) use ($request): void {
            self::handle($exception, $request);
        });

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if ((error_reporting() & $severity) === 0) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        register_shutdown_function(static function (): void {
            $error = error_get_last();

            if ($error === null || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                return;
            }

            error_log(sprintf('[pcd] fatal: %s in %s:%d', $error['message'], $error['file'], $error['line']));

            if (!Response::sent()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo Response::encode([
                    'error' => ['code' => 'internal_error', 'message' => 'Error interno del servidor.'],
                    'request_id' => 'unavailable',
                ]);
            }
        });
    }

    public static function handle(Throwable $exception, Request $request): void
    {
        [$status, $code, $message, $details] = self::describe($exception);

        error_log(sprintf(
            '[pcd] %s %s -> %d %s: %s',
            $request->method,
            $request->path,
            $status,
            $code,
            $exception->getMessage(),
        ));

        if (!Response::sent()) {
            Response::error($request, $code, $message, $status, $details);
        }
    }

    /**
     * @return array{0:int, 1:string, 2:string, 3:array<string, mixed>|null}
     */
    public static function describe(Throwable $exception): array
    {
        if ($exception instanceof HttpException) {
            return [$exception->status, $exception->errorCode, $exception->getMessage(), $exception->details];
        }

        return [500, 'internal_error', 'Error interno del servidor.', null];
    }
}
