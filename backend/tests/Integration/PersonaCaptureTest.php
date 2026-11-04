<?php

declare(strict_types=1);

namespace Pcd\Tests\Integration;

use Pcd\Config\Env;
use Pcd\Database\Db;
use Pcd\Http\HttpException;
use Pcd\Service\PersonaService;
use Pcd\Service\SessionContext;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use PDO;

/** Pruebas I02 contra MySQL 8 de CI, exclusivamente con datos ficticios. */
#[Group('integration')]
final class PersonaCaptureTest extends TestCase
{
    private PDO $pdo;
    private PersonaService $service;
    private SessionContext $ctx;
    private ?SessionContext $other = null;
    /** @var list<int> */ private array $sedes = [];
    /** @var list<int> */ private array $personas = [];
    /** @var list<int> */ private array $fichas = [];
    /** @var list<string> */ private array $keys = [];
    private int $userId;

    protected function setUp(): void
    {
        if (getenv('PCD_DB_HOST') === false) {
            self::markTestSkipped('Requiere MySQL 8 de prueba y seed.');
        }
        $this->pdo = Db::fromEnv()->pdo();
        $this->service = new PersonaService($this->pdo);
        $uid = $this->pdo->query("SELECT id FROM usuario WHERE codigo = 'LIM-DIG-01'")->fetchColumn();
        $presb = $this->pdo->query("SELECT id FROM organizacion WHERE codigo = 'LIM'")->fetchColumn();
        if ($uid === false || $presb === false) {
            self::markTestSkipped('Bootstrap UAT no aplicado.');
        }
        $this->userId = (int) $uid;
        $sede = $this->newSede((int) $presb);
        $this->ctx = $this->context($this->userId, (int) $presb, $sede, 'DIGITADOR');
        $otherPresb = $this->pdo->query("SELECT id FROM organizacion WHERE codigo = 'TRU'")->fetchColumn();
        $otherUser = $this->pdo->query("SELECT id FROM usuario WHERE codigo = 'TRU-DIG-01'")->fetchColumn();
        $this->other = $this->context((int) $otherUser, (int) $otherPresb,
            $this->newSede((int) $otherPresb), 'DIGITADOR');
    }

    protected function tearDown(): void
    {
        if (!isset($this->pdo)) return;
        foreach ($this->keys as $k) {
            $this->pdo->prepare('DELETE FROM idempotencia WHERE clave = ?')->execute([$k]);
        }
        foreach ($this->fichas as $id) {
            $this->pdo->prepare('DELETE FROM ficha WHERE id = ?')->execute([$id]);
        }
        foreach (array_unique($this->personas) as $id) {
            $this->pdo->prepare('DELETE FROM persona WHERE id = ?')->execute([$id]);
        }
        foreach ($this->sedes as $id) {
            $this->pdo->prepare('DELETE FROM organizacion WHERE id = ?')->execute([$id]);
        }
    }

    public function testDniReuseBetweenPresbiteriosDoesNotOverwrite(): void
    {
        $dni = $this->dni();
        $a = $this->save($this->ctx, [
            'tipo_documento' => 'DNI', 'numero_documento' => $dni,
            'nombres' => 'PRUEBA ORIGINAL', 'fecha_nacimiento' => '1990-01-01'
        ]);
        self::assertFalse($a['persona_reutilizada']);
        $b = $this->save($this->other, [
            'tipo_documento' => 'DNI', 'numero_documento' => $dni,
            'nombres' => 'NOMBRE DIVERGENTE', 'fecha_papel' => '2026-11-02'
        ]);
        self::assertTrue($b['persona_reutilizada']);
        self::assertSame($a['persona_id'], $b['persona_id']);
        self::assertNotSame($a['ficha_id'], $b['ficha_id']);
        $this->personas[] = $a['persona_id'];
        $check = $this->pdo->prepare('SELECT nombres, fecha_nacimiento FROM persona WHERE id = ?');
        $check->execute([$a['persona_id']]);
        $row = $check->fetch(PDO::FETCH_ASSOC);
        self::assertSame('PRUEBA ORIGINAL', $row['nombres']);
        self::assertSame('1990-01-01', $row['fecha_nacimiento']);
        $audit = $this->pdo->prepare("SELECT COUNT(*) FROM auditoria_evento WHERE recurso_tipo = 'FICHA' AND recurso_id IN (?, ?)");
        $audit->execute([$a['ficha_id'], $b['ficha_id']]);
        self::assertSame(0, (int) $audit->fetchColumn());
    }

    public function testIdempotencyReturnsSameFichaWithoutNewInsert(): void
    {
        $payload = $this->valid(['tipo_documento' => 'OTRO', 'numero_documento' => 'FAKE-001', 'nombres' => 'SINTETICO']);
        $key = $this->newKey();
        $first = $this->service->create($this->ctx, $payload, $key)['body'];
        $this->fichas[] = $first['ficha_id'];
        $this->personas[] = $first['persona_id'];
        $second = $this->service->create($this->ctx, $payload, $key)['body'];
        self::assertSame($first['ficha_id'], $second['ficha_id']);
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM ficha WHERE id = ?');
        $stmt->execute([$first['ficha_id']]);
        self::assertSame(1, (int) $stmt->fetchColumn());
    }

    public function testSameKeyWithDifferentPayloadReturns409(): void
    {
        $key = $this->newKey();
        $first = $this->service->create($this->ctx, $this->valid(['nombres' => 'SINTETICO']), $key)['body'];
        $this->fichas[] = $first['ficha_id'];
        $this->personas[] = $first['persona_id'];
        $this->expectException(HttpException::class);
        $this->service->create($this->ctx, $this->valid(['nombres' => 'DIFERENTE']), $key);
    }

    public function testAppendRequiresConcordantIdentity(): void
    {
        $first = $this->save($this->ctx, [
            'tipo_documento' => 'DNI', 'numero_documento' => $this->dni(),
        ]);
        try {
            $this->service->create($this->other, $this->valid([
                'tipo_documento' => 'DNI', 'numero_documento' => $this->dni(),
            ]), $this->newKey(), $first['persona_id']);
            self::fail('La vinculación divergente debe rechazarse.');
        } catch (HttpException $e) {
            self::assertSame(409, $e->status);
        }
    }

    public function testReportesCannotCreate(): void
    {
        $reportsId = (int) $this->pdo->query("SELECT id FROM usuario WHERE perfil = 'REPORTES' LIMIT 1")->fetchColumn();
        $reports = $this->context($reportsId, $this->ctx->presbiterioId, $this->ctx->sedeId, 'REPORTES');
        $this->expectException(HttpException::class);
        $this->service->create($reports, ['nombres' => 'PRUEBA'], $this->newKey());
    }

    public function testBadDateLeavesNoPersona(): void
    {
        $before = (int) $this->pdo->query('SELECT COUNT(*) FROM persona')->fetchColumn();
        try {
            $this->service->create($this->ctx, ['fecha_nacimiento' => '2026-02-30'], $this->newKey());
            self::fail('No debe admitirse fecha imposible.');
        } catch (HttpException $e) {
            self::assertSame(422, $e->status);
        }
        self::assertSame($before, (int) $this->pdo->query('SELECT COUNT(*) FROM persona')->fetchColumn());
    }

    public function testNoSedeIsRejected(): void
    {
        $noSede = $this->context($this->userId, $this->ctx->presbiterioId, null, 'DIGITADOR');
        $this->expectException(HttpException::class);
        $this->service->create($noSede, ['nombres' => 'PRUEBA'], $this->newKey());
    }

    private function valid(array $overrides = []): array
    {
        return array_merge([
            'nombres' => 'PERSONA SINTETICA', 'apellido_paterno' => 'PRUEBA',
            'apellido_materno' => 'TEST', 'tipo_documento' => 'DNI',
            'numero_documento' => '00123456', 'telefono' => '999888777',
        ], $overrides);
    }

    /** @return array<string,mixed> */
    private function save(SessionContext $ctx, array $data): array
    {
        $result = $this->service->create($ctx, $this->valid($data), $this->newKey())['body'];
        $this->fichas[] = (int) $result['ficha_id'];
        $this->personas[] = (int) $result['persona_id'];
        return $result;
    }

    private function newKey(): string
    {
        $key = 'i02-ci-' . bin2hex(random_bytes(9));
        $this->keys[] = $key;
        return $key;
    }

    private function dni(): string
    {
        return str_pad((string) random_int(1000000, 8999999), 8, '0', STR_PAD_LEFT);
    }

    private function newSede(int $presb): int
    {
        $name = 'PRUEBA-I02-' . bin2hex(random_bytes(5));
        $stmt = $this->pdo->prepare(
            "INSERT INTO organizacion (tipo, padre_id, nombre, nombre_norm, activo) VALUES ('SEDE', ?, ?, ?, 1)"
        );
        $stmt->execute([$presb, $name, $name]);
        $id = (int) $this->pdo->lastInsertId();
        $this->sedes[] = $id;
        return $id;
    }

    private function context(int $userId, int $presbId, ?int $sedeId, string $role): SessionContext
    {
        return new SessionContext(1, $userId, 'PRUEBA-I02', 'Usuario sintético',
            $role, $presbId, 'Presbiterio test', $sedeId, 'Sede test',
            '2026-11-02 12:00:00', '2026-11-02 12:00:00', null);
    }
}
