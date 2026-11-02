# P01 — Tareas y verificación

## Construcción
- [x] Registrar propuesta, diseño, spec y tareas del cambio.
- [x] Backend Composer/PSR-4 con router, errores, PDO y configuración por entorno.
- [x] Endpoint de liveness y readiness; placeholders 501 para rutas de I01.
- [x] CSRF y CORS preparados con pruebas unitarias.
- [x] Script de aprovisionamiento UAT y modo separado `admin-prod`.
- [x] Frontend React/TS/Vite con cliente HTTP centralizado.
- [x] PHPUnit y runner HTTP con estados PASS/FAIL/BLOCKED/NOT_IMPLEMENTED.
- [x] CI con MySQL 8, PHPUnit, runner y build de frontend.
- [x] READMEs, AGENTS simplificado y CHANGELOG actualizados.
- [x] P01-O01: separar liveness (`/health`, sin MySQL) de readiness (`/ready`, 200/503).
- [x] P01-O02: retirar entorno y configuración sensible de respuestas públicas.
- [x] P01-O03: aprovisionamiento no destructivo con conflictos informados y código 5.

## Verificación ejecutada
| Evidencia | Resultado | Estado |
|---|---|---|
| Backend por HTTP | `GET /api/v1/health` → 200 sin consultar MySQL; `GET /api/v1/ready` → 200/`up` en CI y 503/`down` en local | Verificado |
| Liveness/readiness con pruebas | Unitarias de ambos escenarios y runner con MySQL arriba/abajo | Verificado |
| Frontend compila | `npm ci && npm run build` (local y CI) → `dist/` | Verificado |
| Dependencias reproducibles | `composer install` desde `composer.lock`; `npm ci` desde `package-lock.json` | Verificado |
| PHPUnit | CI: 43 pruebas, 115 aserciones; local: 43/92 con 7 omitidas por falta de MySQL | Verificado |
| Runner HTTP | 23 verificaciones: CI 20 PASS / 0 FAIL / 0 BLOCKED / 3 NOT_IMPLEMENTED; local 19 PASS / 1 BLOCKED / 3 NOT_IMPLEMENTED | Verificado |
| Aprovisionamiento real | MySQL 8: 26 cuentas (24 DIGITADORES + ADMIN + REPORTES), 26 hashes Argon2id; segunda ejecución `creados=0, existentes=26, conflictos=0` | Verificado |
| No destructivo | Pruebas de integración: no reactiva deshabilitados, no cambia perfil ni presbiterio, no sobrescribe hashes | Verificado |
| SQL/OpenAPI intactos | `git diff main -- database docs/04-integracion/openapi.yaml prototypes` vacío | Verificado |
| Sin alcance indebido | Rutas solo salud/ready/placeholder; sin login, captura, consulta, reportes ni SGSP | Verificado |

**Evidencia CI:** ejecución [41440058874](https://github.com/iarostegui/pcd-iecpna/actions/runs/41440058874) (jobs `documentos`, `backend` con MySQL 8 y `frontend`). Los estados locales sin MySQL se reportan como BLOCKED, nunca como PASS.
