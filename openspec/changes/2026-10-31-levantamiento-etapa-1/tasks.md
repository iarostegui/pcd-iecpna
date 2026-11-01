> **HISTÓRICO — NO USAR COMO REQUISITO VIGENTE:** este documento describe decisiones/análisis de PCD V3 de 31/10/2026. El responsable aprobó posteriormente **PCD V4: 11 tablas**, consulta **total por funcionalidad** con filtros presbiterio/sede, documentos **solo tipo+número sin subtipo**, tabla `catalogo`, `organizacion` jerárquica y `ubigeo` único. Las referencias a 17 tablas, `alcance_consulta`, `usuario_presbiterio`, subtipo u otras decisiones contradictorias **están reemplazadas**, sin alterar aquí el registro histórico. Fuente de verdad: [Decisiones actuales](../../docs/01-analisis/decisiones-pendientes.md), [SQL 11 tablas](../../database/migrations/001_initial.sql) y [specs vigentes](../../openspec/specs/README.md). En caso de enlaces relativos distintos, localizar los mismos documentos desde README principal.

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
