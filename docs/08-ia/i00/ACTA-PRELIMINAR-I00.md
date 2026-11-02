# Acta preliminar I00 — GO/NO-GO

**Fecha:** 02/11/2026 · **Rama:** `feature/i00-validacion-entorno` · **Commit:** `aadde22`
**Evidencia:** [run 41443882284](https://github.com/iarostegui/pcd-iecpna/actions/runs/41443882284) · [Matriz I00](MATRIZ-I00.md)

## 1. Veredicto

**NO-GO para el cierre formal de I00.**

Las verificaciones técnicas ejecutables están en verde, pero **permanecen bloqueos obligatorios** que impiden declarar I00 cerrada y, por tanto, **no se autoriza iniciar I01**:

1. **E-07 — hosting cPanel real:** sin acceso para verificar PHP/MySQL/HTTPS/PDO, DocumentRoot, `.htaccess` y políticas del proveedor.
2. **H-14 — XLSX original:** insumo no versionado.
3. **I00-08 — concurrencia/rollback de fichas:** depende de I02; no verificable en I00.
4. **H-40 — privacidad/retención:** decisión institucional previa a datos reales.
5. **Revisión del especialista:** pendiente según AGENTS; no abrir nueva auditoría formal antes de su evaluación.

## 2. Comprobaciones técnicas completadas (CI)

| Grupo | Resultado |
|---|---|
| Entorno (PHP 8.2, extensiones, Composer, Node, build, `.env`, despliegue) | PASS, salvo cPanel BLOCKED |
| Base de datos MySQL 8.0.46 | **16/16 PASS**: 11 tablas, 146 columnas, 23 FK, 11 UNIQUE, 4 CHECK, columna generada, idempotencia, utf8mb4, timestamps, seed, enforcement FK/CHECK/UNIQUE |
| Seguridad | PASS en `.env`, secretos, UAT/producción, Argon2id, sesión y HTTPS previsto; GPS funcional en I01 |
| Aprovisionamiento | 26 cuentas (24 DIGITADORES + ADMIN + REPORTES), 26 Argon2id, segunda ejecución `conflictos=0` |
| PHPUnit | 43 pruebas / 115 aserciones (integración MySQL incluida) |
| Runner HTTP | 20 PASS / 0 FAIL / 0 BLOCKED / 3 NOT_IMPLEMENTED |
| Frontend | `npm ci && npm run build` correcto |
| Validadores SDD | `npm test` correcto |

**Resumen del verificador:** `PASS=30, FAIL=0, BLOCKED=1, NOT_IMPLEMENTED=1` sobre 32 controles.

## 3. Diferencia entre verificación técnica y aprobación institucional

| Elemento | Estado |
|---|---|
| Modelo físico y restricciones en MySQL 8 | **Verificado** (CI) |
| Entorno PHP/Node/Composer y build | **Verificado** (local y CI) |
| Seguridad de secretos y aprovisionamiento UAT | **Verificado** |
| Hosting cPanel y TLS reales | **NO verificado** (BLOCKED) |
| Privacidad, retención y base legal (H-40) | **Sin aprobación institucional** |
| Aprobación del especialista | **Pendiente** |
| Datos reales | **Prohibidos** hasta GO institucional |

El PASS técnico **no sustituye** las aprobaciones institucionales ni el GO de producción.

## 4. Condiciones para pasar a GO de cierre I00

1. Verificar en cPanel: PHP 8.2 + extensiones, MySQL 8.0.16+, HTTPS, DocumentRoot y `.env` fuera del repositorio; registrar evidencia sin secretos.
2. Versionar el XLSX original con SHA (H-14) o registrar decisión formal de sustitución.
3. Cerrar H-40 (finalidad, base legal, retención, responsables) o mantener explícitamente prohibidos los datos reales.
4. Obtener revisión del especialista y aprobación del responsable funcional.
5. Repetir la matriz I00 en el entorno real y firmar acta definitiva.

## 5. Alcance respetado

- Sin cambios en SQL, OpenAPI, prototipo V2.3 ni decisiones funcionales.
- Sin módulos SGSP ni datos personales reales.
- Sin merge a `main`; el PR permanece Draft.
- No se inició I01.
