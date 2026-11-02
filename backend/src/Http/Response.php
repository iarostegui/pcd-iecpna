<?php

declare(strict_types=1);

namespace Pcd\Http;

/**
 * Respuestas JSON compatibles con OpenAPI:
 * éxito = esquema del recurso; error = {error:{code,message,details?},request_id}.
 */
final class Response
{
    private static bool $sent = false;

    /** @var array<string, string> */
    private static array $defaultHeaders = [];

    public static function sent(): bool
    {
        return self::$sent;
    }

    /** @param array<string, string> $headers */
    public static function addDefaultHeaders(array $headers): void
    {
        self::$defaultHeaders = array_merge(self::$defaultHeaders, $headers);
    }

    /** @param array<string, mixed> $payload */
    public static function json(Request $request, int $status, array $payload, array $headers = []): void
    {
        self::emit($request, $status, self::encode($payload), $headers);
    }

    /** @param array<string, mixed>|null $details */
    public static function error(
        Request $request,
        string $code,
        string $message,
        int $status,
        ?array $details = null,
        array $headers = [],
    ): void {
        self::json($request, $status, self::errorPayload($request->requestId, $code, $message, $details), $headers);
    }

    public static function empty(Request $request, int $status = 204, array $headers = []): void
    {
        self::emit($request, $status, '', $headers);
    }

    /** @param array<string, mixed>|null $details */
    public static function errorPayload(string $requestId, string $code, string $message, ?array $details = null): array
    {
        $error = ['code' => $code, 'message' => $message];

        if ($details !== null && $details !== []) {
            $error['details'] = $details;
        }

        return ['error' => $error, 'request_id' => $requestId];
    }

    /** @param array<string, mixed> $payload */
    public static function encode(array $payload): string
    {
        return (string) json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE,
        );
    }

    /** @param array<string, string> $headers */
    private static function emit(Request $request, int $status, string $body, array $headers): void
    {
        if (self::$sent) {
            return;
        }

        self::$sent = true;

        if (!headers_sent()) {
            http_response_code($status);

            $all = array_merge([
                'Content-Type' => 'application/json; charset=utf-8',
                'X-Request-Id' => $request->requestId,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'no-store',
                'Referrer-Policy' => 'no-referrer',
            ], self::$defaultHeaders, $headers);

            foreach ($all as $name => $value) {
                header($name . ': ' . $value);
            }
        }

        if ($request->method !== 'HEAD' && $body !== '') {
            echo $body;
        }
    }
}
