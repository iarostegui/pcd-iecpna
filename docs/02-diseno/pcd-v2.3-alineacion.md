# PCD — Prototipo HTML V2.3 alineado con SDD V4

**Interfaz de referencia para construir React:** [PCD_v2.3.html](../../prototypes/PCD_v2.3.html).  
**Versión anterior conservada e inalterada:** [PCD_v2.2.html](../../prototypes/PCD_v2.2.html).  
**Autoridad funcional:** [alcance](../01-analisis/alcance-funcional.md), [decisiones](../01-analisis/decisiones-pendientes.md), [OpenSpec vigente](../../openspec/specs/README.md).  
**Autoridad técnica:** [OpenAPI V4](../04-integracion/openapi.yaml), [SQL de 11 tablas](../../database/migrations/001_initial.sql).  

## 1. Matriz de alineación pantalla por pantalla

| Pantalla / interacción | Diferencia V2.2 → V2.3 | Regla / implementación real |
|---|---|---|
| Login | Desplegable de cuentas sustituido por **código de usuario escrito**; contraseña sin valor precargado. | AUTH-01, AUTH-05; en backend, cuentas reales/contraseñas hash, 401/429, cookie protegida, CSRF. **Nunca copiar DEMO_KEYS**. |
| Login de prueba | Claves **ficticias distintas** para digitador, ADMIN y REPORTES. Se aclara que son visibles en HTML. | H-03: en producción clave inicial común exclusivamente DIGITADOR, nacionales con secretos separados, controles compensatorios. |
| Consultar fichas | Consulta **nacional/general** dentro de la funcionalidad para DIGITADOR/ADMIN; filtros opcionales Presbiterio y/o Sede, autor y calidad. | H-12/RN-29/REP-09; backend autentica funcionalidad; **no** autorización territorial de lectura por usuario. |
| Consulta vs edición | Las fichas de otros digitadores se ven pero no presentan acciones de edición. | PER-04: DIGITADOR modifica solo propias; ADMIN según rol; REPORTES no escribe. Comprobar en servidor, no solo UI. |
| Detalle de Persona | Omite coordenadas GPS de un detalle ordinario. | Datos GPS son de `sesion` y solo para controles/auditoría ADMIN, no DTO nominal habitual. |
| Duplicados | Coincidencia DNI confiable accesible para consulta; sin instrucción de conciliación con otro presbiterio. Documento OTRO/C.E. queda como señal potencial, no fusión exacta. | H-13/E1-D01. **IMPORTANTE**: la demo todavía bloquea intentar duplicar un DNI ya registrado; la vinculación de nueva ficha a Persona global es **decisión E1-D01 pendiente**; NO copiar ese bloqueo como regla final hasta cerrar la decisión. |
| Grupo Crecer | Se elimina el selector no respaldado por ficha «¿Ya está digitado?» y su reconciliación. Se conserva **«¿El grupo ya existe?»** del papel. Nombre repetido → advertencia, no bloqueo ni fusión. | GC-03/05, H-08/H-25. **E1-D02/H-23 siguen pendientes**; no inventar maestro de GC o asignación automática a líder distinto. |
| Fechas | Las fechas de calendario imposibles se rechazan también en borradores; se puede dejar en blanco. | H-05 y PER-08; validación fecha real `DATE`/NULL en PHP y React, sin guardar texto 31/02/1998. |
| Revisión de calidad | Se elimina el texto de supervisor; revisión de exactitud del papel será manual **fuera de PCD** por presbiterios. | H-09/RN-22 y PER-09. Reportes de completitud son automáticos, no prueba de fidelidad. |
| GPS | Una sola solicitud por sesión: incluso denegada/no disponible, el botón refleja estado y no permite nuevo intento. | AUTH-03, `sesion.geo_*`, no tabla GPS propia. |
| Reporte de campaña | Estadísticas y filtros de presbiterio/sede generales para perfiles con acceso al reporte; no restricción al presbiterio de captura. | REP-01…09, conteos por autor/actor de auditoría desde backend. |
| Sedes | El número histórico de 48 sedes se elimina como contador fijo; los nombres de demo no son oficiales. | `organizacion` integra presbiterios y sedes. Catálogo real por API, alta de sede solo en contexto autorizado. |
| Ubigeo | Mantiene **campos demostrativos de texto** indicando dependencia del catálogo oficial. | **Pendiente de construcción I02**: reemplazar por selectores INEI dependientes de `ubigeo` con datos reales; no inventar códigos. |
| Tablas físicas | Ningún cambio visible necesario. | 11 tablas, tabla de tablas `catalogo`, `organizacion` jerárquica, `ficha.datos_fuente_json` para cónyuge/responsabilidades. |

## 2. Demostración, no implementación

**PCD_v2.3.html es un HTML autónomo**: `localStorage`, cuentas, claves, sede, reportes, coincidencias y auditoría son simulados y **no representan almacenamiento compartido, autorización server-side, seguridad productiva, conexión MySQL ni procesamiento legal de datos reales**. No introducir información personal real.

El storage ahora usa `pcd_iecpna_demo_v2_3`, **separado** del de V2.2 para evitar contaminación de demostraciones. El formulario sigue el orden del papel y mantiene el diseño responsive.

## 3. Pendientes no resueltos por la maqueta
- **E1-D01:** cómo conservar una ficha adicional vinculada a una Persona documentalmente existente en otro presbiterio sin sobrescribir su consolidado.
- **E1-D02:** alcance de «GC ya existe» ante una captura previa.
- **H-23:** crear una ficha GC vinculada a Persona cuyo autor es otro digitador.
- **H-24:** dato mínimo para evitar una ficha completamente vacía permitiendo incompletas.
- **H-40:** privacidad, retención y legitimación institucional antes de datos reales.
- **I02:** fuente y versión oficial del árbol INEI, carga y dependencias de formulario.

**No atribuir a estas decisiones el comportamiento ficticio actual de la maqueta.** Si hay desacuerdo entre HTML y SDD, prevalecen alcance y specs aprobados; se documenta la brecha antes de codificar.

## 4. Puertas de aceptación para IA
1. Construir primero I00 (11 tablas MySQL/seed probadas), luego I01, I02, etc. No despliegue sin H-40.
2. Para cada pantalla, trazar **RN → CU → UI V2.3 → OpenAPI V4 → 11 tablas → CA**.
3. Exigir casos **CA-101…CA-111**, además de CA existentes, con evidencia de navegación y permisos en React/PHP.
4. Mantener V2.2 intacta como referencia histórica y V2.3 como referencia visual-operativa actual.
5. No implementar flujo supervisor, conciliación, integrantes nominales GC, catálogo inventado de presbiterios/sedes o conexión SGSP.
