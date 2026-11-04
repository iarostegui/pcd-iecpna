# changes — Control de cambios SDD PCD

[↑ openspec](../README.md) · [↑ Índice principal](../../README.md)

**Estado actual:** cambio I01 abierto en PR Draft; P01 aprobado e integrado; el resto son antecedentes.

Cuando una modificación se apruebe, crear una carpeta `YYYY-MM-DD-descripcion/` con:

| Archivo | Qué documenta |
|---|---|
| `proposal.md` | Motivo, objetivo, alcance y exclusiones. |
| `design.md` | Componentes impactados, datos, API, UX, seguridad y riesgos. |
| `specs/<capacidad>/spec.md` | Deltas de los requisitos y escenarios verificables. |
| `tasks.md` | Actividades y verificación por iteración. |

**Regla:** después de implementar y verificar, consolidar el requisito en [specs vigentes](../specs/README.md), actualizar índices/documentos afectados y registrar cierre del cambio. No documentar cambios del **SGSP** en este repositorio PCD.

## Cambio I01 abierto (PR Draft)
- [2026-11-02-i01-autenticacion-contexto/](2026-11-02-i01-autenticacion-contexto/proposal.md) — autenticación, sesión, organización/sedes, contexto ADMIN y GPS opcional. Incluye `proposal.md`, `design.md`, `spec.md` Given/When/Then y `tasks.md`. **No implementa I02–I06** ni modifica tablas/columnas.

## Cambio P01 aprobado e integrado
- [2026-11-02-p01-andamiaje/](2026-11-02-p01-andamiaje/proposal.md) — andamiaje PHP 8.2 + React/TS/Vite, aprovisionamiento idempotente y runner HTTP. **No implementó I01–I05**; base para I01.

## Cambio E1 abierto
[2026-10-31-levantamiento-etapa-1/](2026-10-31-levantamiento-etapa-1/README.md) — deltas H-01, H-02, H-05, H-08, H-09, H-14. **Propuesto, no consolidado en specs vigentes ni codificado**.

## Cambio E2 abierto
- [2026-10-31-levantamiento-etapa-2/](2026-10-31-levantamiento-etapa-2/README.md) — H-03/04/07/12/13/18/28/32/35. Diseño y tareas preliminares, pendientes E2-D01/D02/D03 y validación de API/SQL reales. **No implementar mejoras fuera de ALC-PCD.**

- [2026-10-31-levantamiento-etapa-3/](2026-10-31-levantamiento-etapa-3/README.md) — DTO y filtros API, trazabilidad integral y casos de prueba; sin implementación productiva.

**Cambios históricos:** `2026-10-31-levantamiento-etapa-1`, `etapa-2`, `etapa-3` documentan la evolución de decisiones V3. Cualquier referencia a 17 tablas, subtype o `alcance_consulta` queda reemplazada por [V4](../../docs/01-analisis/decisiones-pendientes.md). **Cambio vigente de preparación**: [Etapa 4](2026-11-01-etapa-4-produccion/README.md).

- `2026-11-03-i02-captura-personas/`: especificación I02, actualmente en PR #5 Draft; H-24, CE/INEI pendientes.
