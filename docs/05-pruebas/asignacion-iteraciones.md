# Asignación de pruebas a las iteraciones PCD
**Estado:** planificación, ninguna prueba ejecutada. Una misma prueba puede repetirse como regresión en integración.

| Iteración | Casos base asignados | Controles esenciales |
|---|---|---|
| I00 — Preparación | CA-51, CA-52 | SQL/seed, esquema/contrato, estructura de datos. |
| I01 — Login/sedes | CA-01…14, CA-36…39, CA-41, CA-56…67, CA-70, CA-76 | Roles, autorización, cookies, GPS, CSRF, revocación, cuentas. |
| I02 — Personas | CA-15…27, **CA-42**, CA-53, CA-69, CA-71/72, CA-75 | Transacción rollback, duplicate, fuente/identidad, fechas, ámbito. |
| I03 — GC | **CA-28**, CA-29…31, CA-74/75, E1-CA-11 | Solo líder, control de versión y relaciones, no integrantes. |
| I04 — Consulta/edición | CA-32…34, CA-43, **CA-54/55**, CA-68/69 | Ficha propia y Persona global, consulta general/restringida. |
| I05 — Admin/calidad/reportes | CA-03/04/21/35/44/45, CA-64/65/67/73 | Control de cuentas, criticidad y métricas. |
| I06 — Pruebas/despliegue | CA-40, CA-46…50, CA-51 (repetición) y todos P0 de regresión | Auditoría, seguridad, rendimiento, restauración, UAT. |

## Casos E3 adicionales (ver matriz principal)
CA-77…CA-90 cubren contratos de éxito/error, consulta configurable, conflicto de versión, idempotencia con payload distinto, métricas, edición admin y no replicar funciones de supervisor. Están **diseñados, no ejecutados**.

**Regla:** los casos con decisión funcional pendiente (E1-D01/D02; H-23/H-24) se marcan `BLOQUEADO` hasta aprobar sus requisitos, en lugar de falsear un PASS.
