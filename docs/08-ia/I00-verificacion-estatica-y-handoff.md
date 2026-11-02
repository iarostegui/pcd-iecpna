# I00 — Verificación estática y entrega a implementación
**Fecha:** 02/11/2026 · **Estado:** verificación técnica ejecutada sobre **MySQL 8 (CI)**; hosting cPanel, H-14 y H-40 mantienen el cierre **NO-GO**. Ver [matriz](i00/MATRIZ-I00.md) y [acta preliminar](i00/ACTA-PRELIMINAR-I00.md).

## 1. Línea base vigente
1. [Alcance PCD](../01-analisis/alcance-funcional.md): app temporal, no SGSP.
2. [Decisiones V4](../01-analisis/decisiones-pendientes.md): consulta general por función y filtros opcionales presbiterio/sede; edición propia; documento solo tipo + número.
3. [SQL 11 tablas](../../database/migrations/001_initial.sql) y [seed](../../database/seeds/002_catalogos_calidad.sql).
4. [Diagrama](../03-base-datos/anexos/anexo-01-diagrama-fisico.md) y [diccionario físico](../03-base-datos/anexos/anexo-02-diccionario-fisico.md).
5. [OpenAPI V4](../04-integracion/openapi.yaml); [PCD V2.3](../../prototypes/PCD_v2.3.html), referencia UX demostrativa, no backend.
6. [Protocolo de auditoría de alcance](../07-auditorias/protocolo-auditoria-alcance.md).

## 2. Prueba de consistencia previa a ejecución
Ejecutar desde la raíz del repositorio:
```bash
node scripts/validar-sdd-i00.cjs
node scripts/validar-prototipo-v2.3.cjs
```
La primera verificación debe fallar cuando el SQL tenga un número distinto de **11 tablas**, el diccionario no cubra **146 columnas**, el OpenAPI tenga referencias locales sin definición, exista `usuario_presbiterio`, `alcance_consulta`, `tipo_documento_detalle`, o haya incongruencia de rutas de anexos. **No reemplaza** lint YAML formal ni motor SQL, solo detecta regresiones frecuentes.

## 3. Tareas reales I00 todavía necesarias
| ID | Actividad | Evidencia exigida | Estado |
|---|---|---|---|
| I00-01 | Clonar repo privado y ejecutar ambos scripts | Salida/versión Node + commit SHA | **PASS** — validadores local y CI (run 41443882284) |
| I00-02 | Versionar el **XLSX original de fichas** con SHA declarado H-14, preservando hojas de ejemplo separadas de producción | Archivo y hash verificado | **PENDIENTE H-14** |
| I00-03 | Verificar versión PHP, MariaDB/MySQL, HTTPS y PDO en cPanel | Captura/registro sin secretos | **BLOCKED** — sin acceso a cPanel |
| I00-04 | Crear una BD **de prueba** vacía; ejecutar 001 y luego 002 | Logs de aplicación sin PII | **PASS** — MySQL 8.0.46 en CI |
| I00-05 | Probar 11 tablas, 146 columnas, 23 FK y restricciones CHECK bajo motor real | Consultas information_schema, tests de FK/CHECK | **PASS** — D-01…D-16 |
| I00-06 | Ejecutar lint OpenAPI 3.0.3 con parser formal y revisar rutas/DTO | Reporte de parser | **PARCIAL** — sintaxis/refs validadas; lint 3.0.3 estricto NOT_IMPLEMENTED |
| I00-07 | Verificar integridad de sedes: tipo SEDE, padre PRESBITERIO; ubigeo por nivel | Casos SQL/PHP de rechazo | **PARCIAL** — CHECK y 0 sedes PASS; validación PHP del tipo padre en I01 |
| I00-08 | Verificar recuperación/rollback ante reintento y conflicto de creación | CA-26, CA-42 y evidencia | **NOT_IMPLEMENTED** — requiere I02 (fichas) |
| I00-09 | Registrar línea base I00 para I01 y riesgos residuales | ACTA GO/NO-GO con responsables | **PRELIMINAR** — acta emitida; cierre NO-GO por E-07/H-40 |

## 4. Riesgos y fronteras
- **No** desplegar 001 sobre una base existente con datos; requerir migración propia.
- Revisión documental **no prueba** creación de FK compuestas ni jerarquías referenciales. El CI ya verifica FK/CHECK/UNIQUE en MySQL 8; la validación PHP del tipo de padre de sede pertenece a I01.
- `UBIGEO` oficial se importa durante **I02**, no llenar filas sintéticas como si fueran INEI.
- Datos de fichas, incluyendo atributos religiosos, requieren controles H-40 antes de producción con datos reales. No crear roles/supervisores ni tablas nuevas.
- E1-D01 está **aprobada**; E1-D02, H-23 y H-24 continúan abiertos; **no impedir I00/I01 por una regla propia de I02/I03**.

## 5. Resultado
I00 tiene **evidencia técnica reproducible en MySQL 8** (run 41443882284): esquema, restricciones, seed, aprovisionamiento Argon2id, PHPUnit, runner HTTP y build del frontend. **El cierre formal sigue NO-GO** por cPanel no verificado (E-07), H-14, H-40 y la revisión del especialista. No existe GO operativo ni autorización para I01; ver [acta](i00/ACTA-PRELIMINAR-I00.md).

## Comprobador V4.1
Después de `npm ci` (instalación reproducible desde `package-lock.json`), ejecutar `npm test` (`js-yaml` analizador formal y comparación de columnas/tipos SQL↔diccionario). Se incluye workflow CI en cada push/PR. Se requiere **prueba SQL real** de `nombre_raiz_norm`, `created_at/updated_at`, índices y CHECK antes de marcar I00 completo. [Entrega al especialista](../07-auditorias/ENTREGA-REVISION-ESPECIALISTA-V4.1.md).

## Evidencia de CI
Run [41443882284](https://github.com/iarostegui/pcd-iecpna/actions/runs/41443882284): jobs `documentos`, `backend` (MySQL 8), `frontend` e `i00` en verde. El verificador I00 reporta `PASS=30, FAIL=0, BLOCKED=1 (E-07), NOT_IMPLEMENTED=1 (S-08)`; PHPUnit 43/115, runner 20/0/0/3 y aprovisionamiento 26 cuentas Argon2id. Esta evidencia **no** cubre cPanel, H-40 ni backups. [Algoritmos obligatorios para IA](CONTRATO-EJECUCION-V4.1.md).
