# 05 — Verificación funcional, seguridad y rendimiento

[↑ docs](../README.md) · [↑ Índice principal](../../README.md)

Contiene el **diseño de las pruebas** para comprobar requisitos de PCD y el rendimiento en hosting compartido.

| Archivo | Contenido |
|---|---|
| [casos-prueba.md](casos-prueba.md) | Casos CA-01…CA-125 organizados por bloques: login, sedes, Persona, GC, duplicados, edición, auditoría, reportes, privacidad y despliegue. |
| [pruebas-rendimiento.md](pruebas-rendimiento.md) | Escenarios de carga, métricas p50/p95 y evaluación de impacto sobre el sitio que comparte hosting. |

**Distinción esencial:** un caso **documentado** no es una prueba **ejecutada**. Los resultados reales, evidencias y scripts aparecerán en [tests/](../../tests/README.md) una vez que exista código. No marcar `PASÓ` sin ejecución verificable.

## Auditoría Etapa 2
Casos CA-56…CA-76 agregados para H-03/04/07/12/13/18/28/32/35. Son **especificaciones de prueba**, no resultados. E2-D02 está **aprobada** (ALC-03); el riesgo E2-D03 se controla con clave común solo UAT y rotación individual obligatoria antes de producción.

Para ejecutar las nuevas pruebas, usar [Asignación por iteración](asignacion-iteraciones.md) y [Matriz principal](casos-prueba.md). Las pruebas están diseñadas, no ejecutadas.

## Pruebas V4
[Casos completos](casos-prueba.md), [asignación por iteraciones](asignacion-iteraciones.md) y [Etapa 4 / checklist productivo](../07-auditorias/etapa-4-produccion-y-privacidad.md). **CA-91…CA-100** prueban 11 tablas, consulta total, tipo/número sin subtipo y respaldo. Ningún caso está ejecutado.

**Validación de estructura I00:** [script](../../scripts/validar-sdd-i00.cjs), [guía](../08-ia/I00-verificacion-estatica-y-handoff.md). No es una prueba SQL o E2E; registrar evidencia por entorno.

## I01 — evidencia ejecutada en desarrollo/CI (02/11/2026)
La implementación I01 ejecuta los casos obligatorios con automatización real:

| Bloque | Comando | Resultado local |
|---|---|---|
| Casos CA-01…14, CA-56…60, CA-61…67, CA-76, CA-84, CA-91/92/94, CA-110/112/113/114 | `php tests/api/i01.php --base-url=... --json=i01-evidencia.json` | 36 PASS / 0 FAIL / 0 BLOCKED |
| Unitarias e integración MySQL | `cd backend && vendor/bin/phpunit` | 64 pruebas / 154 aserciones |
| UX React I01 | `cd frontend && npm test` | 8 pruebas (login, contexto, expiración, GPS, 409, REPORTES) |
| Validadores SDD | `npm test` | OK |
| Build React | `cd frontend && npm run build` | OK |

Los estados son de ejecución local con datos sintéticos y MySQL de prueba; la evidencia CI con
MySQL 8 se adjunta al PR. **No constituyen GO productivo** ni sustituyen E-07, H-14, H-40 ni la
revisión del especialista. Consultar [tests/README](../../tests/README.md) para comandos y variables.
