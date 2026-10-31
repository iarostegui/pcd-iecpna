# 05 — Verificación funcional, seguridad y rendimiento

[↑ docs](../README.md) · [↑ Índice principal](../../README.md)

Contiene el **diseño de las pruebas** para comprobar requisitos de PCD y el rendimiento en hosting compartido.

| Archivo | Contenido |
|---|---|
| [casos-prueba.md](casos-prueba.md) | Casos CA-01…CA-55: login, sedes, Persona, GC, duplicados, edición, auditoría, reportes, privacidad y despliegue. |
| [pruebas-rendimiento.md](pruebas-rendimiento.md) | Escenarios de carga, métricas p50/p95 y evaluación de impacto sobre el sitio que comparte hosting. |

**Distinción esencial:** un caso **documentado** no es una prueba **ejecutada**. Los resultados reales, evidencias y scripts aparecerán en [tests/](../../tests/README.md) una vez que exista código. No marcar `PASÓ` sin ejecución verificable.
