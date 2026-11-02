# 04 — Contratos API e integración futura

[↑ docs](../README.md) · [↑ Índice principal](../../README.md)

Define la frontera **React ↔ PHP** dentro de PCD y documenta la eventual migración de datos a SGSP como trabajo **posterior**.

| Archivo | Contenido |
|---|---|
| [contratos-api.md](contratos-api.md) | Explicación funcional de endpoints, permisos, errores, idempotencia y secuencias. |
| [openapi.yaml](openapi.yaml) | Contrato formal propuesto para implementar y probar la API REST PCD. |
| [estrategia-migracion-sgsp.md](estrategia-migracion-sgsp.md) | Preparación de identificadores y calidad para un **futuro** proceso BD PCD → BD SGSP. |

**Regla:** la IA debe mantener sincronizados servicios, esquema OpenAPI, autorizaciones backend y casos de prueba. **No implementar ahora conexión ni migración a la base de SGSP.**

## Contratos vigentes V4.1
- [OpenAPI 4.1](openapi.yaml) (`info.version: 4.1.0`) contiene endpoints de sesión/contexto, organización, fichas y reportes.
- [Identidad y catálogos](../03-base-datos/catalogos-e-identidad.md) y [criticidad](../03-base-datos/campos-criticos.md) sustentan esquema y valores iniciales.
- E2-D01/D02/D03 quedaron resueltas por V4/ALC-03; E2-D02 consta en [aprobaciones](../08-ia/APROBACIONES.md). El YAML es **diseño propuesto**, no servicio ejecutado.
- [OpenAPI 4.1](openapi.yaml) usa **11 tablas**, consulta general con filtros de presbiterio/sede, documentos solo tipo+número y tabla de catálogos única. [Contratos explicados](contratos-api.md) · [Etapa 4](../07-auditorias/etapa-4-produccion-y-privacidad.md).
- Documentos históricos E2/E3 incompatibles quedan reemplazados y no son orden de construcción.
