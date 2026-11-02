<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use Pcd\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class CsrfTest extends TestCase
{
    public function testEqualTokensMatch(): void
    {
        $this->assertTrue(Csrf::matches('abc123', 'abc123'));
    }

    public function testDifferentTokensDoNotMatch(): void
    {
        $this->assertFalse(Csrf::matches('abc123', 'abc124'));
    }

    public function testEmptyTokensDoNotMatch(): void
    {
        $this->assertFalse(Csrf::matches('', ''));
        $this->assertFalse(Csrf::matches(null, 'abc'));
        $this->assertFalse(Csrf::matches('abc', null));
    }
}
