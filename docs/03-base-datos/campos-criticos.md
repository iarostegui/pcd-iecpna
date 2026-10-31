# CAL-PCD-3.1 — Matriz de criticidad inicial
**Fuente:** constantes del prototipo PCD V2.2 `CRITICAL_PERSON`, `CRITICAL_GC`, `HARD_FIELDS`; estas opciones son *baseline de diseño*, no valores de un catálogo oficial de sedes. La configuración ADMIN puede modificarse en campaña **sin impedir guardar incompletos**.

| Entidad | Código del prototipo | Etiqueta | Ruta PCD de evaluación | Inicial | ¿Desactivable? |
|---|---|---|---|---|---|
| PERSONA | first | Nombres | persona.nombres | Sí | Sí |
| PERSONA | last1 | Apellido paterno | persona.apellido_paterno | Sí | Sí |
| PERSONA | type | Tipo documento | persona.tipo_documento | Sí | Sí |
| PERSONA | doc | N.º documento | persona.numero_documento | Sí | Sí |
| PERSONA | birth | Fecha nacimiento | persona.fecha_nacimiento | Sí | Sí |
| PERSONA | sex | Sexo | persona.sexo_codigo | Sí | Sí |
| PERSONA | pres | Presbiterio | ficha.presbiterio_id | Sí | **No: relación técnica** |
| PERSONA | sede | Sede | ficha.sede_id | Sí | **No: relación técnica** |
| PERSONA | condition | Condición pastoral | persona.condicion_codigo | Sí | Sí |
| GC | personId | Líder registrado | ficha.persona_id | Sí | **No: relación técnica** |
| GC | pres | Presbiterio | ficha.presbiterio_id | Sí | **No: relación técnica** |
| GC | sede | Sede | ficha.sede_id | Sí | **No: relación técnica** |
| GC | type | Tipo GC | grupo.tipo_gc | Sí | Sí |
| GC | start | Fecha inicio | grupo.fecha_inicio | Sí | Sí |
| GC | capacity | Capacidad | grupo.capacidad | Sí | Sí |

**Distinción:** la integridad de claves foráneas y relaciones obligatorias no está parametrizada y se valida al guardar. Los demás campos se pueden marcar críticos para **reporte**, sin hacerlos obligatorios para la captura.

**Cálculo:** la completitud se evalúa al consultar contra la configuración vigente y la ficha/persona autorizada; **no** persistir `marca_calidad` como fuente de verdad porque quedaría desactualizada al cambiar parámetros. Si se requiere cache a futuro, deberá invalidarse/versionarse y aprobarse, sin nuevos procesos residentes.

**Auditoría:** cambios `campo_critico` se guardan con usuario, fecha y `auditoria_evento` antes/después. No hace falta flujo histórico separado de versiones de configuración: auditoría ya permite reconstruir modificaciones.
