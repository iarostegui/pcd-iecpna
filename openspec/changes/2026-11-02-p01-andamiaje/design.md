# P01 — Diseño del andamiaje

## Decisiones técnicas D01–D08
| ID | Decisión | Implementación |
|---|---|---|
| D01 | PHP 8.2 / MySQL 8 | `composer.json` exige PHP >=8.2; CI usa MySQL 8; SQL utf8mb4. |
| D02 | Composer y PSR-4 | `Pcd\` → `backend/src`, `Pcd\Tests\` → `backend/tests`, lockfile versionado. |
| D03 | Router explícito | `Pcd\Http\Router` con métodos y rutas declaradas; 404/405 con sobre JSON. |
| D04 | PDO, utf8mb4, transacciones | `Pcd\Database\Db`: DSN `charset=utf8mb4`, `ERRMODE_EXCEPTION`, prepares reales, `transaction()`. |
| D05 | Contrato JSON uniforme | Éxito según OpenAPI; error `{error:{code,message,details?},request_id}`; `X-Request-Id`. |
| D06 | Entorno y secretos | Variables `PCD_*`; `.env` ignorado por Git; `.env.example` sin valores reales. |
| D07 | PHPUnit + runner HTTP | `backend/phpunit.xml`; `tests/api/run.php` sin dependencias, estados explícitos. |
| D08 | React/TS/Vite/Fetch | `frontend/` con `tsc -b && vite build`, cliente `fetch` centralizado y CSRF en memoria. |

## Componentes backend
- `Config\Env`: carga `.env` sin sobrescribir entorno; helpers `require/int/bool`.
- `Http\Request`: método, ruta, headers, cuerpo, query y `request_id`.
- `Http\Response`: JSON con `Content-Type: application/json; charset=utf-8`, cabeceras de seguridad y sin caché.
- `Http\Router`: rutas estáticas/dinámicas; HEAD usa la ruta GET; 404/405.
- `Http\ErrorHandler`: convierte `HttpException` y `Throwable` en sobre de error; registra en `error_log`, nunca en la respuesta.
- `Security\Csrf`: sesión `HttpOnly/SameSite`, token aleatorio y comparación en tiempo constante.
- `Http\Middleware\CsrfMiddleware`: solo rutas marcadas (p. ej. logout), nunca el login pre-sesión.
- `Http\Middleware\CorsMiddleware`: orígenes permitidos por entorno; preflight 204.
- `Controller\HealthController`: `GET /api/v1/health` con estado de BD.
- `Controller\PlaceholderController`: 501 uniforme para rutas del contrato que pertenecen a I01.
- `Provisioning\Plan` / `Provisioning\Provisioner`: plan puro y ejecución transaccional idempotente.

## API del andamiaje
| Ruta | Estado | Nota |
|---|---|---|
| `GET /api/v1/health` | Implementada | Operativa, fuera de OpenAPI; no es funcionalidad de negocio. |
| `POST /api/v1/auth/login` | Placeholder 501 | Contrato reservado a I01. |
| `GET /api/v1/auth/me` | Placeholder 501 | Contrato reservado a I01. |
| `POST /api/v1/auth/logout` | Placeholder 501 con CSRF | Verifica middleware antes de responder. |
| `GET /api/v1/presbiterios` | Placeholder 501 | Contrato reservado a I01/catálogos. |

## Seguridad
- Sin contraseñas en Git, logs o respuestas; aprovisionamiento solo por entorno.
- CSRF preparado y aplicado a escrituras autenticadas; el token se emitirá en I01.
- Cabeceras `X-Content-Type-Options`, `Cache-Control: no-store`, CORS restringido.
- Errores sin trazas, rutas internas ni datos personales.

## Despliegue cPanel
- DocumentRoot del API en `backend/public` con `.htaccess` de reescritura.
- Frontend compilado a `frontend/dist`; se publica como estático.
- Sin Node ni procesos residentes en producción; PHP-FPM/Apache del hosting.

## Riesgos
- MySQL 8 no disponible en el entorno local de P01: las pruebas de motor se ejecutan en CI y se reportan como BLOCKED en local.
- El login real y la emisión de CSRF pertenecen a I01; el andamiaje no debe confundirse con autenticación implementada.
