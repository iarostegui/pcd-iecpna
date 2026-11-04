# I02 — Matriz técnica de control y límites de aprobación

**Iteración:** I02 Captura Persona/Ficha. **Entorno permitido:** GitHub Actions / MySQL 8 / datos sintéticos.
**Contrato:** V4.1, OpenAPI, SQL inmutable 11/146/23, UX V2.3.

## Implementación en rama I02
| Componente | Evidencia en código | Estado |
|---|---|---|
| Documento DNI 8 dígitos con ceros | PersonaInputValidator | IMPLEMENTADO; PASS en CI |
| CE / OTRO | PersonaInputValidator: clave_identidad=NULL | IMPLEMENTADO SIN FUSIÓN; CE confiable BLOQUEADO |
| Persona y fichas independientes | PersonaService::create | IMPLEMENTADO; PASS en CI |
| Reutilización E1-D01 | PersonaService::findByIdentity | IMPLEMENTADO; PASS en CI |
| Anexo con documento concordante | PersonaService::resolveAttach | IMPLEMENTADO; PASS en CI |
| Idempotencia | tabla idempotencia + hash payload y transacción | IMPLEMENTADO; PASS en CI |
| Sede del contexto | validación PHP + FK compuesta | IMPLEMENTADO; PASS en CI |
| Fechas/JSON fuente estrictos | PersonaInputValidator | IMPLEMENTADO; PASS en CI |
| Autoría y ausencia auditoría INSERT | ficha.created_by, sin insertar auditoria_evento | IMPLEMENTADO; PASS en CI |
| Formulario React | PersonaCapture + Workspace | IMPLEMENTADO; PASS en Vitest/build |
| Catálogos/ubigeos | consultas solo de filas existentes | IMPLEMENTADO; la importación INEI NO está hecha |
| Concurrencia identidad/Idempotency-Key | UNIQUE + reintento de deadlock + curl_multi contra MySQL 8 | **PASS** I02-HTTP-17…19 en CI; mantener pruebas de carga I06 |
| Edición PATCH Persona/Ficha | prevista para I04 | NO_IMPLEMENTADO en I02 |

## Evidencia ejecutada en MySQL 8 (CI)
Ejecución [41756308611](https://github.com/iarostegui/pcd-iecpna/actions/runs/41756308611): backend, frontend, documentos e i00 **SUCCESS**. PHPunit **91 tests / 210 assertions**; HTTP I02 **19 PASS / 0 FAIL**, incluidos dos escenarios de concurrencia real con sesiones paralelas, doble clic y fuentes diferentes; suites previas I01 sin regresión.

## Pruebas trazables (no declarar PASS por diseño)
- PHPUnit: `backend/tests/Unit/PersonaInputValidatorTest.php`, `backend/tests/Integration/PersonaCaptureTest.php`.
- HTTP: `tests/api/i02.php`, 16 controles nominados `I02-HTTP-01…16`.
- React: `frontend/src/__tests__/PersonaCapture.test.tsx` + build y suites I01 sin regresiones.
- CI: `.github/workflows/validacion-sdd.yml`, ejecución en PR #5.

## Bloqueos obligatorios
1. **H-24:** no existe criterio aprobado de «ficha totalmente vacía» frente a «ficha incompleta». La aplicación de pruebas debe usar registros con algún dato; no aceptar como cerrada esa regla sin decisión.
2. **INEI:** se ha localizado el portal oficial https://www.gob.pe/15028-consultar-codigos-estandarizados-del-inei, con descarga de datos estandarizados. Falta descargar el conjunto, fijar corte/fecha, verificar SHA y correspondencia jerárquica antes de importarlo; tabla permanece sin datos sintéticos presentados como oficiales.
3. **C.E.:** falta validar formato de identidad confiable con fuente normativa; no se crea clave para fusionar automáticamente.
4. **H-14:** XLSX original aún no incorporado.
5. **H-40/cPanel/especialista:** NO-GO para datos reales y producción.
6. **Consulta de duplicados por GET:** OpenAPI vigente utiliza `numero_documento` en query. No se habilita aún ese endpoint porque incorporaría PII a URL/logs; debe definirse contrato seguro antes de implementar consulta previa. El alta transaccional reutiliza identidad DNI sin consulta GET previa.
7. **Pruebas de carga/estrés:** las pruebas paralelas de I02 (19 controles HTTP en CI) pasan; carga concurrente de 10/20/30 y línea base p95 siguen reservadas a I06.

**Dictamen:** la entrega puede revisarse como implementación parcial de I02; NO declararla completada formalmente mientras existan controles críticos bloqueados o pruebas no ejecutadas. PR #5 debe permanecer Draft, sin integración automática.

## Importador INEI preparado, fuente real pendiente
Se incorporó `scripts/i02/importar-ubigeo.php` con verificación SHA256 de CSV, fuente/corte declarados, estructura jerárquica, rechazo de diferencias existentes y modo dry-run. La CI comprueba sintaxis y dry-run con un CSV **explícitamente sintético** sin escribir en BD. **No se ha descargado ni importado aún el conjunto oficial del INEI.**
