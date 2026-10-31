# openspec — Especificaciones SDD vigentes de PCD

[↑ Índice principal](../README.md)

Contiene requisitos **normativos y verificables** por capacidad de **PCD** (no SGSP), además del espacio para controlar futuros cambios.

| Carpeta | Qué contiene |
|---|---|
| [specs/](specs/README.md) | Requisitos SDD vigentes, escenarios Given/When/Then, organizados por funcionalidad. |
| [changes/](changes/README.md) | Cambios propuestos y activos; cada cambio incluye análisis de impacto, especificación y tareas antes de modificar código. |

**Procedimiento:** `changes/YYYY-MM-DD-slug/proposal.md` → `design.md` → `specs/<capacidad>/spec.md` → `tasks.md` → implementación/pruebas → consolidar en `specs/` y archivar el cambio.

**Relación con otros documentos:** los requisitos funcionales se fundamentan en [01-análisis](../docs/01-analisis/README.md); el diseño y las decisiones de implementación se encuentran en [02-diseño](../docs/02-diseno/README.md) y [03-base-datos](../docs/03-base-datos/README.md); sus pruebas están en [05-pruebas](../docs/05-pruebas/README.md).
