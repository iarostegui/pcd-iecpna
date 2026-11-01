> **HISTÓRICO V3 (31/10/2026), NO CODIFICAR DESDE AQUÍ.** Prevalecen [SQL V4 de 11 tablas](https://github.com/iarostegui/pcd-iecpna/blob/main/database/migrations/001_initial.sql) y [decisiones vigentes](https://github.com/iarostegui/pcd-iecpna/blob/main/docs/01-analisis/decisiones-pendientes.md): consulta general por funcionalidad con filtros presbiterio/sede, documentos solo tipo+número sin subtipo, catálogo único y ubigeo separado. Cualquier regla histórica contradictoria queda sustituida.

# Checklist E3
| ID | Actividad | Estado |
|---|---|---|
| E3-01 | Analizar hallazgos H-10/11/15/16/17/20…25/33/34/37/38/39/45 bajo ALC-PCD | DOCUMENTADO |
| E3-02 | Completar OpenAPI: DTO y respuestas, filtros, usuario y detalles GC | DOCUMENTADO, lint formal pendiente |
| E3-03 | Crear matriz de pruebas asignadas a iteraciones, casos para fallos | DOCUMENTADO, ejecución pendiente |
| E3-04 | Reconciliar RN→CU→UI→API→SQL→CA incluyendo RN 27/28 | DOCUMENTADO, UAT pendiente |
| E3-05 | Verificar compatibilidad API+SQL+DTO con código real | NO EJECUTADO |
| E3-06 | Cerrar E1-D01/E1-D02/H-23/H-24 funcionalmente | PENDIENTE |
| E3-07 | Ejecutar lint YAML OpenAPI y pruebas automatizadas | NO EJECUTADO |
| E3-08 | Respuesta acumulativa al auditor y etapa 4 de controles productivos | EN CURSO |
