# Casos de uso PCD
| ID | Actor | Caso | Precondición | Postcondición |
|---|---|---|---|---|
| CU-01 | Todos | Login por presbiterio | Usuario activo | Sesión autenticada + log de acceso |
| CU-02 | Digitador/Admin | Seleccionar sede | Presbiterio autenticado | Contexto consistente |
| CU-03 | Digitador/Admin | Crear sede | Nombre no duplicado | Nueva sede activa en mismo presbiterio |
| CU-04 | Digitador/Admin | Registrar Persona | Sesión/sede válida | Persona/ficha y auditoría transaccionales |
| CU-05 | Digitador/Admin | Registrar Líder GC | Persona persistida | Grupo/ficha GC vinculados y auditados |
| CU-06 | Digitador/Admin | Consultar | Acceso a funcionalidad y filtros opcionales | Resultados paginados autorizados |
| CU-07 | Digitador/Admin | Corregir | Digitador autor o Admin | Nueva versión, historial conservado |
| CU-08 | Admin | Gestionar usuarios | Admin | Usuarios activos/inactivos por presbiterio |
| CU-09 | Admin | Configurar criticidad | Admin | Regla versionada; reportes recalculados |
| CU-10 | Reportes/Admin | Consultar informes | Autenticado | Avances y alertas sin escritura |
| CU-11 | Todos | Cerrar sesión | Sesión abierta | Sesión revocada y logout auditado |

## Escenarios de excepción
- Sede ajena: bloquear contextos y guardar con 403/422.
- Ficha sin N.º: persistir; no completar artificialmente.
- Documento duplicado: no duplicar Persona; permitir referencia a identidad existente o documentar incidencia.
- Geolocalización denegada: registrar estado, guardar.
- Timeout tras POST: reiterar con la misma clave idempotente; no duplicar.
- Cuenta no autenticada o sin funcionalidad: 401/403; digitador puede consultar general pero no editar fichas ajenas.


**V4:** CU-06 consulta **general** de la función autorizada con filtros opcionales Presbiterio/Sede; CU-07 conserva edición autor/ADMIN; CU-04 solo tipo+número documental; no crear trámite de subtipo, ámbito de consulta o grupos nominales.
