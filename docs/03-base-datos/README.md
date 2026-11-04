# Diseño de datos PCD V4.1 — índice
**Fuente de verdad:** [001_initial.sql](../../database/migrations/001_initial.sql) con **11 tablas y 146 columnas** (pendiente ejecutar en hosting).

| Documento | Uso |
|---|---|
| [Modelo conceptual](modelo-conceptual.md) | Tres entidades de captura Persona/Ficha/GC, organización y control. |
| [Modelo lógico](modelo-logico.md) | Las 11 tablas y relaciones vigentes. |
| [Modelo físico](modelo-fisico.md) | Integridad, scripts y referencia a anexos. |
| [Anexo 01 — Diagrama físico](anexos/anexo-01-diagrama-fisico.md) | Mermaid de tablas, campos y relaciones. |
| [Anexo 02 — Diccionario columna a columna](anexos/anexo-02-diccionario-fisico.md) | 146 columnas, restricciones, tipos y defaults. |
| [Justificación de tablas](anexos/justificacion-11-tablas.md) | Razón para mantener cada tabla. |
| [Diccionario resumido](diccionario-datos.md) | Campos principales por área. |
| [Catálogos e identidad](catalogos-e-identidad.md) | Una tabla CATALOGO, documento sin subtipo, UBIGEO I02. |
| [Fuente recibida de ubigeos (I02)](fuentes/UBIGEO-RPT-2026-11-04.md) | Libro `rptUbigeo.xlsx` custodiado en Biblioteca; trazabilidad, conteo 25/196/1893, hashes SHA256, derivado CSV/SQL y discrepancia con fuentes INEI. |
| [Campos críticos](campos-criticos.md) | Parametrización mediante CATALOGO.config_json. |
| [Diagrama editable](diagramas/er-pcd-fisico.mmd) | Fuente Mermaid versionada. |

**No usar** el diseño previo de 17 tablas ni crear tablas de listas cortas; **no** incluir `alcance_consulta` ni `tipo_documento_detalle`.

**Control posterior:** [Etapa 5](../07-auditorias/etapa-5-preparacion-i00.md) mantiene el esquema V4 de 11 tablas y obliga a comprobar versiones SQL/hosting; los anexos describen diseño, no demuestran su ejecución.

## Estructura JSON V4.1
[Esquemas cerrados Persona/GC V1.0](esquemas-json/README.md), validados con PHP; no JSON arbitrario ni tablas nuevas para cónyuge. SQL con presbiterios precargados; auditoría de edición, no inserción de fichas.

**Contrato de estructuras fuente:** [JSON Schema V1](esquemas-json/README.md), obligatorio antes de I02.
