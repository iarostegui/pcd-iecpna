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
1. **H-24 (APROBADA):** campos mínimos Persona: presbiterio y sede de sesión; nombres, apellidos paterno y materno, tipo y número de documento y teléfono de contacto. Implementación backend/frontend y pruebas en revisión. La regla para GC se evaluará en I03 sobre su especificación.
2. **INEI:** se ha localizado el portal oficial https://www.gob.pe/15028-consultar-codigos-estandarizados-del-inei, con descarga de datos estandarizados. Falta descargar el conjunto, fijar corte/fecha, verificar SHA y correspondencia jerárquica antes de importarlo; tabla permanece sin datos sintéticos presentados como oficiales.
3. **C.E.:** falta validar formato de identidad confiable con fuente normativa; no se crea clave para fusionar automáticamente.
4. **H-14 (REFERENCIA CONFIRMADA):** el archivo `SGSP_Fichas_Imprimibles_A4 (2).xlsx` fue utilizado para recopilar los datos y debe guiar la digitación. No se incorpora el binario al repositorio público; la revisión exhaustiva de mapeo de campos de Persona debe completarse antes del cierre de I02, y la de Líder GC antes del cierre de I03.
5. **H-40/cPanel/especialista:** NO-GO para datos reales y producción.
6. **Consulta de duplicados por GET:** OpenAPI vigente utiliza `numero_documento` en query. No se habilita aún ese endpoint porque incorporaría PII a URL/logs; debe definirse contrato seguro antes de implementar consulta previa. El alta transaccional reutiliza identidad DNI sin consulta GET previa.
7. **Pruebas de carga/estrés:** las pruebas paralelas de I02 (19 controles HTTP en CI) pasan; carga concurrente de 10/20/30 y línea base p95 siguen reservadas a I06.

**Dictamen:** la entrega puede revisarse como implementación parcial de I02; NO declararla completada formalmente mientras existan controles críticos bloqueados o pruebas no ejecutadas. PR #5 debe permanecer Draft, sin integración automática.

## Importador INEI preparado, fuente real pendiente
Se incorporó `scripts/i02/importar-ubigeo.php` con verificación SHA256 de CSV, fuente/corte declarados, estructura jerárquica, rechazo de diferencias existentes y modo dry-run. La CI comprueba sintaxis y dry-run con un CSV **explícitamente sintético** sin escribir en BD. **No se ha descargado ni importado aún el conjunto oficial del INEI.**

## Validación final de entrega técnica (03/11/2026)
- GitHub Actions: [ejecución 41757545580](https://github.com/iarostegui/pcd-iecpna/actions/runs/41757545580): **4/4 jobs SUCCESS** (`documentos`, `backend`, `frontend`, `i00`).
- PHPUnit: **91 pruebas, 210 aserciones, 1 omitida**; omisión registrada, no ocultada.
- Runner HTTP I02: **19 PASS / 0 FAIL**, incluidos casos de simultaneidad; regresión I01 y build frontend en verde.
- Importador INEI: **dry-run sobre 3 filas sintéticas temporales**, SHA válido; **sin importación de fuente oficial**.
- Seguimiento de decisiones: [H-24 #6](https://github.com/iarostegui/pcd-iecpna/issues/6) y [INEI/CE #7](https://github.com/iarostegui/pcd-iecpna/issues/7).

**Cierre:** operación de desarrollo y verificación técnica completada; **I02 no puede cerrarse formalmente ni integrarse a `main`** por dependencias funcionales e institucionales pendientes. PR #5 continúa Draft. No hay GO productivo.

## H-40 — Asignación funcional aclarada
El perfil **REPORTES** queda designado para **gestionar la designación y aprobación institucional** del PCD. Conserva los permisos de lectura previstos; esta asignación no permite crear, editar ni aprobar cambios operativos en Persona/Ficha. Las evidencias del acto institucional, responsable nominal, finalidad, aviso, base legal y retención permanecen pendientes para producción. No confundir designación del perfil con aprobación institucional ya emitida.
\n## H-14 — referencia documental recibida\nSe recibió como insumo de referencia el libro `SGSP_Fichas_Imprimibles_A4 (2).xlsx`, con cuatro hojas: `Ficha_Persona`, `Ejemplo_Ficha_Persona`, `Ficha_Lider_GC` y `Ejemplo_Lider_GC`. La ficha impresa original señala obligatorios distintos (por ejemplo, nacimiento, sexo, dirección/ubigeo y condición), y el apellido materno/teléfono no figura marcado igual. Para captura PCD prevalece la decisión H-24 vigente, sin alterar la plantilla impresa. En ficha original lista LIM/TRU/IQU y no CHI, pero catálogo inicial PCD sigue cuatro presbiterios. No se sube XLSX al repositorio sin revisión de privacidad/licencia; se documenta referencia funcional. La conciliación fina de campos de Líder GC pertenece a I03.\n