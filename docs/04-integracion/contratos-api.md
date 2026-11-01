# PCD V4.0 — Contratos de API

**Contrato único:** [OpenAPI 4.0](openapi.yaml), `/api/v1` en PHP, una BD PCD independiente del SGSP.

## Autenticación y contexto de digitación
- `GET /presbiterios` **público**: proyección de `organizacion` filtrada tipo PRESBITERIO, solo ID/código/nombre activos; nunca lista usuarios.
- `POST /auth/login`, `GET /auth/me`, `POST /auth/logout`, `POST /auth/ubicacion`: sesión `sesion` y `login_evento`; GPS 1 vez por sesión, opcional/no bloqueante.
- `GET/POST /sedes`, `PUT /contexto/presbiterio`, `PUT /contexto/sede`: `organizacion` unificada. Cambio presbiterio solo ADMIN y sede coherente.

## Captura y consulta
- `GET /personas` y `GET /grupos`: **consulta general de la funcionalidad**, filtros opcionales por presbiterio y/o sede, digitador/fechas y paginación. Ningún permiso GENERAL/PRESBITERIO por usuario.
- `GET /personas/duplicados`: solo **tipo_documento y numero_documento**; DNI/CE con clave confiable; OTRO alerta de posible coincidencia, sin subtipo ni fusión por sí sola.
- `POST /personas`: transacción `persona + ficha + auditoria_evento + idempotencia`. `POST /personas/{id}/fichas` nueva fuente sin sobrescribir consolidado.
- `POST /personas/{id}/grupos`, `GET/PATCH /grupos/{id}`: `grupo_crecer` y `ficha`, líder = `ficha.persona_id`, versión de FICHA. Sin integrantes nominales.
- `GET/PATCH /fichas/{id}`: DIGITADOR solo edita fichas propias (autor inmutable); ADMIN según rol.

## Catálogos, administración e informes
- `GET /catalogos?categoria=`: única `catalogo` de listas y criterios de completitud; `GET /ubigeos?tipo=&padre_codigo=`: árbol INEI cargado en I02.
- `GET/POST/PATCH /admin/usuarios`: `usuario.presbiterio_id`, sin `usuario_presbiterio` y **sin `alcance_consulta`**. Reset de clave revoca sesiones.
- `GET/PUT /config/campos-criticos`: modificar `catalogo.config_json` con validaciones whitelist.
- `GET /reportes/avance`, `GET /reportes/calidad`: criterios dinámicos, filtros opcionales por presbiterio/sede; modificaciones atribuidas a actor `auditoria_evento`.
- `GET /auditoria`: ADMIN con datos sensibles restringidos, sin GPS en consultas normales.

## Errores y pruebas
401 para no autenticado, 403 para funcionalidad/no autorización de escritura, 409 colisión de versión/idempotencia, 422 validación de tipo, 429 abuso. CSRF para mutaciones. No exponer datos sensibles en errores; DTO definido en OpenAPI.

**Pendiente:** parser formal YAML, verificación completa DTO ↔ BD 11 tablas, ejecución PHP/React y pruebas reales; E1-D01/E1-D02/H-23/H-24 aún abiertos.
