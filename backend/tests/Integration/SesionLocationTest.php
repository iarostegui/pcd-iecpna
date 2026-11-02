<?php

declare(strict_types=1);

namespace Pcd\Tests\Integration;

use Pcd\Database\Db;
use Pcd\Repository\SesionRepository;
use Pcd\Support\Clock;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Verifica en MySQL real que la captura GPS es condicional: solo la primera
 * actualización con geo_estado NULL gana y no se sobrescribe.
 */
#[Group('integration')]
final class SesionLocationTest extends TestCase
{
    private Db $db;
    private int $sessionId = 0;

    protected function setUp(): void
    {
        if (getenv('PCD_DB_HOST') === false) {
            $this->markTestSkipped('Requiere MySQL real (PCD_DB_HOST).');
        }

        $this->db = Db::fromEnv();
        $pdo = $this->db->pdo();

        $user = $this->db->fetchOne("SELECT id, auth_version FROM usuario WHERE codigo = 'LIM-DIG-01'");
        $presbiterio = $this->db->fetchOne("SELECT id FROM organizacion WHERE tipo='PRESBITERIO' AND codigo='LIM'");

        if ($user === null || $presbiterio === null) {
            $this->markTestSkipped('Requiere seed y aprovisionamiento UAT.');
        }

        $hash = hash('sha256', 'i01-location-' . bin2hex(random_bytes(8)));
        $statement = $pdo->prepare(
            'INSERT INTO sesion
                (usuario_id, presbiterio_id, sede_id, session_ref_hash, fecha_login, last_seen, auth_version_al_ingreso)
             VALUES (?, ?, NULL, ?, NOW(6), NOW(6), ?)',
        );
        $statement->execute([(int) $user['id'], (int) $presbiterio['id'], $hash, (int) $user['auth_version']]);
        $this->sessionId = (int) $pdo->lastInsertId();
    }

    protected function tearDown(): void
    {
        if ($this->sessionId > 0) {
            $this->db->execute('DELETE FROM sesion WHERE id = ?', [$this->sessionId]);
        }
    }

    public function testFirstLocationWinsAndSecondCannotOverwrite(): void
    {
        $repository = new SesionRepository($this->db->pdo());
        $now = Clock::sql();

        $this->assertTrue($repository->storeLocationIfEmpty($this->sessionId, 'DENEGADA', null, null, null, $now));
        $this->assertFalse($repository->storeLocationIfEmpty($this->sessionId, 'OK', -12.0, -77.0, 15.0, $now));

        $location = $repository->location($this->sessionId);
        $this->assertNotNull($location);
        $this->assertSame('DENEGADA', $location['geo_estado']);
        $this->assertNull($location['geo_latitud']);
        $this->assertNull($location['geo_longitud']);
        $this->assertNotNull($location['geo_capturada_en']);
    }

    public function testTwoConnectionsOnlyFirstWins(): void
    {
        $first = new SesionRepository($this->db->pdo());
        $second = new SesionRepository(Db::fromEnv()->pdo());
        $now = Clock::sql();

        $wonFirst = $first->storeLocationIfEmpty($this->sessionId, 'OK', -12.0, -77.0, 10.0, $now);
        $wonSecond = $second->storeLocationIfEmpty($this->sessionId, 'ERROR', null, null, null, $now);

        $this->assertTrue($wonFirst);
        $this->assertFalse($wonSecond);

        $location = $first->location($this->sessionId);
        $this->assertNotNull($location);
        $this->assertSame('OK', $location['geo_estado']);
        $this->assertEqualsWithDelta(-12.0, (float) $location['geo_latitud'], 0.0000001);
    }
}
