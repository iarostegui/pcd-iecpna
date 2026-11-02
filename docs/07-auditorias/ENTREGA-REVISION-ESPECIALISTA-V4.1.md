# Entrega de documentación V4.1 para revisión del especialista
**Objetivo:** reducir al mínimo la probabilidad de implementación equivocada por IA; la revisión profesional **no es fin en sí misma**.
**Versión:** PCD SDD V4.1. **Estado:** documental, sin backend PHP ni frontend React productivos, SQL sin prueba de motor. No declarar aprobado automáticamente por incluir los archivos.

## Cambios ratificados que el especialista debe revisar
1. Alcance consolidado [ALC-PCD-4.0](../01-analisis/alcance-funcional.md) y [aprobaciones](../08-ia/APROBACIONES.md): los acuerdos previos E1/E2 no deben contradcir los contratos actuales.
2. Autenticación: misma clave digitadores **solo UAT**; antes de datos reales rotación individual y revocación sesiones. La decisión ya no es riesgo productivo de contraseña común.
3. **E1-D01 aprobada**: `POST /personas/{id}/fichas` vincula ficha nueva de presbiterio distinto a Persona documental existente, sin modificar consolidado. `persona_reutilizada` conserva semántica.
4. Presbiterios fijos [LIM, TRU, CHI, IQU](../03-base-datos/catalogos-e-identidad.md) en seed; app solo ofrece `GET /presbiterios` sin alta de nuevos. Alta de sede continúa.
5. `ficha` creada: solo fecha y autor; **sin fila de auditoría**. Correcciones reales: `auditoria_evento` solo campos modificados y snapshot actor.
6. SQL: todas las tablas `created_at/updated_at`; UNIQUE de raíz, índice idempotencia. **Índices de reportes tras EXPLAIN**, no impuestos sin medición.
7. Sedes: nombres comparados en forma normalizada sin tildes, conservar nombres mostrados.
8. JSON fuente con [esquemas estrictos y versionados](../03-base-datos/esquemas-json/README.md), sin tablas adicionales.
9. Reproducibilidad: [validación SDD](../../scripts/validar-sdd-i00.cjs), [workflow CI](../../.github/workflows/validacion-sdd.yml), [casos de prueba](../05-pruebas/casos-prueba.md); ningún test SQL/React/PHP se declara ejecutado.

## Revisión solicitada
Verificar correspondencia entre alcance, OpenSpec, OpenAPI, SQL, diagrama, diccionario, JSON Schema, casos y UI V2.3. Confirmar que no se ha reintroducido subtipo documental ni ámbitos de lectura por presbiterio; que PRESBITERIO no tiene CRUD; que el endpoint E1-D01 no sobrescribe Persona; que la auditoría no duplica filas al crear ficha; y que la estrategia de pruebas y producción es proporcionada a PCD.

## Riesgos declarados para evitar falsos cierres
- H-14 Excel binario sin versionar; insumo oficial de ubigeo pendiente I02.
- E1-D02 y H-23/H-24 no resueltos.
- H-40 decisiones institucionales de datos sensibles, conservación, cierre y tratamiento de ubicación.
- Hosting cPanel y motor SQL real sin validar; la compatibilidad de columna generada y CHECK/FK debe ensayarse.
- Repositorio público: no subir información sensible. La visibilidad/licencia requieren decisión del propietario.

**Próximo hito después de esta revisión:** implementar I00/I01 con transacciones, login real, sede y ficha sintética; comprobar en base de prueba y registrar evidencias, no seguir acumulando documentos.
