# 04 — Contratos API e integración futura

[↑ docs](../README.md) · [↑ Índice principal](../../README.md)

Define la frontera **React ↔ PHP** dentro de PCD y documenta la eventual migración de datos a SGSP como trabajo **posterior**.

| Archivo | Contenido |
|---|---|
| [contratos-api.md](contratos-api.md) | Explicación funcional de endpoints, permisos, errores, idempotencia y secuencias. |
| [openapi.yaml](openapi.yaml) | Contrato formal propuesto para implementar y probar la API REST PCD. |
| [estrategia-migracion-sgsp.md](estrategia-migracion-sgsp.md) | Preparación de identificadores y calidad para un **futuro** proceso BD PCD → BD SGSP. |

**Regla:** la IA debe mantener sincronizados servicios, esquema OpenAPI, autorizaciones backend y casos de prueba. **No implementar ahora conexión ni migración a la base de SGSP.**
