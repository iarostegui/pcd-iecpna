# SDD PCD — Autenticación / sesión
## Requirement AUTH-01: Login por presbiterio
MUST solicitar presbiterio de catálogo, usuario y contraseña; backend verifica cuenta activa y permiso de presbiterio y registra resultado del login.
### Scenario — Digitador válido
GIVEN usuario activo asignado a presbiterio A WHEN ingresa A y credenciales correctas THEN inicia sesión A y accede al selector Sede.
### Scenario — Ámbito distinto
GIVEN usuario de A WHEN intenta ingresar en presbiterio B THEN login rechazado sin revelar datos privados.
## Requirement AUTH-02: Cuentas iniciales
MUST crear seis cuentas digitador por cada presbiterio catalogado, un ADMIN y un REPORTES nacionales. Contraseña común sin cambio forzado, con hash seguro y provisión protegida.
## Requirement AUTH-03: Ubicación única por sesión
MUST solicitar una única captura GPS opcional al login; no repetir al guardar, editar, cambiar sede o recargar; registrar estado OK/DENEGADA/NO_DISPONIBLE/ERROR y reutilizar snapshot en cada operación.
### Scenario — Diez guardados
GIVEN sesión con geo obtenida WHEN se guardan diez fichas THEN las diez auditorías referencian misma geo_sesion y navegador no solicita otra ubicación.
## Requirement AUTH-04: Logout
MUST revocar sesión, registrar evento y limpiar credenciales en navegador sin conservar PII en localStorage.

## Requirements AUTH-05…09 — controles E2
**AUTH-05 (H-04):** permitir `GET /presbiterios` anónimo, solo nombre/código/ID activos. Usuario **escrito** en input, no enumeración pública.
**AUTH-06 (H-03):** seis digitadores por presbiterio con contraseña inicial común sin cambio forzado; ADMIN/REPORTES con secretos individuales distintos, hash. Riesgo de suplantación pendiente E2-D03.
**AUTH-07 (H-03):** aplicar límites por cuenta/IP (5/20 intentos en 15 minutos como valores iniciales propuestos), sin divulgar cuentas, con bitácora.
**AUTH-08 (H-32):** sesión inactiva 45 minutos/absoluta 12 h como defaults propuestos; `/auth/me` devuelve CSRF autenticado; desactivación/reset revoca sesiones.
**AUTH-09 (H-28; E2-D02 PENDIENTE):** ADMIN/REPORTES nacionales por rol; ADMIN cambia presbiterio explícitamente y vuelve a seleccionar sede para escritura; REPORTES nacional sin sede obligatoria. No codificar como decisión aprobada antes de ratificar.
