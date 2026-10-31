# SDD PCD — Personas y fichas
## Requirement PER-01: Captura exacta
MUST permitir los campos de Ficha Persona del Excel según `docs/01-analisis/diccionario-captura.md` en orden papel, con foco en N.º ficha y Enter/Tab hacia siguiente control; fechas DD/MM/AAAA digitables.
## Requirement PER-02: Alta incompleta
MUST guardar Persona/Ficha aunque falten datos críticos o número de ficha impresa. Campos críticos afectan reportes y no bloquean guardado.
### Scenario — Sin DNI ni ficha
GIVEN sede válida y nombre registrado WHEN guarda sin documento ni número impreso THEN se persisten ficha e identidad preliminar con indicador de faltantes; no se inventan valores.
## Requirement PER-03: Identidad y duplicados
MUST comprobar coincidencia inmediata cuando tipo+documento estén disponibles y volver a verificar al guardar con UNIQUE SQL. Si Persona existe, no duplicar identidad; conservar nueva ficha como fuente vinculada o incidencia controlada. Coincidencias débiles muestran advertencia sin bloqueo.
## Requirement PER-04: Autoría y edición
MUST permitir modificar solo propias fichas al digitador; ADMIN puede todas. Conservar autor original inmutable y guardar histórico de cambios y sesión/IP/geo.
### Scenario — Edición ADMIN
GIVEN ficha creada por DIG-01 WHEN ADMIN corrige THEN created_by sigue DIG-01 y evento actor=ADMIN.
## Requirement PER-05: Intención GC
MUST incluir opción «Es líder GC» dentro de Persona; NO mostrar ni crear GC hasta recibir confirmación de persistencia de Persona. Al guardar sin GC ofrecer siguiente Persona con sede y foco restaurados.
## Requirement PER-06: Idempotencia
MUST evitar doble alta por doble clic/reintento; misma Idempotency-Key y payload debe retornar resultado previo.


## Requirement PER-07: Varias fichas de una Persona
MUST persistir snapshot por ficha. Si DIG-B aporta ficha a Persona de DIG-A, DIG-B solo edita su ficha, no los datos consolidados de DIG-A; ADMIN puede editar globalmente. Auditar cada cambio.

## Requirement PER-08: Campos tipados (aprobado E1/H-05)
MUST validar una fecha DD/MM/AAAA como fecha calendario existente tanto en interfaz como en PHP. MUST NOT aceptar `31/02/1998` como valor de un control fecha; el digitador la corrige tras consultar el origen o deja el campo vacío y guarda una ficha incompleta. Persistencia SQL DATE válida o NULL.
### Scenario — Fecha imposible
GIVEN fecha `31/02/1998` WHEN usuario intenta guardarla THEN se solicita corrección/vaciado y no se persiste como DATE.

## Requirement PER-09: Revisión de fidelidad al papel (aprobado E1/H-09)
MUST validar formato, tipo, integridad y campos faltantes según configuración. MUST NOT afirmar que verifica automáticamente que un texto capturado coincide con la hoja física: `JUANA`/Juan o `JU4N4` son ejemplos de transcripción incorrecta no demostrable a partir de la entrada. **Cada presbiterio hace revisión manual fuera de PCD** y corrige por edición autorizada con auditoría. No implementar motor de conciliación, aprobaciones o asignaciones.
### Scenario — Texto plausible mal transcrito
GIVEN nombre sintácticamente admitido pero diferente de ficha original WHEN guarda THEN PCD no lo certifica como fiel al papel; revisión manual externa.
