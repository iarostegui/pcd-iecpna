> **HISTÓRICO — NO USAR COMO REQUISITO VIGENTE:** este documento describe decisiones/análisis de PCD V3 de 31/10/2026. El responsable aprobó posteriormente **PCD V4: 11 tablas**, consulta **total por funcionalidad** con filtros presbiterio/sede, documentos **solo tipo+número sin subtipo**, tabla `catalogo`, `organizacion` jerárquica y `ubigeo` único. Las referencias a 17 tablas, `alcance_consulta`, `usuario_presbiterio`, subtipo u otras decisiones contradictorias **están reemplazadas**, sin alterar aquí el registro histórico. Fuente de verdad: [Decisiones V4](https://github.com/iarostegui/pcd-iecpna/blob/main/docs/01-analisis/decisiones-pendientes.md), [SQL V4](https://github.com/iarostegui/pcd-iecpna/blob/main/database/migrations/001_initial.sql) y [OpenSpec actual](https://github.com/iarostegui/pcd-iecpna/blob/main/openspec/specs/README.md). En caso de enlaces relativos distintos, localizar los mismos documentos desde README principal.

# PCD — Etapa 1: levantamiento de observaciones
**Corte:** 31/10/2026 · **SDD:** V3.1 · **Prototipo PCD:** V2.2, inalterado.  
**Hallazgos:** H-01, H-02, H-05, H-08, H-09, H-14. **PCD es una aplicación temporal de captura**, no el SGSP ni una plataforma de supervisión pastoral.

## Estado por hallazgo
| ID | Problema | Tratamiento de PCD | Estado |
|---|---|---|---|
| **H-01** | Ficha de Persona cuyo documento ya aparece en otro presbiterio. | **Propuesta**: guardar ficha propia vinculada a Persona documentalmente existente, sin duplicarla ni divulgar datos de otra sede. La validación nacional identificará fichas múltiples y posibles Personas duplicadas. | **E1-D01 pendiente de aprobación**. |
| **H-02** | Datos de Persona consolidada vs ficha transcrita, con autores distintos. | Separar fichas fuente con su autor y Persona consolidada; nuevas fichas no sobrescriben datos anteriores; correcciones por autor o ADMIN y auditoría. | Diseño propuesto; pendiente sincronizar SQL/API. |
| **H-05** | Datos inválidos para un tipo, p. ej. 31/02/1998. | **Fecha imposible NO se acepta ni se persiste**; el digitador resuelve contra el papel u otra fuente **fuera del sistema**, o deja el campo fecha en blanco para guardar ficha incompleta. Validar en frontend y backend; DATE válida o NULL. **No** persistir texto imposible como fecha alternativa. | **Criterio funcional aprobado; implementación/pruebas pendientes**. |
| **H-08** | Grupo GC «ya existe» y alcance del formulario. | **Confirmado:** solo se captura la Persona líder y **resumen del grupo**, incluido conteo aproximado de integrantes; **NO** se capturan ni vinculan integrantes nominales. Conservar Sí/No de «grupo ya existe» del papel. | **Alcance aprobado**; interpretación exacta de identidad de grupo **E1-D02 pendiente**. |
| **H-09** | ¿Cómo verificar que el digitador escribió exactamente lo que dice el papel? | La **fidelidad de transcripción** se revisa **manualmente en cada presbiterio, fuera de PCD**. Validaciones automáticas limitadas a tipos, formatos, estructura, permisos y criterios objetivos como incompletitud y coincidencia documental. **No** detector semántico, módulo de conciliación, asignación de errores, revisores ni aprobaciones. | **Criterio funcional aprobado**. |
| **H-14** | Correspondencia con fichas impresas. | Contraste directo del Excel original, [inventario de hojas, opciones y filas](../01-analisis/fuentes/inventario-excel-original.md). | Parcial; **pendiente almacenar XLSX binario en GitHub**. |

## Dos categorías distintas de control de calidad
### A. Validaciones técnicas automáticas — responsabilidad de PCD
- Una fecha escrita como `31/02/1998` **no es una fecha**, por tanto no se acepta. El digitador consulta el papel/corrige o guarda la ficha con ese campo **vacío**, porque los campos incompletos son permitidos.
- Un campo de cantidad acepta valores enteros válidos y no negativos; documento, catálogos y claves foráneas se validan según reglas.
- Los campos faltantes se identifican según parámetros de calidad. Una coincidencia documental verificable puede señalar fichas múltiples de la misma Persona.
- Todos los valores que efectivamente se persisten son válidos para su tipo o NULL donde el dato es opcional.

### B. Revisión de fidelidad a la ficha física — manual por presbiterio
- El sistema **no tiene manera de conocer** que en papel figura `Juan` si el digitador ingresa `JUANA` o `JU4N4`; el texto puede pasar los controles básicos sin ser fiel al documento.
- **Cada presbiterio realiza la verificación manual fuera del aplicativo**, comparando su información con fichas de papel; cualquier subsanación se comunica al digitador y se registra mediante edición existente y auditoría.
- No se crean flujos de revisión/validación/conciliación ni se presume que la documentación automática garantiza exactitud frente al papel.
- El reporte de calidad PCD comprende **solo hechos computables** (campos faltantes, coincidencias documentales, fichas por autor/sede, etc.), no un estado de revisión semántica del papel.

## E1-D01: fichas de Persona coincidente entre presbiterios (aún por confirmar)
**Propuesta de trabajo**:
1. Al guardar, comprobar documento válido y contexto de sede/presbiterio autenticado.
2. Si Persona ya existe con mismo documento, crear nueva ficha con su autor y sede, vinculada internamente; no crear segunda Persona.
3. No exponer ni permitir editar datos de Persona/ficha ajenos por esta coincidencia.
4. Conservar las fichas e identificar múltiples fichas en reportes nacionales; posibles coincidencias sin documento solo se advierten, no fusionan.
5. Guardado transaccional, idempotente, con auditoría.

**No se declara aprobada E1-D01 en esta conversación.**

## H-02: separación de dato consolidado y ficha fuente
- La fuente pertenece a la ficha, conserva lo **realmente digitado y aceptado**, responsable y contexto. No equivale a captura infalible del papel.
- La Persona consolidada contiene los atributos de identidad e información disponibles para consulta y futura migración.
- Ficha adicional no modifica automáticamente consolidado; cambios autorizados deben auditarse.
- Responsable/fecha del papel no equivalen a actor/fecha del sistema.

## H-08: Grupo Crecer (alcance confirmado)
- PCD registra al líder **como Persona existente** y captura datos generales de Grupo Crecer (nombre, sede, tipo, estado, capacidad, fecha y lugar de reunión, integrantes aproximados).
- Las fichas **no contienen miembros nominales** de Grupo Crecer; **no se crean Personas ni relaciones para estos integrantes**.
- Pregunta `grupo_ya_existe` del papel se conserva; interpretación de vínculo con grupos registrados en PCD sigue en E1-D02, no aprobar automáticamente.
- No maestro GC permanente, actividades pastorales ni gestión nominal propias del SGSP.

## Tratamiento de H-09 y exclusiones justificadas
**Se atiende la validez técnica del dato, pero NO se desarrolla verificación automática de fidelidad al papel.** Es responsabilidad operativa externa de cada presbiterio, con revisión manual. No instalar un sistema de tickets/conciliación, panel de supervisores, aprobaciones o seguimiento de errores de transcripción: dado el carácter **temporal** de PCD, su costo y complejidad no ofrecen valor proporcional. **La temporalidad no elimina los controles de integridad, seguridad, trazabilidad, respaldo o protección de datos.**

## Preparación de iteraciones y estado documental
- **I00:** preparativos técnicos pueden iniciarse.
- **I01:** pendiente siguiente etapa de seguridad, sesiones y contexto.
- **I02:** H-01/H-02, contratos SQL/API, permisos/duplicados e inputs tipados sin fechas imposibles; aún no GO.
- **I03:** solo líderes GC, sin integrantes; E1-D02 y consistencia de contratos pendientes; aún no GO.
- **I04:** la revisión del papel **no es un caso de uso de PCD**; sí se desarrollará edición auditada existente.
- **I05:** reportes de faltantes/duplicados, no revisión semántica.
- **I06:** seguridad, respaldos, privacidad y despliegue mantienen su relevancia.

**Aprobado en esta conversación:** H-05, H-09 y alcance GC sin integrantes. **Pendiente:** E1-D01, E1-D02, H-14 XLSX en GitHub, sincronización completa de SQL/OpenAPI/tests. HTML V2.2 sin cambios.

## Control del alcance aplicado a la Etapa 1

Los seis hallazgos se contrastan **contra [ALC-PCD-3.1](../01-analisis/alcance-funcional.md)** y el [protocolo](protocolo-auditoria-alcance.md), no contra funcionalidades hipotéticas de un CRM/SGSP. H-01/H-02 permanecen como brechas de integridad/autoría **dentro del alcance**; H-05 es control de tipos **en alcance**; H-08 limita el GC a **líderes y datos agregados**; H-09 **excluye** automatizar revisión de fidelidad al papel; H-14 exige contrastar y conservar la fuente de campos. Se pueden cuestionar exclusiones, pero no exigir nuevos módulos hasta aceptación. La implementación de los ajustes pendientes aún no se declara terminada.
