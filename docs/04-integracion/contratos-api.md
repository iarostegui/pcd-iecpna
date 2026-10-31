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


## Corrección de ficha individual
`GET /fichas/{id}` y `PATCH /fichas/{id}` operan sobre ficha fuente (`datos_fuente_json`, observaciones). `PATCH` exige `version` y autor/ADMIN; no reemplaza silenciosamente Persona consolidada.

## Contratos y riesgos de Etapa 2 (H-03/04/07/12/13/18/28/32/35)
- **Público previo a login:** `GET /presbiterios` responde exclusivamente id/código/nombre activos. **Nunca** lista de cuentas; usuario se escribe.
- **Credenciales:** digitadores con clave operativa común inicial (decisión conservada), cuentas ADMIN/REPORTES con claves distintas; 5 fallos/usuario o 20/IP en 15 min → control temporal configurable. **Aceptación del riesgo residual E2-D03 pendiente**.
- **Sesión:** `GET /auth/me` autenticado devuelve usuario/perfil/contexto y **token CSRF** para permitir refresh; inactividad 45 min, vida absoluta 12 h (configurables); desactivación/reset de usuario revoca sesiones.
- **Ámbito:** digitador consulta fichas de su presbiterio y edita las propias; no expone PII de otros presbiterios aunque Persona esté enlazada. ADMIN cambia contexto mediante `PUT /contexto/presbiterio` explícito; REPORTES informes nacionales sin sede. **E2-D01/D02 requieren ratificación.**
- **Catálogos:** `GET /catalogos?dominio=` entrega código/etiqueta de opciones verificadas; ubigeo oficial pendiente, no inventar.
- **Identidad:** `tipo_documento_detalle` para OTRO; **clave_identidad** nullable y única solo cuando el documento normalizado es confiable, ceros preservados. [Reglas](../03-base-datos/catalogos-e-identidad.md).
- **Calidad:** `campo_critico` incluye etiqueta, ruta y desactivable; las relaciones técnicas no se desactivan; informe recalculado con parámetros vigentes. [Matriz](../03-base-datos/campos-criticos.md).
- **GC:** no existe segundo `lider_persona_id`; el líder se obtiene de `ficha_captura.persona_id`. Prohibida ficha tipo PERSONA en operación GC. No integrantes nominales.
- **Seguridad:** consultas con 403/404/401 apropiados según exposición; mutaciones CSRF obligatorias, auditar siempre. **No afirmar implementación ni pruebas**.

**H-03/E2-D03:** la clave común operativa se limita a digitadores, no a cuentas nacionales. La autenticación no puede mostrar listado de cuentas. **H-13:** documento OTRO sin subtipo no genera clave de identidad única. **H-35:** GC obtiene líder solo de ficha.persona_id. Estas decisiones están en SQL/seguridad y requieren pruebas reales.

## Actualización Etapa 3 — API V3.2
[OpenAPI](openapi.yaml) es la referencia: incluye **modelos de respuesta de éxito y error**, lectura con GENERAL/PRESBITERIO, filtros de consulta y reportes, `GET /grupos/{id}`, administración de usuarios con ámbito de consulta y reinicio de acceso seguro. El líder GC y su versión provienen de la **ficha GC**. Controles de seguridad/idempotencia se verifican en PHP, no se confía en filtros React. El reporte de modificaciones atribuye actores reales mediante `auditoria_evento`.
**Pendiente de decisión:** E1-D01, E1-D02, H-23, H-24. **No implementar** conciliación pastoral, supervisor, exportación SGSP ni miembros nominales GC.
