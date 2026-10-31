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

