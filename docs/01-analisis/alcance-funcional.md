# PCD — alcance funcional (EF-PCD-3.1)
## Objetivo y contexto
Digitalizar las dos fichas impresas IECPNA: **Ficha Persona** y **Ficha Líder GC**, recibidas con ejemplos respectivos. Campaña temporal para múltiples presbiterios y sedes (se han mencionado 48 sedes; lista oficial incompleta). Operadores pueden trabajar desde cualquier ubicación física con permisos de presbiterio.

## Incluye
1. Login por presbiterio/usuario/contraseña, cierre y log de login.
2. Seis digitadores por cada presbiterio catalogado, un ADMIN y un REPORTES nacionales; mantenimiento de usuarios.
3. Contexto de Sede filtrado; última sede válida; creación inmediata sin aprobación y búsqueda antirrepetición.
4. Captura de Persona respetando papel, con números de ficha opcionales, responsables y condición pastoral.
5. Captura opcional de Líder/Grupo Crecer, siempre vinculada a Persona previamente guardada; captura posterior desde Consulta.
6. Consulta, corrección restringida, detección de duplicados ciertos/posibles y faltantes críticos.
7. Parámetros de calidad modificables por ADMIN en tiempo de ejecución, sin impedir altas incompletas.
8. Reportes nacionales de avance por sede, último día digitado, productividad por digitador, incompletitud y duplicidad.
9. Auditoría de cambios e inicios de sesión; geolocalización única por sesión con copia/referencia en cada guardado; sin bloqueo si falta GPS.
10. Base propia preparada con ID fuente estable para migración futura a SGSP.

## Fuera de alcance
Asistencia, discipulado, seguimiento, ministerios, caja, membresía operativa, registro nominal de integrantes GC, revisión obligatoria, migración efectiva a SGSP, WhatsApp y paneles pastorales del SGSP. No se digitaliza firma física de GC.

## Actores
- `DIGITADOR`: registrar y editar **sus propios** registros; consultar según ámbito permitido y límites de privacidad.
- `ADMIN`: cobertura nacional, alta/baja lógica de usuarios, catálogos, parámetros, consulta y edición de todo; audita usando cuenta ADMIN.
- `REPORTES`: cobertura nacional, **solo lectura** de reportes y consultas de calidad autorizadas; datos personales/ubicación minimizados por defecto.

## Trazabilidad
Referencia campos: `docs/01-analisis/diccionario-captura.md`; comportamiento: `openspec/specs/`; prototipo: `prototypes/PCD_v2.2.html`.

## Decisiones funcionales posteriores a auditoría
- **Solo líderes GC:** se registra la Persona que dirige el grupo y el resumen de Grupo Crecer (tipo, lugar, estado, capacidad y cantidad aproximada). Las fichas fuente no contienen integrantes nominales; PCD **no registra ni relaciona** integrantes.
- **Control de entrada:** PCD valida formatos, fechas reales, integridad y autorización. Si la fecha anotada no existe, no se acepta su ingreso; el digitador la corrige con la fuente o deja vacío para poder guardar incompleto.
- **Fidelidad del papel:** la revisión de que el texto se digitó conforme a la ficha corresponde **manualmente a cada presbiterio fuera de PCD**, sin automatización de revisión semántica ni conciliación.
