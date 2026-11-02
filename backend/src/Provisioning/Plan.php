<?php

declare(strict_types=1);

namespace Pcd\Provisioning;

use InvalidArgumentException;

/**
 * Plan puro de cuentas: no toca BD ni contraseñas. Se comparte con PHPUnit
 * para verificar 6 DIGITADORES por presbiterio + ADMIN + REPORTES.
 */
final class Plan
{
    public const DIGITADORES_POR_PRESBITERIO = 6;

    /**
     * @param list<array{id:int|null, codigo:string}> $presbiterios
     * @return list<array{codigo:string, nombre_display:string, perfil:string, presbiterio_id:int|null, presbiterio_codigo:string|null}>
     */
    public static function build(array $presbiterios): array
    {
        $users = [];
        $seen = [];

        foreach ($presbiterios as $presbiterio) {
            $codigo = strtoupper(trim((string) ($presbiterio['codigo'] ?? '')));

            if ($codigo === '') {
                throw new InvalidArgumentException('Presbiterio sin código; no se puede planificar.');
            }

            for ($i = 1; $i <= self::DIGITADORES_POR_PRESBITERIO; $i++) {
                $userCode = sprintf('%s-DIG-%02d', $codigo, $i);
                self::assertUnique($seen, $userCode);

                $users[] = [
                    'codigo' => $userCode,
                    'nombre_display' => sprintf('Digitador %s %02d', $codigo, $i),
                    'perfil' => 'DIGITADOR',
                    'presbiterio_id' => $presbiterio['id'] ?? null,
                    'presbiterio_codigo' => $codigo,
                ];
            }
        }

        foreach (self::nationalUsers() as $user) {
            self::assertUnique($seen, $user['codigo']);
            $users[] = $user;
        }

        return $users;
    }

    /** @return list<array{codigo:string, nombre_display:string, perfil:string, presbiterio_id:int|null, presbiterio_codigo:string|null}> */
    public static function adminOnly(): array
    {
        return [self::nationalUsers()[0]];
    }

    /** @return list<array{codigo:string, nombre_display:string, perfil:string, presbiterio_id:int|null, presbiterio_codigo:string|null}> */
    public static function nationalUsers(): array
    {
        return [
            [
                'codigo' => 'ADMIN-NAC',
                'nombre_display' => 'Administrador nacional',
                'perfil' => 'ADMIN',
                'presbiterio_id' => null,
                'presbiterio_codigo' => null,
            ],
            [
                'codigo' => 'REPORTES-NAC',
                'nombre_display' => 'Reportes nacional',
                'perfil' => 'REPORTES',
                'presbiterio_id' => null,
                'presbiterio_codigo' => null,
            ],
        ];
    }

    /**
     * @param array{perfil:string} $user
     * @param array<string, string> $passwords
     */
    public static function passwordFor(array $user, array $passwords): string
    {
        $key = $user['perfil'] ?? '';

        if (!isset($passwords[$key]) || $passwords[$key] === '') {
            throw new InvalidArgumentException(sprintf('Falta la contraseña de entorno para el perfil %s.', $key));
        }

        return $passwords[$key];
    }

    /** @param array<string, bool> $seen */
    private static function assertUnique(array &$seen, string $code): void
    {
        if (isset($seen[$code])) {
            throw new InvalidArgumentException(sprintf('Código de usuario duplicado en el plan: %s.', $code));
        }

        $seen[$code] = true;
    }
}
