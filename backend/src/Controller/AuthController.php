<?php

declare(strict_types=1);

namespace Pcd\Controller;

use Pcd\Database\Db;
use Pcd\Http\Middleware\AuthMiddleware;
use Pcd\Http\Request;
use Pcd\Http\Response;
use Pcd\Security\Csrf;
use Pcd\Security\PhpSession;
use Pcd\Service\AuthService;
use Pcd\Support\Ip;
use Pcd\Support\Validation;

final class AuthController
{
    public static function login(Request $request): void
    {
        $payload = $request->json();
        $presbiterioId = Validation::requiredInt($payload, 'presbiterio_id');
        $codigo = Validation::requiredString($payload, 'usuario', 1, 80);
        $password = Validation::requiredString($payload, 'password', 1, 200, false);

        $service = new AuthService(Db::fromEnv()->pdo());
        $result = $service->authenticate(
            $presbiterioId,
            $codigo,
            $password,
            Ip::fromGlobals(),
            Ip::userAgent(),
        );

        PhpSession::start();
        $previousSessionId = PhpSession::id();

        if ($previousSessionId !== '') {
            $service->revokeSessionId($previousSessionId);
        }

        PhpSession::regenerate();
        Csrf::rotateToken();

        $context = $service->createSession(
            $result['usuario'],
            $presbiterioId,
            PhpSession::id(),
            Ip::fromGlobals(),
            Ip::userAgent(),
        );

        Response::json($request, 200, $context->toAuthMe(Csrf::token()));
    }

    public static function me(Request $request): void
    {
        $context = AuthMiddleware::requireSession();

        Response::json($request, 200, $context->toAuthMe(Csrf::token()));
    }

    public static function logout(Request $request): void
    {
        $context = AuthMiddleware::requireSessionAndCsrf($request);
        AuthMiddleware::service()->logout($context, Ip::fromGlobals(), Ip::userAgent());

        Csrf::reset();
        PhpSession::destroy();

        Response::empty($request, 204);
    }

    public static function location(Request $request): void
    {
        $context = AuthMiddleware::requireSessionAndCsrf($request);
        $payload = $request->json();
        $estado = Validation::requiredEnum($payload, 'estado', ['OK', 'DENEGADA', 'NO_DISPONIBLE', 'ERROR']);
        Validation::requiredDateTime($payload, 'capturada_en');

        $latitud = null;
        $longitud = null;
        $precision = null;

        if ($estado === 'OK') {
            $latitud = Validation::optionalFloat($payload, 'latitud', -90.0, 90.0);
            $longitud = Validation::optionalFloat($payload, 'longitud', -180.0, 180.0);

            if ($latitud === null || $longitud === null) {
                throw Validation::invalid('latitud', 'La ubicación OK requiere latitud y longitud.');
            }

            $precision = Validation::optionalFloat($payload, 'precision_m', 0.0, 100000000.0);
        }

        $location = AuthMiddleware::service()->storeLocation($context, [
            'estado' => $estado,
            'latitud' => $latitud,
            'longitud' => $longitud,
            'precision_m' => $precision,
        ]);

        Response::json($request, 200, $location);
    }
}
