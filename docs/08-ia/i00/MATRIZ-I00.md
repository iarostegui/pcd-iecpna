# I00 — Matriz de verificación técnica

**Fecha:** 02/11/2026 · **Rama:** `feature/i00-validacion-entorno` · **Commit:** `aadde22`
**Evidencia CI:** [run 41443882284](https://github.com/iarostegui/pcd-iecpna/actions/runs/41443882284) (jobs `documentos`, `backend`, `frontend`, `i00`)
**Motor:** MySQL **8.0.46** en servicio `mysql:8.0` de GitHub Actions · **PHP:** 8.2.35

**Entorno local:** sin MySQL ni Argon2id nativos; las comprobaciones de motor se ejecutan en CI y localmente se reportan `BLOCKED`. No se inventan versiones del hosting.

## 1. Entorno

| ID | Control | Evidencia | Local | CI |
|---|---|---|---|---|
| E-01 | PHP >= 8.2 | `PHP_VERSION` | PASS (8.2.29) | PASS (8.2.35) |
| E-02 | Extensiones `pdo`, `pdo_mysql`, `mbstring`, `json`, `openssl`, `session` | `extension_loaded` | PASS | PASS |
| E-03 | Composer disponible | `composer --version` | PASS (phar local) | PASS (2.11.1) |
| E-04 | Node >= 20 y npm | `node -v`, `npm -v` | PASS (v24.22.2) | PASS (v22.23.4) |
| E-05 | Build React/TS/Vite | `frontend/dist/index.html` | PASS | PASS |
| E-06 | Variables documentadas sin secretos | `backend/.env.example` | PASS | PASS |
| E-07 | Hosting cPanel real (PHP/MySQL/HTTPS) | Acceso del operador | **BLOCKED** | **BLOCKED** |
| E-08 | Despliegue sin Node residente | `.htaccess` + DocumentRoot | PASS | PASS |

## 2. Base de datos (MySQL 8 real)

| ID | Control | Evidencia | Local | CI |
|---|---|---|---|---|
| D-01 | 11 tablas exactas | `information_schema.tables` | BLOCKED | PASS (11) |
| D-02 | 146 columnas | `information_schema.columns` | BLOCKED | PASS (146) |
| D-03 | 23 claves foráneas | `table_constraints` | BLOCKED | PASS (23) |
| D-04 | 11 restricciones UNIQUE esperadas | `table_constraints` | BLOCKED | PASS (11) |
| D-05 | 4 restricciones CHECK | `check_constraints` | BLOCKED | PASS (4) |
| D-06 | Columna generada `nombre_raiz_norm` STORED | `columns.extra` | BLOCKED | PASS |
| D-07 | Índices de idempotencia | `uq_idem_usuario_clave`, `ix_idem_created_at` | BLOCKED | PASS |
| D-08 | Charset/collation `utf8mb4` / `utf8mb4_unicode_ci` | `tables`, `columns` | BLOCKED | PASS |
| D-09 | `created_at`/`updated_at` en 11 tablas | 22 columnas | BLOCKED | PASS |
| D-10 | Seed LIM/TRU/CHI/IQU, catálogo, 0 sedes ficticias | `organizacion`, `catalogo` | BLOCKED | PASS (36 filas catálogo) |
| D-11 | FK rechaza referencia inválida | inserción sintética + rollback | BLOCKED | PASS (error 23000) |
| D-12 | CHECK rechaza sede sin padre | inserción sintética + rollback | BLOCKED | PASS |
| D-13 | UNIQUE de raíz y columna generada | duplicado sintético + rollback | BLOCKED | PASS (error 23000) |
| D-14 | Aprovisionamiento UAT | 26 cuentas, 24 DIGITADORES, 26 Argon2id | BLOCKED | PASS |
| D-15 | GPS solo en `sesion` | `geo_*` únicamente en `sesion` | BLOCKED | PASS |
| D-16 | Motor MySQL 8.0.16+ | `SELECT VERSION()` | BLOCKED | PASS (8.0.46) |

## 3. Seguridad

| ID | Control | Evidencia | Local | CI |
|---|---|---|---|---|
| S-01 | `.env` ignorado y no versionado | `.gitignore`, `git ls-files` | PASS | PASS |
| S-02 | Sin claves privadas ni tokens en Git | `git grep` de patrones | PASS | PASS |
| S-03 | UAT separado de producción | `bootstrap-provision.php` | PASS | PASS |
| S-04 | Argon2id | `PASSWORD_ARGON2ID` | BLOCKED (PHP local) | PASS |
| S-05 | Sesión preparada (HttpOnly/SameSite/Secure) | `Csrf.php`; emisión real en I01 | PASS | PASS |
| S-06 | HTTPS previsto en producción | cookie `Secure` en producción; TLS del hosting | PASS (código) | PASS (código) |
| S-07 | Sin PII real ni dumps | solo migración y seed | PASS | PASS |
| S-08 | GPS opcional, una vez por sesión | comportamiento funcional I01 | NOT_IMPLEMENTED | NOT_IMPLEMENTED |

## 4. Pruebas y automatización

| ID | Control | Local | CI |
|---|---|---|---|
| T-01 | Validadores SDD (`npm test`) | PASS | PASS (`documentos`) |
| T-02 | PHPUnit | PASS 43 pruebas / 92 aserciones / 7 omitidas | PASS 43 / 115 (sin omitidas) |
| T-03 | Runner HTTP | PASS 19 / FAIL 0 / BLOCKED 1 / NI 3 (23) | PASS 20 / FAIL 0 / BLOCKED 0 / NI 3 (23) |
| T-04 | Build frontend (`npm ci && npm run build`) | PASS | PASS |
| T-05 | Integración MySQL (PHPUnit) | BLOCKED (sin motor) | PASS |
| T-06 | Aprovisionamiento idempotente | BLOCKED (sin Argon2id/MySQL) | PASS: `creados=0, existentes=26, conflictos=0` |

**Resumen CI:** `PASS=30, FAIL=0, BLOCKED=1 (E-07 hosting), NOT_IMPLEMENTED=1 (S-08, I01)` sobre 32 controles.
**Resumen local:** `PASS=13, FAIL=0, BLOCKED=18, NOT_IMPLEMENTED=1`; los bloqueos son ausencia de MySQL/Argon2id, no fallos.

## 5. Restricciones del hosting

| Requisito | Estado | Nota |
|---|---|---|
| PHP 8.2 con PDO/`pdo_mysql`/mbstring/openssl | **BLOCKED** | Sin acceso a cPanel; verificar versión real. |
| MySQL 8.0.16+ (CHECK aplicado) | **BLOCKED** | CI usa 8.0.46; el hosting debe confirmarse. |
| HTTPS y cookie `Secure` | **BLOCKED** | TLS del subdominio no verificable desde el repo. |
| DocumentRoot en `backend/public` y `.htaccess` | **BLOCKED** | Debe verificarse en cPanel. |
| Sin Node residente | PASS conceptual | Build estático `frontend/dist`. |
| Secretos fuera del repositorio | PASS | Variables de entorno; `.env` ignorado. |
| Backup/restore y carga real | **BLOCKED** | Requiere entorno y ventana de operación. |

## 6. Comandos reproducibles

```bash
# 1) Base MySQL 8 vacía de pruebas
mysql -h127.0.0.1 -uroot -p -e "CREATE DATABASE pcd_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -h127.0.0.1 -uroot -p pcd_test < database/migrations/001_initial.sql
mysql -h127.0.0.1 -uroot -p pcd_test < database/seeds/002_catalogos_calidad.sql

# 2) Aprovisionamiento UAT idempotente (credenciales por entorno)
php scripts/bootstrap-provision.php --mode=uat
php scripts/bootstrap-provision.php --mode=uat

# 3) Verificación técnica (32 controles)
PCD_DB_HOST=127.0.0.1 PCD_DB_NAME=pcd_test PCD_DB_USER=root PCD_DB_PASSWORD=... \
  php scripts/i00/verificar.php --json=i00-evidencia.json

# 4) Pruebas transversales
(cd backend && vendor/bin/phpunit --configuration phpunit.xml)
php tests/api/run.php --base-url=http://127.0.0.1:8081/api/v1
(cd frontend && npm ci && npm run build)
npm test
```

## 7. Hallazgos técnicos corregidos

| Hallazgo | Corrección | Commit |
|---|---|---|
| El verificador comparaba `information_schema` en minúsculas y producía FAIL falsos | Normalización de claves a minúsculas | `008ba24` |
| `/health` mezclaba liveness con dependencia y exponía el entorno | `/health` liveness y `/ready` readiness; sin `env` | `0ab95e2` (P01) |
| El aprovisionamiento actualizaba cuentas existentes | Modo no destructivo + conflictos informados | `0ab95e2` (P01) |

Sin cambios en SQL, OpenAPI, prototipo ni decisiones funcionales.
