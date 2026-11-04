<?php

declare(strict_types=1);

namespace Pcd\Controller;

use Pcd\Database\Db;
use Pcd\Http\Middleware\AuthMiddleware;
use Pcd\Http\Request;
use Pcd\Http\Response;
use Pcd\Support\Validation;
use PDO;

/** Catálogos y ubigeos reales precargados; no genera registros ficticios. */
final class CatalogoController
{
    public static function catalogos(Request $request): void
    {
        AuthMiddleware::requireSession();
        $categoria = $request->query['categoria'] ?? null;
        if ($categoria !== null && (!is_string($categoria) || strlen($categoria) > 70)) {
            throw Validation::invalid('categoria', 'Categoría no válida.');
        }
        $pdo = Db::fromEnv()->pdo();
        $stmt = $pdo->prepare('SELECT categoria, codigo, etiqueta, orden FROM catalogo
            WHERE activo = 1 AND (? IS NULL OR categoria = ?)
            ORDER BY categoria, orden, etiqueta LIMIT 500');
        $stmt->execute([$categoria, $categoria]);
        Response::json($request, 200, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function ubigeos(Request $request): void
    {
        AuthMiddleware::requireSession();
        $tipo = $request->query['tipo'] ?? null;
        $padre = $request->query['padre_codigo'] ?? null;
        if ($tipo !== null && !in_array($tipo, ['DEPARTAMENTO', 'PROVINCIA', 'DISTRITO'], true)) {
            throw Validation::invalid('tipo', 'Nivel de ubigeo no válido.');
        }
        if ($padre !== null && (!is_string($padre) || preg_match('/^[0-9]{2}([0-9]{2})?$/D', $padre) !== 1)) {
            throw Validation::invalid('padre_codigo', 'Código padre inválido.');
        }
        $stmt = Db::fromEnv()->pdo()->prepare(
            'SELECT codigo, tipo, padre_codigo, nombre FROM ubigeo
             WHERE activo = 1 AND (? IS NULL OR tipo = ?)
             AND (? IS NULL OR padre_codigo = ?)
             ORDER BY codigo LIMIT 3000'
        );
        $stmt->execute([$tipo, $tipo, $padre, $padre]);
        Response::json($request, 200, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
