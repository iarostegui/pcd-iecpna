---
estado: supersedido
reemplazado_por: docs/01-analisis/alcance-funcional.md
especificaciones_vigentes: openspec/specs/
nota: "Antecedente de auditoría; no implementar decisiones que contradigan PCD V4.1"
---

> **HISTÓRICO:** este cambio E1/E2/E3 es evidencia del proceso, no autoridad vigente para código. Para implementación leer [ALC-PCD-4.0](../../../docs/01-analisis/alcance-funcional.md), [aprobaciones](../../../docs/08-ia/APROBACIONES.md), [SQL](../../../database/migrations/001_initial.sql) y [OpenAPI](../../../docs/04-integracion/openapi.yaml). La ruta canónica de la norma está en `AGENTS.md`. **E1-D01 aprobada**, contraseña común solo UAT, presbiterios fijos sin CRUD, 11 tablas.

> **HISTÓRICO V3 (31/10/2026), NO CODIFICAR DESDE AQUÍ.** Prevalecen [las 11 tablas V4](https://github.com/iarostegui/pcd-iecpna/blob/main/database/migrations/001_initial.sql) y [las decisiones actuales](https://github.com/iarostegui/pcd-iecpna/blob/main/docs/01-analisis/decisiones-pendientes.md). Sin subtipo documental ni permiso de lectura territorial; consulta general por funcionalidad con filtros opcionales.

# Tasks — E2
| ID | Tarea | Estado |
|---|---|---|
| E2-T01 | Leer evidencia original H-03/04/07/12/13/18/28/32/35 y contrastar SDD | **HECHO** |
| E2-T02 | Definir controles mínimos H-03/32 y riesgos de clave compartida | **DISEÑO PROPUESTO** |
| E2-T03 | Definir catálogo inicial/normalización/criticidad desde papel y prototipo | **DISEÑO PROPUESTO** |
| E2-T04 | Definir ámbito nacional, digitador y consulta | **E2-D01/D02 PENDIENTES** |
| E2-T05 | Actualizar SQL, API/OpenAPI, modelos, SDD y pruebas | **DOCUMENTACIÓN SINCRONIZADA COMO PROPUESTA; requiere aceptación y pruebas reales** |
| E2-T06 | Ejecutar SQL en versión real de MySQL/MariaDB del hosting | **NO EJECUTADO** |
| E2-T07 | Verificar OpenAPI con herramienta de lint y pruebas de seguridad | **NO EJECUTADO** |
| E2-T08 | Obtener conformidad de riesgo de clave común E2-D03 | **PENDIENTE** |
| E2-T09 | Ajustar reporte de respuesta al auditor (sin presentarlo como final) | **ACTUALIZADO CON MATRIZ E2, aún borrador** |
| E2-T10 | Preparar I01 en tareas SDD con contratos listos y pruebas previstas | **ESPECIFICADO PARA PLANIFICACIÓN; E2-D02 y ensayos bloquean GO** |

No modificar el prototipo HTML V2.2 ni implementar código productivo hasta completar planificación correspondiente.

## Decisiones del responsable (31/10/2026)
E2-D01 = **APROBADO CON MODIFICACIÓN** (consulta `GENERAL|PRESBITERIO` por usuario, independiente de edición). E2-D02 = **APROBADO** (ADMIN/REPORTES nacionales). E2-D03 = **ACEPTADO FUNCIONALMENTE** (clave común digitadores y mitigaciones; riesgo no eliminado). Catálogo ubigeo = **ENTRADA DE I02**. H-13 = **APROBADO**. La verificación real de SQL y API permanece pendiente.
