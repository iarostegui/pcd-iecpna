# 04 — Contratos API e integración futura

[↑ docs](../README.md) · [↑ Índice principal](../../README.md)

Define la frontera **React ↔ PHP** dentro de PCD y documenta la eventual migración de datos a SGSP como trabajo **posterior**.

| Archivo | Contenido |
|---|---|
| [contratos-api.md](contratos-api.md) | Explicación funcional de endpoints, permisos, errores, idempotencia y secuencias. |
| [openapi.yaml](openapi.yaml) | Contrato formal propuesto para implementar y probar la API REST PCD. |
| [estrategia-migracion-sgsp.md](estrategia-migracion-sgsp.md) | Preparación de identificadores y calidad para un **futuro** proceso BD PCD → BD SGSP. |

**Regla:** la IA debe mantener sincronizados servicios, esquema OpenAPI, autorizaciones backend y casos de prueba. **No implementar ahora conexión ni migración a la base de SGSP.**

## Contratos E2
- [OpenAPI V3.1.1](openapi.yaml) contiene los endpoints públicos restringidos y contratos de sesión/contexto de la etapa 2.
- [Identidad y catálogos](../03-base-datos/catalogos-e-identidad.md) y [criticidad](../03-base-datos/campos-criticos.md) sustentan esquema y valores iniciales.
- Las reglas E2-D01/D02/D03 no están ratificadas. El archivo YAML es **diseño propuesto**, no servicio ejecutado.

## Etapa 3
La [especificación OpenAPI 3.2](openapi.yaml) y el [levantamiento E3](../07-auditorias/etapa-3-contratos-pruebas.md) contienen los esquemas de respuesta/filtros. La conformidad formal de YAML y las pruebas reales siguen pendientes.

## Contrato vigente PCD V4
[OpenAPI 4.0](openapi.yaml) utiliza **11 tablas**, consulta general con filtros de presbiterio/sede, documentos solo tipo+número y tabla de catálogos única. [Contratos explicados](contratos-api.md) y [Etapa 4](../07-auditorias/etapa-4-produccion-y-privacidad.md). Documentos históricos de E2/E3 incompatibles quedan reemplazados.
