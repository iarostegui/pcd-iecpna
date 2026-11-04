# I02 — Matriz de cierre técnico (PCD IECPNA)

**Fecha de verificación:** 04/11/2026. **Ámbito:** I02 Captura de Persona y ficha; desarrollo/CI con MySQL 8 y datos sintéticos. **No equivale a GO de producción.**

## 1. Requisitos aprobados y operación probada

| Control | Implementación / evidencia | Resultado |
|---|---|---|
| H-14 formulario de digitación | `SGSP_Fichas_Imprimibles_A4 (2).xlsx`, hoja Persona. [Matriz papel → API/BD/UI](../02-diseno/I02-MATRIZ-CAMPOS-PAPEL-DIGITACION.md). | CUBIERTO I02 |
| H-24 campos obligatorios | Presbiterio+sede desde sesión; nombres, ambos apellidos, tipo/número documento y teléfono; validación PHP y HTML. | PASS |
| Identidad DNI | Longitud 8 y ceros preservados, `clave_identidad` única. | PASS |
| E1-D01 | Consolidado Persona único para DNI coincidente de distintos presbiterios; fichas independientes sin sobrescribir consolidado. | PASS |
| Anexar ficha por ID | `POST /personas/{id}/fichas`; identidad concordante exigida, anexo válido y discrepancia bloqueada. | PASS |
| C.E. y OTRO | Captura de CE alfanumérico 1–11 conservando ceros; ninguna fusión automática ni validación de titularidad fingida. OTRO no fusiona. [Criterio y fuentes](../02-diseno/I02-MATRIZ-CAMPOS-PAPEL-DIGITACION.md). | PASS en política conservadora |
| Fecha, correo y secciones fuente | Fechas reales; cónyuge/responsabilidades bajo JSON cerrado 1.0, sin tablas redundantes. | PASS |
| Control del impreso | Campos de H-14; marcas de completitud/validación **transcritas**, no certificación institucional. | PASS |
| Catálogos reales | Sexo, estado civil, condición desde `catalogo` MySQL vía API, sin códigos libres en UI. | PASS |
| UBIGEO recibido | `rptUbigeo.xlsx` → CSV SHA256 fijo → paquetes versionados de geografía → MySQL 8, 25/196/1893. | PASS de integridad/carga/API |
| Jerarquía geográfica | API por tipo/padre y selección dependiente React; rechazo de provincia ajena al departamento; ceros preservados. | PASS |
| Idempotencia y simultaneidad | Replay, clave/payload, dos POST simultáneos mismo DNI con clave igual/distinta; sin doble alta accidental en UI. | PASS |
| Autenticación, contexto, REPORTES | Rol REPORTES solo lectura; sede/presbiterio exigidos en backend. | PASS |
| Autoría y auditoría | Alta con `ficha.created_at/created_by`, sin evento INSERT; lecturas sin editar. | PASS |
| PATCH y gestión de GC | Alcance posterior I04 e I03, respectivamente; **no simularlos**. | FUERA DE I02 |

## 2. Evidencia de CI ejecutada

**GitHub Actions:** [ejecución 41916350881](https://github.com/iarostegui/pcd-iecpna/actions/runs/41916350881): cuatro trabajos **SUCCESS** (`backend`, `frontend`, `documentos`, `i00`).

- PHPUnit backend/MySQL: **96 pruebas, 222 aserciones, 1 omitida** (omisión registrada).
- Runner HTTP I02: **28 PASS / 0 FAIL**, incluidos casos `I02-U01…U04`, `I02-CE01`, `I02-H14` y `I02-A01`.
- Frontend: **13 pruebas, 3 archivos; build React/TypeScript satisfactorio**.
- Importación real en MySQL 8 de CI: **2114 filas** (25 departamentos, 196 provincias, 1893 distritos); segunda importación **0 creadas / 2114 iguales**.
- Dataset reconstruido desde `database/referencias/ubigeo-20261104/parte-{01,02,03}.b64`; SHA256 del CSV **8a3abc99898d2927d926536e4f31120b324e08e571111414846c6d2508cb650b**; cero padres inexistentes.
- Paquete UAT sin despliegue: workflow `paquete-uat.yml` produce artefacto y verifica SHA; sin credenciales ni información personal.

## 3. Requisitos externos posteriores, no falsificar

**Ubigeos:** la fuente geográfica entregada por responsable es válida **como referencia operativa para digitación** y está integrada en MySQL de CI, **pero no tiene corte INEI oficial acreditado**. Publicación INEI citada reporta 1892 distritos; libro recibido tiene 1893. La conciliación con un catálogo oficial y su fecha es una tarea de validación de procedencia para la puesta en producción, no motivo para inventar una fila ni bloquear indefinidamente las pruebas del PCD. [Custodia](../03-base-datos/fuentes/UBIGEO-RPT-2026-11-04.md).

**C.E.:** se permite digitación sin fusión porque la longitud/número no acredita identidad contra Migraciones. La integración con un verificador oficial requeriría autorización y diseño independiente; no se promete.

**H-40 / hosting / especialista:** REPORTES **gestiona** la designación y aprobación institucional, sin modificar su perfil de solo lectura; falta evidencia formal de finalidad, base legal/aviso, retención, política de GPS y respaldos. No se ha desplegado en cPanel ni probado PHP/MySQL productivo, restore o carga 10/20/30 (I06). Ninguna persona real ni dato sensible puede cargarse antes del GO.

**Consulta GET de duplicados con documento en URL:** contrato legacy debe corregirse en I04 o mediante modificación segura de contrato (evitar PII en URLs/logs). Para I02 la identidad se reutiliza automáticamente en la transacción de alta.

## 4. Dictamen

**I02 preparada para integración técnica a `main`** con la funcionalidad aprobada, base real MySQL de CI, SDD y pruebas exitosas. La aceptación de I02 **no autoriza producción**. I03 (Líder Grupo Crecer), I04 (edición completa), I05 (administración/reportes) e I06 (integración/hosting) siguen independientes.

Seguimiento original: [H-24 #6](https://github.com/iarostegui/pcd-iecpna/issues/6) (cerrado) y [UBIGEO/C.E. #7](https://github.com/iarostegui/pcd-iecpna/issues/7) (fuente usuario operativa, procedencia INEI pendiente para producción).
