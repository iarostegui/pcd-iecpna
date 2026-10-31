# Procesos de negocio PCD (operación vs automatización)
## P1 — Preparación campaña
**Negocio:** organizar fichas impresas, asignar presbiterios y digitadores, recopilar catálogo existente. **PCD:** ADMIN configura presbiterios, sedes disponibles, seis usuarios iniciales por presbiterio, un usuario ADMIN y REPORTES; configura campos críticos.

## P2 — Iniciar jornada
**Negocio:** digitador identifica su presbiterio de trabajo y paquete de fichas de una sede. **PCD:** login (presbiterio/usuario/password) → registrar intento y éxito/fallo → adquirir ubicación una vez sin bloquear → seleccionar única sede compatible (preferir última) → almacenar contexto seguro.

## P3 — Digitar Persona
**Negocio:** leer ficha física, registrar literalmente datos disponibles, anotar vacíos sin inventar. **PCD:** autocentrar foco en N.º de ficha (opcional) → Enter/Tab avanza → fechas tecleables → validación de identidad documental y coincidencias → guardar completo o incompleto → transacción con auditoría → siguiente ficha conservando contexto. Si Líder GC elegido, navegar a P4 **después** del éxito.

## P4 — Digitar Líder GC
**Negocio:** usar hoja del líder y características resumidas del grupo. **PCD:** identificar Persona persistida → capturar GC nuevo o existente (sin sustituir líder) → guardar con auditoría → siguiente Persona / consulta. Cantidad aproximada NO crea miembros nominales.

## P5 — Consultar/corregir
**Negocio:** un digitador subsana datos de sus fichas; ADMIN atiende errores globales. **PCD:** buscar/filtrar por presbiterio, sede, autor, incompletitud y duplicados → editar autorizado → preservar creador → guardar historial antes/después y nueva georreferencia de la sesión.

## P6 — Monitorear calidad
**Negocio:** consultar avance y detectar vacíos sin exigir revisión individual. **PCD:** reportes actualizados por presbiterio/sede/digitador y fecha última digitación; comparar campos críticos parametrizados, exportación de reportes si se implementa con permisos; no migración final en esta fase.

## P7 — Cerrar campaña
**Negocio:** definir fecha, conciliar duplicados y planificar migración. **PCD:** mantener IDs y trazas para proyecto posterior de migración; respaldos verificables. No activar migración automática al SGSP en esta entrega.

