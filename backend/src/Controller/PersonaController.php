<?php

declare(strict_types=1);

namespace Pcd\Controller;

use Pcd\Database\Db;
use Pcd\Http\Middleware\AuthMiddleware;
use Pcd\Http\Request;
use Pcd\Http\Response;
use Pcd\Service\PersonaService;
use Pcd\Support\Validation;

/**
 * I02: alta Persona/Ficha y lecturas necesarias para reutilización.
 * PATCH se reserva a I04 (edición auditada), sin simularla.
 */
final class PersonaController
{
    public static function create(Request $request): void
    {
        $context = AuthMiddleware::requireSessionAndCsrf($request);
        $service = new PersonaService(Db::fromEnv()->pdo());
        $result = $service->create($context, $request->json(), $request->header('idempotency-key'));
        Response::json($request, $result['status'], $result['body']);
    }

    /** @param array<string,string> $params */
    public static function append(Request $request, array $params): void
    {
        $context = AuthMiddleware::requireSessionAndCsrf($request);
        $id = Validation::requiredInt($params, 'id');
        $result = (new PersonaService(Db::fromEnv()->pdo()))->create(
            $context, $request->json(), $request->header('idempotency-key'), $id
        );
        // OpenAPI anexo devuelve FichaDetalle; los IDs de la operación son
        // metadatos adicionales para conservar trazabilidad del reintento.
        $ficha = $service->ficha($context, (int) $result['body']['ficha_id']);
        Response::json($request, $result['status'], array_merge($ficha, $result['body']));
    }

    /** @param array<string,string> $params */
    public static function detail(Request $request, array $params): void
    {
        $context = AuthMiddleware::requireSession();
        $id = Validation::requiredInt($params, 'id');
        $data = (new PersonaService(Db::fromEnv()->pdo()))->detail($context, $id);
        Response::json($request, 200, $data);
    }

    /** @param array<string,string> $params */
    public static function ficha(Request $request, array $params): void
    {
        $context = AuthMiddleware::requireSession();
        $id = Validation::requiredInt($params, 'id');
        $data = (new PersonaService(Db::fromEnv()->pdo()))->ficha($context, $id);
        Response::json($request, 200, $data);
    }

    public static function list(Request $request): void
    {
        $context = AuthMiddleware::requireSession();
        $result = (new PersonaService(Db::fromEnv()->pdo()))->list($context, $request->query);
        Response::json($request, 200, $result);
    }
}
