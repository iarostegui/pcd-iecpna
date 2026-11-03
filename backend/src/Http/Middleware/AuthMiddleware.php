<?php

declare(strict_types=1);

namespace Pcd\Http\Middleware;

use Pcd\Database\Db;
use Pcd\Http\HttpException;
use Pcd\Http\Request;
use Pcd\Security\Csrf;
use Pcd\Security\PhpSession;
use Pcd\Service\AuthService;
use Pcd\Service\SessionContext;

final class AuthMiddleware
{
    public static function service(): AuthService
    {
        return new AuthService(Db::fromEnv()->pdo());
    }

    public static function requireSession(): SessionContext
    {
        try {
            return self::service()->current(PhpSession::id());
        } catch (HttpException $exception) {
            if ($exception->status === 401) {
                Csrf::reset();
                PhpSession::destroy();
            }

            throw $exception;
        }
    }

    public static function requireCsrf(Request $request): void
    {
        if (!Csrf::validate($request->header('x-csrf-token'))) {
            throw new HttpException(403, 'csrf_invalid', 'Token CSRF ausente o inválido.');
        }
    }

    public static function requireSessionAndCsrf(Request $request): SessionContext
    {
        $context = self::requireSession();
        self::requireCsrf($request);

        return $context;
    }
}
