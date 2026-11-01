# specs — Requisitos funcionales vigentes PCD

[↑ openspec](../README.md) · [↑ Índice principal](../../README.md)

Estas especificaciones son los contratos **WHAT / QUÉ debe hacer PCD**, con escenarios verificables. La IA debe consultarlas antes de programar cada capacidad.

| Capacidad | Documento |
|---|---|
| [Autenticación, sesión y ubicación](autenticacion/spec.md) | Presbiterio/usuario/clave, cuentas iniciales, login y captura única GPS. |
| [Catálogos y sedes](catalogos/spec.md) | Filtrado por presbiterio, alta inmediata y deduplicación. |
| [Personas](personas/spec.md) | Captura de ficha, incompletos, coincidencias, autoría y vinculación a GC. |
| [Grupos Crecer](grupos-crecer/spec.md) | Registro de líder GC después de guardar Persona, campos propios y no sustitución destructiva. |
| [Administración](administracion/spec.md) | Usuarios por presbiterio, ADMIN y criticidad configurable. |
| [Reportes y consultas](reportes/spec.md) | Avance, productividad, calidad y consultas protegidas. |
| [Auditoría](auditoria/spec.md) | Login, cambios, autor original y ubicación por sesión. |

Para cambios usar [openspec/changes/](../changes/README.md). Mantener coherencia con [casos de prueba](../../docs/05-pruebas/casos-prueba.md) y [API](../../docs/04-integracion/openapi.yaml).

**Versión funcional V4 obligatoria:** consulta general por funcionalidad con filtros opcionales de presbiterio/sede, 11 tablas, solo tipo+número de documento, catálogo único, ubigeo I02. Deltas E1/E2/E3 anteriores no prevalecen sobre estas specs.

[SQL](../../database/migrations/001_initial.sql) · [Anexos](../../docs/03-base-datos/anexos/README.md) · [Etapa 4](../../docs/07-auditorias/etapa-4-produccion-y-privacidad.md).
