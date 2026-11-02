# PCD — Plataforma de Captura de Datos IECPNA
> **Aplicación temporal independiente del SGSP.** Digitaliza las fichas impresas de Persona y Líder de Grupo Crecer; no implementa procesos pastorales, registro nominal de miembros GC ni migración operativa.
>
> **Fuente vigente para construcción:** **SDD V4.1**, [ALC-PCD-4.0](docs/01-analisis/alcance-funcional.md), [decisiones](docs/01-analisis/decisiones-pendientes.md), [aprobaciones](docs/08-ia/APROBACIONES.md), [OpenSpec](openspec/specs/README.md), [API](docs/04-integracion/openapi.yaml), [SQL de 11 tablas](database/migrations/001_initial.sql).
>
> **Estado:** especificaciones y prototipo HTML V2.3 disponibles; todavía NO existen frontend React/backend PHP productivos ni SQL probado en hosting.

## Para empezar
1. Leer [AGENTS.md](AGENTS.md) antes de usar una IA para implementar o revisar.
2. Revisar [alcance aprobado](docs/01-analisis/alcance-funcional.md) y [cambios V4.1 para especialista](docs/07-auditorias/ENTREGA-REVISION-ESPECIALISTA-V4.1.md).
3. Revisar [modelo lógico/físico](docs/03-base-datos/README.md), [JSON fuente](docs/03-base-datos/esquemas-json/README.md), [OpenAPI](docs/04-integracion/openapi.yaml), [prototipo V2.3](prototypes/PCD_v2.3.html).
4. Ejecutar validación estática con dependencias reproducibles: `npm ci` y `npm test` (lockfile versionado); las pruebas SQL/API reales siguen pendientes.
5. [Plan I00–I06](docs/08-ia/plan-implementacion.md) y [checklist productivo](docs/06-despliegue/checklist-produccion.md).

## Directorios
| Carpeta | Uso |
|---|---|
| [docs/](docs/README.md) | Análisis, arquitectura, SQL, API, pruebas, despliegue y control documental. |
| [openspec/specs/](openspec/specs/README.md) | **Especificaciones vigentes** por capacidad. |
| [openspec/changes/](openspec/changes/README.md) | Cambios históricos y decisiones de ingeniería. No implementar versiones antiguas sin comprobación. |
| [database/](database/README.md) | SQL/semillas; cuatro presbiterios precargados, sedes editables. |
| [prototypes/](prototypes/README.md) | V2.3 = UI vigente; V2.2 = histórico. |
| [frontend/](frontend/README.md) | Futuro React/TS/Vite. |
| [backend/](backend/README.md) | Futuro PHP REST/PDO. |
| [tests/](tests/README.md) | Pruebas automatizadas y evidencias de ejecución. |
| [scripts/](scripts/README.md) | Validadores documentales. |

**Cambios:** [CHANGELOG](CHANGELOG.md). **Estado real:** [estado-documental](docs/08-ia/estado-documental.md). **Protocolo de auditoría:** [AUD-PCD-01](docs/07-auditorias/protocolo-auditoria-alcance.md).

**Seguridad:** el repositorio es actualmente público; no subir PII ni contraseñas. [SECURITY.md](SECURITY.md). La decisión institucional H-40 debe cerrarse antes de datos reales.

**Contrato para construir sin interpretaciones:** [CONTRATO-EJECUCION-V4.1](docs/08-ia/CONTRATO-EJECUCION-V4.1.md). Contiene pasos transaccionales de alta/vinculación E1-D01, validación de sede, auditoría por cambios y restricciones de producción.
