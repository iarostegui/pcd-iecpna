# PCD — Etapa 1 de levantamiento de observaciones
**Corte:** 31/10/2026 · **Documento:** E1-PCD-01 · **Base:** auditoría SDD V3.1, prototipo PCD V2.2, Excel de fichas.  
**Alcance:** H-01, H-02, H-05, H-08, H-09, H-14. No se ha modificado el prototipo ni desarrollado producción.  
**Criterio rector:** PCD es **captura temporal**. Su función es preservar la información transcrita, identificar coincidencias y producir reportes; no incorporar flujos de aprobación, un módulo de conciliación sofisticado ni prestaciones del SGSP.

## Matriz de tratamiento
| ID | Problema real | Tratamiento mínimo propuesto | ¿Cambia el concepto aprobado? | Verificación / efecto |
|---|---|---|---|---|
| **H-01** | Una Persona ya está registrada por otro presbiterio; el endpoint actual impide registrar una nueva ficha | **Una operación de guardado** con documento completo: resolver identidad en servidor, crear **nueva ficha de la sede actual** vinculada a Persona existente **sin revelar otros datos**; nunca descartar la hoja. Transacción atómica e idempotente. | No: mantiene la captura y la no duplicidad documental. **Detalle de autorización técnica pendiente de aprobación.** | E1-CA-01/02/03, I02; contrato API requiere ajuste. |
| **H-02** | Persona consolidada vs ficha fuente sin reglas sobre ediciones y cónyuge/responsabilidades | **Ficha fuente es el registro de lo transcrito** (snapshot versionado). Primera ficha crea Persona consolidada. Siguientes fichas **no sobrescriben** automáticamente atributos de Persona. Digitador edita su ficha; creador/ADMIN edita Persona; ADMIN puede todas. Cónyuge y responsabilidades se atribuyen **a la ficha que los contiene**; las filas auxiliares son proyecciones de esa ficha. | No: precisa la autoría/historia ya acordadas. **Sincronización exacta de proyecciones pendiente de diseño técnico.** | E1-CA-04/05/06, I02/I04; modelo y API deben ajustarse. |
| **H-05** | Fechas/correos/documentos imposibles no caben en columnas SQL tipadas | Guardar **texto literal** en snapshot; columna tipada de Persona/GC solo contiene dato válido o NULL; generar observación de calidad, **nunca bloquear por dato inválido del papel**. | No: conserva datos y permite fichas incompletas. | E1-CA-07/08, I02/I03. |
| **H-08** | “¿El grupo ya existe?” no determina identidad confiable de GC | Guardar el **Sí/No del formulario** como dato; generar ID interno por **ficha GC**. Sugerir coincidencias por sede, nombre, líder/tipo cuando haya suficientes campos, **sin fusionar** por coincidencia débil ni reemplazar líder; informar duplicidad potencial. | No: se limita a registrar fichas GC; **no añade maestro complejo de grupos**. | E1-CA-09/10/11, I03. |
| **H-09** | Incidencias no tienen destino documentado | Usar observaciones estructuradas de calidad en la **ficha**, derivables de su snapshot y datos consolidados; filtros/reportes y correcciones auditadas por autor/ADMIN. **No implementar** módulo de tickets, asignaciones, aprobaciones ni estados de conciliación propios. | No: control de calidad simple sin supervisión obligatoria. | E1-CA-12/13, I02/I04/I05. |
| **H-14** | Excel original no contrastado con diccionario | Excel original **disponible y leído**; cuatro hojas relevantes, una hoja vacía; publicar [inventario fiel](../01-analisis/fuentes/inventario-excel-original.md) con coordenadas de fila, opciones y diferencias respecto del modelo anterior. | No. | Inventario verificable; **pendiente subir binario XLSX original al repositorio**. |

## Flujo objetivo H-01/H-02, sin pantallas nuevas
1. Digitador autenticado en Presbiterio A / Sede A captura los datos de la hoja, incluso incompletos.
2. En el guardado, backend valida contexto/autoría, documento solo si normalizable, e idempotencia.
3. Si no hay Persona con documento normalizado: crea Persona consolidada y ficha fuente de Persona; si hay Persona existente, **crea solo ficha fuente** de A/Sede A vinculada a esa Persona (no expone detalles de otra jurisdicción).
4. La respuesta confirma el guardado de la **ficha** y si se reutilizó identidad; al digitador no se le concede por esto acceso a editar la Persona consolidada ni a ver fichas ajenas.
5. Guardar snapshot fuente y auditoría en la misma transacción. En caso de doble clic o falla, misma clave idempotente evita fichas duplicadas.
6. Si la ficha marca líder GC, habilitar el siguiente formulario **después** del COMMIT. La API debe permitir operar desde una ficha propia aun cuando la Persona consolidada se originó en otro presbiterio (evitar exponer PII ajena).
7. Corrección posterior: editar fuente propia, sin sobrescritura silenciosa de identidad de otra autoría; ADMIN resuelve datos consolidados cuando sea necesario.

**Decisión técnica sugerida (aún no aprobada):** adaptar `POST /personas` para registrar ficha incluso si la identidad documental ya existía, devolver `ficha_id` y `persona_reutilizada` (sin PII de otra sede). Para GC desde otra sede, autorizar por **ficha fuente propia**, no por lectura libre de la Persona consolidada. No se define aquí un nuevo proceso de negocio.

## Semántica de dato fuente (H-02/H-05)
- `ficha_captura.datos_fuente_json` conserva el valor escrito, selección y fuente de papel; no se borra aunque el valor sea inválido.
- `persona_captura` guarda la **vista consolidada tipada**, no un duplicado indiscriminado de todas las fichas.
- `fecha_papel`, `responsable_papel` y observaciones son de ficha; `created_by` y hora de guardado son técnicos.
- Cónyuge/responsabilidades se mantienen por **ficha** (FK `ficha_id`), sin crear Persona ficticia. Una corrección a ficha puede ajustar su propia proyección normalizada, **no** la de otra ficha.
- Documento solo cuando el tipo y número resultan válidos según reglas de normalización; si no, conservar literal y no asignar identidad documental falsa.
- Las incidencias de lectura/datos imposibles/posible duplicado se expresan como códigos de calidad asociados a campos y/o ficha, **no** como proceso de revisión obligatorio.

## Semántica GC mínima (H-08)
- `grupo_ya_existe`: dato del papel sobre antigüedad del grupo, **no prueba de que ya exista un registro en PCD**.
- `grupo_crecer_captura`: un registro por ficha capturada, autogenerado; puede identificarse una **posible coincidencia** con otras fichas, no fusionar de forma automática.
- Nuevo líder no sustituye ni borra ficha anterior. Si el grupo fue anotado de nuevo con líder distinto, conservar ambas fichas y marcar la posible relación; la reconciliación definitiva se realizará con los datos al migrar a SGSP.
- Los integrantes reportados son un **conteo aproximado**, sin registro nominal en PCD.

## Lo que NO se hará por tratarse de una app temporal
- No crear módulo sofisticado de conciliación con casos, asignaciones, bandejas por estado y aprobaciones.
- No agregar pantalla/flujo de supervisor ni autorizar alta de sedes por terceros.
- No generar un catálogo corporativo maestro de grupos GC para gestión pastoral.
- No intentar homologación online ni migración anticipada con la BD del SGSP.
- **No omitir** seguridad, auditoría mínima, integridad, respaldo ni protección de información; la temporalidad **no** los hace opcionales.

## Estado honesto
**Resultado E1: propuesta de levantamiento documentada; NO GO todavía para I02/I03.** Los puntos funcionales se explican sin ampliar alcance, pero SQL, OpenAPI, modelos y pruebas necesitan sincronización verificable. No marcar H-14 cerrado por completo hasta versionar el Excel original. El prototipo V2.2 continúa vigente e inalterado.

## Decisiones que requieren conformidad funcional, antes de dar GO
- **E1-D01:** autorizar la vinculación automática silenciosa de una ficha documentalmente coincidente a una Persona existente de **otro presbiterio**, sin mostrar datos ajenos; solución recomendada por coherencia con reglas aprobadas.
- **E1-D02:** confirmar que “grupo ya existe” es solo el dato **Sí/No del papel** y no obliga a encontrar un GC previo en PCD.
- **E1-D03:** confirmar que no habrá proceso de conciliación supervisado: solo incidencias observables, edición autorizada y auditoría.
Estas tres precisiones no introducen pantallas nuevas y pueden aceptarse como **aclaraciones del diseño existente**.
