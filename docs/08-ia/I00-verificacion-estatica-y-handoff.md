# I00 — Verificación estática y entrega a implementación
**Fecha:** 01/11/2026 · **Estado:** documentación lista para comprobar en un clon; **NO** equivale a SQL ejecutado ni entorno habilitado.

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
La primera verificación debe fallar cuando el SQL tenga un número distinto de **11 tablas**, el diccionario no cubra **134 columnas**, el OpenAPI tenga referencias locales sin definición, exista `usuario_presbiterio`, `alcance_consulta`, `tipo_documento_detalle`, o haya incongruencia de rutas de anexos. **No reemplaza** lint YAML formal ni motor SQL, solo detecta regresiones frecuentes.

## 3. Tareas reales I00 todavía necesarias
| ID | Actividad | Evidencia exigida | Estado |
|---|---|---|---|
| I00-01 | Clonar repo privado y ejecutar ambos scripts | Salida/versión Node + commit SHA | **PENDIENTE DE EJECUCIÓN EN CLON** |
| I00-02 | Versionar el **XLSX original de fichas** con SHA declarado H-14, preservando hojas de ejemplo separadas de producción | Archivo y hash verificado | **PENDIENTE** |
| I00-03 | Verificar versión PHP, MariaDB/MySQL, HTTPS y PDO en cPanel | Captura/registro sin secretos | **PENDIENTE** |
| I00-04 | Crear una BD **de prueba** vacía; ejecutar 001 y luego 002 | Logs de aplicación sin PII | **PENDIENTE** |
| I00-05 | Probar 11 tablas, 134 columnas, 23 FK y restricciones CHECK bajo motor real | Consultas information_schema, tests de FK/CHECK | **PENDIENTE** |
| I00-06 | Ejecutar lint OpenAPI 3.0.3 con parser formal y revisar rutas/DTO | Reporte de parser | **PENDIENTE** |
| I00-07 | Verificar integridad de sedes: tipo SEDE, padre PRESBITERIO; ubigeo por nivel | Casos SQL/PHP de rechazo | **PENDIENTE** |
| I00-08 | Verificar recuperación/rollback ante reintento y conflicto de creación | CA-26, CA-42 y evidencia | **PENDIENTE DE IMPLEMENTACIÓN** |
| I00-09 | Registrar línea base I00 para I01 y riesgos residuales | ACTA GO/NO-GO con responsables | **PENDIENTE** |

## 4. Riesgos y fronteras
- **No** desplegar 001 sobre una base existente con datos; requerir migración propia.
- Revisión documental **no prueba** creación de FK compuestas ni jerarquías referenciales. El PHP valida tipos y relaciones de organización/ubigeo; los CHECK no garantizan por sí solos que el padre de una sede sea presbiterio.
- `UBIGEO` oficial se importa durante **I02**, no llenar filas sintéticas como si fueran INEI.
- Datos de fichas, incluyendo atributos religiosos, requieren controles H-40 antes de producción con datos reales. No crear roles/supervisores ni tablas nuevas.
- E1-D01, E1-D02, H-23 y H-24 continúan abiertos; **no impedir I00/I01 por una regla propia de I02/I03**.

## 5. Resultado
El entregable documental de I00 identifica herramientas, controles, bloqueos y ruta de verificación. **No existe evidencia de ejecución en hosting**, código PHP/React productivo, restauración de backups ni GO operativo. La siguiente actividad técnica se ejecuta al disponer del entorno y de los secretos por vía segura.
