# SDD PCD — Auditoría y geolocalización
## Requirement AUD-01: Login
MUST registrar resultado éxito/fallo, presbiterio, usuario intentado, hora/IP y sesión cuando exista sin contraseña ni información sensible.
## Requirement AUD-02: Guardado
MUST NOT registrar alta de una nueva ficha en `auditoria_evento`: su autor y hora están en `ficha.created_by/created_at`. Al editar, MUST registrar únicamente campos cuyo valor cambió, con valores antes/después, actor ID y código/perfil congelados, hora/IP/sesión. La auditoría acompaña la transacción de PATCH.
## Requirement AUD-03: Ubicación reutilizada
MUST asociar a todos los guardados una única captura por sesión y nunca solicitarla de nuevo hasta nueva sesión; tras refresh leer snapshot del backend.
### Scenario — No disponible
GIVEN usuario deniega permiso GPS WHEN guarda ficha THEN guarda correctamente; el estado DENEGADA queda en la sesión y únicamente una edición real genera auditoría referenciando dicha sesión.
## Requirement AUD-04: Consulta protegida
Solo ADMIN MUST acceder a detalle completo de auditoría y ubicación; REPORTES solo indicadores agregados autorizados.


## AUD-05 V4 — Ubicación integrada y privacidad
MUST guardar estado y coordenadas opcionales en la fila única de `sesion`, no tabla adicional; auditoría referencia `sesion_id`. Solo ADMIN con función de auditoría autorizada puede acceder a datos de ubicación, nunca a través de consultas generales. `login_evento` y `auditoria_evento` son distintos porque un fallo previo al login no tiene sesión válida. Retención pendiente H-40.

## AUD-06 — Minimización e integridad temporal
MUST conservar `created_at/updated_at` en cada tabla. En el evento de auditoría de modificación solo campos alterados; nunca copiar ficha completa ni generar evento al crear ficha. `login_evento` continúa separado como bitácora de autenticación. GPS del navegador es telemetría no probatoria.
