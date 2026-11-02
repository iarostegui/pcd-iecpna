<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use InvalidArgumentException;
use Pcd\Provisioning\Plan;
use PHPUnit\Framework\TestCase;

final class ProvisioningPlanTest extends TestCase
{
    /** @return list<array{id:int|null, codigo:string}> */
    private function presbiterios(): array
    {
        return [
            ['id' => 1, 'codigo' => 'LIM'],
            ['id' => 2, 'codigo' => 'TRU'],
            ['id' => 3, 'codigo' => 'CHI'],
            ['id' => 4, 'codigo' => 'IQU'],
        ];
    }

    public function testSixDigitadoresPerPresbiterioPlusNational(): void
    {
        $plan = Plan::build($this->presbiterios());

        $this->assertCount(26, $plan);
        $this->assertCount(24, array_filter($plan, static fn (array $user): bool => $user['perfil'] === 'DIGITADOR'));
        $this->assertCount(1, array_filter($plan, static fn (array $user): bool => $user['perfil'] === 'ADMIN'));
        $this->assertCount(1, array_filter($plan, static fn (array $user): bool => $user['perfil'] === 'REPORTES'));
    }

    public function testCodesAreUniqueAndDeterministic(): void
    {
        $plan = Plan::build($this->presbiterios());
        $codes = array_column($plan, 'codigo');

        $this->assertCount(count($codes), array_unique($codes));
        $this->assertContains('LIM-DIG-01', $codes);
        $this->assertContains('IQU-DIG-06', $codes);
        $this->assertContains('ADMIN-NAC', $codes);
        $this->assertContains('REPORTES-NAC', $codes);
    }

    public function testNationalUsersHaveNoPresbiterio(): void
    {
        $national = Plan::nationalUsers();

        $this->assertNull($national[0]['presbiterio_id']);
        $this->assertNull($national[1]['presbiterio_id']);
    }

    public function testMissingPasswordThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Plan::passwordFor(['perfil' => 'ADMIN'], []);
    }

    public function testAdminOnlyContainsSingleAdmin(): void
    {
        $plan = Plan::adminOnly();

        $this->assertCount(1, $plan);
        $this->assertSame('ADMIN-NAC', $plan[0]['codigo']);
    }
}
