# Requisitos de consultas y reportes PCD V4.0

## Consulta operativa general
Un usuario autorizado para utilizar la funcionalidad **consulta la totalidad de registros disponibles de esa funcionalidad**, con posibilidad de filtrar por **Presbiterio y/o Sede**, de manera independiente. Puede buscar por documento (solo tipo+número), nombre/apellidos y número de ficha opcional; añadir digitador, rango de fechas, tipo de ficha, incompletitud y posible coincidencia cuando corresponda.

**No** existe permiso por registro/presbiterio para lectura, ni parámetro `alcance_consulta`. La selección de presbiterio/sede del login es para **registrar fichas**, no para restringir búsquedas. Seguridad obliga a que el usuario esté autenticado y autorizado para la funcionalidad; **no** habilita operaciones de modificación.

- DIGITADOR consulta las funciones permitidas, **edita exclusivamente sus fichas**. ADMIN consulta/edita de acuerdo con su rol. REPORTES solo lectura de sus informes autorizados.
- Paginación backend 25 por página, máximo 50; sin descargar datasets completos al abrir pantalla.
- Datos personales para consulta en pantalla acorde a finalidad; **no incluir coordenadas GPS ni datos de auditoría sensibles**.
- Filtros combinables Presbiterio/Sede validan que sede pertenezca al presbiterio si se indican ambos.
- Datos se consultan mediante `persona`, `ficha`, `grupo_crecer` y `organizacion`, no desde tablas antiguas.

## Reporte A — Avance
Personas y GC capturados por presbiterio/sede, fechas de guardado, último digitador, últimos cambios. Altas contadas por autor inicial.

## Reporte B — Productividad
Por digitador, presbiterio y sede en período: fichas nuevas, correcciones, última actividad. Las **modificaciones** se asignan al usuario actor de `auditoria_evento`, no al creador original.

## Reporte C — Calidad
Indicadores computables de fichas incompletas y campos críticos desde `catalogo` con `categoria=CAMPO_CRITICO_PERSONA/GC`. Los parámetros pueden cambiar durante campaña sin editar registros. **No** se mide fidelidad al papel automáticamente.

## Reporte D — Posibles duplicados
DNI/C.E. coincidentes y validables, candidatos por nombres+fecha o documento OTRO no concluyente. Advertir sin fusionar Personas inciertas. La identificación global puede apoyar conteos nacionales, pero E1-D01 (vincular fichas de diferente presbiterio) sigue por cerrar.

## Seguridad y rendimiento
Permiso por funcionalidad en PHP, consultas parametrizadas, filtros opcionales, paginación; límites de resultados, índices y `EXPLAIN`. No implementar mecanismo de supervisión de digitación, miembros nominales GC ni exportación a SGSP.
