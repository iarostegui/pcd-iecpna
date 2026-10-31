# SDD PCD — Administración
## Requirement ADM-01: Perfil ADMIN
MUST ser nacional y poder crear/activar/desactivar usuarios, gestionar sedes y cambiar parámetros, consultar y editar cualquier ficha con su propio actor ADMIN.
## Requirement ADM-02: Usuarios iniciales
MUST crear seis usuarios por presbiterio existente independientemente de sedes disponibles, más un ADMIN nacional y un REPORTES nacional. La clave común no se almacena en texto plano ni en Git y no se exige cambio al primer login.
## Requirement ADM-03: Calidad configurable
MUST mostrar campos críticos de Persona/GC con interruptores editables por ADMIN en tiempo real. Cambios recalculan conteos de calidad sin alterar integridad ni volver obligatorios los guardados.
### Scenario — Campo desmarcado
GIVEN campo nacimiento crítico WHEN ADMIN lo desmarca THEN el reporte deja de contabilizarlo como faltante pero no modifica fichas.
## Requirement ADM-04: Restricciones
MUST rechazar escrituras de perfil REPORTES y DIGITADOR en administración; registrar cambios en auditoría.

## Requirement ADM-05 — sesiones y rol nacional (E2)
ADMIN puede desactivar usuarios; **desactivar o cambiar credencial debe revocar todas sus sesiones** (versión/autorización y registro). ADMIN y REPORTES tienen claves distintas de digitadores y ámbitos nacionales por perfil (E2-D02 a ratificar). No se muestran nombres de usuarios en endpoints públicos.

## Requirement ADM-06: Configurar ámbito de consulta (APROBADO E2-D01)
Solo ADMIN MUST poder asignar `GENERAL` / `PRESBITERIO` a una cuenta; aplicar cambios desde PHP, auditar antes/después y revocar/revalidar sesiones en cambios de permisos. No modificar rol ni permisos de **edición** implícitamente. `GET /admin/usuarios` y `PATCH /admin/usuarios/{id}` mantienen este campo.
