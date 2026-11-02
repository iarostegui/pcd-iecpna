# Anexo 02 — Diccionario físico completo PCD V4.1

Derivado de [SQL](../../../database/migrations/001_initial.sql): **11 tablas, 146 columnas, 23 FK**. Diseño propuesto, **no ejecutado en MySQL**. Timestamps created_at/updated_at en TODAS las tablas.

## organizacion — 11 columnas

| Columna | Tipo SQL | Admite NULL | Default / control | Clave | Regla |
|---|---|:---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id |
| `tipo` | `ENUM('PRESBITERIO','SEDE')` | No | — | — | tipo |
| `padre_id` | `BIGINT UNSIGNED` | Sí | — | FK | padre id |
| `codigo` | `VARCHAR(50)` | Sí | — | UK | codigo |
| `nombre` | `VARCHAR(200)` | No | — | — | nombre |
| `nombre_norm` | `VARCHAR(200)` | No | — | — | Normalizar sin tildes, mayúsculas y espacios; conservar nombre visible |
| `nombre_raiz_norm` | `VARCHAR(200)` | Sí | GENERATED STORED | UK | Nombre presbiterio normalizado UNIQUE aun con padre NULL |
| `referencia` | `VARCHAR(300)` | Sí | — | — | referencia |
| `activo` | `TINYINT(1)` | No | 1 | — | activo |
| `created_at` | `DATETIME(6)` | No | CURRENT_TIMESTAMP(6) | — | created at |
| `updated_at` | `DATETIME(6)` | Sí | NULL + ON UPDATE | — | updated at |

- **FK** `fk_org_padre`: (padre_id) → `organizacion`(id).
- **UNIQUE KEY** `uq_org_codigo`: (codigo).
- **UNIQUE KEY** `uq_org_presb_nombre`: (nombre_raiz_norm).
- **UNIQUE KEY** `uq_org_sede_nombre`: (padre_id,nombre_norm).
- **UNIQUE KEY** `uq_org_id_padre`: (id,padre_id).
- **KEY** `ix_org_tipo_padre`: (tipo,padre_id,activo).
- **CHECK** `chk_org_jerarquia`: ` (tipo='PRESBITERIO' AND padre_id IS NULL AND codigo IS NOT NULL) OR (tipo='SEDE' AND padre_id IS NOT NULL) `.

## usuario — 10 columnas

| Columna | Tipo SQL | Admite NULL | Default / control | Clave | Regla |
|---|---|:---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id |
| `codigo` | `VARCHAR(80)` | No | — | UK | codigo |
| `nombre_display` | `VARCHAR(180)` | No | — | — | nombre display |
| `perfil` | `ENUM('DIGITADOR','ADMIN','REPORTES')` | No | — | — | perfil |
| `presbiterio_id` | `BIGINT UNSIGNED` | Sí | — | FK | presbiterio id |
| `password_hash` | `VARCHAR(255)` | No | — | — | password hash |
| `activo` | `TINYINT(1)` | No | 1 | — | activo |
| `auth_version` | `INT UNSIGNED` | No | 1 | — | auth version |
| `created_at` | `DATETIME(6)` | No | CURRENT_TIMESTAMP(6) | — | created at |
| `updated_at` | `DATETIME(6)` | Sí | NULL + ON UPDATE | — | updated at |

- **FK** `fk_usuario_presb`: (presbiterio_id) → `organizacion`(id).
- **UNIQUE KEY** `uq_usuario_codigo`: (codigo).
- **KEY** `ix_user_presb`: (presbiterio_id).

## sesion — 19 columnas

| Columna | Tipo SQL | Admite NULL | Default / control | Clave | Regla |
|---|---|:---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id |
| `usuario_id` | `BIGINT UNSIGNED` | No | — | FK | usuario id |
| `presbiterio_id` | `BIGINT UNSIGNED` | No | — | FK | presbiterio id |
| `sede_id` | `BIGINT UNSIGNED` | Sí | — | FK | sede id |
| `session_ref_hash` | `CHAR(64)` | No | — | UK | session ref hash |
| `ip_login` | `VARBINARY(16)` | Sí | — | — | ip login |
| `user_agent` | `VARCHAR(255)` | Sí | — | — | user agent |
| `fecha_login` | `DATETIME(6)` | No | — | — | fecha login |
| `fecha_logout` | `DATETIME(6)` | Sí | — | — | fecha logout |
| `last_seen` | `DATETIME(6)` | Sí | — | — | last seen |
| `revocada_en` | `DATETIME(6)` | Sí | — | — | revocada en |
| `auth_version_al_ingreso` | `INT UNSIGNED` | No | — | — | auth version al ingreso |
| `geo_estado` | `ENUM('OK','DENEGADA','NO_DISPONIBLE','ERROR')` | Sí | — | — | geo estado |
| `geo_latitud` | `DECIMAL(10,7)` | Sí | — | — | geo latitud |
| `geo_longitud` | `DECIMAL(10,7)` | Sí | — | — | geo longitud |
| `geo_precision_m` | `DECIMAL(10,2)` | Sí | — | — | geo precision m |
| `geo_capturada_en` | `DATETIME(6)` | Sí | — | — | geo capturada en |
| `created_at` | `DATETIME(6)` | No | CURRENT_TIMESTAMP(6) | — | created at |
| `updated_at` | `DATETIME(6)` | Sí | NULL + ON UPDATE | — | updated at |

- **FK** `fk_sesion_usuario`: (usuario_id) → `usuario`(id).
- **FK** `fk_sesion_presb`: (presbiterio_id) → `organizacion`(id).
- **FK** `fk_sesion_sede`: (sede_id,presbiterio_id) → `organizacion`(id,padre_id).
- **UNIQUE KEY** `uq_sesion_hash`: (session_ref_hash).
- **KEY** `ix_sesion_user_fecha`: (usuario_id,fecha_login).
- **CHECK** `chk_sesion_geo`: ` (geo_estado IS NULL AND geo_capturada_en IS NULL) OR (geo_estado IS NOT NULL AND geo_capturada_en IS NOT NULL) `.

## login_evento — 9 columnas

| Columna | Tipo SQL | Admite NULL | Default / control | Clave | Regla |
|---|---|:---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id |
| `usuario_id` | `BIGINT UNSIGNED` | Sí | — | FK | usuario id |
| `presbiterio_id` | `BIGINT UNSIGNED` | Sí | — | FK | presbiterio id |
| `codigo_intentado` | `VARCHAR(80)` | Sí | — | — | codigo intentado |
| `resultado` | `ENUM('OK','FALLIDO','BLOQUEADO','LOGOUT')` | No | — | — | resultado |
| `ip` | `VARBINARY(16)` | Sí | — | — | ip |
| `user_agent` | `VARCHAR(255)` | Sí | — | — | user agent |
| `created_at` | `DATETIME(6)` | No | CURRENT_TIMESTAMP(6) | — | created at |
| `updated_at` | `DATETIME(6)` | Sí | NULL + ON UPDATE | — | updated at |

- **FK** `fk_login_usuario`: (usuario_id) → `usuario`(id).
- **FK** `fk_login_presb`: (presbiterio_id) → `organizacion`(id).
- **KEY** `ix_login_codigo_fecha`: (codigo_intentado,created_at).
- **KEY** `ix_login_ip_fecha`: (ip,created_at).

## catalogo — 10 columnas

| Columna | Tipo SQL | Admite NULL | Default / control | Clave | Regla |
|---|---|:---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id |
| `categoria` | `VARCHAR(70)` | No | — | — | categoria |
| `codigo` | `VARCHAR(85)` | No | — | — | codigo |
| `etiqueta` | `VARCHAR(160)` | No | — | — | etiqueta |
| `orden` | `SMALLINT UNSIGNED` | No | 0 | — | orden |
| `activo` | `TINYINT(1)` | No | 1 | — | activo |
| `config_json` | `JSON` | Sí | — | — | config json |
| `updated_by` | `BIGINT UNSIGNED` | Sí | — | FK | updated by |
| `created_at` | `DATETIME(6)` | No | CURRENT_TIMESTAMP(6) | — | created at |
| `updated_at` | `DATETIME(6)` | Sí | NULL + ON UPDATE | — | updated at |

- **FK** `fk_catalogo_editor`: (updated_by) → `usuario`(id).
- **UNIQUE KEY** `uq_catalogo_categoria_codigo`: (categoria,codigo).

## ubigeo — 7 columnas

| Columna | Tipo SQL | Admite NULL | Default / control | Clave | Regla |
|---|---|:---:|---|---|---|
| `codigo` | `VARCHAR(6)` | No | — | PK | codigo |
| `tipo` | `ENUM('DEPARTAMENTO','PROVINCIA','DISTRITO')` | No | — | — | tipo |
| `padre_codigo` | `VARCHAR(6)` | Sí | — | FK | padre codigo |
| `nombre` | `VARCHAR(160)` | No | — | — | nombre |
| `activo` | `TINYINT(1)` | No | 1 | — | activo |
| `created_at` | `DATETIME(6)` | No | CURRENT_TIMESTAMP(6) | — | created at |
| `updated_at` | `DATETIME(6)` | Sí | NULL + ON UPDATE | — | updated at |

- **FK** `fk_ubigeo_padre`: (padre_codigo) → `ubigeo`(codigo).
- **KEY** `ix_ubigeo_padre`: (padre_codigo,tipo,nombre).
- **CHECK** `chk_ubigeo_nivel`: ` (tipo='DEPARTAMENTO' AND padre_codigo IS NULL AND CHAR_LENGTH(codigo)=2) OR (tipo='PROVINCIA' AND padre_codigo IS NOT NULL AND CHAR_LENGTH(codigo)=4) OR (tipo='DISTRITO' AND padre_codigo IS NOT NULL AND CHAR_LENGTH(codigo)=6) `.

## persona — 26 columnas

| Columna | Tipo SQL | Admite NULL | Default / control | Clave | Regla |
|---|---|:---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id |
| `tipo_documento` | `VARCHAR(24)` | Sí | — | — | tipo documento |
| `numero_documento` | `VARCHAR(60)` | Sí | — | — | numero documento |
| `numero_documento_norm` | `VARCHAR(60)` | Sí | — | — | numero documento norm |
| `clave_identidad` | `VARCHAR(120)` | Sí | — | UK | E1-D01: reutilizar Persona documentalmente coincidente en cualquier presbiterio |
| `nombres` | `VARCHAR(150)` | Sí | — | — | nombres |
| `apellido_paterno` | `VARCHAR(100)` | Sí | — | — | apellido paterno |
| `apellido_materno` | `VARCHAR(100)` | Sí | — | — | apellido materno |
| `fecha_nacimiento` | `DATE` | Sí | — | — | fecha nacimiento |
| `sexo_codigo` | `VARCHAR(24)` | Sí | — | — | sexo codigo |
| `estado_civil_codigo` | `VARCHAR(24)` | Sí | — | — | estado civil codigo |
| `telefono` | `VARCHAR(60)` | Sí | — | — | telefono |
| `correo` | `VARCHAR(180)` | Sí | — | — | correo |
| `facebook_usuario` | `VARCHAR(180)` | Sí | — | — | facebook usuario |
| `direccion` | `VARCHAR(500)` | Sí | — | — | direccion |
| `ubigeo_dpto_codigo` | `VARCHAR(6)` | Sí | — | FK | ubigeo dpto codigo |
| `ubigeo_prov_codigo` | `VARCHAR(6)` | Sí | — | FK | ubigeo prov codigo |
| `ubigeo_dist_codigo` | `VARCHAR(6)` | Sí | — | FK | ubigeo dist codigo |
| `condicion_codigo` | `VARCHAR(40)` | Sí | — | — | condicion codigo |
| `fecha_condicion` | `DATE` | Sí | — | — | fecha condicion |
| `es_lider_gc_intencion` | `TINYINT(1)` | No | 0 | — | es lider gc intencion |
| `created_by` | `BIGINT UNSIGNED` | No | — | FK | created by |
| `updated_by` | `BIGINT UNSIGNED` | Sí | — | FK | updated by |
| `created_at` | `DATETIME(6)` | No | CURRENT_TIMESTAMP(6) | — | created at |
| `updated_at` | `DATETIME(6)` | Sí | NULL + ON UPDATE | — | updated at |
| `version` | `INT UNSIGNED` | No | 1 | — | version |

- **FK** `fk_persona_creador`: (created_by) → `usuario`(id).
- **FK** `fk_persona_editor`: (updated_by) → `usuario`(id).
- **FK** `fk_persona_dpto`: (ubigeo_dpto_codigo) → `ubigeo`(codigo).
- **FK** `fk_persona_prov`: (ubigeo_prov_codigo) → `ubigeo`(codigo).
- **FK** `fk_persona_dist`: (ubigeo_dist_codigo) → `ubigeo`(codigo).
- **UNIQUE KEY** `uq_persona_identidad`: (clave_identidad).
- **KEY** `ix_persona_documento`: (tipo_documento,numero_documento_norm).
- **KEY** `ix_persona_apellidos`: (apellido_paterno,apellido_materno,nombres).

## ficha — 17 columnas

| Columna | Tipo SQL | Admite NULL | Default / control | Clave | Regla |
|---|---|:---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id |
| `persona_id` | `BIGINT UNSIGNED` | No | — | FK | persona id |
| `presbiterio_id` | `BIGINT UNSIGNED` | No | — | FK | presbiterio id |
| `sede_id` | `BIGINT UNSIGNED` | No | — | FK | sede id |
| `tipo_ficha` | `ENUM('PERSONA','GC')` | No | — | — | tipo ficha |
| `numero_ficha` | `VARCHAR(50)` | Sí | — | — | numero ficha |
| `fecha_papel` | `DATE` | Sí | — | — | fecha papel |
| `responsable_papel` | `VARCHAR(180)` | Sí | — | — | responsable papel |
| `observaciones` | `TEXT` | Sí | — | — | observaciones |
| `control_papel` | `TEXT` | Sí | — | — | control papel |
| `datos_fuente_json` | `JSON` | Sí | — | — | datos fuente json |
| `posible_duplicado` | `TINYINT(1)` | No | 0 | — | posible duplicado |
| `created_by` | `BIGINT UNSIGNED` | No | — | FK | created by |
| `updated_by` | `BIGINT UNSIGNED` | Sí | — | FK | updated by |
| `created_at` | `DATETIME(6)` | No | CURRENT_TIMESTAMP(6) | — | Registro de alta (junto con created_by), sin fila de auditoría |
| `updated_at` | `DATETIME(6)` | Sí | NULL + ON UPDATE | — | updated at |
| `version` | `INT UNSIGNED` | No | 1 | — | version |

- **FK** `fk_ficha_persona`: (persona_id) → `persona`(id).
- **FK** `fk_ficha_presb`: (presbiterio_id) → `organizacion`(id).
- **FK** `fk_ficha_sede`: (sede_id,presbiterio_id) → `organizacion`(id,padre_id).
- **FK** `fk_ficha_creador`: (created_by) → `usuario`(id).
- **FK** `fk_ficha_editor`: (updated_by) → `usuario`(id).
- **UNIQUE KEY** `uq_ficha_id_tipo`: (id,tipo_ficha).
- **KEY** `ix_ficha_presb_tipo`: (presbiterio_id,tipo_ficha,created_at).
- **KEY** `ix_ficha_sede_fecha`: (sede_id,created_at).
- **KEY** `ix_ficha_creador_fecha`: (created_by,created_at).
- **KEY** `ix_ficha_persona`: (persona_id).

## grupo_crecer — 15 columnas

| Columna | Tipo SQL | Admite NULL | Default / control | Clave | Regla |
|---|---|:---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id |
| `ficha_id` | `BIGINT UNSIGNED` | No | — | FK,UK | ficha id |
| `ficha_tipo` | `ENUM('PERSONA','GC')` | No | 'GC' | FK | ficha tipo |
| `grupo_ya_existe` | `TINYINT(1)` | Sí | — | — | grupo ya existe |
| `nombre` | `VARCHAR(220)` | Sí | — | — | nombre |
| `tipo_gc` | `VARCHAR(60)` | Sí | — | — | tipo gc |
| `estado_gc` | `VARCHAR(45)` | Sí | — | — | estado gc |
| `fecha_inicio` | `DATE` | Sí | — | — | fecha inicio |
| `capacidad` | `INT UNSIGNED` | Sí | — | — | capacidad |
| `cantidad_integrantes_aprox` | `INT UNSIGNED` | Sí | — | — | cantidad integrantes aprox |
| `direccion_lugar` | `VARCHAR(500)` | Sí | — | — | direccion lugar |
| `referencia` | `VARCHAR(350)` | Sí | — | — | referencia |
| `es_matrimonios` | `TINYINT(1)` | Sí | — | — | es matrimonios |
| `created_at` | `DATETIME(6)` | No | CURRENT_TIMESTAMP(6) | — | created at |
| `updated_at` | `DATETIME(6)` | Sí | NULL + ON UPDATE | — | updated at |

- **FK** `fk_gc_ficha_tipo`: (ficha_id,ficha_tipo) → `ficha`(id,tipo_ficha).
- **UNIQUE KEY** `uq_gc_ficha`: (ficha_id).
- **CHECK** `chk_gc_tipo`: `ficha_tipo='GC'`.

## auditoria_evento — 14 columnas

| Columna | Tipo SQL | Admite NULL | Default / control | Clave | Regla |
|---|---|:---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id |
| `request_id` | `VARCHAR(75)` | No | — | — | request id |
| `sesion_id` | `BIGINT UNSIGNED` | Sí | — | FK | sesion id |
| `usuario_id` | `BIGINT UNSIGNED` | No | — | FK | usuario id |
| `actor_codigo` | `VARCHAR(80)` | No | — | — | Código actor al momento del cambio, inmutable |
| `actor_perfil` | `ENUM('DIGITADOR','ADMIN','REPORTES')` | No | — | — | Perfil del actor al momento del cambio |
| `accion` | `VARCHAR(60)` | No | — | — | accion |
| `recurso_tipo` | `VARCHAR(60)` | No | — | — | recurso tipo |
| `recurso_id` | `BIGINT UNSIGNED` | Sí | — | — | recurso id |
| `ip` | `VARBINARY(16)` | Sí | — | — | ip |
| `antes_json` | `JSON` | Sí | — | — | Solo campos realmente modificados antes, nunca ficha completa |
| `despues_json` | `JSON` | Sí | — | — | Solo campos realmente modificados después |
| `created_at` | `DATETIME(6)` | No | CURRENT_TIMESTAMP(6) | — | created at |
| `updated_at` | `DATETIME(6)` | Sí | NULL + ON UPDATE | — | updated at |

- **FK** `fk_audit_sesion`: (sesion_id) → `sesion`(id).
- **FK** `fk_audit_usuario`: (usuario_id) → `usuario`(id).
- **KEY** `ix_auditoria_recurso`: (recurso_tipo,recurso_id,created_at).
- **KEY** `ix_auditoria_actor`: (usuario_id,created_at).

## idempotencia — 8 columnas

| Columna | Tipo SQL | Admite NULL | Default / control | Clave | Regla |
|---|---|:---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id |
| `usuario_id` | `BIGINT UNSIGNED` | No | — | FK | usuario id |
| `clave` | `VARCHAR(110)` | No | — | — | clave |
| `payload_hash` | `CHAR(64)` | No | — | — | payload hash |
| `response_status` | `SMALLINT UNSIGNED` | Sí | — | — | response status |
| `response_json` | `JSON` | Sí | — | — | response json |
| `created_at` | `DATETIME(6)` | No | CURRENT_TIMESTAMP(6) | — | Índice para limpieza futura con ventana aprobada |
| `updated_at` | `DATETIME(6)` | Sí | NULL + ON UPDATE | — | updated at |

- **FK** `fk_idem_usuario`: (usuario_id) → `usuario`(id).
- **UNIQUE KEY** `uq_idem_usuario_clave`: (usuario_id,clave).
- **KEY** `ix_idem_created_at`: (created_at).

