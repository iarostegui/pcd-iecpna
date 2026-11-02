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

    /** @return list<array{id:int|null, codigo:string}> */
    private function presbiterios(): array
    {
        return $this->db->fetchAll(
            "SELECT id, codigo FROM organizacion
             WHERE tipo = 'PRESBITERIO' AND activo = 1 AND codigo IS NOT NULL
             ORDER BY codigo",
        );
    }

    /** @return array<string, string> */
    private function passwords(): array
    {
        return [
            'DIGITADOR' => Env::require('PCD_UAT_DIGITADOR_PASSWORD'),
            'ADMIN' => Env::require('PCD_ADMIN_PASSWORD'),
            'REPORTES' => Env::require('PCD_REPORTES_PASSWORD'),
        ];
    }

    private function provision(): array
    {
        return (new Provisioner($this->db->pdo()))->run(Plan::build($this->presbiterios()), $this->passwords());
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

    public function testSecondRunIsIdempotentAndConflictFree(): void
    {
        $summary = $this->provision();

        $this->assertSame(0, $summary['created']);
        $this->assertSame(26, $summary['existing']);
        $this->assertSame([], $summary['conflicts']);

        $total = $this->db->fetchOne('SELECT COUNT(*) AS total FROM usuario');
        $this->assertSame(26, (int) $total['total']);
    }

    public function testPasswordHashIsNeverOverwritten(): void
    {
        $before = $this->db->fetchOne('SELECT password_hash FROM usuario WHERE codigo = ?', ['LIM-DIG-02']);
        $this->assertNotNull($before);

        $this->provision();

        $after = $this->db->fetchOne('SELECT password_hash FROM usuario WHERE codigo = ?', ['LIM-DIG-02']);

        $this->assertSame($before['password_hash'], $after['password_hash']);
    }

    public function testDisabledAccountIsNotReactivatedAndConflictIsReported(): void
    {
        $this->db->execute("UPDATE usuario SET activo = 0 WHERE codigo = 'LIM-DIG-06'");

        try {
            $summary = $this->provision();
            $row = $this->db->fetchOne("SELECT activo FROM usuario WHERE codigo = 'LIM-DIG-06'");

            $this->assertSame(0, (int) $row['activo']);
            $this->assertNotEmpty($summary['conflicts']);
            $this->assertContains('LIM-DIG-06', array_column($summary['conflicts'], 'codigo'));
        } finally {
            $this->db->execute("UPDATE usuario SET activo = 1 WHERE codigo = 'LIM-DIG-06'");
        }
    }

    public function testProfileConflictIsReportedAndNotCorrected(): void
    {
        $this->db->execute("UPDATE usuario SET perfil = 'ADMIN' WHERE codigo = 'TRU-DIG-06'");

        try {
            $summary = $this->provision();
            $row = $this->db->fetchOne("SELECT perfil FROM usuario WHERE codigo = 'TRU-DIG-06'");

            $this->assertSame('ADMIN', $row['perfil']);
            $this->assertSame('perfil', $summary['conflicts'][0]['conflicts'][0]['campo']);
        } finally {
            $this->db->execute("UPDATE usuario SET perfil = 'DIGITADOR' WHERE codigo = 'TRU-DIG-06'");
        }
    }

    public function testPresbiterioConflictIsReportedAndNotCorrected(): void
    {
        $row = $this->db->fetchOne("SELECT presbiterio_id FROM usuario WHERE codigo = 'CHI-DIG-06'");
        $this->assertNotNull($row);
        $original = (int) $row['presbiterio_id'];
        $other = $original === 1 ? 2 : 1;

        $this->db->execute('UPDATE usuario SET presbiterio_id = ? WHERE codigo = ?', [$other, 'CHI-DIG-06']);

        try {
            $summary = $this->provision();
            $after = $this->db->fetchOne("SELECT presbiterio_id FROM usuario WHERE codigo = 'CHI-DIG-06'");

            $this->assertSame($other, (int) $after['presbiterio_id']);
            $this->assertNotEmpty($summary['conflicts']);
        } finally {
            $this->db->execute('UPDATE usuario SET presbiterio_id = ? WHERE codigo = ?', [$original, 'CHI-DIG-06']);
        }
    }
}
