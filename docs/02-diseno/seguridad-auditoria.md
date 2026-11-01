# SEC-PCD-4.0 — Seguridad y auditoría proporcionadas a PCD

**Alcance:** [PCD oficial](../01-analisis/alcance-funcional.md) · [Esquema 11 tablas](../03-base-datos/modelo-logico.md). Aplicación temporal, pero protege los datos personales.

## Identidad y roles
DIGITADOR: seis cuentas iniciales por presbiterio, un **presbiterio operativo** por cuenta (`usuario.presbiterio_id`); clave inicial común sin cambio forzado, hash PHP. ADMIN y REPORTES: cuentas nacionales con secretos distintos de digitadores. **Riesgo residual de suplantación conocido y aceptado funcionalmente**, sin eliminar deber de mitigar.

El login pide presbiterio, código de usuario **tecleado** (sin listado de usuarios públicos) y contraseña; el selector de presbiterio muestra exclusivamente registros activos de `organizacion` tipo PRESBITERIO.

## Diferenciar consulta de escritura
El permiso de **acceso a la funcionalidad de consulta** permite ver el conjunto general de registros de esa función. Los filtros de presbiterio y/o sede **no son permisos**, son herramientas de búsqueda; no hay `alcance_consulta` ni tabla de asignaciones territoriales para lectura.

**Escritura:** DIGITADOR crea fichas en su presbiterio/sede y edita solo sus fichas; ADMIN edita según facultades nacionales; REPORTES ninguna. Prohibido inferir permiso de edición a partir de acceso a consulta o de un ID suministrado por el cliente.

## Sesiones y GPS
`sesion` almacena fecha login/logout, versión de credenciales, revocación, última actividad y **geolocalización única opcional** (`geo_estado` + coordenadas/precisión/fecha). Tras DENEGADA/ERROR/NO_DISPONIBLE se conserva el estado y no se vuelve a pedir GPS en esa sesión. La ausencia no bloquea. No existe tabla `geo_sesion`.

- Cookie `HttpOnly; Secure; SameSite=Lax`, regenerar ID PHP al autenticar; HTTPS. CSRF por sesión recuperable en `GET /auth/me`, nunca en localStorage.
- Propuesta operativa: 45 minutos inactividad y 12 horas vida absoluta, configurables; revocar sesiones tras logout, desactivar cuenta o reset (comparar `usuario.auth_version`).
- Mitigación de abuso inicial: 5 fallos por cuenta y 20 por IP en 15 minutos, bloqueo temporal sin enumerar usuarios, registrando `login_evento`; revisar en pruebas que no produzca DoS a usuarios tras IP compartida.
- Guardar `session_ref_hash`, no identificador de cookie en claro. No registrar contraseñas, tokens, PII ni GPS completo en URLs, consola o logs ordinarios.

## Auditoría e integridad
`auditoria_evento` registra actor, sesión, fecha, IP, acción y antes/después bajo acceso ADMIN; referencia `sesion` para GPS cuando existe. `login_evento` captura fallos incluso sin sesión. `idempotencia` previene duplicados y asegura mismo resultado en reintento idéntico. Fichas mantienen `created_by` inmutable. Los reportes se calculan de datos y `catalogo`, sin tablas de seguimiento de validadores.

## Documentos y datos personales
Solo `tipo_documento` y `numero_documento`; sin subtipo. Normalizar DNI/CE sin perder ceros; OTRO como coincidencia no concluyente, no fusionar automáticamente. La exactitud frente al papel es revisión manual **externa** por cada presbiterio.

## Condiciones previas a producción
**H-40 sigue pendiente**: decidir aviso/finalidad, base jurídica, responsables, plazo de conservación GPS/datos/logs/backups, acceso y borrado de cierre. Ver [Etapa 4](../07-auditorias/etapa-4-produccion-y-privacidad.md). La duración temporal **no** exime protección de datos ni pruebas en hosting.
