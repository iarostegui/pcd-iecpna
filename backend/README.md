# PCD — backend PHP 8.2

[↑ Índice principal](../README.md)

**Estado:** I01 implementado en desarrollo/CI. Autenticación real contra MySQL, sesiones persistidas en `sesion`, CSRF, límites de intentos, presbiterios/sedes, contexto ADMIN y geolocalización opcional de una captura por sesión. **I02–I05 permanecen pendientes** (captura, consulta, reportes y administración). No existe GO productivo.

## Requisitos
- PHP 8.2 con `pdo_mysql`, `mbstring`, `json` y, preferentemente, `intl` para NFKD.
- Composer 2.
- MySQL 8 / MariaDB compatible con `database/migrations/001_initial.sql`.
- Argon2id en producción. Solo desarrollo/CI sin Argon2id puede usar bcrypt costo 12 con
  `PCD_ALLOW_BCRYPT_FALLBACK=true` y `PCD_ENV` distinto de `production` (CA-114).

## Instalación
```bash
cd backend
composer install
cp .env.example .env   # editar localmente; .env está ignorado por Git
```

## Ejecutar en desarrollo
```bash
php -S 127.0.0.1:8080 -t backend/public backend/public/router.php
curl -s http://127.0.0.1:8080/api/v1/health
```

## Endpoints I01
| Método y ruta | Auth / CSRF | Estado |
|---|---|---|
| `GET /api/v1/health` | Público | Liveness; no consulta MySQL. |
| `GET /api/v1/ready` | Público | Readiness 200/503 según MySQL. |
| `POST /api/v1/auth/login` | Público | Login con presbiterio, código tecleado y contraseña; 200/401/422/429. |
| `GET /api/v1/auth/me` | Sesión | Contexto, CSRF y vencimientos; 401 sin sesión. |
| `POST /api/v1/auth/logout` | Sesión + CSRF | 204; invalida `sesion` y registra LOGOUT. |
| `POST /api/v1/auth/ubicacion` | Sesión + CSRF | Una captura por sesión; 200/409/422. |
| `GET /api/v1/presbiterios` | Público | Solo `id`, `codigo`, `nombre` de LIM/TRU/CHI/IQU activos. |
| `GET /api/v1/sedes` | Sesión | Sedes activas del presbiterio de la sesión; `q` y `similares`. |
| `POST /api/v1/sedes` | Sesión + CSRF + `Idempotency-Key` | Alta normalizada; 201/409. |
| `PUT /api/v1/contexto/presbiterio` | ADMIN + CSRF | Cambio explícito; anula sede y audita. |
| `PUT /api/v1/contexto/sede` | Sesión + CSRF | Valida pertenencia al presbiterio. |

Las rutas de I02–I06 (`/catalogos`, `/ubigeos`, personas, GC, reportes, admin) no se amplían en I01.

## Sesión y seguridad
- Cookie `__Host-pcd_sid` en producción; `HttpOnly`, `SameSite=Lax`, `Secure` con HTTPS/producción y `Path=/`.
- `session_ref_hash = SHA-256(cookie)` en `sesion`; el valor de cookie no se almacena en claro.
- Regeneración de ID al autenticar, CSRF en memoria recuperado de `/auth/me`, rotación al login y logout real.
- Inactividad 45 min y vida absoluta 12 h configurables; `last_seen` se actualiza como máximo cada 60 s.
- Revocación por `auth_version`, cuenta inactiva o logout. Login genérico para no enumerar cuentas.
- 5 fallos por cuenta y 20 por IP en 15 min; `login_evento` registra OK/FALLIDO/BLOQUEADO/LOGOUT sin contraseñas.

## Variables principales
`PCD_DB_*`, `PCD_SESSION_NAME`, `PCD_COOKIE_SECURE`, `PCD_SESSION_IDLE_MINUTES`,
`PCD_SESSION_ABSOLUTE_HOURS`, `PCD_SESSION_TOUCH_SECONDS`, `PCD_LOGIN_MAX_ACCOUNT`,
`PCD_LOGIN_MAX_IP`, `PCD_LOGIN_WINDOW_MINUTES`, `PCD_ALLOW_BCRYPT_FALLBACK` y credenciales UAT
`PCD_UAT_DIGITADOR_PASSWORD`, `PCD_ADMIN_PASSWORD`, `PCD_REPORTES_PASSWORD` (nunca en Git).

## Estructura
- `public/index.php`: front controller; `public/.htaccess` para Apache/cPanel.
- `src/Http`: request, response, router, errores, CORS y middleware de autenticación/CSRF.
- `src/Repository` y `src/Service`: usuarios, organización, sesión, login_evento, idempotencia,
  auditoría, autenticación y sedes.
- `src/Support`: normalización NFKD, validación, reloj UTC, JSON canónico e IP.
- `src/Security`: `PhpSession`, `Csrf`, `PasswordHasher`.
- `tests/`: PHPUnit unitario e integración MySQL.
- `../tests/api/i01.php`: runner HTTP de CA-01…CA-14, CA-56…CA-60, CA-61…CA-67, CA-91/92/94,
  CA-110/112/113/114.

## Pruebas
```bash
cd backend
vendor/bin/phpunit --configuration phpunit.xml
cd ..
php tests/api/i01.php --base-url=http://127.0.0.1:8081/api/v1 --json=i01-evidencia.json
```
La integración PHPUnit se ejecuta solo si existe `PCD_DB_HOST`; el runner I01 exige MySQL real,
seed, aprovisionamiento UAT y variables de credenciales. En local sin MySQL se reporta BLOCKED,
nunca PASS.

## Despliegue cPanel
DocumentRoot del subdominio → `backend/public`. Sin Node ni procesos residentes. Configurar
variables de entorno en el panel, mantener `.env` fuera del repositorio y usar HTTPS con
`PCD_SESSION_NAME=__Host-pcd_sid` y `PCD_COOKIE_SECURE=true`. **No desplegar con datos reales
antes de H-40/E-07 y el GO institucional.**
