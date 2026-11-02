# tests — Pruebas automatizadas y evidencias

[↑ Índice principal](../README.md)

**Estado:** I01 incorpora PHPUnit (unitarias + integración MySQL), runner HTTP funcional y pruebas UX
React. Los casos obligatorios de I01 se ejecutan contra MySQL real; los de I02–I05 siguen sin
implementación. **Un caso documentado no es un caso ejecutado.**

## Backend (PHPUnit)
```bash
cd backend
composer install
vendor/bin/phpunit --configuration phpunit.xml --testdox
```
- Unitarias: router, errores, entorno, CSRF, PDO, aprovisionamiento, normalización NFKD, validación,
  JSON canónico, hashing y contexto de sesión.
- Integración: cuentas, hashing fuerte, idempotencia y conflictos; se **omite** si no existe `PCD_DB_HOST`.

## Runner HTTP I01
```bash
php tests/api/i01.php --base-url=http://127.0.0.1:8081/api/v1 --json=i01-evidencia.json
```
Requiere MySQL real con `001_initial.sql`, `002_catalogos_calidad.sql`, aprovisionamiento UAT y las
variables `PCD_UAT_DIGITADOR_PASSWORD`, `PCD_ADMIN_PASSWORD`, `PCD_REPORTES_PASSWORD` (nunca en Git).
Cubre CA-01…CA-14, CA-56…CA-60, CA-61…CA-67, CA-76, CA-84, CA-91, CA-92, CA-94, CA-110,
CA-112, CA-113, CA-114 y los escenarios negativos de sesión, CSRF, revocación, sede ajena,
concurrencia e idempotencia. Escribe evidencia JSON con estados `PASS/FAIL/BLOCKED` y sale con
código 1 si existe un fallo o bloqueo.

## Runner HTTP P01
```bash
php tests/api/run.php --base-url=http://127.0.0.1:8081/api/v1
```
Verifica liveness/readiness, sobre de error, 404/405/400, autenticación mínima, CSRF, CORS y
cabeceras. Ya no espera placeholders 501 de I01.

## Frontend (Vitest + Testing Library)
```bash
cd frontend
npm test
```
Cubre CA-101 (sin enumeración de cuentas), flujo login/contexto/logout, expiración por 401,
GPS denegado una sola vez y 409 de sede duplicada; comprueba que no se usa `localStorage`.

## Evidencia CI I01
Run [41451546914](https://github.com/iarostegui/pcd-iecpna/actions/runs/41451546914): `backend` con MySQL 8.0.46/PHP 8.2.35, runner I01 39/39 PASS, PHPUnit 64/153 (1 omitida por Argon2id), runner P01 23/23; `frontend` 8/8 y build; `i00` 31 PASS/0 FAIL/1 BLOCKED (E-07); artefacto `i01-evidencia`.

## Reglas de evidencia
Cada ejecución registra comando, commit, entorno y resultado. **No marcar PASS sin ejecución** y
nunca usar datos personales reales. El CI con MySQL 8 es la evidencia de motor; el entorno local sin
MySQL reporta `BLOCKED`.
