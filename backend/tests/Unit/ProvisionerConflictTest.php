<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use Pcd\Provisioning\Provisioner;
use PHPUnit\Framework\TestCase;

final class ProvisionerConflictTest extends TestCase
{
    /** @return array{codigo:string, nombre_display:string, perfil:string, presbiterio_id:int|null} */
    private function expected(?int $presbiterioId = 1): array
    {
        return [
            'codigo' => 'LIM-DIG-01',
            'nombre_display' => 'Digitador LIM 01',
            'perfil' => 'DIGITADOR',
            'presbiterio_id' => $presbiterioId,
        ];
    }

    /** @return array<string, mixed> */
    private function existing(array $overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'perfil' => 'DIGITADOR',
            'presbiterio_id' => 1,
            'activo' => 1,
        ], $overrides);
    }

    public function testMatchingUserHasNoConflicts(): void
    {
        $this->assertSame([], Provisioner::detectConflicts($this->expected(), $this->existing()));
    }

    public function testProfileMismatchIsReported(): void
    {
        $conflicts = Provisioner::detectConflicts($this->expected(), $this->existing(['perfil' => 'ADMIN']));

        $this->assertCount(1, $conflicts);
        $this->assertSame('perfil', $conflicts[0]['campo']);
        $this->assertSame('DIGITADOR', $conflicts[0]['esperado']);
        $this->assertSame('ADMIN', $conflicts[0]['actual']);
    }

    public function testPresbiterioMismatchIsReported(): void
    {
        $conflicts = Provisioner::detectConflicts($this->expected(1), $this->existing(['presbiterio_id' => 2]));

        $this->assertCount(1, $conflicts);
        $this->assertSame('presbiterio_id', $conflicts[0]['campo']);
        $this->assertSame('1', $conflicts[0]['esperado']);
        $this->assertSame('2', $conflicts[0]['actual']);
    }

    public function testDisabledAccountIsReportedButNotTreatedAsActive(): void
    {
        $conflicts = Provisioner::detectConflicts($this->expected(), $this->existing(['activo' => 0]));

        $this->assertCount(1, $conflicts);
        $this->assertSame('activo', $conflicts[0]['campo']);
        $this->assertSame('1', $conflicts[0]['esperado']);
        $this->assertSame('0', $conflicts[0]['actual']);
    }

    public function testNationalUserWithNullPresbiterioMatches(): void
    {
        $expected = [
            'codigo' => 'ADMIN-NAC',
            'nombre_display' => 'Administrador nacional',
            'perfil' => 'ADMIN',
            'presbiterio_id' => null,
        ];

        $this->assertSame([], Provisioner::detectConflicts($expected, [
            'id' => 99,
            'perfil' => 'ADMIN',
            'presbiterio_id' => null,
            'activo' => 1,
        ]));
    }

    public function testMultipleConflictsAreAllReported(): void
    {
        $conflicts = Provisioner::detectConflicts(
            $this->expected(1),
            $this->existing(['perfil' => 'REPORTES', 'presbiterio_id' => 3, 'activo' => 0]),
        );

        $fields = array_column($conflicts, 'campo');

        $this->assertSame(['perfil', 'presbiterio_id', 'activo'], $fields);
    }
}
