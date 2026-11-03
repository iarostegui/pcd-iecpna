<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use Pcd\Config\Env;
use Pcd\Service\SessionContext;
use PHPUnit\Framework\TestCase;

final class SessionContextTest extends TestCase
{
    public function testAuthMeContainsContextAndExpiryButNoCoordinates(): void
    {
        $context = new SessionContext(
            7,
            9,
            'LIM-DIG-01',
            'Digitador LIM 01',
            'DIGITADOR',
            1,
            'Lima',
            3,
            'Sede Centro',
            '2026-11-02 10:00:00.000000',
            '2026-11-02 10:10:00.000000',
            'DENEGADA',
        );

        $payload = $context->toAuthMe('csrf-token');

        $this->assertSame('LIM-DIG-01', $payload['usuario_codigo']);
        $this->assertSame('Lima', $payload['presbiterio_nombre']);
        $this->assertSame('Sede Centro', $payload['sede_nombre']);
        $this->assertSame('DENEGADA', $payload['geo_estado']);
        $this->assertArrayNotHasKey('latitud', $payload);
        $this->assertArrayNotHasKey('longitud', $payload);
        $this->assertSame('2026-11-02T10:55:00Z', $payload['vence_inactividad_en']);
        $this->assertSame('2026-11-02T22:00:00Z', $payload['vence_absoluta_en']);
    }

    public function testExpiryConfigurationIsReadFromEnv(): void
    {
        $previousIdle = Env::get('PCD_SESSION_IDLE_MINUTES');
        $previousAbsolute = Env::get('PCD_SESSION_ABSOLUTE_HOURS');
        putenv('PCD_SESSION_IDLE_MINUTES=30');
        putenv('PCD_SESSION_ABSOLUTE_HOURS=1');

        try {
            $context = new SessionContext(
                1,
                1,
                'X',
                'X',
                'ADMIN',
                1,
                'Lima',
                null,
                null,
                '2026-11-02 10:00:00.000000',
                '2026-11-02 10:00:00.000000',
                null,
            );
            $payload = $context->toAuthMe('csrf');

            $this->assertSame('2026-11-02T10:30:00Z', $payload['vence_inactividad_en']);
            $this->assertSame('2026-11-02T11:00:00Z', $payload['vence_absoluta_en']);
        } finally {
            $previousIdle === null
                ? putenv('PCD_SESSION_IDLE_MINUTES')
                : putenv('PCD_SESSION_IDLE_MINUTES=' . $previousIdle);
            $previousAbsolute === null
                ? putenv('PCD_SESSION_ABSOLUTE_HOURS')
                : putenv('PCD_SESSION_ABSOLUTE_HOURS=' . $previousAbsolute);
        }
    }
}
