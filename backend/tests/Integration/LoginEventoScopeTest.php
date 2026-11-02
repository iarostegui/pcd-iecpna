<?php

declare(strict_types=1);

namespace Pcd\Tests\Integration;

use Pcd\Database\Db;
use Pcd\Repository\LoginEventoRepository;
use Pcd\Support\Clock;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Verifica que el cubo de intentos usa (usuario_id|codigo, presbiterio_id):
 * los fallos de un presbiterio no cuentan para otro.
 */
#[Group('integration')]
final class LoginEventoScopeTest extends TestCase
{
    private const MARKER = 'I01O01TEST';

    private Db $db;

    protected function setUp(): void
    {
        if (getenv('PCD_DB_HOST') === false) {
            $this->markTestSkipped('Requiere MySQL real (PCD_DB_HOST).');
        }

        $this->db = Db::fromEnv();
    }

    protected function tearDown(): void
    {
        $this->db->execute('DELETE FROM login_evento WHERE user_agent = ?', [self::MARKER]);
    }

    public function testFailuresAreScopedByAccountAndPresbiterio(): void
    {
        $user = $this->db->fetchOne("SELECT id FROM usuario WHERE codigo = 'LIM-DIG-06'");
        $lim = $this->db->fetchOne("SELECT id FROM organizacion WHERE tipo='PRESBITERIO' AND codigo='LIM'");
        $tru = $this->db->fetchOne("SELECT id FROM organizacion WHERE tipo='PRESBITERIO' AND codigo='TRU'");

        if ($user === null || $lim === null || $tru === null) {
            $this->markTestSkipped('Requiere seed y aprovisionamiento UAT.');
        }

        $repository = new LoginEventoRepository($this->db->pdo());
        $unknownCode = 'I01-O01-SCOPE-' . bin2hex(random_bytes(3));

        foreach ([
            [(int) $user['id'], (int) $lim['id'], 'LIM-DIG-06'],
            [(int) $user['id'], (int) $lim['id'], 'LIM-DIG-06'],
            [(int) $user['id'], (int) $tru['id'], 'LIM-DIG-06'],
            [null, (int) $lim['id'], $unknownCode],
        ] as [$usuarioId, $presbiterioId, $codigo]) {
            $repository->record([
                'usuario_id' => $usuarioId,
                'presbiterio_id' => $presbiterioId,
                'codigo' => $codigo,
                'resultado' => 'FALLIDO',
                'ip' => null,
                'user_agent' => self::MARKER,
            ]);
        }

        $since = Clock::sql(Clock::now()->modify('-15 minutes'));

        $this->assertSame(
            2,
            $repository->failedCountByAccount((int) $user['id'], 'LIM-DIG-06', (int) $lim['id'], $since),
        );
        $this->assertSame(
            1,
            $repository->failedCountByAccount((int) $user['id'], 'LIM-DIG-06', (int) $tru['id'], $since),
        );
        $this->assertSame(
            1,
            $repository->failedCountByAccount(null, $unknownCode, (int) $lim['id'], $since),
        );
    }
}
