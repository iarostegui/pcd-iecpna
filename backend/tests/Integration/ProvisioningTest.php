<?php

declare(strict_types=1);

namespace Pcd\Tests\Integration;

use Pcd\Config\Env;
use Pcd\Database\Db;
use Pcd\Provisioning\Plan;
use Pcd\Provisioning\Provisioner;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Solo se ejecuta contra MySQL real (CI o entorno con PCD_DB_HOST).
 * Requiere 001_initial.sql, 002_catalogos_calidad.sql y el bootstrap UAT previos.
 */
#[Group('integration')]
final class ProvisioningTest extends TestCase
{
    private Db $db;

    protected function setUp(): void
    {
        if (getenv('PCD_DB_HOST') === false) {
            $this->markTestSkipped('Requiere MySQL real (PCD_DB_HOST) y datos sembrados.');
        }

        $this->db = Db::fromEnv();
        $this->db->pdo();
    }

    public function testAccountCountsAfterBootstrap(): void
    {
        $total = $this->db->fetchOne('SELECT COUNT(*) AS total FROM usuario');
        $digitadores = $this->db->fetchOne("SELECT COUNT(*) AS total FROM usuario WHERE perfil = 'DIGITADOR'");
        $admin = $this->db->fetchOne("SELECT COUNT(*) AS total FROM usuario WHERE perfil = 'ADMIN'");
        $reportes = $this->db->fetchOne("SELECT COUNT(*) AS total FROM usuario WHERE perfil = 'REPORTES'");

        $this->assertSame(26, (int) $total['total']);
        $this->assertSame(24, (int) $digitadores['total']);
        $this->assertSame(1, (int) $admin['total']);
        $this->assertSame(1, (int) $reportes['total']);
    }

    public function testPasswordsAreArgon2idAndVerifiable(): void
    {
        $this->assertTrue(defined('PASSWORD_ARGON2ID'), 'PHP debe ofrecer Argon2id.');

        $row = $this->db->fetchOne('SELECT password_hash FROM usuario WHERE codigo = ?', ['LIM-DIG-01']);
        $this->assertNotNull($row);
        $this->assertStringStartsWith('$argon2id$', (string) $row['password_hash']);

        $uatPassword = Env::require('PCD_UAT_DIGITADOR_PASSWORD');
        $this->assertNotSame($uatPassword, $row['password_hash']);
        $this->assertTrue(password_verify($uatPassword, (string) $row['password_hash']));
    }

    public function testSecondRunIsIdempotent(): void
    {
        $presbiterios = $this->db->fetchAll(
            "SELECT id, codigo FROM organizacion
             WHERE tipo = 'PRESBITERIO' AND activo = 1 AND codigo IS NOT NULL
             ORDER BY codigo",
        );
        $plan = Plan::build($presbiterios);
        $passwords = [
            'DIGITADOR' => Env::require('PCD_UAT_DIGITADOR_PASSWORD'),
            'ADMIN' => Env::require('PCD_ADMIN_PASSWORD'),
            'REPORTES' => Env::require('PCD_REPORTES_PASSWORD'),
        ];

        $summary = (new Provisioner($this->db->pdo()))->run($plan, $passwords);

        $this->assertSame(0, $summary['created']);
        $this->assertSame(26, $summary['existing']);

        $total = $this->db->fetchOne('SELECT COUNT(*) AS total FROM usuario');
        $this->assertSame(26, (int) $total['total']);
    }
}
