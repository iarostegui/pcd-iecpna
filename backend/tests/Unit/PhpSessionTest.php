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

    public function testProductionCookieContractForHostPrefix(): void
    {
        $previousEnv = getenv('PCD_ENV');
        $previousSecure = getenv('PCD_COOKIE_SECURE');
        $previousName = getenv('PCD_SESSION_NAME');
        putenv('PCD_ENV=production');
        putenv('PCD_COOKIE_SECURE=true');
        putenv('PCD_SESSION_NAME=__Host-pcd_sid');

        try {
            $options = PhpSession::cookieOptions();

            $this->assertSame('__Host-pcd_sid', PhpSession::cookieName());
            $this->assertSame('/', $options['path']);
            $this->assertSame('', $options['domain']);
            $this->assertTrue($options['secure']);
            $this->assertTrue($options['httponly']);
            $this->assertSame('Lax', $options['samesite']);
        } finally {
            $previousEnv === false ? putenv('PCD_ENV') : putenv('PCD_ENV=' . $previousEnv);
            $previousSecure === false ? putenv('PCD_COOKIE_SECURE') : putenv('PCD_COOKIE_SECURE=' . $previousSecure);
            $previousName === false ? putenv('PCD_SESSION_NAME') : putenv('PCD_SESSION_NAME=' . $previousName);
        }
    }

    public function testForwardedProtoHeaderIsNotTrusted(): void
    {
        $previousEnv = getenv('PCD_ENV');
        $previousSecure = getenv('PCD_COOKIE_SECURE');
        $previousHttps = $_SERVER['HTTPS'] ?? null;
        $previousPort = $_SERVER['SERVER_PORT'] ?? null;
        $previousForwarded = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;

        putenv('PCD_ENV=testing');
        putenv('PCD_COOKIE_SECURE=false');
        unset($_SERVER['HTTPS']);
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

        try {
            $this->assertFalse(PhpSession::secure());
            $this->assertFalse(PhpSession::cookieOptions()['secure']);
        } finally {
            $previousEnv === false ? putenv('PCD_ENV') : putenv('PCD_ENV=' . $previousEnv);
            $previousSecure === false ? putenv('PCD_COOKIE_SECURE') : putenv('PCD_COOKIE_SECURE=' . $previousSecure);
            if ($previousHttps === null) {
                unset($_SERVER['HTTPS']);
            } else {
                $_SERVER['HTTPS'] = $previousHttps;
            }
            if ($previousPort === null) {
                unset($_SERVER['SERVER_PORT']);
            } else {
                $_SERVER['SERVER_PORT'] = $previousPort;
            }
            if ($previousForwarded === null) {
                unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
            } else {
                $_SERVER['HTTP_X_FORWARDED_PROTO'] = $previousForwarded;
            }
        }
    }

    public function testDirectHttpsSignalEnablesSecureCookie(): void
    {
        $previousEnv = getenv('PCD_ENV');
        $previousSecure = getenv('PCD_COOKIE_SECURE');
        $previousHttps = $_SERVER['HTTPS'] ?? null;

        putenv('PCD_ENV=testing');
        putenv('PCD_COOKIE_SECURE=false');
        $_SERVER['HTTPS'] = 'on';

        try {
            $this->assertTrue(PhpSession::secure());
            $this->assertTrue(PhpSession::cookieOptions()['secure']);
        } finally {
            $previousEnv === false ? putenv('PCD_ENV') : putenv('PCD_ENV=' . $previousEnv);
            $previousSecure === false ? putenv('PCD_COOKIE_SECURE') : putenv('PCD_COOKIE_SECURE=' . $previousSecure);
            if ($previousHttps === null) {
                unset($_SERVER['HTTPS']);
            } else {
                $_SERVER['HTTPS'] = $previousHttps;
            }
        }
    }
}
