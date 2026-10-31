# SEC-PCD-3.1 · Seguridad y auditoría
## Autenticación y perfiles
- `DIGITADOR`: seis cuentas iniciales por cada presbiterio existente, aun con sedes incompletas; acceso exclusivo a ámbito autorizado. Contraseña compartida NO forzada a cambiar; decisión de negocio, con riesgo residual documentado.
- `ADMIN`: una cuenta nacional de administración/edición/parametrización; toda acción se atribuye a ADMIN, nunca al digitador original.
- `REPORTES`: una cuenta nacional de lectura, sin escrituras de fichas, catálogos, calidad ni usuarios.
- Almacenar contraseñas únicamente en hash con `password_hash`; **nunca guardar contraseña en Git ni persistir en claro**. Valor inicial se inyecta durante provisión.
- Cookie PHP `HttpOnly`, `Secure`, SameSite, CSRF para mutaciones, login throttling y bloqueo/retardo transitorio. No almacenar PII en localStorage.

## Geolocalización — regla exacta V2.2
Una llamada `navigator.geolocation.getCurrentPosition` por **sesión** después de login. Se captura resultado (`OK`, `DENEGADA`, `NO_DISPONIBLE`, `ERROR`), lat/lon opcionales, precisión y `captured_at`. Persistir una única `geo_sesion`; reusar snapshot en cada operación, incluso tras refrescar página o cambiar sede. No solicitar por cada guardado, no bloquear si denegado, no usar GPS para autorización. Guardar `geo_sesion_id` más copia de estado/fecha relevantes en auditoría, según diseño.

## Registro de acceso
`login_event`: fecha, username solicitado, presbiterio, resultado, IP, user-agent resumido, session_id si éxito. En falla no registrar contraseña ni información sensible. `sesion`: usuario, presbiterio, sede, inicio/cierre, last_seen, último contexto.

## Edición trazable
Persistir `created_by` inmutable, `updated_by`, `created_at`, `updated_at`; `auditoria_evento`: acción, recurso, ID, antes/después estructurado con minimización y control, sesión, IP, geo, request ID. Evitar exposición en usuario REPORTES de datos personales y coordenadas.

## Seguridad de registros cruzados
Consultas de duplicados de otras sedes/presbiterios deben informar **existencia o coincidencia genérica** sin revelar ficha de otro ámbito a un digitador. ADMIN ve detalle. Autorización en cada operación y revalidación transaccional de sede/presbiterio.

## Privacidad
La pertenencia religiosa y las coordenadas son datos sensibles/delicados: informar finalidad de captura, permisos restrictivos, TLS, respaldo cifrado si disponible, retención a definir, acceso a auditoría solo ADMIN. Un GPS aproximado no prueba presencia física del digitador.

