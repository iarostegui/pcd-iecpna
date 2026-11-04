# Especificación I02 — Escenarios Given/When/Then

**Referencia normativa:** ALC-PCD-4.0, H-12/H-13, E1-D01, RN-31, OpenAPI, SQL 11 tablas, esquema JSON ficha-persona 1.0.

## I02-S01: DNI confiable y ceros
Given sesión DIGITADOR/ADMIN con sede válida, When se registra DNI '00123456' y datos permitidos, Then se conserva el número textual, clave documental única, Persona y Ficha independientes y autor real.

## I02-S02: reutilización entre presbiterios
Given existe Persona con DNI válido creada en LIM, When un usuario autorizado de TRU registra el mismo DNI en una ficha nueva, Then se reutiliza exactamente la Persona y no se sobrescribe ningún atributo, con una segunda ficha atribuida a TRU.

## I02-S03: documento ambiguo
Given tipo OTRO o CE no ratificado, When se crea una nueva captura, Then el campo de identidad confiable queda NULL y no se fusionan Personas automáticamente; se admite advertencia de coincidencia potencial.

## I02-S04: idempotencia
Given la misma Idempotency-Key y mismo payload para el mismo usuario, When se reintenta la solicitud, Then se recupera respuesta y no se crea otra ficha. Una misma clave con payload distinto obtiene 409.

## I02-S05: integridad de autoría
Given usuario REPORTES, When intenta crear ficha, Then recibe 403; Given usuario DIGITADOR, When crea ficha, Then created_by coincide con la sesión y no se crea auditoria_evento del INSERT.

## I02-S06: validaciones
Given fecha imposible, documento DNI mal formado, JSON con propiedad extra o estructura inválida, When se envía, Then 422 y ninguna escritura persistida.

## I02-S07: alta anexa
Given Persona documentalmente confiable, When se invoca POST /personas/{id}/fichas con documento concordante, Then se crea nueva ficha sin alterar la Persona; con documento divergente responde 409/422 y hace rollback.

## I02-S08: sede contextual
Given sesión sin sede o sede de otro presbiterio, When solicita alta, Then no crea Persona ni Ficha.

## I02-S09: umbral mínimo H-24
Given ficha absolutamente vacía, When se quiera definir si se admite o bloquea, Then **BLOCKED** hasta aprobación explícita H-24; no implementar criterio arbitrario.

## I02-S10: fuente ubigeo y CE
Given fuente oficial sin corte/verificación, When se solicita importación INEI o regla CE confiable, Then **BLOCKED** hasta verificar versión y fuente; mantener lectura vacía sin inventar registros.

## I02-S11: concurrencia
Given dos solicitudes simultáneas con la misma identidad confiable, When ambas intentan crear Persona, Then una sola identidad persiste y cada ficha legítima tiene ID propio; Idempotency-Key repetida no duplica fichas.