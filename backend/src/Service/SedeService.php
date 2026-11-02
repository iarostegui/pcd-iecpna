<?php

declare(strict_types=1);

namespace Pcd\Service;

use Pcd\Http\HttpException;
use Pcd\Repository\IdempotenciaRepository;
use Pcd\Repository\OrganizacionRepository;
use Pcd\Support\Json;
use Pcd\Support\TextNormalizer;
use Pcd\Support\Validation;
use PDO;
use PDOException;

final class SedeService
{
    private readonly OrganizacionRepository $organizacion;
    private readonly IdempotenciaRepository $idempotencia;

    public function __construct(private readonly PDO $pdo)
    {
        $this->organizacion = new OrganizacionRepository($pdo);
        $this->idempotencia = new IdempotenciaRepository($pdo);
    }

    /**
     * @return array{items:list<array<string, mixed>>, similares:list<array<string, mixed>>, advertencia:?string}
     */
    public function search(SessionContext $context, ?string $query): array
    {
        $sedes = $this->organizacion->sedesForPresbiterio($context->presbiterioId);
        $normalizedQuery = $query === null ? '' : TextNormalizer::normalize($query);

        $items = [];
        $similares = [];

        foreach ($sedes as $sede) {
            if ($normalizedQuery === '') {
                $items[] = self::publicSede($sede);
                continue;
            }

            if (str_contains($sede['nombre_norm'], $normalizedQuery)) {
                $items[] = self::publicSede($sede);
            } elseif (TextNormalizer::similar($sede['nombre_oficial'], $query ?? '')) {
                $similares[] = self::publicSede($sede);
            }
        }

        return [
            'items' => $items,
            'similares' => $similares,
            'advertencia' => $similares === [] ? null : 'Existen sedes con nombres parecidos. Verifique que no sea la misma antes de crear otra.',
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{status:int, body:array<string, mixed>}
     */
    public function create(SessionContext $context, array $payload, ?string $idempotencyKey): array
    {
        if ($context->perfil === 'REPORTES') {
            throw new HttpException(403, 'forbidden', 'El perfil REPORTES es de solo lectura.');
        }

        if ($idempotencyKey === null || trim($idempotencyKey) === '') {
            throw new HttpException(400, 'idempotency_key_required', 'La cabecera Idempotency-Key es obligatoria.');
        }

        $idempotencyKey = trim($idempotencyKey);

        if (mb_strlen($idempotencyKey, 'UTF-8') > 110) {
            throw new HttpException(422, 'validation_error', 'Idempotency-Key supera 110 caracteres.', ['campo' => 'Idempotency-Key']);
        }

        $nombre = Validation::requiredString($payload, 'nombre_oficial', 2, 200);
        $referencia = Validation::optionalString($payload, 'ubicacion_referencia', 300);
        $payloadHash = Json::hashPayload(['nombre_oficial' => $nombre, 'ubicacion_referencia' => $referencia]);

        $existingIdempotency = $this->idempotencia->find($context->usuarioId, $idempotencyKey);

        if ($existingIdempotency !== null) {
            return $this->replay($existingIdempotency, $payloadHash);
        }

        $normalized = TextNormalizer::normalize($nombre);

        if ($normalized === '') {
            throw new HttpException(422, 'validation_error', 'El nombre de la sede no contiene caracteres comparables.', ['campo' => 'nombre_oficial']);
        }

        $sedes = $this->organizacion->sedesForPresbiterio($context->presbiterioId);
        $warnings = [];

        foreach ($sedes as $sede) {
            if ($sede['nombre_norm'] === $normalized) {
                throw new HttpException(
                    409,
                    'sede_duplicada',
                    'Ya existe una sede con el mismo nombre normalizado en este presbiterio.',
                    ['sede_id' => $sede['id'], 'nombre_oficial' => $sede['nombre_oficial']],
                );
            }

            if (TextNormalizer::similar($sede['nombre_oficial'], $nombre)) {
                $warnings[] = $sede['nombre_oficial'];
            }
        }

        try {
            $this->pdo->beginTransaction();
            $sede = $this->organizacion->createSede($context->presbiterioId, $nombre, $referencia);
            $body = self::publicSede($sede);

            if ($warnings !== []) {
                $body['advertencia'] = 'Existen sedes con nombres parecidos: ' . implode('; ', $warnings) . '. Verifique que no sea la misma.';
            }

            $this->idempotencia->store(
                $context->usuarioId,
                $idempotencyKey,
                $payloadHash,
                201,
                Json::encode($body),
            );
            $this->pdo->commit();

            return ['status' => 201, 'body' => $body];
        } catch (PDOException $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $replayed = $this->idempotencia->find($context->usuarioId, $idempotencyKey);

            if ($replayed !== null) {
                return $this->replay($replayed, $payloadHash);
            }

            if ($exception->getCode() === '23000') {
                throw new HttpException(
                    409,
                    'sede_duplicada',
                    'Ya existe una sede con el mismo nombre normalizado en este presbiterio.',
                );
            }

            throw $exception;
        }
    }

    /** @param array<string, mixed> $sede
     *  @return array<string, mixed> */
    public static function publicSede(array $sede): array
    {
        return [
            'id' => (int) $sede['id'],
            'presbiterio_id' => (int) $sede['presbiterio_id'],
            'nombre_oficial' => (string) $sede['nombre_oficial'],
            'ubicacion_referencia' => $sede['ubicacion_referencia'],
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array{status:int, body:array<string, mixed>}
     */
    private function replay(array $row, string $payloadHash): array
    {
        if (!hash_equals((string) $row['payload_hash'], $payloadHash)) {
            throw new HttpException(409, 'idempotency_conflict', 'La clave de idempotencia ya se usó con otro contenido.');
        }

        $body = Json::decodeObject($row['response_json'] === null ? null : (string) $row['response_json']);

        if ($body === null) {
            throw new HttpException(500, 'internal_error', 'La respuesta idempotente almacenada no es válida.');
        }

        return ['status' => (int) $row['response_status'], 'body' => $body];
    }
}
