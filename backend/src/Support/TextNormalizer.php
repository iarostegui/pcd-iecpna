<?php

declare(strict_types=1);

namespace Pcd\Support;

/**
 * Normalización aprobada RN-33/CA-116/CA-123:
 * trim -> espacios compactados -> NFKD -> sin marcas diacríticas -> mayúsculas.
 *
 * Se conserva la etiqueta original en organizacion.nombre; esta función se usa
 * únicamente para comparar y para organizacion.nombre_norm.
 */
final class TextNormalizer
{
    public static function normalize(string $value): string
    {
        $value = trim($value);
        $value = self::collapseSpaces($value);

        if ($value === '') {
            return '';
        }

        if (class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($value, \Normalizer::FORM_KD);
            $value = $normalized === false ? $value : $normalized;
        } elseif (function_exists('iconv')) {
            // Fallback documentado para PHP sin ext-intl. No sustituye NFKD en
            // todos los alfabetos, pero conserva la comparación española UAT.
            // GNU iconv translitera los acentos como comillas simples
            // (p. ej. JES'US); se retiran para comparar, no para mostrar.
            $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            $value = $transliterated === false ? $value : $transliterated;
            $value = str_replace(["'", '`', '´'], '', $value);
        }

        $value = preg_replace('/\p{Mn}+/u', '', $value) ?? $value;
        $value = mb_strtoupper($value, 'UTF-8');

        return self::collapseSpaces($value);
    }

    /**
     * Similitud no bloqueante: contención (mínimo 4 caracteres) o distancia
     * de edición <= 2 en nombres suficientemente largos.
     */
    public static function similar(string $left, string $right): bool
    {
        $a = self::normalize($left);
        $b = self::normalize($right);

        if ($a === '' || $b === '' || $a === $b) {
            return false;
        }

        $shorter = mb_strlen($a) <= mb_strlen($b) ? $a : $b;
        $longer = $shorter === $a ? $b : $a;

        if (mb_strlen($shorter) >= 4 && str_contains($longer, $shorter)) {
            return true;
        }

        return mb_strlen($a) >= 6
            && mb_strlen($b) >= 6
            && levenshtein($a, $b) <= 2;
    }

    private static function collapseSpaces(string $value): string
    {
        return trim((string) (preg_replace('/\s+/u', ' ', $value) ?? $value));
    }
}
