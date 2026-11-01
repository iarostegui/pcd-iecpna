> **HISTÓRICO V3 (31/10/2026), NO CODIFICAR DESDE AQUÍ.** Prevalecen [SQL V4 de 11 tablas](https://github.com/iarostegui/pcd-iecpna/blob/main/database/migrations/001_initial.sql) y [decisiones vigentes](https://github.com/iarostegui/pcd-iecpna/blob/main/docs/01-analisis/decisiones-pendientes.md): consulta general por funcionalidad con filtros presbiterio/sede, documentos solo tipo+número sin subtipo, catálogo único y ubigeo separado. Cualquier regla histórica contradictoria queda sustituida.

# Delta E2 — ámbito de consulta y criticidad
## REP-07 PROPOSED (E2-D01): consulta por presbiterio
GIVEN DIGITADOR autenticado en presbiterio A WHEN consulta THEN ve solo fichas de A y los datos de esa fuente, aunque Persona consolidada tenga fichas de B; puede editar solo las creadas por su cuenta. NO PII ni GPS de B. ADMIN nacional y REPORTES agregado.
## REP-08 ADDED: calidad computada
GIVEN ADMIN desactiva campo crítico permitido WHEN se recalcula reporte THEN aplica la configuración vigente sin depender de estado materializado obsoleto. PRES/SEDE y líder GC son relaciones estructurales y no se pueden desactivar.
