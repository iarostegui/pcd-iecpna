# Andamiaje ejecutable — requisitos verificables

## AND-01 — Salud y dependencias
GIVEN el proceso HTTP activo WHEN se solicita `GET /api/v1/health` THEN responde 200 con `ok=true`, `request_id`, servicio y versión, **sin consultar MySQL ni exponer el entorno**.
GIVEN MySQL caído WHEN se solicita `GET /api/v1/ready` THEN responde 503 con `ok=false` y `database.status=down`; GIVEN MySQL operativo THEN responde 200 con `status=ready` y `database.status=up`.

## AND-02 — Sobre de error uniforme
GIVEN una ruta inexistente, un método no permitido o un cuerpo JSON inválido WHEN se procesa la solicitud THEN responde 404/405/400 con `{error:{code,message},request_id}` y sin trazas.

## AND-03 — CSRF en escrituras protegidas
GIVEN una ruta marcada como CSRF (p. ej. logout) WHEN se envía sin token válido THEN responde 403 `csrf_invalid`; el login pre-sesión no exige token.

## AND-04 — Aprovisionamiento idempotente
GIVEN una BD con los cuatro presbiterios del seed WHEN se ejecuta `bootstrap-provision.php --mode=uat` dos veces THEN existen 24 DIGITADORES (6 por presbiterio), 1 ADMIN y 1 REPORTES, sin duplicados y con hashes Argon2id verificables.

## AND-05 — Secretos fuera de Git
GIVEN el repositorio público WHEN se revisan archivos y CI THEN no contiene contraseñas, `.env` ni hashes reales; las credenciales provienen del entorno.

## AND-06 — Runner con estados
GIVEN el backend en ejecución WHEN se ejecuta `tests/api/run.php` THEN cada verificación reporta PASS, FAIL, BLOCKED o NOT_IMPLEMENTED y el comando falla solo si hay FAIL.

## AND-07 — Frontend construible
GIVEN Node 22 WHEN se ejecuta `npm ci && npm run build` THEN se genera `dist/` sin Node residente y el cliente HTTP adjunta CSRF cuando existe token.

## AND-08 — Límites de alcance
GIVEN P01 WHEN se revisan rutas y código THEN no hay login, captura, consulta, reportes, SGSP ni cambios en SQL/OpenAPI/prototipo.
