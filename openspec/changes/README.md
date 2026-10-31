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
