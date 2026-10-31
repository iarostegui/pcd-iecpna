# Requisitos de consultas y reportes PCD
## Consulta operativa
- Búsqueda con lupa por documento, nombres, apellidos y número opcional de ficha.
- Filtros: presbiterio permitido, sede, digitador de origen, rango de fecha guardado, tipo de ficha, estado incompleto/posible duplicado.
- Paginar servidor (25 por página, máximo 50), ordenar por fecha reciente, mostrar autor original y última modificación.
- DIGITADOR consulta bajo sus ámbitos, **edita únicamente propias fichas**. ADMIN consulta/edita todas. REPORTES solo lectura agregada y calidad autorizada, sin acceso GPS ni datos personales extensos por defecto.
- No confundir «número de ficha» (puede repetirse/faltar) con `ficha_captura.id` autogenerado.

## Reporte A: Avance por sede
Columnas: presbiterio, sede, Personas capturadas, fichas GC, total por período, última fecha de guardado por sede, digitador último guardado, fecha último dato corregido si aplica. Filtrar período y ámbito nacional.

## Reporte B: Productividad
Por presbiterio/sede/digitador/fecha: fichas Persona creadas, fichas GC creadas, modificaciones, último registro. Contar **altas** por autor original y **ediciones** por actor real, no atribuir correcciones ADMIN al autor.

## Reporte C: Calidad
Campos críticos parametrizables por `campo_critico`; contabilizar personas/GC con faltantes, detalle por campo, digitador creador, sede y fecha. El cambio de criticidad altera cálculo de reportes; **no modifica fichas ni impide captura**.

## Reporte D: Posibles duplicados
Separar coincidencia documental exacta, coincidencia fuerte por nombre+fecha nacimiento y coincidencia parcial. Personas documentales iguales no deben convertirse automáticamente en dos identidades; mostrar datos minimizados en digitadores.

## Seguridad y tiempos
Filtrar por rol/ámbito antes de devolver datos; paginar. Índices por sede, autor, fecha, documento; medir p95 y planes EXPLAIN. Exportar reporte solo cuando esté expresamente habilitado y con minimización; no equivale a migrar al SGSP.

## Etapa 2 — consulta y calidad mínima propuestas
- **H-12/E2-D01 pendiente:** DIGITADOR podría consultar fichas del **propio presbiterio** (comportamiento del prototipo) y solo editar propias; nunca mostrar PII/GPS de otros presbiterios incluso si comparten Persona consolidada. Esta política debe ratificarse antes de I04.
- **H-18:** indicadores de campos críticos se **recalculan al consultar** mediante [campos-criticos.md](../03-base-datos/campos-criticos.md); no confiar en `marca_calidad` fija ni añadir tareas de recomputación.
- **H-28/E2-D02 pendiente:** ADMIN puede cambiar contexto explícito de presbiterio/sede; REPORTES nacional accede a agregados sin sede. No hacer accesible información nominal a REPORTES por simple rol nacional.
