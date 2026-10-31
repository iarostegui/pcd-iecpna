# 03 — Análisis y diseño de base de datos PCD

[↑ docs](../README.md) · [↑ Índice principal](../../README.md)

Contiene el **diseño de la base propia de PCD**, distinta de la base futura del SGSP. Describe entidades, relaciones, atributos, reglas de integridad y decisiones de persistencia.

| Archivo/directorio | Propósito |
|---|---|
| [modelo-conceptual.md](modelo-conceptual.md) | Entidades de negocio y cardinalidades sin fijar detalles SQL. |
| [modelo-logico.md](modelo-logico.md) | Tablas lógicas y relaciones, claves y restricciones. |
| [modelo-fisico.md](modelo-fisico.md) | Normas MySQL/MariaDB, índices, tipos, concurrencia y transacciones. |
| [diccionario-datos.md](diccionario-datos.md) | Campos y características de cada entidad PCD. |
| [diagramas/](diagramas/README.md) | Diagrama entidad–relación de PCD en Mermaid. |
| [database/migrations/001_initial.sql](../../database/migrations/001_initial.sql) | Script inicial propuesto para crear el esquema físico; **no ejecutado ni validado aún en el hosting**. |

**Puntos críticos:** número de ficha opcional; documento único solo cuando es válido/presente; fichas múltiples por Persona; registros incompletos; calidad parametrizable; auditoría con autor original y ubicación de sesión; integridad sede–presbiterio.

**Flujo de revisión:** [diccionario de captura](../01-analisis/diccionario-captura.md) → modelo conceptual → modelo lógico → ER → modelo físico → SQL → pruebas. No asumir compatibilidad total con el esquema SGSP.

## Artefactos E2
- [catalogos-e-identidad.md](catalogos-e-identidad.md): códigos del papel, `tipo_documento_detalle`, normalización segura, clave identidad opcional y ubigeo pendiente.
- [campos-criticos.md](campos-criticos.md): matriz inicial del prototipo, campos desactivables y cálculo de reportes según configuración vigente.
- [001_initial.sql](../../database/migrations/001_initial.sql): propuesta física revisada. **NO ejecutada**, requiere prueba de CHECK y FK compuestas en hosting.
- [002_catalogos_calidad.sql](../../database/seeds/002_catalogos_calidad.sql): semillas técnicas basadas en fuente, sin datos personales.

## Entregables físicos obligatorios I00
- [Anexo 01 — Diagrama físico completo](anexos/anexo-01-diagrama-fisico.md), con fuente [Mermaid editable](diagramas/er-pcd-fisico.mmd).
- [Anexo 02 — Diccionario físico por columna](anexos/anexo-02-diccionario-fisico.md).
Ambos derivados del SQL vigente (17 tablas, 109 columnas). Actualizar con cada cambio. **No ejecutado todavía en motor MySQL/MariaDB.**
