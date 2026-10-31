# SDD PCD — Ficha Líder GC
## Requirement GC-01: Líder guardado
MUST invocarse por demanda después del guardado de Persona o desde Consulta; tomar ID/nombre/documento persistido sin volver a digitarlos.
### Scenario — Antes de guardar Persona
GIVEN Persona nueva no guardada WHEN elige «Es líder GC» THEN no se crea GC y solo se habilita después de confirmar Persona.
## Requirement GC-02: Captura ficha GC
MUST capturar papel según diccionario, con número impreso opcional e independiente, estado/tipo, fecha inicio, ubicación, capacidad y número aproximado de integrantes, observaciones y control.
## Requirement GC-03: No sustitución destructiva líder
MUST no actualizar líder de GC vigente como simple reemplazo; si se registra nuevo líder, requerir nuevo GC o incidencia de conciliación.
## Requirement GC-04: Resumen no nominal
MUST mantener capacidad y participantes aproximados separados; NO crear miembros GC individuales desde conteos agregados.
## Requirement GC-05: Contexto/auditoría
MUST heredar presbiterio/sede y verificar ámbito en backend; guardar actor, autor, geo_sesion e historial para toda alta/edición.

## Requirement GC-06: Solo líderes y resumen (alcance aprobado E1)
MUST registrar únicamente el líder **previamente capturado como Persona** y datos generales del grupo, como nombre, estado, capacidad y número aproximado de integrantes. MUST NOT crear ni relacionar **integrantes nominales** porque su información no figura en las fichas del levantamiento.
### Scenario — Grupo de 12 integrantes aproximados
GIVEN ficha GC indica 12 integrantes aproximados WHEN se registra THEN se guarda el conteo 12 y el líder, sin crear 12 Personas ni relaciones de miembro.
