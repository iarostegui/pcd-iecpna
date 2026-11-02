# tests — Pruebas automatizadas y evidencias

[↑ Índice principal](../README.md)

**Estado:** P01 incorpora PHPUnit (unitarias + integración MySQL) y un runner HTTP sin dependencias. **Los casos funcionales CA-01…CA-125 siguen sin ejecutarse** hasta I01–I05.

## Backend (PHPUnit)
```bash
cd backend
composer install
vendor/bin/phpunit --configuration phpunit.xml --testdox
```
- Unitarias: router, errores, entorno, CSRF, PDO (DSN) y plan de aprovisionamiento.
- Integración: cuentas, Argon2id e idempotencia; se **omite** si no existe `PCD_DB_HOST`.

## Runner HTTP
```bash
php tests/api/run.php --base-url=http://127.0.0.1:8081/api/v1
```
Verifica liveness (`/health`), readiness (`/ready` con MySQL arriba/abajo), sobre de error, 404/405/400, placeholders 501, CSRF, CORS y cabeceras. Estados:

| Estado | Significado |
|---|---|
| `PASS` | Ejecutada y correcta. |
| `FAIL` | Ejecutada y fallida (el runner sale con código 1). |
| `BLOCKED` | No ejecutable por dependencia (p. ej. MySQL ausente). |
| `NOT_IMPLEMENTED` | Endpoint del contrato aún no implementado (501 esperado). |

## Reglas de evidencia
Cada ejecución registra comando, commit, entorno y resultado. **No marcar PASS sin ejecución** y nunca usar datos personales reales. El CI con MySQL 8 es la evidencia de motor; el entorno local sin MySQL reporta `BLOCKED`.
