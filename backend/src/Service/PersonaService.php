<?php

declare(strict_types=1);

namespace Pcd\Service;

use Pcd\Http\HttpException;
use Pcd\Repository\IdempotenciaRepository;
use Pcd\Support\Json;
use Pcd\Support\Validation;
use PDO;
use PDOException;
use Throwable;

/**
 * Captura Persona/Ficha sin alterar el consolidado documental existente.
 * Exclusivamente datos sintéticos hasta GO institucional H-40.
 */
final class PersonaService
{
    private readonly IdempotenciaRepository $idem;

    public function __construct(private readonly PDO $pdo)
    {
        $this->idem = new IdempotenciaRepository($pdo);
    }

    /** @param array<string,mixed> $payload
     * @return array{status:int,body:array<string,mixed>}
     */
    public function create(SessionContext $context, array $payload, ?string $idempotencyKey, ?int $attachPersonaId = null, int $attempt = 0): array
    {
        if ($context->perfil === 'REPORTES') {
            throw new HttpException(403, 'forbidden', 'El perfil REPORTES no puede registrar fichas.');
        }
        if ($context->sedeId === null) {
            throw new HttpException(422, 'sede_required', 'Seleccione una sede antes de capturar.');
        }
        if ($idempotencyKey === null || preg_match('/^[A-Za-z0-9._:-]{8,110}$/D', trim($idempotencyKey)) !== 1) {
            throw new HttpException(400, 'idempotency_key_required', 'Se requiere Idempotency-Key válida.');
        }
        $key = trim($idempotencyKey);
        $data = PersonaInputValidator::normalize($payload);
        $hash = Json::hashPayload(['operation' => $attachPersonaId === null ? 'create' : 'attach:' . $attachPersonaId, 'data' => $data]);
        $this->assertSede($context);
        $this->assertUbigeos($data['persona']);

        $this->pdo->beginTransaction();
        try {
            $old = $this->idem->find($context->usuarioId, $key);
            if ($old !== null) {
                $result = $this->replay($old, $hash);
                $this->pdo->commit();
                return $result;
            }

            $reused = false;
            if ($attachPersonaId !== null) {
                $personaId = $this->resolveAttach($attachPersonaId, $data);
                $reused = true;
            } elseif ($data['clave_identidad'] !== null) {
                $found = $this->findByIdentity($data['clave_identidad']);
                if ($found !== null) {
                    $personaId = (int) $found['id'];
                    $reused = true;
                } else {
                    try {
                        $personaId = $this->insertPersona($data['persona'], $data['clave_identidad'], $context->usuarioId);
                    } catch (PDOException $e) {
                        if ((string) $e->getCode() !== '23000') {
                            throw $e;
                        }
                        // Carrera de inserción: el índice UNIQUE arbitra la identidad.
                        $winner = $this->findByIdentity($data['clave_identidad']);
                        if ($winner === null) {
                            throw $e;
                        }
                        $personaId = (int) $winner['id'];
                        $reused = true;
                    }
                }
            } else {
                // OTRO, CE no ratificado o sin documento: sin fusión automática.
                $personaId = $this->insertPersona($data['persona'], null, $context->usuarioId);
            }

            $fichaId = $this->insertFicha($personaId, $context, $data);
            $body = [
                'ficha_id' => $fichaId,
                'persona_id' => $personaId,
                'persona_reutilizada' => $reused,
                'advertencias' => $data['advertencias'],
            ];
            $this->idem->store($context->usuarioId, $key, $hash, 201, Json::encode($body));
            $this->pdo->commit();
            return ['status' => 201, 'body' => $body];
        } catch (PDOException $e) {
            $this->rollback();
            // InnoDB puede detectar un deadlock entre dos capturas de DNI aún
            // inexistente. Reintentar tras rollback abre una instantánea nueva.
            // Máximo dos reintentos, sin cambiar clave/payload ni crear dobles altas.
            if (((string) $e->getCode() === '40001' || (int) ($e->errorInfo[1] ?? 0) === 1213)
                && $attempt < 2) {
                usleep(20000 * ($attempt + 1));
                return $this->create($context, $payload, $idempotencyKey, $attachPersonaId, $attempt + 1);
            }
            if ((string) $e->getCode() === '23000') {
                // Clave idempotente disputada; releer después del rollback para
                // evitar una instantánea de lectura anterior a la otra transacción.
                $old = $this->idem->find($context->usuarioId, $key);
                if ($old !== null) {
                    return $this->replay($old, $hash);
                }
                throw new HttpException(409, 'constraint_conflict', 'Conflicto de integridad; reintente la operación.');
            }
            throw $e;
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /** @return array<string,mixed> */
    public function detail(SessionContext $context, int $personaId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, tipo_documento, numero_documento, nombres, apellido_paterno,
            apellido_materno, fecha_nacimiento, sexo_codigo, estado_civil_codigo, telefono,
            correo, direccion, version FROM persona WHERE id = ?');
        $stmt->execute([$personaId]);
        $persona = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($persona === false) {
            throw new HttpException(404, 'not_found', 'Persona no encontrada.');
        }
        $stmt = $this->pdo->prepare("SELECT id AS ficha_id, tipo_ficha, presbiterio_id, sede_id,
            created_by, version, fecha_papel, observaciones, control_papel, datos_fuente_json
            FROM ficha WHERE persona_id = ? AND tipo_ficha = 'PERSONA' ORDER BY id DESC");
        $stmt->execute([$personaId]);
        $fichas = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $fichas[] = $this->fichaDto($row, $context);
        }
        return ['persona' => $persona, 'fichas' => $fichas];
    }

    /** @return array<string,mixed> */
    public function ficha(SessionContext $context, int $fichaId): array
    {
        $stmt = $this->pdo->prepare("SELECT id AS ficha_id, tipo_ficha, presbiterio_id, sede_id,
            created_by, version, fecha_papel, observaciones, control_papel, datos_fuente_json
            FROM ficha WHERE id = ? AND tipo_ficha = 'PERSONA'");
        $stmt->execute([$fichaId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            throw new HttpException(404, 'not_found', 'Ficha no encontrada.');
        }
        return $this->fichaDto($row, $context);
    }

    /** @return array<string,mixed> */
    public function list(SessionContext $context, array $query): array
    {
        $page = isset($query['pagina']) ? Validation::requiredInt($query, 'pagina') : 1;
        $size = isset($query['tamano']) ? Validation::requiredInt($query, 'tamano') : 25;
        if ($size > 50 || $page > 100000) {
            throw Validation::invalid('tamano', 'Paginación fuera del rango permitido.');
        }
        $where = "f.tipo_ficha = 'PERSONA'";
        $params = [];
        foreach (['presbiterio_id' => 'f.presbiterio_id', 'sede_id' => 'f.sede_id',
                   'digitador_id' => 'f.created_by'] as $key => $column) {
            if (isset($query[$key])) {
                $where .= ' AND ' . $column . ' = ?';
                $params[] = Validation::requiredInt($query, $key);
            }
        }
        $count = $this->pdo->prepare("SELECT COUNT(*) FROM ficha f WHERE $where");
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $offset = ($page - 1) * $size;
        $sql = "SELECT p.id, p.tipo_documento, p.numero_documento,
            p.nombres, p.apellido_paterno, p.apellido_materno, f.id AS ficha_id,
            f.presbiterio_id, f.sede_id, f.created_by, f.created_at
            FROM ficha f JOIN persona p ON p.id = f.persona_id
            WHERE $where ORDER BY f.id DESC LIMIT $size OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return ['pagina' => $page, 'tamano' => $size, 'total' => $total,
            'items' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function resolveAttach(int $id, array $data): int
    {
        if ($data['clave_identidad'] === null) {
            throw new HttpException(422, 'identity_required', 'Adjuntar ficha por ID exige identidad documental confiable concordante.');
        }
        $stmt = $this->pdo->prepare('SELECT id, clave_identidad FROM persona WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            throw new HttpException(404, 'not_found', 'Persona no encontrada.');
        }
        if (!hash_equals((string) ($row['clave_identidad'] ?? ''), $data['clave_identidad'])) {
            throw new HttpException(409, 'identity_conflict', 'Documento incompatible con la Persona indicada.');
        }
        return $id;
    }

    private function findByIdentity(string $key): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id FROM persona WHERE clave_identidad = ? LIMIT 1 FOR UPDATE');
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }

    private function insertPersona(array $persona, ?string $key, int $userId): int
    {
        $persona['clave_identidad'] = $key;
        $persona['created_by'] = $userId;
        $names = array_keys($persona);
        $sql = 'INSERT INTO persona (' . implode(', ', $names) . ') VALUES ('
            . implode(', ', array_fill(0, count($names), '?')) . ')';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($persona));
        return (int) $this->pdo->lastInsertId();
    }

    private function insertFicha(int $personaId, SessionContext $ctx, array $data): int
    {
        $f = $data['ficha'];
        $stmt = $this->pdo->prepare(
            "INSERT INTO ficha (persona_id, presbiterio_id, sede_id, tipo_ficha,
                numero_ficha, fecha_papel, responsable_papel, observaciones, control_papel,
                datos_fuente_json, created_by)
             VALUES (?, ?, ?, 'PERSONA', ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $personaId, $ctx->presbiterioId, $ctx->sedeId,
            $f['numero_ficha'] ?? null, $f['fecha_papel'] ?? null,
            $f['responsable_papel'] ?? null, $f['observaciones'] ?? null,
            $f['control_papel'] ?? null, $data['datos_fuente'], $ctx->usuarioId,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    private function assertSede(SessionContext $ctx): void
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM organizacion WHERE id = ? AND padre_id = ?
            AND tipo = 'SEDE' AND activo = 1 LIMIT 1");
        $stmt->execute([$ctx->sedeId, $ctx->presbiterioId]);
        if ($stmt->fetchColumn() === false) {
            throw new HttpException(403, 'invalid_context', 'La sede no pertenece al contexto activo.');
        }
    }

    private function assertUbigeos(array $persona): void
    {
        $previous = null;
        foreach (['ubigeo_dpto_codigo' => ['DEPARTAMENTO', 2],
                   'ubigeo_prov_codigo' => ['PROVINCIA', 4],
                   'ubigeo_dist_codigo' => ['DISTRITO', 6]] as $field => [$tipo, $len]) {
            $code = $persona[$field] ?? null;
            if ($code === null) {
                if ($previous !== null) {
                    // Los hijos se validan únicamente si el padre también existe.
                    $previous = null;
                }
                continue;
            }
            if (!is_string($code) || preg_match('/^[0-9]{' . $len . '}$/D', $code) !== 1) {
                throw Validation::invalid($field, 'Código ubigeo inválido.');
            }
            if ($tipo !== 'DEPARTAMENTO' && $previous === null) {
                throw Validation::invalid($field, 'Seleccione primero el ubigeo padre.');
            }
            $stmt = $this->pdo->prepare('SELECT padre_codigo FROM ubigeo WHERE codigo = ? AND tipo = ? AND activo = 1');
            $stmt->execute([$code, $tipo]);
            $parent = $stmt->fetchColumn();
            if ($parent === false || ($tipo !== 'DEPARTAMENTO' && (string) $parent !== $previous)) {
                throw Validation::invalid($field, 'Ubigeo inexistente o jerarquía incompatible.');
            }
            $previous = $code;
        }
    }

    /** @return array{status:int,body:array<string,mixed>} */
    private function replay(array $row, string $hash): array
    {
        if (!hash_equals((string) $row['payload_hash'], $hash)) {
            throw new HttpException(409, 'idempotency_conflict', 'La clave ya se utilizó para otra operación.');
        }
        $body = Json::decodeObject($row['response_json'] === null ? null : (string) $row['response_json']);
        if ($body === null || (int) $row['response_status'] !== 201) {
            throw new HttpException(409, 'idempotency_pending', 'Operación en curso o respuesta incompleta.');
        }
        return ['status' => 201, 'body' => $body];
    }

    /** @return array<string,mixed> */
    private function fichaDto(array $row, SessionContext $ctx): array
    {
        return [
            'ficha_id' => (int) $row['ficha_id'], 'tipo_ficha' => $row['tipo_ficha'],
            'presbiterio_id' => (int) $row['presbiterio_id'],
            'sede_id' => (int) $row['sede_id'],
            'created_by' => (int) $row['created_by'],
            'version' => (int) $row['version'],
            'fecha_papel' => $row['fecha_papel'],
            'observaciones' => $row['observaciones'],
            'control_papel' => $row['control_papel'],
            'datos_fuente' => Json::decodeObject($row['datos_fuente_json'] === null ? null : (string) $row['datos_fuente_json']),
            'puede_editar' => $ctx->perfil === 'ADMIN' || ($ctx->perfil === 'DIGITADOR' && $ctx->usuarioId === (int) $row['created_by']),
        ];
    }

    private function rollback(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }
}
