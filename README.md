# PCD — Plataforma de Captura de Datos IECPNA
> **Aplicación temporal independiente del SGSP.** Digitaliza las fichas impresas de Persona y Líder de Grupo Crecer; no implementa procesos pastorales, registro nominal de miembros GC ni migración operativa.
>
> **Fuente vigente para construcción:** **SDD V4.1**, [ALC-PCD-4.0](docs/01-analisis/alcance-funcional.md), [decisiones](docs/01-analisis/decisiones-pendientes.md), [aprobaciones](docs/08-ia/APROBACIONES.md), [OpenSpec](openspec/specs/README.md), [API](docs/04-integracion/openapi.yaml), [SQL de 11 tablas](database/migrations/001_initial.sql).
>
> **Estado:** P00 consolidó la especificación V4.1. **P01 aportó el andamiaje ejecutable** (API PHP 8.2 + React/TS/Vite). **I01 está implementado en desarrollo/CI** con login real contra MySQL, sesiones, CSRF, presbiterios/sedes, contexto ADMIN y GPS opcional de una captura por sesión; **I02 está implementado y validado en CI (PR #5)**, incluidas capturas, 2114 ubicaciones geográficas del archivo suministrado en MySQL de prueba, H-14/H-24 y pruebas de regresión; **I03–I05 siguen pendientes**. El SQL no está probado en el hosting ni existe GO productivo.

## Para empezar
1. Leer [AGENTS.md](AGENTS.md) antes de usar una IA para implementar o revisar.
2. Revisar [alcance aprobado](docs/01-analisis/alcance-funcional.md) y [cambios V4.1 para especialista](docs/07-auditorias/ENTREGA-REVISION-ESPECIALISTA-V4.1.md).
3. Revisar [modelo lógico/físico](docs/03-base-datos/README.md), [JSON fuente](docs/03-base-datos/esquemas-json/README.md), [OpenAPI](docs/04-integracion/openapi.yaml), [prototipo V2.3](prototypes/PCD_v2.3.html).
4. Ejecutar validación documental reproducible: `npm ci` y `npm test`.
5. [Plan I00–I06](docs/08-ia/plan-implementacion.md) y [checklist productivo](docs/06-despliegue/checklist-produccion.md).

### Andamiaje P01
```bash
# API PHP 8.2
(cd backend && composer install)
cp backend/.env.example backend/.env      # editar localmente
php -S 127.0.0.1:8080 -t backend/public backend/public/router.php

# Frontend React/TS/Vite
(cd frontend && npm ci && npm run dev)
```
Pruebas: `(cd backend && vendor/bin/phpunit)`, `php tests/api/run.php --base-url=http://127.0.0.1:8080/api/v1`, `php tests/api/i01.php --base-url=http://127.0.0.1:8080/api/v1 --json=i01-evidencia.json` y `(cd frontend && npm test)`.

## Directorios
| Carpeta | Uso |
|---|---|
| [docs/](docs/README.md) | Análisis, arquitectura, SQL, API, pruebas, despliegue y control documental. |
| [openspec/specs/](openspec/specs/README.md) | **Especificaciones vigentes** por capacidad. |
| [openspec/changes/](openspec/changes/README.md) | Cambios históricos y decisiones de ingeniería. No implementar versiones antiguas sin comprobación. |
| [database/](database/README.md) | SQL/semillas; cuatro presbiterios precargados, sedes editables. |
| [prototypes/](prototypes/README.md) | V2.3 = UI vigente; V2.2 = histórico. |
| [frontend/](frontend/README.md) | React/TS/Vite: login, contexto de sede, alta de sede y sesión I01; captura I02 implementada; I03–I05 pendiente. |
| [backend/](backend/README.md) | API PHP 8.2/PDO: autenticación, sesión, organización y contexto I01. |
| [tests/](tests/README.md) | PHPUnit y runner HTTP con estados verificables. |
| [scripts/](scripts/README.md) | Validadores documentales y aprovisionamiento de cuentas. |

**Cambios:** [CHANGELOG](CHANGELOG.md). **Estado real:** [estado-documental](docs/08-ia/estado-documental.md). **Protocolo de auditoría:** [AUD-PCD-01](docs/07-auditorias/protocolo-auditoria-alcance.md).

**CI I01:** [run 41456246321](https://github.com/iarostegui/pcd-iecpna/actions/runs/41456246321) con MySQL 8.0.46/PHP 8.2.35, I01 42/42, PHPUnit 70/178 (1 omitida), I00 31 PASS/0 FAIL/1 BLOCKED y frontend 8/8.

**Seguridad:** el repositorio es actualmente público; no subir PII ni contraseñas. [SECURITY.md](SECURITY.md). La decisión institucional H-40 debe cerrarse antes de datos reales.

**Contrato para construir sin interpretaciones:** [CONTRATO-EJECUCION-V4.1](docs/08-ia/CONTRATO-EJECUCION-V4.1.md). Contiene pasos transaccionales de alta/vinculación E1-D01, validación de sede, auditoría por cambios y restricciones de producción.

## Desarrollo I02 (implementación y verificación CI)
La captura Persona/Ficha de I02 se implementó en `feature/i02-captura-personas`, PR #5, con MySQL 8 de CI y **solo datos sintéticos de Persona**. La fuente de ubigeos operativa procede del XLSX entregado: 25/196/1893; **sin certificación de fecha/corte oficial INEI**. Se aprobaron H-14 y H-24, se implementó CE sin fusión automática, y se ejecutaron [28 pruebas HTTP I02 y 96 PHPUnit](docs/08-ia/I02-MATRIZ-AVANCE.md). H-40 y el GO productivo continúan pendientes.
