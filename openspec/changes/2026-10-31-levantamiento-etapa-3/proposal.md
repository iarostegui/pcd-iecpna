---
estado: supersedido
reemplazado_por: docs/01-analisis/alcance-funcional.md
especificaciones_vigentes: openspec/specs/
nota: "Antecedente de auditoría; no implementar decisiones que contradigan PCD V4.1"
---

> **HISTÓRICO:** este cambio E1/E2/E3 es evidencia del proceso, no autoridad vigente para código. Para implementación leer [ALC-PCD-4.0](../../../../docs/01-analisis/alcance-funcional.md), [aprobaciones](../../../../docs/08-ia/APROBACIONES.md), [SQL](../../../../database/migrations/001_initial.sql) y [OpenAPI](../../../../docs/04-integracion/openapi.yaml). El enlace relativo desde estos archivos puede requerir ruta distinta según su nivel; la ruta canónica está en `AGENTS.md`. **E1-D01 aprobada**, contraseña común solo UAT, presbiterios fijos sin CRUD, 11 tablas.

> **HISTÓRICO V3 (31/10/2026), NO CODIFICAR DESDE AQUÍ.** Prevalecen [SQL V4 de 11 tablas](https://github.com/iarostegui/pcd-iecpna/blob/main/database/migrations/001_initial.sql) y [decisiones vigentes](https://github.com/iarostegui/pcd-iecpna/blob/main/docs/01-analisis/decisiones-pendientes.md): consulta general por funcionalidad con filtros presbiterio/sede, documentos solo tipo+número sin subtipo, catálogo único y ubigeo separado. Cualquier regla histórica contradictoria queda sustituida.

# Proposal E3 — Cerrar contratos y pruebas sin ampliar PCD
Observaciones H-10/11/15/16/17/20/21/22/23/24/25/33/34/37/38/39/45.
Se requiere completar DTO, filtros/roles, referencia de versión GC, matriz de trazabilidad y pruebas. Se aprovechan funciones ya previstas de consulta, administración, calidad y reportes.
**Exclusiones:** revisión pastoral, conciliación, firma, miembros nominales GC, SGSP, nueva infraestructura.
**Decisiones pendientes que E3 NO aprueba:** vinculación cross-presbiterio E1-D01, grupo preexistente E1-D02, crear ficha GC ajena H-23 y mínimo de contenido de ficha H-24. Antes de I02/I03 cerrar cada punto.
