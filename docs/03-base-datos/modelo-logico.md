# Modelo lógico PCD V3.1
| Tabla | PK | Relaciones y reglas |
|---|---|---|
| presbiterio | id | código único; presbiterios disponibles aunque sedes incompletas |
| sede | id | `presbiterio_id` FK, `(presbiterio_id,nombre_norm)` único |
| usuario | id | `codigo` único; perfil DIGITADOR/ADMIN/REPORTES; hash |
| usuario_presbiterio | usuario_id,presbiterio_id | ámbitos autorizados |
| campana | id | campaña temporal; estado |
| sesion_captura | id | usuario/presbiterio/sede + inicio/cierre; `session_hash` único |
| geo_sesion | sesion_id | máximo una ubicación/estado por sesión |
| login_evento | id | audit intentos, incluso fallidos |
| persona_captura | id | identidad por documento parcial; UNIQUE condicional via NULL |
| ficha_captura | id | tipo PERSONA o GC, presbiterio+sede, `persona_id`, `created_by`, campos papel, versión |
| conyuge_pendiente | id | FK persona; referencia libre sin alta ficticia |
| persona_responsabilidad_captura | id | FK ficha/persona, tipo y fechas/ámbito de responsabilidad |
| grupo_crecer_captura | id | FK ficha GC (única) y líder Persona, resumen grupo |
| campo_critico | entidad,campo_codigo | criticidad configurable; FK usuario editor |
| auditoria_evento | id | acción + recurso + antes/después + actor + sesión/GPS |
| idempotencia | id | (usuario_id, clave) único + hash payload y resultado |
## Integridad
- En todas las escrituras el backend resuelve `presbiterio_id` desde sesión.
- Restricción compuesta de ficha y sesión `(presbiterio_id,sede_id)` hacia sede; la sede no puede cambiar a otro presbiterio.
- Ficha GC debe enlazar Persona persistida; tipo de ficha debe concordar con tabla grupo.
- Documentos faltantes no colapsan identidad; se marcan coincidencias potenciales para revisión por reporte.
- Responsabilidades pastorales no crean privilegios de seguridad.
## Normalización y diseño físico
Mayoría de datos 3FN; datos transcritos de papel se conservan sin imponer calidad artificial. Historial de cambios en auditoría. Consultar diccionario de captura, SQL y diagramas.

