# API PCD V3.1 — contrato funcional
Servidor: mismo origen `https://pcd.unobdata.com/api/v1`. REST JSON UTF-8. Contrato formal: `openapi.yaml`. No exponer credenciales/ubicación en URL.

## Modelo de error común
```json
{"error":{"code":"VALIDATION_ERROR","message":"Revise los campos","details":{"campo":["Motivo"]}},"request_id":"uuid"}
```
HTTP: 400 formato, 401 no autenticado, 403 sin ámbito, 404 no revelable, 409 conflicto/versión/idempotencia, 422 campo inválido, 429 throttling, 500 interno genérico.

## Autenticación y contexto
- `POST /auth/login` — presbiterio + usuario + password; emite cookie HttpOnly segura + CSRF, registra login; ADMIN/REPORTES con cobertura nacional.
- `POST /auth/logout`, `GET /auth/me`.
- `POST /auth/ubicacion` — máximo **una vez por sesión**, estado/lat/lon/precisión/captured_at; idempotente y recuperable tras refresh.
- `GET /presbiterios`, `GET /sedes?presbiterio_id=`, `POST /sedes`, `PUT /contexto/sede` — no confiar en presbiterio arbitrario del cliente.

## Personas, GC y documentos de fuente
- `GET /personas/duplicados?tipo_documento=...&numero_documento=...`: coincidencia sin exponer identidad fuera de ámbito.
- `POST /personas`: crea identidad+ficha (se permiten faltantes críticos); header `Idempotency-Key`.
- `POST /personas/{id}/fichas`: vincula nueva ficha impresa a identidad ya existente sin duplicar Persona.
- `GET /personas`: filtros y paginación; `GET /personas/{id}`: ámbito.
- `PATCH /personas/{id}`: cambios documentados; `version` en petición para control de concurrencia.
- `POST /personas/{id}/grupos`: GC nuevo o relación existente conforme a reglas, **solo Persona persistida**.
- `GET /grupos`, `PATCH /grupos/{id}`: cambios autorizados, no sustitución destructiva de líder.

## Administración, calidad, reportes, auditoría
- `GET/POST /admin/usuarios`, `PATCH /admin/usuarios/{id}`: solo ADMIN.
- `GET /config/campos-criticos`, `PUT /config/campos-criticos`: lectura permitida a perfiles autorizados, escritura ADMIN.
- `GET /reportes/avance`: filtros presbiterio/sede/digitador; última digitación.
- `GET /reportes/calidad`: conteos faltantes, posibles duplicados, autores; acceso ADMIN/REPORTES y datos minimizados.
- `GET /auditoria`: solo ADMIN.
- Prohibido endpoint de **migración al SGSP** en esta versión.

## Reglas de contrato
- Mutaciones con cookie sesión requieren header CSRF.
- Idempotency-Key en POST de creación.
- N.º ficha y datos críticos son opcionales en payload de captura.
- Toda respuesta exitosa confirma COMMIT con IDs internos persistidos.
- Fecha papel `YYYY-MM-DD` en API; UI traduce `DD/MM/AAAA`.

