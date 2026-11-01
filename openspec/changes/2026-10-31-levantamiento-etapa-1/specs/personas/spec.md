> **HISTÓRICO — NO USAR COMO REQUISITO VIGENTE:** este documento describe decisiones/análisis de PCD V3 de 31/10/2026. El responsable aprobó posteriormente **PCD V4: 11 tablas**, consulta **total por funcionalidad** con filtros presbiterio/sede, documentos **solo tipo+número sin subtipo**, tabla `catalogo`, `organizacion` jerárquica y `ubigeo` único. Las referencias a 17 tablas, `alcance_consulta`, `usuario_presbiterio`, subtipo u otras decisiones contradictorias **están reemplazadas**, sin alterar aquí el registro histórico. Fuente de verdad: [Decisiones actuales](../../docs/01-analisis/decisiones-pendientes.md), [SQL 11 tablas](../../database/migrations/001_initial.sql) y [specs vigentes](../../openspec/specs/README.md). En caso de enlaces relativos distintos, localizar los mismos documentos desde README principal.

# Delta PCD Personas — propuesto
## PER-03 MODIFIED — Persona documentalmente existente
MUST guardar una nueva ficha fuente en sede autorizada aunque el documento normalizado corresponda a Persona originada en otra sede, sin duplicarla ni exponer datos privados ajenos.
### E1-CA-01
GIVEN persona documental A existente WHEN digitador B registra hoja de su sede THEN se crea nueva ficha de B vinculada a Persona, no segunda Persona ni divulgación de ficha A.
## PER-07 MODIFIED — fuentes independientes
MUST preservar snapshot literal de cada hoja y creador original, sin sobrescritura silenciosa de consolidado por fichas posteriores.
## PER-08 ADDED — validación de fechas reales (H-05; aprobado)
MUST rechazar como valor fecha cualquier día inexistente (`31/02/1998` o 29/02 en año no bisiesto), tanto en UI como en PHP. El digitador corrige el dato fuera del sistema o deja el campo vacío para guardar ficha incompleta; SQL DATE solo tiene fecha real o NULL. No persistir fecha imposible como dato de tipo fecha.
### E1-CA-04
GIVEN una fecha imposible en el formulario WHEN intenta guardar THEN no se acepta ese valor; se solicita corrección o vaciado, tras lo cual puede guardar la ficha incompleta.
## PER-09 ADDED — fidelidad al Excel
MUST capturar opciones y detalles del [inventario](../../../../../docs/01-analisis/fuentes/inventario-excel-original.md) sin convertir a bloqueantes los campos marcados con asterisco en papel.

## PER-10 ADDED — revisión de fidelidad del papel (H-09; aprobado)
MUST aplicar controles técnicos (tipos, seguridad, catálogos, integridad y faltantes). MUST NOT afirmar que valida automáticamente si el nombre u otro texto coincide con lo escrito en papel: `JUANA` vs `Juan`, `JU4N4` son errores de transcripción que pueden pasar reglas de texto. **Cada presbiterio revisa manualmente fuera de PCD** y el digitador/ADMIN usa la edición auditada existente para corregir. No proceso interno de conciliación/revisión.
### E1-CA-05
GIVEN texto sintácticamente aceptable pero distinto de la ficha física WHEN se guarda THEN PCD no declara fidelidad verificada; el presbiterio realiza la comprobación externa.
