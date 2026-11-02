<?php

declare(strict_types=1);

namespace Pcd\Service;

use Pcd\Config\Env;
use Pcd\Http\HttpException;
use Pcd\Repository\AuditoriaRepository;
use Pcd\Repository\LoginEventoRepository;
use Pcd\Repository\OrganizacionRepository;
use Pcd\Repository\SesionRepository;
use Pcd\Repository\UsuarioRepository;
use Pcd\Security\PasswordHasher;
use Pcd\Support\Clock;
use PDO;
use Throwable;

final class AuthService
{
    private readonly UsuarioRepository $usuarios;
    private readonly OrganizacionRepository $organizacion;
    private readonly SesionRepository $sesiones;
    private readonly LoginEventoRepository $loginEventos;
    private readonly AuditoriaRepository $auditoria;

    public function __construct(private readonly PDO $pdo)
    {
        $this->usuarios = new UsuarioRepository($pdo);
        $this->organizacion = new OrganizacionRepository($pdo);
        $this->sesiones = new SesionRepository($pdo);
        $this->loginEventos = new LoginEventoRepository($pdo);
        $this->auditoria = new AuditoriaRepository($pdo);
    }

    /**
     * Verifica credenciales y aplica los límites de intentos. No crea sesión.
     *
     * @return array{usuario:array<string, mixed>, presbiterio:array<string, mixed>}
     */
    public function authenticate(int $presbiterioId, string $codigo, string $password, ?string $ip, ?string $userAgent): array
    {
        $presbiterio = $this->organizacion->findActivePresbiterio($presbiterioId);

        if ($presbiterio === null) {
            throw new HttpException(422, 'invalid_presbiterio', 'El presbiterio seleccionado no está disponible.');
        }

        $codigo = mb_strtoupper(trim($codigo), 'UTF-8');

        if ($codigo === '') {
            throw new HttpException(422, 'validation_error', 'El código de usuario es obligatorio.', ['campo' => 'usuario']);
        }

        $usuario = $this->usuarios->findByCodigo($codigo);
        $this->assertRateLimit(
            $codigo,
            $usuario === null ? null : (int) $usuario['id'],
            $presbiterioId,
            $ip,
            $userAgent,
        );

        $hash = $usuario === null ? PasswordHasher::dummyHash() : (string) $usuario['password_hash'];
        $passwordOk = PasswordHasher::verify($password, $hash);

        $userOk = $usuario !== null && (int) $usuario['activo'] === 1;
        $presbiterioOk = $usuario !== null
            && ($usuario['perfil'] !== 'DIGITADOR' || (int) $usuario['presbiterio_id'] === $presbiterioId);

        if (!$passwordOk || !$userOk || !$presbiterioOk) {
            $this->loginEventos->record([
                'usuario_id' => $usuario === null ? null : (int) $usuario['id'],
                'presbiterio_id' => $presbiterioId,
                'codigo' => $codigo,
                'resultado' => 'FALLIDO',
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);

            throw new HttpException(401, 'invalid_credentials', 'Presbiterio, usuario o clave incorrectos.');
        }

        return ['usuario' => $usuario, 'presbiterio' => $presbiterio];
    }

    /**
     * Crea la sesión de servidor para un usuario ya autenticado.
     *
     * @param array<string, mixed> $usuario
     */
    public function createSession(
        array $usuario,
        int $presbiterioId,
        string $sessionId,
        ?string $ip,
        ?string $userAgent,
    ): SessionContext {
        $hash = hash('sha256', $sessionId);
        $now = Clock::sql();

        $this->pdo->beginTransaction();

        try {
            $id = $this->sesiones->create([
                'usuario_id' => (int) $usuario['id'],
                'presbiterio_id' => $presbiterioId,
                'sede_id' => null,
                'hash' => $hash,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'fecha_login' => $now,
                'last_seen' => $now,
                'auth_version' => (int) $usuario['auth_version'],
            ]);

            $this->loginEventos->record([
                'usuario_id' => (int) $usuario['id'],
                'presbiterio_id' => $presbiterioId,
                'codigo' => (string) $usuario['codigo'],
                'resultado' => 'OK',
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }

        return $this->loadById($id);
    }

    public function revokeSessionId(string $sessionId): void
    {
        $this->sesiones->markRevokedByHash(hash('sha256', $sessionId), Clock::sql());
    }

    public function current(string $sessionId): SessionContext
    {
        $hash = hash('sha256', $sessionId);
        $row = $this->sesiones->findByHash($hash);

        if ($row === null) {
            throw new HttpException(401, 'unauthenticated', 'Se requiere una sesión autenticada.');
        }

        $this->validateRow($row);
        $this->touch($row);

        return $this->buildContext($row);
    }

    public function logout(SessionContext $context, ?string $ip, ?string $userAgent): void
    {
        $this->pdo->beginTransaction();

        try {
            $this->sesiones->markLogout($context->sesionId, Clock::sql());
            $this->loginEventos->record([
                'usuario_id' => $context->usuarioId,
                'presbiterio_id' => $context->presbiterioId,
                'codigo' => $context->usuarioCodigo,
                'resultado' => 'LOGOUT',
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function changeSede(SessionContext $context, int $sedeId): SessionContext
    {
        $sede = $this->organizacion->findActiveSede($sedeId);

        if ($sede === null || (int) $sede['presbiterio_id'] !== $context->presbiterioId) {
            throw new HttpException(403, 'sede_fuera_de_contexto', 'La sede no pertenece al presbiterio autenticado.');
        }

        $this->sesiones->updateSede($context->sesionId, $sedeId);

        return $this->loadById($context->sesionId);
    }

    public function changePresbiterio(
        SessionContext $context,
        int $presbiterioId,
        string $requestId,
        ?string $ip,
    ): SessionContext {
        if ($context->perfil !== 'ADMIN') {
            throw new HttpException(403, 'forbidden', 'Solo ADMIN puede cambiar el presbiterio de contexto.');
        }

        $presbiterio = $this->organizacion->findActivePresbiterio($presbiterioId);

        if ($presbiterio === null) {
            throw new HttpException(422, 'invalid_presbiterio', 'El presbiterio seleccionado no está disponible.');
        }

        $changed = $presbiterioId !== $context->presbiterioId;

        $this->pdo->beginTransaction();

        try {
            $this->sesiones->updatePresbiterio($context->sesionId, $presbiterioId);

            if ($changed) {
                $this->auditoria->record(
                    $requestId,
                    $context->sesionId,
                    $context->usuarioId,
                    $context->usuarioCodigo,
                    $context->perfil,
                    'CAMBIO_PRESBITERIO',
                    'SESION',
                    $context->sesionId,
                    $ip,
                    ['presbiterio_id' => $context->presbiterioId, 'sede_id' => $context->sedeId],
                    ['presbiterio_id' => $presbiterioId, 'sede_id' => null],
                );
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }

        return $this->loadById($context->sesionId);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function storeLocation(SessionContext $context, array $payload): array
    {
        $estado = (string) $payload['estado'];
        $latitud = $payload['latitud'] ?? null;
        $longitud = $payload['longitud'] ?? null;
        $precision = $payload['precision_m'] ?? null;

        if ($estado !== 'OK') {
            $latitud = null;
            $longitud = null;
            $precision = null;
        }

        $won = $this->sesiones->storeLocationIfEmpty(
            $context->sesionId,
            $estado,
            $latitud === null ? null : (float) $latitud,
            $longitud === null ? null : (float) $longitud,
            $precision === null ? null : (float) $precision,
            Clock::sql(),
        );

        $stored = $this->sesiones->location($context->sesionId);

        if ($stored === null) {
            throw new HttpException(500, 'internal_error', 'No se pudo recuperar la ubicación guardada.');
        }

        if (!$won) {
            throw new HttpException(
                409,
                'ubicacion_ya_capturada',
                'La ubicación de esta sesión ya fue registrada.',
                ['ubicacion' => self::locationPayload($stored)],
            );
        }

        return self::locationPayload($stored);
    }

    /** @param array<string, mixed> $row
     *  @return array<string, mixed> */
    public static function locationPayload(array $row): array
    {
        $captured = $row['geo_capturada_en'] === null ? null : Clock::iso((string) $row['geo_capturada_en']);

        return [
            'estado' => $row['geo_estado'],
            'latitud' => $row['geo_latitud'] === null ? null : (float) $row['geo_latitud'],
            'longitud' => $row['geo_longitud'] === null ? null : (float) $row['geo_longitud'],
            'precision_m' => $row['geo_precision_m'] === null ? null : (float) $row['geo_precision_m'],
            'capturada_en' => $captured,
            'recibida_en' => $captured,
        ];
    }

    private function assertRateLimit(
        string $codigo,
        ?int $usuarioId,
        int $presbiterioId,
        ?string $ip,
        ?string $userAgent,
    ): void {
        $windowMinutes = Env::int('PCD_LOGIN_WINDOW_MINUTES', 15);
        $maxAccount = Env::int('PCD_LOGIN_MAX_ACCOUNT', 5);
        $maxIp = Env::int('PCD_LOGIN_MAX_IP', 20);
        $since = Clock::sql(Clock::now()->modify('-' . $windowMinutes . ' minutes'));

        $accountFailures = $this->loginEventos->failedCountByAccount(
            $usuarioId,
            $codigo,
            $presbiterioId,
            $since,
        );
        $ipFailures = $this->loginEventos->failedCountByIp($ip, $since);

        if ($accountFailures < $maxAccount && $ipFailures < $maxIp) {
            return;
        }

        $this->loginEventos->record([
            'usuario_id' => $usuarioId,
            'presbiterio_id' => $presbiterioId,
            'codigo' => $codigo,
            'resultado' => 'BLOQUEADO',
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);

        throw new HttpException(
            429,
            'too_many_attempts',
            'Demasiados intentos de acceso. Intente nuevamente más tarde.',
        );
    }

    /** @param array<string, mixed> $row */
    private function validateRow(array $row): void
    {
        if ($row['fecha_logout'] !== null || $row['revocada_en'] !== null) {
            throw new HttpException(401, 'session_invalid', 'La sesión ya no es válida.');
        }

        if ((int) $row['usuario_activo'] !== 1) {
            $this->sesiones->markRevoked((int) $row['sesion_id'], Clock::sql());
            throw new HttpException(401, 'session_revoked', 'La sesión fue revocada.');
        }

        if ((int) $row['auth_version_al_ingreso'] !== (int) $row['usuario_auth_version']) {
            $this->sesiones->markRevoked((int) $row['sesion_id'], Clock::sql());
            throw new HttpException(401, 'session_revoked', 'La sesión fue revocada.');
        }

        if ($row['perfil'] === 'DIGITADOR'
            && (int) ($row['usuario_presbiterio_id'] ?? 0) !== (int) $row['presbiterio_id']) {
            $this->sesiones->markRevoked((int) $row['sesion_id'], Clock::sql());
            throw new HttpException(401, 'session_revoked', 'La sesión fue revocada.');
        }

        $now = time();
        $lastSeen = Clock::timestamp((string) ($row['last_seen'] ?? $row['fecha_login']));
        $loginAt = Clock::timestamp((string) $row['fecha_login']);
        $idleSeconds = Env::int('PCD_SESSION_IDLE_MINUTES', 45) * 60;
        $absoluteSeconds = Env::int('PCD_SESSION_ABSOLUTE_HOURS', 12) * 3600;

        if ($now - $lastSeen > $idleSeconds || $now - $loginAt > $absoluteSeconds) {
            $this->sesiones->markRevoked((int) $row['sesion_id'], Clock::sql());
            throw new HttpException(401, 'session_expired', 'La sesión expiró. Inicie sesión nuevamente.');
        }
    }

    /** @param array<string, mixed> $row */
    private function touch(array &$row): void
    {
        $touchSeconds = max(0, Env::int('PCD_SESSION_TOUCH_SECONDS', 60));
        $lastSeen = Clock::timestamp((string) ($row['last_seen'] ?? $row['fecha_login']));

        if (time() - $lastSeen < $touchSeconds) {
            return;
        }

        $now = Clock::sql();
        $threshold = Clock::sql(Clock::now()->modify('-' . $touchSeconds . ' seconds'));
        $this->sesiones->touch((int) $row['sesion_id'], $now, $threshold);
        $row['last_seen'] = $now;
    }

    /** @param array<string, mixed> $row */
    private function buildContext(array $row): SessionContext
    {
        return new SessionContext(
            (int) $row['sesion_id'],
            (int) $row['usuario_id'],
            (string) $row['usuario_codigo'],
            (string) $row['usuario_nombre'],
            (string) $row['perfil'],
            (int) $row['presbiterio_id'],
            (string) $row['presbiterio_nombre'],
            $row['sede_id'] === null ? null : (int) $row['sede_id'],
            $row['sede_nombre'] === null ? null : (string) $row['sede_nombre'],
            (string) $row['fecha_login'],
            (string) ($row['last_seen'] ?? $row['fecha_login']),
            $row['geo_estado'] === null ? null : (string) $row['geo_estado'],
        );
    }

    private function loadById(int $sessionId): SessionContext
    {
        $row = $this->sesiones->findById($sessionId);

        if ($row === null) {
            throw new HttpException(500, 'internal_error', 'No se pudo recuperar la sesión.');
        }

        return $this->buildContext($row);
    }
}
