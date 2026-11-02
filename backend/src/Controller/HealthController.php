<?php

declare(strict_types=1);

namespace Pcd\Controller;

use Pcd\Database\Db;
use Pcd\Http\Request;
use Pcd\Http\Response;
use Throwable;

/**
 * Salud operativa del andamiaje (no es funcionalidad de negocio):
 * - /health: liveness del proceso HTTP; no depende de MySQL ni expone entorno.
 * - /ready: readiness de dependencias; 200 si MySQL responde, 503 si no.
 */
final class HealthController
{
    /** @param array<string, string> $params */
    public static function liveness(Request $request, array $params = []): void
    {
        Response::json($request, 200, self::livenessPayload($request->requestId));
    }

    /** @param array<string, string> $params */
    public static function readiness(Request $request, array $params = []): void
    {
        $databaseUp = false;

        try {
            $databaseUp = Db::fromEnv()->ping();
        } catch (Throwable) {
            $databaseUp = false;
        }

        [$status, $payload] = self::readinessPayload($request->requestId, $databaseUp);
        Response::json($request, $status, $payload);
    }

    /** @return array<string, mixed> */
    public static function livenessPayload(string $requestId): array
    {
        return [
            'ok' => true,
            'request_id' => $requestId,
            'status' => 'ok',
            'service' => 'pcd-api',
            'version' => 'p01',
        ];
    }

    /**
     * @return array{0:int, 1:array<string, mixed>}
     */
    public static function readinessPayload(string $requestId, bool $databaseUp): array
    {
        $status = $databaseUp ? 200 : 503;

        return [$status, [
            'ok' => $databaseUp,
            'request_id' => $requestId,
            'status' => $databaseUp ? 'ready' : 'not_ready',
            'service' => 'pcd-api',
            'version' => 'p01',
            'database' => ['status' => $databaseUp ? 'up' : 'down'],
        ]];
    }
}
