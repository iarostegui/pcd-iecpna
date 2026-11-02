<?php

declare(strict_types=1);

namespace Pcd\Http;

use JsonException;
use Pcd\Http\HttpException;

final class Request
{
    /**
     * @param array<string, string> $headers
     * @param array<string, mixed>  $query
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $headers,
        public readonly string $body,
        public readonly array $query,
        public readonly string $requestId,
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = (string) (parse_url($uri, PHP_URL_PATH) ?: '/');
        $headers = self::collectHeaders();
        $providedRequestId = self::normalizeRequestId($headers['x-request-id'] ?? null);

        return new self(
            $method,
            $path,
            $headers,
            (string) file_get_contents('php://input'),
            $_GET,
            $providedRequestId ?? self::uuid4(),
        );
    }

    public function header(string $name, ?string $default = null): ?string
    {
        $key = strtolower($name);

        return $this->headers[$key] ?? $default;
    }

    public function withPath(string $path): self
    {
        return new self($this->method, $path, $this->headers, $this->body, $this->query, $this->requestId);
    }

    /**
     * Decodifica el cuerpo JSON y falla con 400 si no es un objeto/arreglo válido.
     *
     * @return array<string, mixed>
     */
    public function json(): array
    {
        if (trim($this->body) === '') {
            throw new HttpException(400, 'empty_body', 'El cuerpo JSON es obligatorio.');
        }

        try {
            $decoded = json_decode($this->body, true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new HttpException(400, 'invalid_json', 'El cuerpo JSON no es válido.');
        }

        if (!is_array($decoded)) {
            throw new HttpException(400, 'invalid_json', 'El cuerpo JSON debe ser un objeto.');
        }

        return $decoded;
    }

    /** @return array<string, string> */
    private static function collectHeaders(): array
    {
        $headers = [];

        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                $headers[strtolower((string) $name)] = (string) $value;
            }

            return $headers;
        }

        foreach ($_SERVER as $key => $value) {
            if (!str_starts_with((string) $key, 'HTTP_') || !is_scalar($value)) {
                continue;
            }

            $name = strtolower(str_replace('_', '-', substr((string) $key, 5)));
            $headers[$name] = (string) $value;
        }

        return $headers;
    }

    private static function normalizeRequestId(?string $value): ?string
    {
        if ($value === null || preg_match('/^[A-Za-z0-9._-]{8,64}$/', $value) !== 1) {
            return null;
        }

        return $value;
    }

    private static function uuid4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
