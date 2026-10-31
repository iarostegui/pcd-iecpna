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

