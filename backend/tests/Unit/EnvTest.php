<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use Pcd\Config\Env;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class EnvTest extends TestCase
{
    public function testParseIgnoresCommentsAndQuotes(): void
    {
        $parsed = Env::parse(<<<'ENV'
# comentario
PCD_ENV=local
PCD_DB_NAME="pcd_test"
PCD_DB_USER='pcd_user'
INVALID LINE
ENV);

        $this->assertSame('local', $parsed['PCD_ENV']);
        $this->assertSame('pcd_test', $parsed['PCD_DB_NAME']);
        $this->assertSame('pcd_user', $parsed['PCD_DB_USER']);
        $this->assertArrayNotHasKey('INVALID', $parsed);
    }

    public function testRequireThrowsWhenMissing(): void
    {
        $this->expectException(RuntimeException::class);

        Env::require('PCD_VAR_QUE_NO_EXISTE');
    }

    public function testBoolAndIntFallbacks(): void
    {
        $this->assertTrue(Env::bool('PCD_TEST_BOOL_TRUE', false));
        $this->assertFalse(Env::bool('PCD_TEST_BOOL_MISSING', false));
        $this->assertSame(7, Env::int('PCD_TEST_INT_MISSING', 7));
    }

    protected function setUp(): void
    {
        putenv('PCD_TEST_BOOL_TRUE=true');
    }

    protected function tearDown(): void
    {
        putenv('PCD_TEST_BOOL_TRUE');
    }
}
