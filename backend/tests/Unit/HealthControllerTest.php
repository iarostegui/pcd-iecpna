<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use Pcd\Controller\HealthController;
use PHPUnit\Framework\TestCase;

final class HealthControllerTest extends TestCase
{
    public function testLivenessDoesNotDependOnDatabase(): void
    {
        $payload = HealthController::livenessPayload('req-live-1');

        $this->assertTrue($payload['ok']);
        $this->assertSame('ok', $payload['status']);
        $this->assertSame('req-live-1', $payload['request_id']);
        $this->assertArrayNotHasKey('database', $payload);
    }

    public function testLivenessDoesNotExposeEnvironmentOrSecrets(): void
    {
        $payload = HealthController::livenessPayload('req-live-2');

        $this->assertEqualsCanonicalizing(
            ['ok', 'request_id', 'status', 'service', 'version'],
            array_keys($payload),
        );
        $this->assertArrayNotHasKey('env', $payload);
        $this->assertArrayNotHasKey('environment', $payload);
        $this->assertStringNotContainsString('password', strtolower((string) json_encode($payload)));
    }

    public function testReadinessReturns200WhenDatabaseIsUp(): void
    {
        [$status, $payload] = HealthController::readinessPayload('req-ready-up', true);

        $this->assertSame(200, $status);
        $this->assertTrue($payload['ok']);
        $this->assertSame('ready', $payload['status']);
        $this->assertSame('up', $payload['database']['status']);
    }

    public function testReadinessReturns503WhenDatabaseIsDown(): void
    {
        [$status, $payload] = HealthController::readinessPayload('req-ready-down', false);

        $this->assertSame(503, $status);
        $this->assertFalse($payload['ok']);
        $this->assertSame('not_ready', $payload['status']);
        $this->assertSame('down', $payload['database']['status']);
    }

    public function testReadinessDoesNotExposeEnvironmentOrStackTrace(): void
    {
        [, $upPayload] = HealthController::readinessPayload('req-ready-1', true);
        [, $downPayload] = HealthController::readinessPayload('req-ready-2', false);

        foreach ([$upPayload, $downPayload] as $payload) {
            $this->assertArrayNotHasKey('env', $payload);
            $this->assertArrayNotHasKey('environment', $payload);
            $this->assertArrayNotHasKey('error', $payload);
            $this->assertArrayNotHasKey('trace', $payload);
        }
    }
}
