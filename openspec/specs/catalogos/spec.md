# SDD PCD — Presbiterio, sede y catálogos
## Requirement CAT-01: Sede por presbiterio
MUST devolver exclusivamente sedes del presbiterio autorizado; no pasar a siguiente formulario sin sede consistente y activa.
### Scenario — Manipulación de contexto
GIVEN login presbiterio A WHEN cliente envía sede de B THEN backend retorna 403 y mantiene contexto A.
## Requirement CAT-02: Alta de sede integrada
MUST ofrecer «+ Agregar nueva sede» dentro del selector, sin supervisor ni navegación a otro módulo. Debe detectar duplicados exactos normalizados (acentos/case/espacios/prefijo IECPNA) y sugerir similares sin bloquear nombres legítimos distintos.
### Scenario — Sede nueva
GIVEN no existe nombre normalizado WHEN digitador crea THEN sede queda en A y seleccionada automáticamente.
## Requirement CAT-03: Reuso contexto
MUST conservar sede seleccionada mientras esté autorizada; siguientes personas no repiten selección; cambio de sede no cambia presbiterio.
## Requirement CAT-04: Datos catalogados
MUST representar tipo documento, sexo, estado civil, condición pastoral, tipo/estado GC y ubigeo como listas parametrizables o referencia validada, no inventadas. Si información oficial falta, registrarla como pendiente, no falsear datos.

