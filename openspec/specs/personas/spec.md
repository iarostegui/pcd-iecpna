# PCD V4 — Personas y fichas

## PER-01 — Transcripción de Persona
MUST representar los campos y orden del [Excel original](../../docs/01-analisis/fuentes/inventario-excel-original.md), tecla Enter/Tab y número impreso opcional, como prototipo PCD V2.2.

## PER-02 — Incompletos
MUST guardar fichas sin campos críticos o sin número impreso; integridad técnica de usuario/presbiterio/sede no es desactivable. La definición de una ficha completamente vacía sigue pendiente **H-24**.

## PER-03 — Identidad documental H-13
MUST usar solo tipo y número. DNI confiable con 8 dígitos, C.E. confiable después de validación oficial aplicable, clave única nullable sin perder ceros. OTRO y documentos inciertos: coincidencia posible, no fusionar automáticamente. E1-D01 (fuentes cross-presbiterio) pendiente de definición detallada.
### Scenario
GIVEN DNI 00123456 y otra ficha mismo tipo/valor WHEN valida identidad THEN reconoce coincidencia documental y conserva dos fichas fuente sin sobrescritura silenciosa.

## PER-04 — Edición
MUST distinguir **consulta general** de **escritura**. DIGITADOR consulta dentro de funcionalidades permitidas y solo puede modificar fichas creadas por él. ADMIN puede corregir y queda como actor, autor original inmutable. REPORTES no edita.
### Scenario
GIVEN FICHA registrada por DIG-01 WHEN DIG-02 la consulta y trata PATCH THEN la lectura está permitida en Consulta autorizada y PATCH devuelve 403.

## PER-05 — Líder GC
MUST activar flujo GC después de persistir Persona, o desde Consulta. No cambiar Persona por registrar GC.

## PER-06 — Idempotencia
MUST retornar resultado anterior si se repite `Idempotency-Key` con mismo payload; 409 si payload difiere; transacción atomiza identidad, ficha y auditoría.

## PER-07 — Fuente estructurada
MUST conservar cada `ficha.datos_fuente_json`, incluyendo cónyuge y responsabilidades del papel sin tablas independientes; JSON validado contra estructura conocida, sin SQL arbitrario.

## PER-08 — Fechas reales
MUST rechazar 31/02/1998 (también en PHP), o permitir dejar fecha vacía y guardar incompleto. Solo DATE real o NULL.

## PER-09 — Fidelidad documental
MUST NOT afirmar detección automática de error de transcripción JUANA/Juan, JU4N4; cada presbiterio revisa papel manualmente fuera de PCD, corrige mediante edición auditada.

**BD:** persona, ficha, organizacion, catalogo, ubigeo, auditoria_evento, idempotencia.
