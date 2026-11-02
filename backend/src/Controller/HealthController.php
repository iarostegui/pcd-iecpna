<?php

declare(strict_types=1);

namespace Pcd\Controller;

use Pcd\Config\Env;
use Pcd\Database\Db;
use Pcd\Http\Request;
use Pcd\Http\Response;
use Throwable;

final class HealthController
{
    /**
     * GET /api/v1/health — endpoint operativo del andamiaje (no es negocio).
     * No expone datos personales ni detalles de conexión.
     *
     * @param array<string, string> $params
     */
    public static function show(Request $request, array $params = []): void
    {
        $database = 'down';

        try {
            if (Db::fromEnv()->ping()) {
                $database = 'up';
            }
        } catch (Throwable) {
            $database = 'down';
        }

        Response::json($request, 200, [
            'ok' => true,
            'request_id' => $request->requestId,
            'status' => 'ok',
            'service' => 'pcd-api',
            'version' => 'p01',
            'env' => Env::get('PCD_ENV', 'local'),
            'database' => ['status' => $database],
        ]);
    }
}
