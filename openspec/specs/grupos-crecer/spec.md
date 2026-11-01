# PCD V4 — Líder de Grupo Crecer

## GC-01 — Líder previamente registrado
MUST elegir Persona ya persistida como líder, desde continuación de ficha o Consulta.

## GC-02 — Ficha GC
MUST guardar `ficha.tipo_ficha=GC` y `grupo_crecer` vinculado a esa ficha, con datos del formulario: nombre, tipo, estado, fecha, dirección, capacidad, **cantidad aproximada**, nombre/número de ficha en fuente.

## GC-03 — No sustitución destructiva
MUST NOT sobrescribir líder histórico por otro. E1-D02 (grupo existente) sigue pendiente; no construir maestro definitivo.

## GC-04/06 — Sin integrantes nominales
MUST guardar capacidad y cantidad estimada sin crear entidades de miembros de GC ni vínculos nominales.

## GC-05 — Contexto y auditoría
MUST guardar en sede/presbiterio de sesión y actor; DIGITADOR edita únicamente fichas propias. H-23 (crear ficha GC vinculada a Persona ajena) sigue pendiente, no inferir permiso de escritura ajena.

## GC-08/09 — Integridad y versión
El líder se deriva **exclusivamente de `ficha.persona_id`**, sin columna de líder en GC. FK compuesta GC.ficha_id+tipo debe apuntar a ficha GC. PATCH grupo usa versión de **ficha**, no tabla GC.
### Scenario
GIVEN grupo con ficha.version=3 WHEN PATCH incluye version=2 THEN responder 409 sin cambios parciales.
