<?php

declare(strict_types=1);

namespace Pcd\Controller;

use Pcd\Database\Db;
use Pcd\Http\Middleware\AuthMiddleware;
use Pcd\Http\Request;
use Pcd\Http\Response;
use Pcd\Repository\OrganizacionRepository;
use Pcd\Service\SedeService;
use Pcd\Support\Validation;

final class OrganizacionController
{
    public static function presbiterios(Request $request): void
    {
        $repository = new OrganizacionRepository(Db::fromEnv()->pdo());

        Response::json($request, 200, $repository->activePresbiterios());
    }

    public static function sedes(Request $request): void
    {
        $context = AuthMiddleware::requireSession();
        $query = $request->query['q'] ?? null;

        if ($query !== null && !is_string($query)) {
            throw Validation::invalid('q', 'El parámetro q debe ser texto.');
        }

        $service = new SedeService(Db::fromEnv()->pdo());

        Response::json($request, 200, $service->search($context, $query));
    }

    public static function createSede(Request $request): void
    {
        $context = AuthMiddleware::requireSessionAndCsrf($request);
        $service = new SedeService(Db::fromEnv()->pdo());
        $result = $service->create($context, $request->json(), $request->header('idempotency-key'));

        Response::json($request, $result['status'], $result['body']);
    }
}
