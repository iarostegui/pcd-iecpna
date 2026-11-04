<?php

declare(strict_types=1);

namespace Pcd\Service;

use DateTimeImmutable;
use Pcd\Support\Validation;

/**
 * Lista cerrada según PersonaInput (OpenAPI) + ficha-persona.schema.json.
 * Rechazo de fechas imposibles y claves ajenas; ninguna regla H-24 inventada.
 */
final class PersonaInputValidator
{
    public const PERSONA = [
        'tipo_documento' => 24, 'numero_documento' => 60,
        'nombres' => 150, 'apellido_paterno' => 100, 'apellido_materno' => 100,
        'sexo_codigo' => 24, 'estado_civil_codigo' => 24,
        'telefono' => 60, 'correo' => 180, 'facebook_usuario' => 180,
        'direccion' => 500, 'ubigeo_dpto_codigo' => 6,
        'ubigeo_prov_codigo' => 6, 'ubigeo_dist_codigo' => 6,
        'condicion_codigo' => 40,
    ];
    public const FICHA = [
        'numero_ficha' => 50, 'responsable_papel' => 180,
        'observaciones' => 65535,
    ];
    private const DATES = ['fecha_nacimiento', 'fecha_condicion', 'fecha_papel'];
    private const BOOLEAN = ['es_lider_gc_intencion'];
    private const CONYUGE = ['tipo_documento' => 24, 'numero_documento' => 60, 'nombres_apellidos' => 260];
    private const RESPONSABILIDADES = [
        'es_pastor_sede', 'nombre_sede_pastoreada', 'es_presbitero',
        'region_presbitero', 'tiene_responsabilidad', 'descripcion',
        'fecha_desde', 'otra_descripcion', 'otra_fecha_desde',
    ];

    /** @param array<string,mixed> $payload
     * @return array{persona:array<string,mixed>,ficha:array<string,mixed>,datos_fuente:?string,clave_identidad:?string,numero_norm:?string,advertencias:list<string>}
     */
    public static function normalize(array $payload): array
    {
        $allowed = array_merge(
            array_keys(self::PERSONA), array_keys(self::FICHA),
            self::DATES, self::BOOLEAN, ['conyuge', 'responsabilidades']
        );
        foreach ($payload as $key => $_value) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw Validation::invalid((string) $key, 'Propiedad de PersonaInput no reconocida.');
            }
        }

        $persona = [];
        foreach (self::PERSONA as $key => $max) {
            if (array_key_exists($key, $payload)) {
                $persona[$key] = Validation::optionalString($payload, $key, $max);
            }
        }
        $ficha = [];
        foreach (self::FICHA as $key => $max) {
            if (array_key_exists($key, $payload)) {
                $ficha[$key] = Validation::optionalString($payload, $key, $max);
            }
        }
        foreach (self::DATES as $key) {
            if (array_key_exists($key, $payload)) {
                $date = self::date($payload[$key], $key);
                if ($key === 'fecha_papel') {
                    $ficha[$key] = $date;
                } else {
                    $persona[$key] = $date;
                }
            }
        }
        if (array_key_exists('es_lider_gc_intencion', $payload)) {
            if (!is_bool($payload['es_lider_gc_intencion'])) {
                throw Validation::invalid('es_lider_gc_intencion', 'Debe ser booleano.');
            }
            $persona['es_lider_gc_intencion'] = (int) $payload['es_lider_gc_intencion'];
        }

        $tipo = $persona['tipo_documento'] ?? null;
        $numero = $persona['numero_documento'] ?? null;
        if ($tipo !== null && !in_array($tipo, ['DNI', 'CE', 'OTRO'], true)) {
            throw Validation::invalid('tipo_documento', 'Tipo documental no admitido.');
        }
        if (($tipo === null) !== ($numero === null)) {
            throw Validation::invalid('numero_documento', 'Tipo y número de documento deben informarse juntos.');
        }

        $clave = null;
        $norm = $numero === null ? null : mb_strtoupper(preg_replace('/\s+/u', '', $numero) ?? $numero, 'UTF-8');
        $warnings = [];
        if ($tipo === 'DNI') {
            if (preg_match('/^[0-9]{8}$/D', (string) $norm) !== 1) {
                throw Validation::invalid('numero_documento', 'El DNI debe contener exactamente ocho dígitos.');
            }
            $clave = 'DNI:' . $norm;
        } elseif ($tipo === 'CE') {
            // No dar por confiable hasta constatar la regla oficial del formato.
            $warnings[] = 'C.E. pendiente de regla oficial verificable; sin fusión automática.';
        } elseif ($tipo === 'OTRO') {
            $warnings[] = 'Documento OTRO: coincidencias solo orientativas, sin fusión automática.';
        }
        if ($tipo !== null) {
            $persona['tipo_documento'] = $tipo;
            $persona['numero_documento'] = $numero;
            $persona['numero_documento_norm'] = $norm;
        }
        if (($persona['correo'] ?? null) !== null
            && filter_var($persona['correo'], FILTER_VALIDATE_EMAIL) === false) {
            throw Validation::invalid('correo', 'Formato de correo inválido.');
        }

        $fuente = [];
        foreach (['conyuge', 'responsabilidades'] as $section) {
            if (!array_key_exists($section, $payload) || $payload[$section] === null) {
                continue;
            }
            if (!is_array($payload[$section]) || array_is_list($payload[$section])) {
                throw Validation::invalid($section, 'La sección debe ser un objeto JSON.');
            }
            $data = $payload[$section];
            $allowedSection = $section === 'conyuge'
                ? array_keys(self::CONYUGE) : self::RESPONSABILIDADES;
            $clean = [];
            foreach ($data as $key => $value) {
                if (!in_array($key, $allowedSection, true)) {
                    throw Validation::invalid($section, 'Sección JSON contiene campos no admitidos.');
                }
                if ($section === 'conyuge') {
                    $clean[$key] = self::nullableString($value, self::CONYUGE[$key], $section . '.' . $key);
                    if ($key === 'tipo_documento' && $clean[$key] !== null
                        && !in_array($clean[$key], ['DNI', 'CE', 'OTRO'], true)) {
                        throw Validation::invalid($section . '.' . $key, 'Tipo documental no válido.');
                    }
                } elseif (str_starts_with($key, 'es_') || $key === 'tiene_responsabilidad') {
                    if ($value !== null && !is_bool($value)) {
                        throw Validation::invalid($section . '.' . $key, 'Se esperaba booleano o nulo.');
                    }
                    $clean[$key] = $value;
                } elseif (in_array($key, ['fecha_desde', 'otra_fecha_desde'], true)) {
                    $clean[$key] = self::date($value, $section . '.' . $key);
                } else {
                    $clean[$key] = self::nullableString($value, 500, $section . '.' . $key);
                }
            }
            $fuente[$section] = $clean;
        }
        $json = $fuente === [] ? null : json_encode(
            array_merge(['schema_version' => '1.0'], $fuente),
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        // H-24: no establecer umbral de campos mínimos sin autorización funcional.
        return [
            'persona' => $persona,
            'ficha' => $ficha,
            'datos_fuente' => $json,
            'clave_identidad' => $clave,
            'numero_norm' => $norm,
            'advertencias' => $warnings,
        ];
    }

    public static function date(mixed $value, string $key): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_string($value) || preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $value) !== 1) {
            throw Validation::invalid($key, 'Fecha debe tener formato YYYY-MM-DD.');
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw Validation::invalid($key, 'Fecha calendario inexistente.');
        }
        return $value;
    }

    private static function nullableString(mixed $value, int $max, string $key): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!is_string($value) || mb_strlen($value, 'UTF-8') > $max) {
            throw Validation::invalid($key, 'Texto inválido o excesivamente largo.');
        }
        return $value;
    }
}
