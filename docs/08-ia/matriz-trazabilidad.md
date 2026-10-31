# Matriz mínima de trazabilidad Requisito → UI → API → BD → Test
| Requisito | UI del prototipo V2.2 | API | Persistencia | Pruebas |
|---|---|---|---|---|
| AUTH-01/02 | Login | POST /auth/login | usuario, usuario_presbiterio, login_evento | CA-01..07 |
| AUTH-03/AUD-03 | Login, todas las fichas | POST /auth/ubicacion | sesion_captura, geo_sesion, auditoria_evento | CA-36..40 |
| CAT-01/03 | Selector Sede | GET /sedes, PUT /contexto/sede | sede, sesion_captura | CA-08,09,14 |
| CAT-02 | + Agregar sede | POST /sedes | sede | CA-10..13 |
| PER-01/02 | Registro Persona | POST /personas | persona_captura, ficha_captura | CA-15..21 |
| PER-03 | Validación Documento | GET /personas/duplicados, POST /personas | persona_captura UNIQUE, ficha_captura | CA-22..25 |
| PER-04/07 | Consulta / edición ficha | PATCH /fichas/{id}, PATCH /personas/{id} | ficha_captura.snapshot, persona, auditoria | CA-32..34,53..55 |
| PER-05 | Checkbox líder GC | POST /personas | persona_captura, ficha_captura | CA-27..29 |
| PER-06 | Guardar | Idempotency-Key | idempotencia | CA-26 |
| GC-01/02 | Registro GC | POST /personas/{id}/grupos | grupo_crecer_captura, ficha_captura | CA-28..30 |
| GC-03/04 | GC | PATCH /grupos/{id} | grupo_crecer_captura | CA-30,31 |
| ADM-01/02 | Usuarios | /admin/usuarios | usuario, usuario_presbiterio | CA-03..06,35 |
| ADM-03 | Campos críticos | /config/campos-criticos | campo_critico, auditoria | CA-20,21,45 |
| REP-01/02/03 | Consulta/Reportes | /reportes/avance, /reportes/calidad, GET /personas | fichas, personas, grupo, calidad | CA-43..45 |
| AUD-01/02/04 | Auditoría | GET /auditoria | login_evento, auditoria_evento | CA-40..42 |
| RNF-01..24 | Global | Todas | Esquema y hosting | CA-46..52 |
**Puerta de salida:** ningún requisito crítico puede declararse listo sin evidencia del test asociado. Mantener esta matriz actualizada por cada PR.

## Trazabilidad de decisiones E1 aprobadas
| Requisito | Interfaz / control | Backend/BD | Prueba diseñada (no ejecutada) |
|---|---|---|---|
| PER-08 | Fecha DD/MM/AAAA, impedir día inexistente, permitir vacío | Validar fecha real en PHP; DATE o NULL | CA-18, E1-CA-04 y E1-CA-13 |
| PER-09 / REP-06 | Sin validación automática de texto contra papel; correcciones por interfaz existente | Auditoría de ediciones, sin tabla/tickets de conciliación | E1-CA-05, E1-CA-08, E1-CA-12 |
| GC-06 | Solo ficha de líder y campos generales GC; conteo aproximado | GC con lider_persona_id; no miembros nominales | CA-30, E1-CA-11 |
**Estado:** requisitos funcionales aprobados, codificación y ejecución de pruebas **pendientes**. Los deltas E1-D01/E1-D02 siguen sin aprobar, no se consolidan aquí.
