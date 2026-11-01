# Pruebas de aceptación del prototipo PCD V2.3 y la futura implementación

**Artefacto visual:** [PCD_v2.3.html](../../prototypes/PCD_v2.3.html). **Contrato vigente:** [SDD V4](../02-diseno/pcd-v2.3-alineacion.md). Esta tabla es especificación de pruebas; **NO significa que se hayan ejecutado ni que PHP/React esté implementado**.

| ID | Perfil y escenario | Resultado requerido |
|---|---|---|
| **CA-101** | Abrir login, cambiar presbiterio | No aparece selector/lista de cuentas; el código de usuario es texto y la contraseña está vacía al cargar. |
| **CA-102** | Probar digitador ficticio LIM-DIG-01/1234 y clave de ADMIN distinta | Login de demo funciona sin dar privilegios nacionales a digitador; no copiar credenciales demo a producción. |
| **CA-103** | DIGITADOR abre Consultar sin filtros | Ve fichas de distintos presbiterios que estén en datos de demo; filtro Presbiterio y Sede se mantiene opcional. |
| **CA-104** | DIGITADOR visualiza ficha de autor distinto, mismo u otro presbiterio | Puede abrir el detalle, no editarlo; intento PATCH sobre API real debe producir 403. |
| **CA-105** | Consulta coincidencia DNI válido fuera de presbiterio | Permite revisión por lectura; no ofrece fusión automática ni sugiere un supervisor. E1-D01 queda pendiente para guardar nueva ficha. |
| **CA-106** | Tipo documento Otro y mismo número | Solo advertencia de posible coincidencia, sin bloquear por identificador exacto, sin subtipo. |
| **CA-107** | Guardar borrador con fecha 31/02/1998 | Rechazo y explicación; al dejar campo vacío sí puede guardarse ficha incompleta. |
| **CA-108** | GC «¿El grupo ya existe?» = Sí | No aparece «¿Ya está digitado?». Nombre repetido genera aviso, no rechazo ni cambio automático de líder. |
| **CA-109** | GPS autorizado, denegado, no disponible | Una sola petición en la sesión; después no se habilita reintento ni se vuelven a solicitar permisos en cada captura. |
| **CA-110** | Consulta Persona/detalle/reportes | Ninguna ficha expone coordenadas en detalle habitual; ADMIN puede tener auditoría separada con accesos. |
| **CA-111** | Todos los roles y reportes | Reporte general con filtros presbiterio/sede, conteos sin sede fija ficticia y perfil REPORTES solo lectura. |

**Pruebas obligatorias de regresión React:** navegación móvil 320/390/768/1440, teclado, guardar incompletos, persistencia de autor, login y contexto, escalado de listas, seguridad y fechas. **Ubigeo oficial depende de I02**: no se afirma implementado por mostrar campos textuales.

**Nota E1-D01:** el prototipo no incorpora la creación automática de una segunda ficha con un DNI existente; implementar solo tras aprobación funcional.
