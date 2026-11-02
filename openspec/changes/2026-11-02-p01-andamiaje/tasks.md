# P01 — Tareas y verificación

## Construcción
- [x] Registrar propuesta, diseño, spec y tareas del cambio.
- [x] Backend Composer/PSR-4 con router, errores, PDO y configuración por entorno.
- [x] Endpoint de salud y placeholders 501 para rutas de I01.
- [x] CSRF y CORS preparados con pruebas unitarias.
- [x] Script de aprovisionamiento UAT y modo separado `admin-prod`.
- [x] Frontend React/TS/Vite con cliente HTTP centralizado.
- [x] PHPUnit y runner HTTP con estados PASS/FAIL/BLOCKED/NOT_IMPLEMENTED.
- [x] CI con MySQL 8, PHPUnit, runner y build de frontend.
- [x] READMEs, AGENTS simplificado y CHANGELOG actualizados.

## Verificación ejecutada
| Evidencia | Resultado | Estado |
|---|---|---|
| Backend por HTTP | `GET /api/v1/health` → 200 con `request_id` y `database.status=up` en CI | Verificado |
| Frontend compila | `npm ci && npm run build` (local y CI) → `dist/` | Verificado |
| Dependencias reproducibles | `composer install` desde `composer.lock`; `npm ci` desde `package-lock.json` | Verificado |
| PHPUnit | 28 pruebas, 65 aserciones (integración MySQL incluida) en CI | Verificado |
| Runner HTTP | 22 verificaciones: CI 19 PASS / 0 FAIL / 0 BLOCKED / 3 NOT_IMPLEMENTED; local 18 PASS / 1 BLOCKED (sin MySQL) | Verificado |
| Aprovisionamiento real | MySQL 8: 26 usuarios (24 DIGITADORES + ADMIN + REPORTES), 26 hashes Argon2id; segunda ejecución creados=0, existentes=26 | Verificado |
| SQL/OpenAPI intactos | `git diff main -- database docs/04-integracion/openapi.yaml prototypes` vacío | Verificado |
| Sin alcance indebido | Rutas solo salud/placeholder; sin login, captura, consulta, reportes ni SGSP | Verificado |

**Evidencia CI:** ejecución [41438002349](https://github.com/iarostegui/pcd-iecpna/actions/runs/41438002349) (jobs `documentos`, `backend` con MySQL 8 y `frontend`). Los estados locales sin MySQL se reportan como BLOCKED, nunca como PASS.
