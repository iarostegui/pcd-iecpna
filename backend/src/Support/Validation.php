<?php

declare(strict_types=1);

namespace Pcd\Support;

use Pcd\Http\HttpException;

/**
 * Validadores mínimos de entrada. Los mensajes describen el campo, nunca
 * incluyen valores sensibles ni el cuerpo completo.
 */
final class Validation
{
    /** @param array<string, mixed> $data */
    public static function requiredString(array $data, string $key, int $min, int $max, bool $trim = true): string
    {
        $value = $data[$key] ?? null;

        if (!is_string($value)) {
            throw self::invalid($key, sprintf('El campo %s es obligatorio y debe ser texto.', $key));
        }

        if ($trim) {
            $value = trim($value);
        }

        $length = mb_strlen($value, 'UTF-8');

        if ($length < $min || $length > $max) {
            throw self::invalid($key, sprintf('El campo %s debe tener entre %d y %d caracteres.', $key, $min, $max));
        }

        return $value;
    }

    /** @param array<string, mixed> $data */
    public static function optionalString(array $data, string $key, int $max): ?string
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }

        if (!is_string($data[$key])) {
            throw self::invalid($key, sprintf('El campo %s debe ser texto o nulo.', $key));
        }

        $value = trim($data[$key]);

        if ($value === '') {
            return null;
        }

        if (mb_strlen($value, 'UTF-8') > $max) {
            throw self::invalid($key, sprintf('El campo %s supera %d caracteres.', $key, $max));
        }

        return $value;
    }

    /** @param array<string, mixed> $data */
    public static function requiredInt(array $data, string $key, int $min = 1): int
    {
        $value = $data[$key] ?? null;

        if (is_string($value) && preg_match('/^\d+$/', $value) === 1) {
            $value = (int) $value;
        }

        if (!is_int($value) || $value < $min) {
            throw self::invalid($key, sprintf('El campo %s debe ser un entero mayor o igual a %d.', $key, $min));
        }

        return $value;
    }

    /** @param array<string, mixed> $data */
    public static function optionalFloat(array $data, string $key, float $min, float $max): ?float
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }

        $value = $data[$key];

        if (!is_int($value) && !is_float($value) && !(is_string($value) && is_numeric($value))) {
            throw self::invalid($key, sprintf('El campo %s debe ser numérico.', $key));
        }

        $number = (float) $value;

        if ($number < $min || $number > $max) {
            throw self::invalid($key, sprintf('El campo %s está fuera de rango.', $key));
        }

        return $number;
    }

    /** @param array<string, mixed> $data */
    public static function requiredEnum(array $data, string $key, array $allowed): string
    {
        $value = $data[$key] ?? null;

        if (!is_string($value) || !in_array($value, $allowed, true)) {
            throw self::invalid($key, sprintf('El campo %s no es un valor permitido.', $key));
        }

        return $value;
    }

    /** @param array<string, mixed> $data */
    public static function requiredDateTime(array $data, string $key): string
    {
        $value = self::requiredString($data, $key, 10, 40, false);

        try {
            $moment = Clock::fromIso($value);
        } catch (\Throwable) {
            throw self::invalid($key, sprintf('El campo %s debe ser una fecha ISO 8601 válida.', $key));
        }

        return $moment->format('Y-m-d\TH:i:s\Z');
    }

    public static function invalid(string $field, string $message): HttpException
    {
        return new HttpException(422, 'validation_error', $message, ['campo' => $field]);
    }
}
