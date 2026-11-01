# PCD V4 — Organización, catálogos y ubigeo

## CAT-01 — Jerarquía presbiterio y sede
`organizacion` almacena nodos tipo PRESBITERIO/SEDE, sede con `padre_id` de un presbiterio. Al seleccionar sede para **digitación**, backend valida relación y condición activa. **No confundir con filtros de consulta general.**
### Scenario
GIVEN sesión de captura en presbiterio A WHEN se intenta guardar ficha en sede cuyo padre es B THEN 403/422 y ninguna inserción.

## CAT-02 — Alta de sede
MUST permitir alta desde selector sin aprobación de supervisor; misma combinación padre+nombre_normalizado no se duplica; similitudes se advierten, sin bloqueo por nombres legítimamente diferentes.

## CAT-03 — Contexto de digitación
MUST recordar sede por sesión y reusarla entre fichas; cambio explícito para ADMIN, sin perder datos en UI.

## CAT-04/05 — Tabla de tablas
MUST utilizar **una sola tabla `catalogo`**, `categoria/codigo/etiqueta/config_json`, para los dominios pequeños de tipo documento, sexo, estado civil, condición, GC y campos críticos de informes. `GET /catalogos?categoria=` no expone listas inventadas.

## CAT-06 — Documento de solo tipo/número (H-13)
MUST capturar **tipo_documento** y **numero_documento**, sin subtipo. DNI 8 dígitos, conservar ceros, C.E. con reglas aplicables verificables durante I02; OTRO es posible coincidencia, sin fusión documental exacta automática. Una Persona sin documento válido no queda bloqueada para guardar.
### Scenario
GIVEN dos fichas con "OTRO" y mismo número WHEN se revisa duplicidad THEN ofrecer alerta, no fusionar automáticamente.

## CAT-07 — Ubigeo de construcción
`ubigeo` es tabla jerárquica INEI de departamento/provincia/distrito, cargada **durante I02** con fuente y fecha verificadas; sin inventar códigos, sin crear tres tablas por nivel.
