<?php

declare(strict_types=1);

namespace Pcd\Repository;

use Pcd\Support\TextNormalizer;
use PDO;
use PDOException;

final class OrganizacionRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<array{id:int, codigo:string, nombre:string}> */
    public function activePresbiterios(): array
    {
        $statement = $this->pdo->query(
            "SELECT id, codigo, nombre
             FROM organizacion
             WHERE tipo = 'PRESBITERIO' AND activo = 1 AND codigo IS NOT NULL
             ORDER BY nombre",
        );

        return array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'codigo' => (string) $row['codigo'],
                'nombre' => (string) $row['nombre'],
            ],
            $statement->fetchAll(),
        );
    }

    /** @return array<string, mixed>|null */
    public function findActivePresbiterio(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT id, codigo, nombre, tipo, padre_id, activo
             FROM organizacion WHERE id = ? AND tipo = 'PRESBITERIO' AND activo = 1 LIMIT 1",
        );
        $statement->execute([$id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** @return array<string, mixed>|null */
    public function findActiveSede(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT s.id, s.padre_id AS presbiterio_id, s.nombre, s.referencia, s.nombre_norm
             FROM organizacion s
             JOIN organizacion p ON p.id = s.padre_id AND p.tipo = 'PRESBITERIO' AND p.activo = 1
             WHERE s.id = ? AND s.tipo = 'SEDE' AND s.activo = 1 LIMIT 1",
        );
        $statement->execute([$id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @return list<array{id:int, presbiterio_id:int, nombre_oficial:string, ubicacion_referencia:?string, nombre_norm:string}>
     */
    public function sedesForPresbiterio(int $presbiterioId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT id, padre_id AS presbiterio_id, nombre, referencia, nombre_norm
             FROM organizacion
             WHERE tipo = 'SEDE' AND padre_id = ? AND activo = 1
             ORDER BY nombre",
        );
        $statement->execute([$presbiterioId]);

        return array_map([$this, 'mapSede'], $statement->fetchAll());
    }

    /** @return array{id:int, presbiterio_id:int, nombre_oficial:string, ubicacion_referencia:?string, nombre_norm:string} */
    public function createSede(int $presbiterioId, string $nombre, ?string $referencia): array
    {
        $nombreNorm = TextNormalizer::normalize($nombre);

        $statement = $this->pdo->prepare(
            "INSERT INTO organizacion (tipo, padre_id, codigo, nombre, nombre_norm, referencia, activo)
             VALUES ('SEDE', ?, NULL, ?, ?, ?, 1)",
        );
        $statement->execute([$presbiterioId, $nombre, $nombreNorm, $referencia]);

        $sede = $this->findActiveSede((int) $this->pdo->lastInsertId());

        if ($sede === null) {
            throw new PDOException('La sede recién creada no pudo recuperarse.');
        }

        return $this->mapSede($sede);
    }

    /** @param array<string, mixed> $row
     *  @return array{id:int, presbiterio_id:int, nombre_oficial:string, ubicacion_referencia:?string, nombre_norm:string} */
    public function mapSede(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'presbiterio_id' => (int) $row['presbiterio_id'],
            'nombre_oficial' => (string) $row['nombre'],
            'ubicacion_referencia' => $row['referencia'] === null ? null : (string) $row['referencia'],
            'nombre_norm' => (string) $row['nombre_norm'],
        ];
    }
}
