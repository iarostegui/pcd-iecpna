# Diseño de componentes PCD V4.0
## Frontend React/TypeScript/Vite
```text
src/
  app/         App, rutas, sesión, contexto, layouts y gestión de errores
  features/
    auth/      LoginPage, sesión, ubicación una vez
    sedes/     SedeSelector, SedeCreateModal, búsqueda anti duplicidad
    personas/  PersonaForm, secciones físicas, conyuge, responsabilidades, duplicados
    gc/        GrupoCrecerForm, líder ya registrado
    consulta/  QueryPage, filtros, tabla y detalle editable autorizado
    usuarios/  UsuariosAdminPage (solo ADMIN)
    calidad/   CamposCriticosPage (solo ADMIN)
    reportes/  Avance, Calidad, Productividad (ADMIN/REPORTES)
    auditoria/ AuditoriaPage (solo ADMIN)
  shared/
    components/   Input, Select, FieldError, DataTable, ConfirmDialog
    api/          cliente HTTP tipado, CSRF, errores normalizados, idempotencia
    validation/   fecha DD/MM/AAAA, documento, texto y códigos de catálogo
    types/        DTOs OpenAPI, Persona, GC, Ficha, Usuario
```
**Responsabilidad:** UX y validaciones inmediatas; el navegador nunca decide permisos finales ni persistencia.

## Backend PHP modular con PDO
```text
backend/
  public/index.php      front-controller API
  src/Http/              Router, Request, Response, CsrfMiddleware, AuthMiddleware
  src/Auth/              Login, Logout, Session, GeolocationSnapshot
  src/Catalogos/         PresbiterioService, SedeService (deduplicación)
  src/Persona/           CreatePersona, AttachFicha, UpdatePersona, UpdateFicha, DuplicateSearch
  src/GrupoCrecer/       RegisterGC, UpdateGC
  src/Admin/             UserManagement, CriticalFieldsConfig
  src/Reportes/          Avance, Calidad, Productividad
  src/Auditoria/         AuditTransaction, LoginEvent
  src/Db/                PDOConnection, Repositorios
  config/                configuración protegida fuera webroot
```
**Responsabilidad:** autorización por funcionalidad y edición, validación estructural, normalización documental, idempotencia, transacciones y auditoría.

## Secuencias clave
### Login y ubicación
1. React POST login → PHP crea sesión y login_evento.
2. React pide ubicación **una vez**; POST /auth/ubicacion incluso si denegada.
3. PHP actualiza sesion.geo_estado/geo_* UNA vez por sesión; en futuros refresh, GET /auth/me devuelve resumen y no se llama geolocalización de nuevo.
4. Guardados referencian estado/valor en sesion.geo_*.

### Persona y GC
1. Formulario captura papel, valida local, búsqueda documento por API.
2. POST Persona (Idempotency-Key); PHP transacción Persona/Ficha/Auditoría.
3. Retorna persona_id/ficha_id; checkbox GC activa navegación después del COMMIT.
4. POST GC relaciona líder Persona, ficha GC, auditoría, dentro de transacción.
5. Formulario siguiente conserva presbiterio/sede.

### Cambio de ficha propia
1. GET ficha autorizada, cargar snapshot y version.
2. PATCH ficha con version; comprobar autor o ADMIN en PHP.
3. En una transacción: UPDATE ficha snapshot, version+1 y auditoría before/after.
4. No altera automáticamente Persona consolidada si ficha pertenece a otro autor.

## Responsabilidad de despliegue
Solo `frontend/dist` y `backend/public` accesibles por HTTP; API /api/v1 en mismo dominio; secretos, scripts SQL, documentación y fuentes fuera de Document Root.


## V4 — Proyección de entidades
Frontend consulta `GET /personas` sin filtro o con filtros combinados Presbiterio/Sede; no hay selector de permiso territorial. El backend sirve `organizacion`, `catalogo`, `ubigeo` y `persona`/`ficha`/`grupo_crecer`. Cónyuge/responsabilidades son secciones validadas de `ficha.datos_fuente_json`. Sesión y geolocalización conviven en `sesion`. Solo tipo+número documental, sin subtipo. SQL [11 tablas](../../database/migrations/001_initial.sql).
