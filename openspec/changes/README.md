# changes — Control de cambios SDD PCD

[↑ openspec](../README.md) · [↑ Índice principal](../../README.md)

**Estado actual:** no hay cambios abiertos registrados; el directorio contenía un marcador `.gitkeep`.

Cuando una modificación se apruebe, crear una carpeta `YYYY-MM-DD-descripcion/` con:

| Archivo | Qué documenta |
|---|---|
| `proposal.md` | Motivo, objetivo, alcance y exclusiones. |
| `design.md` | Componentes impactados, datos, API, UX, seguridad y riesgos. |
| `specs/<capacidad>/spec.md` | Deltas de los requisitos y escenarios verificables. |
| `tasks.md` | Actividades y verificación por iteración. |

**Regla:** después de implementar y verificar, consolidar el requisito en [specs vigentes](../specs/README.md), actualizar índices/documentos afectados y registrar cierre del cambio. No documentar cambios del **SGSP** en este repositorio PCD.

## Cambio E1 abierto
[2026-10-31-levantamiento-etapa-1/](2026-10-31-levantamiento-etapa-1/README.md) — deltas H-01, H-02, H-05, H-08, H-09, H-14. **Propuesto, no consolidado en specs vigentes ni codificado**.

## Cambio E2 abierto
- [2026-10-31-levantamiento-etapa-2/](2026-10-31-levantamiento-etapa-2/README.md) — H-03/04/07/12/13/18/28/32/35. Diseño y tareas preliminares, pendientes E2-D01/D02/D03 y validación de API/SQL reales. **No implementar mejoras fuera de ALC-PCD.**
