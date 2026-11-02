---
estado: supersedido
reemplazado_por: docs/01-analisis/alcance-funcional.md
especificaciones_vigentes: openspec/specs/
nota: "Antecedente de auditoría; no implementar decisiones que contradigan PCD V4.1"
---

> **HISTÓRICO:** este cambio E1/E2/E3 es evidencia del proceso, no autoridad vigente para código. Para implementación leer [ALC-PCD-4.0](../../../../docs/01-analisis/alcance-funcional.md), [aprobaciones](../../../../docs/08-ia/APROBACIONES.md), [SQL](../../../../database/migrations/001_initial.sql) y [OpenAPI](../../../../docs/04-integracion/openapi.yaml). El enlace relativo desde estos archivos puede requerir ruta distinta según su nivel; la ruta canónica está en `AGENTS.md`. **E1-D01 aprobada**, contraseña común solo UAT, presbiterios fijos sin CRUD, 11 tablas.

> **HISTÓRICO — NO USAR COMO REQUISITO VIGENTE:** este documento describe decisiones/análisis de PCD V3 de 31/10/2026. El responsable aprobó posteriormente **PCD V4: 11 tablas**, consulta **total por funcionalidad** con filtros presbiterio/sede, documentos **solo tipo+número sin subtipo**, tabla `catalogo`, `organizacion` jerárquica y `ubigeo` único. Las referencias a 17 tablas, `alcance_consulta`, `usuario_presbiterio`, subtipo u otras decisiones contradictorias **están reemplazadas**, sin alterar aquí el registro histórico. Fuente de verdad: [Decisiones V4](https://github.com/iarostegui/pcd-iecpna/blob/main/docs/01-analisis/decisiones-pendientes.md), [SQL V4](https://github.com/iarostegui/pcd-iecpna/blob/main/database/migrations/001_initial.sql) y [OpenSpec actual](https://github.com/iarostegui/pcd-iecpna/blob/main/openspec/specs/README.md). En caso de enlaces relativos distintos, localizar los mismos documentos desde README principal.

# Tareas y estados — Etapa 1
| Tarea | Estado |
|---|---|
| Extraer y contrastar Excel original, documentar hojas/filas/opciones y SHA | HECHO |
| Versionar binario XLSX exacto en GitHub | PENDIENTE |
| Documentar H-05 validación tipada, H-09 revisión manual por presbiterio y H-08 solo líderes GC | HECHO; decisiones funcionales aprobadas |
| Aprobar E1-D01/D02; E1-D03 ya aprobado | PENDIENTE solo D01/D02 |
| Completar esquema de ficha con valores de entrada válidos o ausentes, sin persistir fechas imposibles | PENDIENTE |
| Alinear datos conceptual/lógico/físico, SQL, ER, API y pruebas | PENDIENTE |
| Ejecutar pruebas I02/I03 en aplicación real | NO INICIADO |
| Mantener informe final al auditor por todas las etapas | EN CURSO |
**I00** habilitada para preparación técnica independiente; **I02/I03 bloqueadas** hasta cierre verificable.
