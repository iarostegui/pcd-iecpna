# UI-PCD-2.2 · Diseño y experiencia de digitación
**Autoridad visual:** `prototypes/PCD_v2.2.html` — identidad destacada **PCD / Plataforma de Captura de Datos IECPNA**, versión 2.2 sin cambios funcionales.

## Flujos de pantalla
UI-01 Login: Presbiterio / Usuario / Contraseña. Logo PCD dominante. Usuarios nacionales disponibles según permisos.
UI-02 Selección de sede: mostrar solo Sede del presbiterio del login; búsqueda, última sede válida, alta rápida en modal; no dejar continuar si inconsistente.
UI-03 Inicio: contexto usuario/presbiterio/sede, accesos a Persona/Consulta; permisos determinan menú.
UI-04 Persona: secciones del papel, cabecera heredada, foco en N.º ficha opcional, navegación Tab y Enter sin submit, DD/MM/AAAA directo.
UI-05 GC: opción solo tras guardar Persona o desde consulta de Persona existente; nombre de líder precargado.
UI-06 Consulta: lupa, filtros sede, digitador, incompletitud, duplicados y edición autorizada.
UI-07 Administración: usuarios por presbiterio, alta/baja lógica; ADMIN.
UI-08 Calidad: toggle de criticidad de campos con efecto en reportes; ADMIN.
UI-09 Reportes: presbiterio/sede, fecha último registro, volumen Personas/GC, faltantes críticos, autor, productividad; REPORTES/ADMIN.
UI-10 Auditoría: consulta limitada a ADMIN.

## Reglas de interacción
- Una selección contextual por sesión, no por ficha.
- Enter/Tab avanza desde número de ficha por campos visibles; Shift+Tab vuelve; campos textarea permiten Enter.
- No solicitar GPS nuevamente durante una sesión ni por cambio de sede.
- Al guardar sin GC, siguiente Persona preserva sede y foco; con GC, abrir GC solo con ID persistido.
- Errores de validación junto al control, sin borrar contenido; accesible 320px, 390px, 768px y escritorio, scroll horizontal de tablas cuando necesario.
- El prototipo contiene simulaciones locales; la implementación debe replicar experiencia, pero usar API/BD reales.

## Diferencias reconocidas del HTML V2.2 (E1)
El prototipo bloquea un documento duplicado y no representa fichas fuente múltiples. **No replicar esas limitaciones** en producción. La [propuesta E1](../07-auditorias/etapa-1-resolucion.md) permite guardar una ficha adicional sin duplicar Persona ni mostrar datos de otro presbiterio. No se cambia el HTML V2.2.

## Decisión E1 aprobada sobre controles y calidad
- **Fechas:** DD/MM/AAAA debe corresponder a un día real. `31/02/1998` no se acepta; el digitador corrige consultando el origen o deja fecha vacía (permite ficha incompleta). El backend verifica lo mismo.
- **Nombres:** `JUANA` frente a `Juan` o `JU4N4` puede superar una validación de texto. **PCD no verifica fidelidad al papel**. Cada presbiterio hace revisión manual fuera de la aplicación, con correcciones por funciones existentes y auditoría.
- **Grupo Crecer:** solo Persona líder y datos generales (incluido conteo aproximado); no interfaz nominal de integrantes.
- No modificar el HTML V2.2 por estas reglas: el código productivo debe respetarlas sin agregar pantalla de conciliación.

## Etapa 2 — diferencias productivo / demostración
**H-03/H-04:** el prototipo V2.2 muestra cuentas seleccionables; **NO replicar listado de usuarios en login productivo**. Mantener selección pública de presbiterio activo (solo código/nombre) y control de texto para usuario + contraseña segura. ADMIN/REPORTES con secretos propios distintos de digitadores.

**H-28:** rol ADMIN nacional puede cambiar **explícitamente** presbiterio/sede; REPORTES ve informes agregados nacionales sin sede seleccionada. El prototipo que cambia contexto automáticamente al editar **no es regla productiva**. E2-D02 pendiente de ratificación.

**H-12:** como base del prototipo se propone que DIGITADOR consulte fichas de su presbiterio pero edite solo sus registros. La Persona compartida entre presbiterios solo revela fuente de su ámbito, sin PII de otros. E2-D01 pendiente de ratificación.

**H-18:** críticos iniciales se toman del prototipo, pero relaciones técnicas no desactivables; no se guardan estados fijos de completitud. Ver [Matriz](../03-base-datos/campos-criticos.md). **HTML V2.2 no modificado**.

## Exclusiones del prototipo que no se implementan en producción (E3)
- **H-03:** listado de usuarios en login; los códigos se escriben, sin enumeración pública.
- **H-20:** coordenadas GPS completas en vista Persona/consulta; solo ADMIN accede a auditoría sensible.
- **H-21:** etiquetas/acciones de Supervisor, aprobación de fichas y exportación hacia SGSP: **fuera de ALC-PCD**.
- **H-22:** reintento manual GPS en la misma sesión tras denegación: **no replicar**; una única captura/estado de ubicación por sesión.
- **H-25:** bloqueo de nombre GC repetido en demostración: no imponer UNIQUE sin cerrar E1-D02.
**No se modifica el HTML V2.2**, que permanece como referencia visual y de flujo, no autoridad de políticas.
