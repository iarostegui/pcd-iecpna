---
estado: supersedido
reemplazado_por: docs/01-analisis/alcance-funcional.md
especificaciones_vigentes: openspec/specs/
nota: "Antecedente de auditoría; no implementar decisiones que contradigan PCD V4.1"
---

> **HISTÓRICO:** este cambio E1/E2/E3 es evidencia del proceso, no autoridad vigente para código. Para implementación leer [ALC-PCD-4.0](../../../docs/01-analisis/alcance-funcional.md), [aprobaciones](../../../docs/08-ia/APROBACIONES.md), [SQL](../../../database/migrations/001_initial.sql) y [OpenAPI](../../../docs/04-integracion/openapi.yaml). La ruta canónica de la norma está en `AGENTS.md`. **E1-D01 aprobada**, contraseña común solo UAT, presbiterios fijos sin CRUD, 11 tablas.

> **HISTÓRICO V3 (31/10/2026), NO CODIFICAR DESDE AQUÍ.** Prevalecen [SQL V4 de 11 tablas](https://github.com/iarostegui/pcd-iecpna/blob/main/database/migrations/001_initial.sql) y [decisiones vigentes](https://github.com/iarostegui/pcd-iecpna/blob/main/docs/01-analisis/decisiones-pendientes.md): consulta general por funcionalidad con filtros presbiterio/sede, documentos solo tipo+número sin subtipo, catálogo único y ubigeo separado. Cualquier regla histórica contradictoria queda sustituida.

# Cambio OpenSpec — Etapa 3: contratos y pruebas PCD
**Estado:** documentación y diseño propuestos; backend/SQL sin pruebas reales. [Auditoría E3](../../../docs/07-auditorias/etapa-3-contratos-pruebas.md).
- [proposal.md](proposal.md) — objetivo, decisiones y alcance.
- [design.md](design.md) — contrato de consulta, validación, errores y métricas.
- [tasks.md](tasks.md) — tareas y evidencia antes de declarar GO.
No modificar el prototipo V2.2 ni SGSP; respetar [ALC-PCD-3.1](../../../docs/01-analisis/alcance-funcional.md).
