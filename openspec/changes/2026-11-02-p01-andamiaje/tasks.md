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

## Verificación exigida
| Evidencia | Cómo se comprueba | Estado |
|---|---|---|
| Backend por HTTP | `GET /api/v1/health` con servidor PHP | Pendiente de ejecución |
| Frontend compila | `npm ci && npm run build` | Pendiente |
| Dependencias reproducibles | `composer install` (lock) y `npm ci` (lock) | Pendiente |
| PHPUnit | `backend/vendor/bin/phpunit` | Pendiente |
| Runner HTTP | `php tests/api/run.php` | Pendiente |
| Aprovisionamiento real | Bootstrap + consultas en MySQL 8 (CI) | Pendiente |
| SQL/OpenAPI intactos | `git diff main -- database docs/04-integracion/openapi.yaml` | Pendiente |
| Sin alcance indebido | Revisión de rutas y archivos | Pendiente |

**Regla:** este archivo no se marca completo por código generado; solo por ejecución verificable. Los estados finales se registran en el PR.
