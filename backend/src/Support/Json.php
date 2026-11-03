<?php

declare(strict_types=1);

namespace Pcd\Support;

use JsonException;

final class Json
{
    /** @throws JsonException */
    public static function encode(mixed $value): string
    {
        return (string) json_encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
    }

    /**
     * Ordena claves recursivamente para que el hash de idempotencia no dependa
     * del orden de serialización del cliente.
     */
    public static function canonical(mixed $value): string
    {
        return self::encode(self::sort($value));
    }

    public static function hashPayload(mixed $value): string
    {
        return hash('sha256', self::canonical($value));
    }

    /** @return array<string, mixed>|null */
    public static function decodeObject(?string $json): ?array
    {
        if ($json === null || trim($json) === '') {
            return null;
        }

        try {
            $decoded = json_decode($json, true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    private static function sort(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(static fn (mixed $item): mixed => self::sort($item), $value);
        }

        ksort($value, SORT_STRING);

        foreach ($value as $key => $item) {
            $value[$key] = self::sort($item);
        }

        return $value;
    }
}
