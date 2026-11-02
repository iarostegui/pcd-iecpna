<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use Pcd\Config\Env;
use Pcd\Security\PasswordHasher;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PasswordHasherTest extends TestCase
{
    public function testHashAndVerifyWithAvailableAlgorithm(): void
    {
        $previous = getenv('PCD_ALLOW_BCRYPT_FALLBACK');
        putenv('PCD_ALLOW_BCRYPT_FALLBACK=true');
        $_ENV['PCD_ALLOW_BCRYPT_FALLBACK'] = 'true';

        try {
            $hash = PasswordHasher::hash('clave-sintetica');
            $this->assertTrue(PasswordHasher::isStrongHash($hash));
            $this->assertTrue(PasswordHasher::verify('clave-sintetica', $hash));
            $this->assertFalse(PasswordHasher::verify('otra', $hash));
            $this->assertNotSame('clave-sintetica', $hash);
        } finally {
            if ($previous === false) {
                putenv('PCD_ALLOW_BCRYPT_FALLBACK');
                unset($_ENV['PCD_ALLOW_BCRYPT_FALLBACK']);
            } else {
                putenv('PCD_ALLOW_BCRYPT_FALLBACK=' . $previous);
            }
        }
    }

    public function testFallbackIsForbiddenInProduction(): void
    {
        if (defined('PASSWORD_ARGON2ID')) {
            $this->markTestSkipped('El entorno ofrece Argon2id.');
        }

        $previousEnv = getenv('PCD_ENV');
        $previousFallback = getenv('PCD_ALLOW_BCRYPT_FALLBACK');
        putenv('PCD_ENV=production');
        putenv('PCD_ALLOW_BCRYPT_FALLBACK=true');

        try {
            $this->assertFalse(PasswordHasher::fallbackAllowed());
            $this->expectException(RuntimeException::class);
            PasswordHasher::assertAvailable();
        } finally {
            $previousEnv === false ? putenv('PCD_ENV') : putenv('PCD_ENV=' . $previousEnv);
            $previousFallback === false
                ? putenv('PCD_ALLOW_BCRYPT_FALLBACK')
                : putenv('PCD_ALLOW_BCRYPT_FALLBACK=' . $previousFallback);
        }
    }

    public function testKnownDummyHashIsNotUsedForAuthentication(): void
    {
        $previous = getenv('PCD_ALLOW_BCRYPT_FALLBACK');
        putenv('PCD_ALLOW_BCRYPT_FALLBACK=true');

        try {
            $this->assertTrue(PasswordHasher::isStrongHash(PasswordHasher::dummyHash()));
            $this->assertFalse(PasswordHasher::verify('pcd-dummy-password-not-used', PasswordHasher::dummyHash()));
        } finally {
            $previous === false
                ? putenv('PCD_ALLOW_BCRYPT_FALLBACK')
                : putenv('PCD_ALLOW_BCRYPT_FALLBACK=' . $previous);
        }
    }
}
