# CAL-PCD-4.0 — Matriz inicial de completitud (tabla CATALOGO)

Los campos críticos de calidad **no son campos obligatorios de guardado**. Se almacenan como filas de `catalogo` en categorías `CAMPO_CRITICO_PERSONA` y `CAMPO_CRITICO_GC`, con `config_json` para `ruta_dato`, `es_critico` y `desactivable`.

| Tipo | Código | Ruta de evaluación | Desactivable |
|---|---|---|---|
| PERSONA | first | persona.nombres | Sí |
| PERSONA | last1 | persona.apellido_paterno | Sí |
| PERSONA | type | persona.tipo_documento | Sí |
| PERSONA | doc | persona.numero_documento | Sí |
| PERSONA | birth | persona.fecha_nacimiento | Sí |
| PERSONA | sex | persona.sexo_codigo | Sí |
| PERSONA | pres | ficha.presbiterio_id | **No** |
| PERSONA | sede | ficha.sede_id | **No** |
| PERSONA | condition | persona.condicion_codigo | Sí |
| GC | personId | ficha.persona_id | **No** |
| GC | pres | ficha.presbiterio_id | **No** |
| GC | sede | ficha.sede_id | **No** |
| GC | type | grupo_crecer.tipo_gc | Sí |
| GC | start | grupo_crecer.fecha_inicio | Sí |
| GC | capacity | grupo_crecer.capacidad | Sí |

La configuración inicial se carga con [seed oficial](../../database/seeds/002_catalogos_calidad.sql), derivada del prototipo V2.2. PHP debe validar las rutas del JSON contra una **lista blanca**, no convertir la ruta en SQL arbitrario. Los cambios realizados por ADMIN se auditan y la completitud se calcula en consulta con valores actuales: **no** se persiste `marca_calidad` fija ni se implementa supervisión automática de concordancia con papel.
