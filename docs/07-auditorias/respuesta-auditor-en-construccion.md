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
| H-05 | **Atender** | Guardar valores literales imposibles en snapshot y NULL en tipos cuando invalidez; alertas de calidad. | No bloquear por datos inválidos del papel: contradice finalidad de captura. | Definición documentada |
| H-08 | **Atender de forma simplificada** | Registrar respuesta del papel sobre GC existente; ID interno por ficha, detección de coincidencias como aviso. | No administrar identidad institucional definitiva de GC ni fusionar grupos automáticamente: es función del futuro SGSP, costosa y riesgosa en campaña temporal. | Pendiente conformidad funcional |
| H-09 | **Atender de forma simplificada** | Incidencias de calidad asociadas a ficha, consultas, reportes y correcciones auditadas por ADMIN o autor. | No construir módulo separado de conciliación con bandejas/estados/aprobaciones: no aporta valor proporcional a un aplicativo temporal. | Pendiente conformidad funcional |
| H-14 | **Atender, parcial** | Analizado Excel original, inventario de campos/opciones/filas + comprobación hash. | No inventar campos ni usar datos de ejemplo como reales. **La carga del XLSX original al repositorio sigue pendiente**, no se rechaza. | Parcial |

## Justificación reutilizable en informe final
> **Proporcionalidad y ciclo de vida:** PCD tiene una vida útil limitada a la digitación de fichas impresas y posterior migración controlada de sus datos a SGSP. Por ello se excluyen procesos administrativos y maestros permanentes que no mejoren la captura, integridad, seguridad, auditoría o calidad necesaria para migrar. Los requisitos esenciales sí se implementan con alternativas ligeras, verificables y transaccionales.

## Hallazgos fuera de Etapa 1
H-03, H-07, H-10…H-13, H-18, H-20, H-28, H-32, H-35, H-40 y restantes se decidirán por etapa. **No se consideran rechazados ni corregidos** por figurar en esta matriz.
