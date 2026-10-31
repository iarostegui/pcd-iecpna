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

