> **HISTÓRICO V3 (31/10/2026), NO CODIFICAR DESDE AQUÍ.** Prevalecen [las 11 tablas V4](https://github.com/iarostegui/pcd-iecpna/blob/main/database/migrations/001_initial.sql) y [las decisiones actuales](https://github.com/iarostegui/pcd-iecpna/blob/main/docs/01-analisis/decisiones-pendientes.md). Sin subtipo documental ni permiso de lectura territorial; consulta general por funcionalidad con filtros opcionales.

# Delta E2 — autenticación y contexto nacional
## AUTH-05 ADDED: catálogo de login
GIVEN usuario anónimo WHEN solicita GET /presbiterios THEN recibe SOLO id/código/nombre activos, sin usuarios ni información privada.
## AUTH-06 ADDED: credencial no enumerable
GIVEN pantalla de login productivo WHEN se abre THEN código de usuario es texto tecleable, NO desplegable de nombres/cuentas. Los digitadores comparten clave operativa inicial; ADMIN/REPORTES usan contraseña independiente.
## AUTH-07 ADDED: límites de intentos
GIVEN 5 fallos por usuario o 20 por IP en 15 min WHEN insiste en autenticarse THEN servidor aplica control temporal sin divulgar existencia de cuentas; lo registra.
## AUTH-08 ADDED: expiración y revocación
GIVEN sesión inactiva 45 min o absoluta 12 h WHEN solicita operación THEN 401. GIVEN desactivación/reset de cuenta WHEN usa sesión previa THEN 401 y sesión revocada. GET /auth/me retorna CSRF a sesión válida.
## AUTH-09 PROPOSED (E2-D02): perfiles nacionales
GIVEN ADMIN o REPORTES WHEN hace login con presbiterio inicial THEN mantiene ámbito nacional **por rol**; ADMIN puede cambiar contexto explícitamente, REPORTES consulta informes nacionales sin sede; DIGITADOR no cambia presbiterio fuera de su asignación.
