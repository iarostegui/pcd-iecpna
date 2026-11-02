<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use Pcd\Support\Json;
use PHPUnit\Framework\TestCase;

final class JsonTest extends TestCase
{
    public function testCanonicalOrderIsStable(): void
    {
        $left = ['b' => 2, 'a' => ['d' => 4, 'c' => 3]];
        $right = ['a' => ['c' => 3, 'd' => 4], 'b' => 2];

        $this->assertSame(Json::canonical($left), Json::canonical($right));
        $this->assertSame(Json::hashPayload($left), Json::hashPayload($right));
    }

    public function testDifferentPayloadsHaveDifferentHashes(): void
    {
        $this->assertNotSame(
            Json::hashPayload(['nombre_oficial' => 'Sede A']),
            Json::hashPayload(['nombre_oficial' => 'Sede B']),
        );
    }

    public function testDecodeObjectRejectsInvalidJson(): void
    {
        $this->assertNull(Json::decodeObject('{'));
        $this->assertSame(['a' => 1], Json::decodeObject('{"a":1}'));
    }
}
