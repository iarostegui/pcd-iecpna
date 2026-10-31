# Respuesta al auditor PCD SDD V3.1 — REGISTRO ACUMULATIVO
**NO es el informe final.** No enviar como respuesta oficial hasta concluir todas las etapas y validar evidencias.

## Principios de respuesta
- **Se atiende** cuando elimina incoherencia o riesgo que afecta captura, integridad, seguridad o migrabilidad.
- **Se atiende simplificado** cuando la necesidad está justificada pero la solución propuesta introduce procesos no solicitados.
- **No se atiende** cuando el desarrollo sugerido añade complejidad desproporcionada a una aplicación **temporal** y no aporta beneficio al objetivo de captura. Justificación debe ser individual y demostrar control alternativo.
- **Se difiere** si requiere datos externos, validación de hosting o decisión de negocio; no equivale a rechazado.
- La vida temporal **no justifica** rechazar seguridad, protección de datos, trazabilidad, respaldos o integridad básica.

## Etapa 1: propuestas de disposición
| Hallazgo | Tratamiento propuesto | ¿Qué se atenderá? | ¿Qué no se implementará y por qué? | Estado |
|---|---|---|---|---|
| H-01 | **Atender** | Guardar ficha asociada a Persona documentalmente existente incluso fuera del ámbito, sin exponer PII ni perder la hoja. | No segundo registro Persona ni búsqueda libre entre presbiterios; duplicar datos incrementaría errores. | Diseño propuesto, API/SQL pendientes |
| H-02 | **Atender** | Fuente separada de Persona consolidada; snapshots por ficha; derechos de edición, auditoría. | No maestro histórico amplio de identidades ni flujo complejo de consolidación; temporalidad y control de alcance. | Diseño propuesto |
| H-05 | **Atender con validación de tipo** | Fecha imposible es rechazada como dato fecha; el digitador resuelve la discrepancia fuera del sistema o deja campo vacío y puede guardar incompleto. Frontend y PHP revalidan. | No crear almacenamiento alternativo de valores fecha imposibles ni un flujo de incidencias para resolverlas; no aporta valor al objetivo temporal. | **Criterio funcional aprobado**, pruebas pendientes |
| H-08 | **Atender solo alcance mínimo** | Registrar exclusivamente **líder** y datos generales del GC, con número aproximado de integrantes. Conservar pregunta de existencia del papel; detalle de identidad pendiente E1-D02. | **No registrar integrantes nominales** ni crear administración pastoral de miembros/grupos. Su información no existe en las fichas; sobrecoste sin fuente. | **Alcance funcional aprobado**, E1-D02 pendiente |
| H-09 | **No implementar revisión semántica automática** | PCD valida tipos, integridad y campos faltantes y permite corrección posterior auditada. | **Cada presbiterio revisa manualmente fuera de PCD** la fidelidad de la digitación al papel; no existe mecanismo fiable para detectar automáticamente `JUANA`/`Juan` o `JU4N4` sin comparar con el papel. No construir conciliación, tickets, supervisión o aprobaciones. | **Criterio funcional aprobado** |
| H-14 | **Atender, parcial** | Analizado Excel original, inventario de campos/opciones/filas + comprobación hash. | No inventar campos ni usar datos de ejemplo como reales. **La carga del XLSX original al repositorio sigue pendiente**, no se rechaza. | Parcial |

## Justificación reutilizable en informe final
> **Proporcionalidad y ciclo de vida:** PCD tiene una vida útil limitada a la digitación de fichas impresas y posterior migración controlada de sus datos a SGSP. Por ello se excluyen procesos administrativos y maestros permanentes que no mejoren la captura, integridad, seguridad, auditoría o calidad necesaria para migrar. Los requisitos esenciales sí se implementan con alternativas ligeras, verificables y transaccionales.

## Hallazgos fuera de Etapa 1
H-03, H-07, H-10…H-13, H-18, H-20, H-28, H-32, H-35, H-40 y restantes se decidirán por etapa. **No se consideran rechazados ni corregidos** por figurar en esta matriz.

## Aclaración de H-09 del responsable funcional (31/10/2026)
**Dos problemas diferentes:** (1) un dato imposible para el control —por ejemplo, `31/02/1998` en fecha— es **rechazado** y el digitador corrige con el origen o deja el valor vacío para guardar una ficha incompleta; (2) un texto plausible pero mal copiado —`JUANA` en vez de `Juan`, o `JU4N4`— no se verifica automáticamente en PCD. **Cada presbiterio es responsable de revisión manual externa**. Esta última verificación de calidad **no es alcance de desarrollo de PCD** por proporcionalidad y vida temporal. Se mantienen controles técnicos, seguridad e integridad.

## Criterio formal de alcance para elaborar la respuesta definitiva

El [alcance oficial ALC-PCD-3.1](../01-analisis/alcance-funcional.md) y el [protocolo de auditoría](protocolo-auditoria-alcance.md) constituyen el marco de evaluación. **Toda recomendación fuera de EXC-01…EXC-10 se analizará sin transformarla automáticamente en obligación de desarrollo**. Cuando sea razonable cuestionar el alcance, el auditor debe describir el valor y los riesgos de cambiarlo y solicitar decisión del responsable funcional, sin considerar como defecto la exclusión aprobada.

Al redactar la respuesta individual a cada observación, distinguir entre **corrección de una brecha en alcance**, **control esencial de seguridad/integridad**, **sugerencia fuera de alcance** y **dependencia futura**. La temporalidad justifica descartar estructuras permanentes que no añaden valor proporcional, pero **no** omitir controles esenciales de PCD. En particular, la revisión manual de fidelidad al papel (H-09) y la ausencia de miembros nominales GC (H-08) son decisiones de alcance **ya aprobadas**, no brechas técnicas a resolver con nuevos módulos.
