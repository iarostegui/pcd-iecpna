# SDD PCD — Consultas y reportes
## Requirement REP-01: Avance
MUST reportar por presbiterio/sede fecha último día digitado, Personas y GC digitados, digitador y cantidades; nacional para ADMIN/REPORTES.
## Requirement REP-02: Calidad
MUST filtrar registros incompletos conforme a campos críticos dinámicos, posibles duplicados, autor original, sede y fechas; permitir al digitador localizar propios incompletos para corrección.
## Requirement REP-03: Consulta protegida
MUST ofrecer lupa por documento/nombre/N.º ficha y paginación; respetar ámbito de consulta y no revelar datos de otras sedes o presbiterios sin permiso.
## Requirement REP-04: Solo lectura
REPORTES MUST consultar indicadores/agrupados sin poder modificar registros, usuarios o configuración; limitar PII y GPS por defecto.
## Requirement REP-05: Sin proceso de migración
MUST NO ofrecer acción de exportación/importación de datos hacia SGSP en esta fase; migración es proyecto posterior.

## Requirement REP-06: Alcance de calidad frente al papel (aprobado E1)
MUST calcular indicadores objetivos de datos faltantes y posibles coincidencias documentales, no afirmar haber verificado exactitud de transcripción. **Revisión manual por cada presbiterio fuera de PCD**; no tableros de supervisión, colas ni estados de conciliación. La edición posterior autorizada sí queda auditada.

## Requisitos E2 — H-12/H-18
**REP-07 PROPUESTO E2-D01:** DIGITADOR consulta fichas del propio presbiterio, edita solo propias y nunca recibe PII de otras jurisdicciones por Persona compartida; ADMIN nacional; REPORTES agregados nacionales. **Pendiente ratificación del detalle nominal dentro del presbiterio.**
**REP-08:** críticos iniciales del prototipo [matriz](../../docs/03-base-datos/campos-criticos.md); campos estructurales no desactivables; la calidad es cálculo con parámetros vigentes, no `marca_calidad` histórica fija. No revisión automática contra papel.
