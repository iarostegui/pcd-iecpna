<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use Pcd\Security\PhpSession;
use PHPUnit\Framework\TestCase;

final class PhpSessionTest extends TestCase
{
    public function testProductionForcesSecureCookie(): void
    {
        $previousEnv = getenv('PCD_ENV');
        $previousSecure = getenv('PCD_COOKIE_SECURE');
        putenv('PCD_ENV=production');
        putenv('PCD_COOKIE_SECURE=false');

        try {
            $this->assertTrue(PhpSession::secure());
        } finally {
            $previousEnv === false ? putenv('PCD_ENV') : putenv('PCD_ENV=' . $previousEnv);
            $previousSecure === false ? putenv('PCD_COOKIE_SECURE') : putenv('PCD_COOKIE_SECURE=' . $previousSecure);
        }
    }

    public function testExplicitSecureFlagOutsideProduction(): void
    {
        $previousEnv = getenv('PCD_ENV');
        $previousSecure = getenv('PCD_COOKIE_SECURE');
        putenv('PCD_ENV=testing');
        putenv('PCD_COOKIE_SECURE=true');

        try {
            $this->assertTrue(PhpSession::secure());
        } finally {
            $previousEnv === false ? putenv('PCD_ENV') : putenv('PCD_ENV=' . $previousEnv);
            $previousSecure === false ? putenv('PCD_COOKIE_SECURE') : putenv('PCD_COOKIE_SECURE=' . $previousSecure);
        }
    }

    public function testLocalHttpIsNotForcedSecure(): void
    {
        $previousEnv = getenv('PCD_ENV');
        $previousSecure = getenv('PCD_COOKIE_SECURE');
        putenv('PCD_ENV=testing');
        putenv('PCD_COOKIE_SECURE=false');

        try {
            $this->assertFalse(PhpSession::secure());
        } finally {
            $previousEnv === false ? putenv('PCD_ENV') : putenv('PCD_ENV=' . $previousEnv);
            $previousSecure === false ? putenv('PCD_COOKIE_SECURE') : putenv('PCD_COOKIE_SECURE=' . $previousSecure);
        }
    }
}
