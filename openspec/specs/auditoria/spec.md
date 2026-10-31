# SDD PCD — Auditoría y geolocalización
## Requirement AUD-01: Login
MUST registrar resultado éxito/fallo, presbiterio, usuario intentado, hora/IP y sesión cuando exista sin contraseña ni información sensible.
## Requirement AUD-02: Guardado
MUST registrar evento al crear/editar con actor autenticado, autor inicial preservado, recurso/ID, before/after, hora servidor, IP, sesión y estado/geo/precisión/fecha de captura. Auditoría de cambios dentro de la misma transacción.
## Requirement AUD-03: Ubicación reutilizada
MUST asociar a todos los guardados una única captura por sesión y nunca solicitarla de nuevo hasta nueva sesión; tras refresh leer snapshot del backend.
### Scenario — No disponible
GIVEN usuario deniega permiso GPS WHEN guarda ficha THEN guarda correctamente y auditoría indica DENEGADA con coordenadas null.
## Requirement AUD-04: Consulta protegida
Solo ADMIN MUST acceder a detalle completo de auditoría y ubicación; REPORTES solo indicadores agregados autorizados.

