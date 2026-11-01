# 05 — Verificación funcional, seguridad y rendimiento

[↑ docs](../README.md) · [↑ Índice principal](../../README.md)

Contiene el **diseño de las pruebas** para comprobar requisitos de PCD y el rendimiento en hosting compartido.

| Archivo | Contenido |
|---|---|
| [casos-prueba.md](casos-prueba.md) | Casos CA-01…CA-76: login, sedes, Persona, GC, duplicados, edición, auditoría, reportes, privacidad y despliegue. |
| [pruebas-rendimiento.md](pruebas-rendimiento.md) | Escenarios de carga, métricas p50/p95 y evaluación de impacto sobre el sitio que comparte hosting. |

**Distinción esencial:** un caso **documentado** no es una prueba **ejecutada**. Los resultados reales, evidencias y scripts aparecerán en [tests/](../../tests/README.md) una vez que exista código. No marcar `PASÓ` sin ejecución verificable.

## Auditoría Etapa 2
Casos CA-56…CA-76 agregados para H-03/04/07/12/13/18/28/32/35. Son **especificaciones de prueba**, no resultados. GO de I01 requiere acordar E2-D02 y controlar riesgo E2-D03 antes de publicar con datos reales.

Para ejecutar las nuevas pruebas, usar [Asignación por iteración](asignacion-iteraciones.md) y [Matriz principal](casos-prueba.md). Las pruebas están diseñadas, no ejecutadas.

## Pruebas V4
[Casos completos](casos-prueba.md), [asignación por iteraciones](asignacion-iteraciones.md) y [Etapa 4 / checklist productivo](../07-auditorias/etapa-4-produccion-y-privacidad.md). **CA-91…CA-100** prueban 11 tablas, consulta total, tipo/número sin subtipo y respaldo. Ningún caso está ejecutado.

**Validación de estructura I00:** [script](../../scripts/validar-sdd-i00.cjs), [guía](../08-ia/I00-verificacion-estatica-y-handoff.md). No es una prueba SQL o E2E; registrar evidencia por entorno.
