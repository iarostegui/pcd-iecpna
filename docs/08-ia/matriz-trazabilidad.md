# Matriz de trazabilidad integral PCD V4.1 — RN → Spec → CU → UI → API → SQL → CA

**Alcance:** [ALC-PCD-4.0](../01-analisis/alcance-funcional.md). **Estado:** contratos diseñados; E1-D01 aprobada mediante endpoint existente, E1-D02 pendiente. **I01 implementado en desarrollo/CI**; I02–I05 pendientes; no existe GO productivo.

| RN | Spec/EXC | Caso de uso | Pantalla | Endpoint | Persistencia | Pruebas |
|---|---|---|---|---|---|---|
| RN-01 | AUTH-01/05 | CU-01 | UI-01 | POST /auth/login; GET /presbiterios | usuario; sesion; login_evento | CA-01/02/56 |
| RN-02 | AUTH-02; ADM-02 | CU-08 | UI-07 | /admin/usuarios | usuario; usuario.presbiterio_id | CA-03/04 |
| RN-03 | AUTH-06/07 | CU-01 | UI-01 | POST /auth/login | usuario; login_evento | CA-06/07/57…60 |
| RN-04 | CAT-01/03 | CU-02 | UI-02 | GET /sedes; PUT /contexto/sede | sesion; organizacion | CA-08/14 |
| RN-05 | CAT-02 | CU-03 | UI-02 | POST /sedes | organizacion | CA-10…13 |
| RN-06 | CAT-01 | CU-02/04 | UI-02/04 | PUT /contexto/sede; POST /personas | sede; ficha | CA-09/14 |
| RN-07 | PER-01 | CU-04 | UI-04 | POST /personas | ficha; persona | CA-15/16 |
| RN-08 | PER-08 | CU-04 | UI-04 | POST /personas | persona.fecha_nacimiento | CA-17/18; E1-CA-04 |
| RN-09 | PER-02 | CU-04/05 | UI-04/05 | POST /personas; POST /personas/{id}/grupos | ficha.numero_ficha | CA-19 |
| RN-10 | PER-02; ADM-03 | CU-04/09 | UI-04/08 | /config/campos-criticos; POST /personas | catalogo (CAMPO_CRITICO_*); ficha | CA-20/21/73 |
| RN-11 | PER-03; CAT-06 | CU-04/06 | UI-04/06 | GET /personas/duplicados; POST /personas | persona.clave_identidad; ficha | CA-22/23/25/71/72/119 (E1-D01 aprobada) |
| RN-12 | PER-03; REP-02 | CU-04/06 | UI-04/06 | GET /personas/duplicados; GET /reportes/calidad | persona; ficha | CA-24/45 |
| RN-13 | PER-05; GC-01 | CU-05 | UI-04/05 | POST /personas; POST /personas/{id}/grupos | persona; grupo_crecer | CA-27/28/29 |
| RN-14 | GC-01/05 | CU-05 | UI-05 | POST /personas/{id}/grupos | ficha; grupo_crecer | CA-29/74 |
| RN-15 | GC-03 | CU-05/07 | UI-05/06 | PATCH /grupos/{id} | grupo_crecer; ficha | CA-31/81 (E1-D02 pendiente) |
| RN-16 | GC-04/06 | CU-05 | UI-05 | POST /personas/{id}/grupos | grupo_crecer | CA-30/89 |
| RN-17 | PER-04; ADM-01 | CU-07 | UI-06 | PATCH /fichas/{id}; PATCH /personas/{id} | ficha; auditoria_evento | CA-32/33/34/53…55 |
| RN-18 | REP-04 | CU-10 | UI-09 | GET /reportes/avance; GET /reportes/calidad | ficha; usuario | CA-35/44/67 |
| RN-19 | AUTH-03; AUD-03 | CU-01/04 | UI-01/04 | POST /auth/ubicacion | sesion.geo_*; sesion | CA-36…39/87 |
| RN-20 | AUD-01/02/04 | CU-04/07/10 | UI-09/10 | GET /auditoria | auditoria_evento; login_evento | CA-40/41/42 |
| RN-21 | ADM-03; REP-08 | CU-09/10 | UI-08/09 | /config/campos-criticos; /reportes/calidad | catalogo (CAMPO_CRITICO_*) | CA-21/45/73/88 |
| RN-22 | REP-05; EXC-03 | CU-04 | UI-04 | NO endpoint supervisor | ficha; auditoria_evento | CA-88 |
| RN-23 | REP-05; EXC-06 | CU-04/10 | UI-04/09 | NO endpoint migración | persona; ficha | CA-52 |
| RN-24 | PER-01 | CU-04 | UI-04 | POST /personas | sesion | CA-15/16 |
| RN-25 | PER-06 | CU-04/05 | UI-04/05 | POST /personas; POST /personas/{id}/grupos | persona; ficha; idempotencia (**sin auditoría de alta**) | CA-26/42/82/117 |
| RN-26 | PER-07 | CU-07 | UI-06 | PATCH /fichas/{id} | ficha; persona | CA-53/54/55 |
| RN-27 | PER-09; REP-06 | CU-04/10 | UI-04/09 | POST /personas; GET /reportes/calidad | ficha; catalogo (CAMPO_CRITICO_*) | CA-18; E1-CA-12 |
| RN-28 | GC-06 | CU-05 | UI-05 | POST /personas/{id}/grupos | grupo_crecer | CA-30/89 |
| RN-29 | REP-03/09; ADM-06 | CU-06/10 | UI-06/09 | GET /personas; GET /reportes/* | persona; ficha; organizacion | CA-77/78/85/90 (consulta general y filtros, edición separada) |

**CAMBIO OBLIGATORIO V4:** la tabla `organizacion` reemplaza presbiterio+sede; `ubigeo` es insumo I02; `catalogo` agrupa listas y criticidad. **RN-29 implica consulta total por funcionalidad, con filtros opcionales; no restricción territorial por usuario ni subtipo documental**. Los nombres de antiguos CU/UI se conservan como identificadores de prototipo y no autorizan módulos nuevos.

**Puerta SDD:** un cambio de regla obliga a actualizar spec, endpoint, SQL/índices y caso de prueba correspondiente. La columna «Pruebas» enumera **diseños**, no evidencia de ejecución. Consultar [asignación](../05-pruebas/asignacion-iteraciones.md), [OpenAPI](../04-integracion/openapi.yaml) y [anexos SQL](../03-base-datos/anexos/README.md).

## Reglas V4.1 — trazabilidad adicional
| RN | Función / contrato | SQL | Pruebas |
|---|---|---|---|
| RN-30 | `GET /presbiterios` fijo, sin POST de presbiterio | organizacion (seed LIM/TRU/CHI/IQU) | CA-115 |
| RN-31 | POST FICHA sin auditoría; PATCH solo diferencias | ficha, auditoria_evento | CA-117/118 |
| RN-32 | Timestamps servidor todas las tablas | 11 tablas, created_at/updated_at | CA-122 |
| RN-33 | Normalizar nombres sin tildes | organizacion.nombre_norm + UNIQUE | CA-116/123 |
| RN-34 | Clave común solo UAT; rotación antes producción | usuario.password_hash, sesion.auth_version | CA-120 |
| RN-35 | Vincular ficha a Persona existente cross-presbiterio | persona, ficha | CA-119 |
**Prioridad al generar código:** reglas aprobadas V4.1, no cambios históricos E1-E3.


## I01 — implementación verificada en desarrollo/CI (02/11/2026)
| Requisito | Implementación backend | UI React | Persistencia | Evidencia |
|---|---|---|---|---|
| AUTH-01/02 | `AuthController`, `AuthService`, hashing y límites | Login I01 | `usuario`, `login_evento` | CA-01/02/03/04/05/06/57/58 |
| AUTH-03/04 | `PhpSession`, `Csrf`, `SesionRepository`, revocación | Expiración y logout | `sesion` | CA-61/62/63/64/94/113 |
| AUTH-05 | `GET /presbiterios` público | Selector de login | `organizacion` | CA-56/115 |
| AUTH-06/07 | Bloqueo por cuenta/IP y CSRF | Mensajes 401/403/429 | `login_evento` | CA-07/59/60 |
| AUTH-09 | `PUT /contexto/presbiterio` ADMIN | Cambio de presbiterio | `sesion`; `auditoria_evento` | CA-66/67 |
| CAT-01/02/03/09 | `SedeService`, normalización NFKD, idempotencia | Contexto, búsqueda y alta | `organizacion`; `idempotencia` | CA-08…14/112/116/123 |
| ALC-10 | `AuthService::storeLocation` | GPS opcional único | `sesion.geo_*` | CA-94/109/110 |
| Modelo físico | Sin cambios de esquema | — | 11 tablas/146 columnas/23 FK | CA-91/92/122 |

**Evidencia ejecutada:** local `npm test` (SDD), PHPUnit 64/154, runner HTTP I01 36 PASS/0 FAIL/0 BLOCKED y build React. CI [run 41450632188](https://github.com/iarostegui/pcd-iecpna/actions/runs/41450632188) con MySQL 8.0.46/PHP 8.2.35: 4 jobs verdes, I01 36/36, PHPUnit 64/153 (1 omitida por Argon2id), I00 31 PASS/0 FAIL/1 BLOCKED y frontend 8/8.
**No sustituye** la revisión del especialista ni el GO productivo (E-07/H-14/H-40).
