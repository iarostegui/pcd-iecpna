# ALC-PCD-3.1 — Alcance oficial y límites del producto
**Producto:** PCD — Plataforma de Captura de Datos IECPNA (NO SGSP).  
**Tipo:** aplicación web **temporal** para la digitación de fichas impresas de la campaña de levantamiento IECPNA.  
**Estado:** definición de alcance vigente para diseño, auditoría y desarrollo; las decisiones pendientes están identificadas explícitamente.  
**Referencias:** [fichas Excel e inventario](fuentes/inventario-excel-original.md), [reglas de negocio](reglas-negocio.md), [procesos](procesos-negocio.md), [prototipo V2.2](../../prototypes/PCD_v2.2.html), [criterios de auditoría](../07-auditorias/protocolo-auditoria-alcance.md).

> **Límite rector:** PCD registra, preserva, consulta y reporta datos de fichas físicas. **No reemplaza las operaciones pastorales del SGSP, ni automatiza la revisión humana de fidelidad entre papel y digitación, ni construye procesos administrativos permanentes.** Una auditoría puede advertir insuficiencias o **cuestionar este límite**, pero no transformarlo unilateralmente en un nuevo requisito.

## 1. Propósito y resultado esperado

PCD permite que digitadores capturen centralizadamente la información de las **fichas impresas de Persona** y de **Líder de Grupo Crecer** disponibles en la campaña IECPNA. Conserva el contexto de presbiterio/sede, el origen de cada captura, autoría, fecha y trazabilidad. Facilita reportes nacionales y de calidad **objetiva** (campos incompletos y posibles coincidencias) y prepara identificadores/estructura para una **futura** migración a SGSP. La captura es temporal; la gestión pastoral permanente será de SGSP y es un proyecto distinto.

**No se ofrece como garantía:** que cada dato digitado sea idéntico a lo escrito en papel, que todos los duplicados potenciales queden automáticamente resueltos ni que PCD realice un control pastoral definitivo.

## 2. Entradas y fuentes válidas

- **Únicos formularios del levantamiento en alcance:** `Ficha_Persona` y `Ficha_Lider_GC`, con las hojas de ejemplo como referencia. El archivo original conserva su nombre histórico, que incluye la palabra SGSP, sin que eso cambie el producto.
- El **Excel original** es fuente de campos y opciones impresas; el [inventario](fuentes/inventario-excel-original.md) y [diccionario](diccionario-captura.md) documentan la correspondencia. El **binario XLSX aún no está en GitHub** (pendiente H-14); no se debe dar por resuelto.
- El **prototipo PCD HTML V2.2** es referencia de flujos y aspecto visual, pero contiene mecanismos demostrativos: no es autoridad para reemplazar reglas funcionales aprobadas ni para autenticación/almacenamiento reales.
- Los catálogos reales de sedes/presbiterios se completarán con fuentes verificadas o altas autorizadas. **No inventar sedes ni datos reales** para satisfacer una auditoría.

## 3. Funcionalidades que SÍ pertenecen a PCD

| Código | Capacidad / requisito de alcance | Límite funcional |
|---|---|---|
| ALC-01 | **Inicio/cierre de sesión** con presbiterio, cuenta de usuario y contraseña; trazabilidad de accesos. | Autenticación y permisos del backend obligatorios; no crear usuarios pastorales SGSP. |
| ALC-02 | **Usuarios operativos:** seis digitadores iniciales por presbiterio existente; una cuenta ADMIN nacional y una de REPORTES nacional; administración de usuarios. | Digitador modifica solo sus registros de origen; ADMIN modifica todos; REPORTES es de lectura. |
| ALC-03 | **Selección de sede** del presbiterio autenticado, recordar contexto y **crear sedes** durante digitación con comprobación de repetición. | No hay aprobación de alta de sede ni cambio arbitrario de presbiterio. |
| ALC-04 | **Digitación de ficha Persona** siguiendo los campos disponibles en el papel, fechas y opciones, con navegación por teclado. | Número impreso de ficha **opcional**, no es clave única; no inventar información no disponible. |
| ALC-05 | **Guardado de fichas incompletas**, con validación de tipos, formatos, estructura e integridad. | Campo crítico faltante **no bloquea** la captura; valor de tipo inválido (p. ej. 31/02/1998 en fecha) **no se acepta**: el digitador corrige en origen o deja el campo vacío. |
| ALC-06 | **Ficha Líder GC** después de guardar una Persona (o desde su consulta): captura al **líder** y resumen del grupo: identificación del grupo, tipo/estado, lugar, fecha, capacidad y cantidad **aproximada** de integrantes. | **No se registran ni vinculan integrantes nominales**: no hay fichas que contengan sus datos. No se gestiona pertenencia, actividades ni seguimiento de grupos. |
| ALC-07 | **Consultas y correcciones autorizadas** sobre registros digitados, con filtros y búsqueda; control de coincidencias documentales/posibles duplicados dentro de lo técnicamente definido. | No resolver automáticamente identidades inciertas, no revelar datos personales fuera del ámbito autorizado, conservar origen y autoría. |
| ALC-08 | **Calidad objetiva de los datos**: parámetros de campos críticos, indicadores de faltantes, reglas de tipos y detección de coincidencias posibles. | **No verifica fidelidad al papel** (JUANA por Juan; JU4N4); esa revisión es **manual, responsabilidad de cada presbiterio y fuera de PCD**. |
| ALC-09 | **Reportes y consultas nacionales**, como avance por presbiterio/sede, última digitación, volumen Persona/GC, productividad, incompletitud y posibles coincidencias. | Reporte de calidad refleja hechos computables, **no** un certificado de revisión manual ni un flujo de conciliación. |
| ALC-10 | **Auditoría de operaciones**, autor original, usuario editor, fecha/hora, cambios, inicio de sesión, IP; **geolocalización opcional una sola vez por sesión** y reuso. | GPS denegado/no disponible no impide registrar; no usarlo como autorización ni pedirlo por cada guardado. |
| ALC-11 | **Persistencia propia de PCD** en MySQL/MariaDB, con identificación/autoria/fichas fuente y preparación de correspondencias futuras con SGSP. | SGSP tiene su propia BD y código; **no se implementa la migración ahora**. |
| ALC-12 | **Despliegue temporal** en el hosting existente `pcd.unobdata.com` con interfaz compilada React/TS/Vite, API PHP y BD propia. | Sin procesos residentes nuevos, microservicios ni costos de infraestructura innecesarios; validar rendimiento en cPanel. |

La definición fina de relaciones entre fichas/Persona de **distintos presbiterios** (E1-D01) y el tratamiento de un **GC ya existente** (E1-D02) siguen abiertos. No se puede inferir que las soluciones propuestas están aprobadas ni usarlas para ampliar el alcance.

## 4. Responsables y límites organizativos

| Responsable | Actividad |
|---|---|
| **Digitador** | Ingresa datos de las dos fichas; resuelve dudas de lectura o fechas imposibles **fuera del sistema**; guarda incluso cuando faltan datos permitidos; corrige registros propios, con trazabilidad. |
| **Cada presbiterio** | Organiza la campaña y revisa **manualmente** la correspondencia entre lo digitado y las hojas físicas; comunica correcciones a digitadores. **No existe rol ni flujo de revisión dentro de PCD.** |
| **ADMIN nacional** | Gestiona cuentas y parámetros autorizados; consulta y corrige registros, preservando autoría y auditoría. |
| **REPORTES nacional** | Consulta informes y calidad autorizada **sin operaciones de escritura**, con protección de datos. |
| **PCD** | Garantiza validaciones técnicas, controles de acceso, almacenamiento y reportes definidos; **no sustituye la supervisión física de la campaña**. |
| **SGSP (futuro)** | Gestionará procesos pastorales permanentes y recibirá información por una migración **posterior y separada**. |

## 5. Fuera de alcance: exclusiones explícitas

| Código | Exclusión | Motivo y tratamiento fuera de PCD |
|---|---|---|
| EXC-01 | **Registro nominal de integrantes de Grupo Crecer** y vínculos de pertenencia miembro–grupo. | No existe información fuente nominal de integrantes en las fichas; solo líderes y conteos. |
| EXC-02 | **Discipulado, atención/seguimiento pastoral, asistencia, actividades, ministerios, administración de miembros, bajas/traslados pastorales, caja y finanzas.** | Son capacidades del SGSP o de otros procesos permanentes; no contribuyen al objetivo inmediato de digitación. |
| EXC-03 | **Supervisión dentro del aplicativo, validación/aprobación de cada ficha, asignación de revisores, bandejas, tickets y estados de conciliación**. | La revisión de fidelidad es **manual por presbiterio y fuera de PCD**; implementar workflow agrega costo/tiempo a herramienta temporal. |
| EXC-04 | **Detección automática de errores semánticos de transcripción contra la ficha de papel, OCR, IA para comparar texto, certificación de exactitud.** | No existe fuente digital verificada para comparar; no confundir validez sintáctica con fidelidad documental. |
| EXC-05 | **Maestro permanente de grupos GC, administración de miembros de GC, fusión histórica automática o seguimiento de grupos.** | La ficha GC solo captura líderes y datos generales; lo permanente corresponde al SGSP. |
| EXC-06 | **Migración, sincronización o conectividad operativa con SGSP** en la entrega PCD. | La preparación de IDs/campos sí pertenece a PCD; ejecutar la migración será otra fase/proyecto. |
| EXC-07 | **WhatsApp masivo, chatbot, campañas, notificaciones, marketing, mensajería pastoral**. | No forman parte de los formularios ni de los procesos de captura. |
| EXC-08 | **Plataforma de múltiples campañas, CRM permanente, repositorio nacional de gestión pastoral o permisos complejos de otras áreas.** | PCD está diseñado para esta campaña temporal, no como producto de operación permanente. |
| EXC-09 | **Escaneo obligatorio de fichas, carga de imágenes, firma digital del líder y captura nominal a partir de documentos inexistentes.** | El alcance validado consiste en digitación de campos, no digitalización de soportes como imágenes. |
| EXC-10 | **Nuevos módulos o servicios por iniciativa de la IA/auditor sin aceptación del responsable funcional**. | Las propuestas fuera de alcance se documentan y se elevan a decisión, **no son requisitos aprobados**. |

Las exclusiones anteriores **no permiten ignorar riesgos obligatorios** de integridad, seguridad, privacidad, respaldos, controles de acceso, protección de datos ni auditoría de las operaciones incluidas.

## 6. Alcance de los controles de calidad — diferencia obligatoria

**PCD valida automáticamente (SÍ):** fechas reales, formatos/tipos, identificadores y referencias, permisos, existencia de campos críticos, duplicados documentales identificables y consistencia transaccional.

**Cada presbiterio valida manualmente (FUERA de PCD):** que `JUANA` sea realmente el nombre escrito, que `JU4N4` no sea un error de transcripción de `Juan`, y en general que los datos coincidan exactamente con el papel. Una vez advertido un error, se utiliza la **edición autorizada existente**, sin circuito de aprobación automatizado.

Una fecha imposible no es un «dato de texto que PCD debe conservar como fecha»; se solicita corregirla o dejar el campo en blanco, manteniendo la posibilidad de guardar incompleto. El formulario no se convierte en un sistema de gestión de discrepancias con el papel.

## 7. Criterios para evaluar el alcance en una auditoría

La auditoría debe contrastar cada requisito **contra ALC-01…ALC-12**, las fuentes verificadas y las reglas aprobadas, **no** contra funciones típicas de un SGSP, CRM o sistema de gestión de calidad más amplio. Para todas las observaciones debe:

1. Identificar evidencia y requisito de PCD afectado; o declarar explícitamente **«propuesta de ampliación fuera de alcance»**.
2. Distinguir un **defecto dentro del alcance** de una **funcionalidad no solicitada**, incluso si esta parece conveniente.
3. Permitir **cuestionar el alcance** mediante justificación, valor esperado, esfuerzo/costo, riesgo de no adoptar y alternativa proporcional; **no imponer unilateralmente** la ampliación como incumplimiento ni bloquear iteraciones por mejoras opcionales.
4. Si observa un riesgo esencial de **seguridad, privacidad, integridad o legalidad**, señalarlo expresamente aunque no esté detallado; proponer el **control mínimo suficiente** para las funcionalidades incluidas, sin afirmar que está resuelto.
5. Mantener separadas las decisiones **aprobadas**, las **propuestas** y las **pendientes**. Ninguna especulación es una autorización para programar.
6. Preparar la respuesta al auditor: `ATENDIDA`, `ATENDIDA_SIMPLIFICADA`, `NO_SE_ATIENDE_FUERA_DE_ALCANCE`, `PENDIENTE_DECISION` o `DIFERIDA`, con motivación y evidencia.

**Documento complementario obligatorio:** [Protocolo de auditoría y control de alcance](../07-auditorias/protocolo-auditoria-alcance.md).

## 8. Gobierno de cambios al alcance y preparación de iteraciones

- **Autoridad de cambio:** el responsable funcional/propietario de PCD aprueba explícitamente toda ampliación. Una observación de auditoría **no cambia** el alcance por sí sola.
- **Proceso:** registrar propuesta y valor/impacto → clasificar en alcance o fuera → revisar con responsable → si aprobada, formalizar un cambio OpenSpec y actualizar análisis, API, BD, pruebas y plan; **no modificar el HTML V2.2 automáticamente**.
- **Puerta de entrada de cada iteración:** requerimientos vinculados a una capacidad ALC, propuestas pendientes visibles, contratos y casos de prueba coherentes; no se bloquea por ampliaciones opcionales no aprobadas.
- **No transferir obligaciones entre proyectos:** la preparación de la migración a SGSP pertenece a PCD; la **migración efectiva** y las funciones pastorales, no.

## 9. Decisiones abiertas y estado de implementación

- **E1-D01:** vinculación entre fichas con documento coincidente de presbiterios diferentes — propuesta, pendiente de aprobación funcional.
- **E1-D02:** interpretación operativa de `grupo_ya_existe` y coincidencias de GC — propuesta, pendiente de aprobación funcional.
- **H-14:** Excel original contrastado e inventariado, **archivo XLSX todavía no versionado en GitHub**.
- **Infraestructura:** validar versiones y capacidad reales de PHP/MySQL/cPanel.
- **Documentación:** SDD V3.1 es diseño; **React/PHP/MySQL no están desplegados ni probados en producción**.

**Regla final:** toda observación/auditoría/implementación debe partir de **este alcance oficial**. Si se considera insuficiente, se plantea una propuesta formal para decisión, sin alterar por iniciativa propia el alcance ni la versión del prototipo.

## Etapa 2 — precisión técnica sin ampliación del alcance
La [Etapa 2](../07-auditorias/etapa-2-arquitectura-seguridad-datos.md) define controles de contraseña/sesiones, ámbito, catálogo de campos e integridad de líder GC **dentro de ALC-01…12**. No crea roles nuevos, miembros nominales de GC ni procesos pastorales. Los comportamientos de **E2-D01/D02** (consulta/ámbito nacional) son propuestas sujetas a confirmación; **E2-D03** exige aceptación explícita del riesgo residual de la clave compartida antes de producción. SQL, OpenAPI y semillas son diseño, **no pruebas ejecutadas**.

## Precisión aprobada posterior: H-12
La **consulta** se controla por el parámetro de usuario `GENERAL` o `PRESBITERIO`; no confundir el ámbito de lectura con el presbiterio de digitación ni con el permiso de **edición solo propia** del digitador. ADMIN nacional y REPORTES de solo lectura conservan su función. Se auditan operaciones y se restringe PII/GPS según finalidad, especialmente en el reporte nacional.

## Actualización vinculante V4.0 — simplificación aprobada (01/11/2026)

**La consulta de una funcionalidad autorizada es GENERAL**, con filtros opcionales por Presbiterio y/o Sede; no existe autorización de lectura territorial configurable. No incorporar `alcance_consulta` ni `usuario_presbiterio`. Editar sigue limitado a autor o ADMIN, y REPORTES continúa sin escritura.

**H-13:** los únicos campos de identificación documental son **tipo y número**. No existe subtipo. Las coincidencias del tipo Otro no concluyentes requieren revisión manual, no fusión.

**Modelo físico vigente:** [11 tablas](../03-base-datos/anexos/justificacion-11-tablas.md), no 17. ORGANIZACION unifica presbiterio/sede; CATALOGO agrega listas y campos críticos; UBIGEO tiene entidad jerárquica propia; las secciones de cónyuge/responsabilidades están en FICHA.datos_fuente_json. **Una auditoría no puede convertir el modelo anterior en requisito vigente**.
