> **HISTÓRICO — NO USAR COMO REQUISITO VIGENTE:** este documento describe decisiones/análisis de PCD V3 de 31/10/2026. El responsable aprobó posteriormente **PCD V4: 11 tablas**, consulta **total por funcionalidad** con filtros presbiterio/sede, documentos **solo tipo+número sin subtipo**, tabla `catalogo`, `organizacion` jerárquica y `ubigeo` único. Las referencias a 17 tablas, `alcance_consulta`, `usuario_presbiterio`, subtipo u otras decisiones contradictorias **están reemplazadas**, sin alterar aquí el registro histórico. Fuente de verdad: [Decisiones actuales](../../docs/01-analisis/decisiones-pendientes.md), [SQL 11 tablas](../../database/migrations/001_initial.sql) y [specs vigentes](../../openspec/specs/README.md). En caso de enlaces relativos distintos, localizar los mismos documentos desde README principal.

# Delta PCD Grupo Crecer — propuesto
## GC-02 MODIFIED — ¿grupo ya existe?
MUST registrar Sí/No del papel sin exigir localizar previamente una identidad GC en la base PCD. Cada hoja GC se preserva por separado.
### E1-CA-06
GIVEN formulario con grupo ya existe=Sí WHEN no hay grupo previo en PCD THEN se guarda la ficha con ID propio y valor Sí.
## GC-03 MODIFIED — coincidencia no fusiona
MUST advertir grupo posiblemente repetido por sede/nombre/líder sin reemplazar líder ni fusionar dos fichas sin confirmación.
## GC-06 ADDED — incidencias sin workflow
MUST mostrar problemas de calidad y duplicidad en informes y permitir corrección autorizada auditada; sin sistema de tickets ni aprobaciones de supervisor.

## GC-07 ADDED — solo líderes y datos agregados (aprobado)
MUST registrar únicamente el **líder previamente registrado como Persona** y datos generales del GC, incluidos tipo, nombre, lugar, estado, capacidad y **cantidad aproximada** de integrantes. MUST NOT crear integrantes nominales ni vincular Personas a GC como miembros porque esos datos **no existen en las fichas**.
### E1-CA-11
GIVEN un GC con «12 integrantes aproximadamente» WHEN se registra THEN persiste el conteo 12, sin crear 12 Personas ni 12 relaciones de membresía.
