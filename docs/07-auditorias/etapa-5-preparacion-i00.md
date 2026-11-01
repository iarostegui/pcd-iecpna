# Etapa 5 — Transición de auditoría SDD a I00 técnico
**Fecha:** 01/11/2026 · **Meta:** evitar nuevas ampliaciones funcionales y pasar a evidencias de implementación.

## Lo ya consolidado
- Regla de alcance **PCD temporal, no SGSP**; observaciones externas se analizan como propuestas, no se imponen.
- Modelo **11 tablas** con ORGANIZACION, CATALOGO y UBIGEO jerárquicos, Persona/Ficha/GC separadas y seguridad proporcional.
- Consulta de toda la funcionalidad autorizada, filtros presbiterio y/o sede; **sin ACL territorial adicional**.
- Documento exclusivamente tipo y número; sin subtipo.
- Anexos físicos y OpenAPI V4 coherentes como documentación; prototipo **V2.3 referencia de UI** y V2.2 histórico sin modificaciones.

## Auditoría de consistencia antes de I00
Prueba automatizable: [script estático](../../scripts/validar-sdd-i00.cjs) y [guía I00](../08-ia/I00-verificacion-estatica-y-handoff.md).
1. Detectar estructuras obsoletas (17 tablas, alcance_consulta, subtipo).
2. Comprobar 11 tablas, diccionario 134 columnas y OpenAPI sin esquemas huérfanos.
3. **No declarar** que la revisión estática verifica FK/CHECK, seguridad, consentimiento, backup o rendimiento.

## Hallazgos todavía abiertos
- **H-14**: fuente Excel binaria por versionar y contrastar contra inventario SHA.
- **E1-D01**: fichas de distintos presbiterios de la misma Persona.
- **E1-D02**: GC «ya existe» del papel.
- **H-23**: alta GC vinculada a Persona creada por otro digitador.
- **H-24**: mínimo de contenido para ficha.
- **H-40**: definición institucional de datos personales, retención y cierre.
- Compatibilidad MySQL/MariaDB, catálogo INEI como entrada I02, contratación/operación de hosting.

## Clasificación de situación
**ETAPA 5 DOCUMENTADA / I00 SIN GO DE MOTOR**. No se ha ejecutado ningún SQL ni frontend/backend React/PHP productivo. I00 puede avanzar con scripts/entorno de prueba, mientras las decisiones de I02/I03 se mantienen pendientes.

## Próximo gate técnico
La siguiente operación es preparar una BD de prueba, confirmar versiones del hosting y ejecutar `001_initial.sql` y `002_catalogos_calidad.sql`, inspeccionar `information_schema` y registrar resultados. No pedir nuevos módulos ni documentación que repita antecedentes; medir evidencia real.
