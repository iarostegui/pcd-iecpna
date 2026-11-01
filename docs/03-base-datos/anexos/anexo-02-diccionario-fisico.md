# Anexo 02 — Diccionario físico PCD 11 tablas

**Generado desde:** [001_initial.sql](../../../database/migrations/001_initial.sql). Total **11 tablas, 134 columnas y 23 relaciones FK**. Diseño físico propuesto, pendiente de ejecución en motor MySQL/MariaDB.

El campo de documento usa **solo tipo y número**. La consulta es general a la funcionalidad; presbiterio/sede se aplican como filtros, no ámbitos de autorización. Las relaciones estructurales se verifican también en PHP.

[Diagrama físico](anexo-01-diagrama-fisico.md) · [Índice del modelo](../README.md)

## organizacion — Presbiterios y sedes en jerarquía, sede creada durante captura

| Columna | Tipo SQL | NULL | Default | Claves | Significado/validación | Origen |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Catálogo / administración |
| `tipo` | `ENUM('PRESBITERIO','SEDE')` | No | — | — | tipo | Catálogo / administración |
| `padre_id` | `BIGINT UNSIGNED` | Sí | — | FK | padre id | Catálogo / administración |
| `codigo` | `VARCHAR(50)` | Sí | — | UK | codigo | Catálogo / administración |
| `nombre` | `VARCHAR(200)` | No | — | — | nombre | Catálogo / administración |
| `nombre_norm` | `VARCHAR(200)` | No | — | — | nombre norm | Catálogo / administración |
| `referencia` | `VARCHAR(300)` | Sí | — | — | referencia | Catálogo / administración |
| `activo` | `TINYINT(1)` | No | `1` | — | activo | Catálogo / administración |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Catálogo / administración |

**Claves foráneas:**
- `fk_org_padre`: (`padre_id`) → `organizacion` (`id`).

**Índices:**
- UNIQUE KEY `uq_org_codigo` (`codigo`).
- UNIQUE KEY `uq_org_sede_nombre` (`padre_id`, `nombre_norm`).
- UNIQUE KEY `uq_org_id_padre` (`id`, `padre_id`).
- KEY `ix_org_tipo_padre` (`tipo`, `padre_id`, `activo`).

**CHECK:**
- `chk_org_jerarquia`: ` (tipo='PRESBITERIO' AND padre_id IS NULL) OR (tipo='SEDE' AND padre_id IS NOT NULL) `.

## usuario — Cuentas, roles, hash y presbiterio operativo

| Columna | Tipo SQL | NULL | Default | Claves | Significado/validación | Origen |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Sistema / control |
| `codigo` | `VARCHAR(80)` | No | — | UK | codigo | Sistema / control |
| `nombre_display` | `VARCHAR(180)` | No | — | — | nombre display | Sistema / control |
| `perfil` | `ENUM('DIGITADOR','ADMIN','REPORTES')` | No | — | — | perfil | Sistema / control |
| `presbiterio_id` | `BIGINT UNSIGNED` | Sí | — | FK | Presbiterio operativo del digitador, nulo para perfiles nacionales | Sistema / control |
| `password_hash` | `VARCHAR(255)` | No | — | — | password hash | Sistema / control |
| `activo` | `TINYINT(1)` | No | `1` | — | activo | Sistema / control |
| `auth_version` | `INT UNSIGNED` | No | `1` | — | auth version | Sistema / control |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Sistema / control |
| `updated_at` | `DATETIME(6)` | Sí | — | — | updated at | Sistema / control |

**Claves foráneas:**
- `fk_usuario_presb`: (`presbiterio_id`) → `organizacion` (`id`).

**Índices:**
- UNIQUE KEY `uq_usuario_codigo` (`codigo`).
- KEY `ix_user_presb` (`presbiterio_id`).

## sesion — Sesiones, revocación, contexto y un único GPS opcional

| Columna | Tipo SQL | NULL | Default | Claves | Significado/validación | Origen |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Sistema / control |
| `usuario_id` | `BIGINT UNSIGNED` | No | — | FK | usuario id | Sistema / control |
| `presbiterio_id` | `BIGINT UNSIGNED` | No | — | FK | presbiterio id | Sistema / control |
| `sede_id` | `BIGINT UNSIGNED` | Sí | — | FK | sede id | Sistema / control |
| `session_ref_hash` | `CHAR(64)` | No | — | UK | session ref hash | Sistema / control |
| `ip_login` | `VARBINARY(16)` | Sí | — | — | ip login | Sistema / control |
| `user_agent` | `VARCHAR(255)` | Sí | — | — | user agent | Sistema / control |
| `fecha_login` | `DATETIME(6)` | No | — | — | fecha login | Sistema / control |
| `fecha_logout` | `DATETIME(6)` | Sí | — | — | fecha logout | Sistema / control |
| `last_seen` | `DATETIME(6)` | Sí | — | — | last seen | Sistema / control |
| `revocada_en` | `DATETIME(6)` | Sí | — | — | revocada en | Sistema / control |
| `auth_version_al_ingreso` | `INT UNSIGNED` | No | — | — | auth version al ingreso | Sistema / control |
| `geo_estado` | `ENUM('OK','DENEGADA','NO_DISPONIBLE','ERROR')` | Sí | — | — | Indica que geolocalización ya se intentó en esa sesión; no reintentar | Sistema / control |
| `geo_latitud` | `DECIMAL(10,7)` | Sí | — | — | geo latitud | Sistema / control |
| `geo_longitud` | `DECIMAL(10,7)` | Sí | — | — | geo longitud | Sistema / control |
| `geo_precision_m` | `DECIMAL(10,2)` | Sí | — | — | geo precision m | Sistema / control |
| `geo_capturada_en` | `DATETIME(6)` | Sí | — | — | geo capturada en | Sistema / control |

**Claves foráneas:**
- `fk_sesion_usuario`: (`usuario_id`) → `usuario` (`id`).
- `fk_sesion_presb`: (`presbiterio_id`) → `organizacion` (`id`).
- `fk_sesion_sede`: (`sede_id`, `presbiterio_id`) → `organizacion` (`id`, `padre_id`).

**Índices:**
- UNIQUE KEY `uq_sesion_hash` (`session_ref_hash`).
- KEY `ix_sesion_user_fecha` (`usuario_id`, `fecha_login`).

**CHECK:**
- `chk_sesion_geo`: ` (geo_estado IS NULL AND geo_capturada_en IS NULL) OR (geo_estado IS NOT NULL AND geo_capturada_en IS NOT NULL) `.

## login_evento — Intentos, bloqueo y salida incluso sin usuario autenticado

| Columna | Tipo SQL | NULL | Default | Claves | Significado/validación | Origen |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Sistema / control |
| `usuario_id` | `BIGINT UNSIGNED` | Sí | — | FK | usuario id | Sistema / control |
| `presbiterio_id` | `BIGINT UNSIGNED` | Sí | — | FK | presbiterio id | Sistema / control |
| `codigo_intentado` | `VARCHAR(80)` | Sí | — | — | codigo intentado | Sistema / control |
| `resultado` | `ENUM('OK','FALLIDO','BLOQUEADO','LOGOUT')` | No | — | — | resultado | Sistema / control |
| `ip` | `VARBINARY(16)` | Sí | — | — | ip | Sistema / control |
| `user_agent` | `VARCHAR(255)` | Sí | — | — | user agent | Sistema / control |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Sistema / control |

**Claves foráneas:**
- `fk_login_usuario`: (`usuario_id`) → `usuario` (`id`).
- `fk_login_presb`: (`presbiterio_id`) → `organizacion` (`id`).

**Índices:**
- KEY `ix_login_codigo_fecha` (`codigo_intentado`, `created_at`).
- KEY `ix_login_ip_fecha` (`ip`, `created_at`).

## catalogo — Tabla de tablas de listas cortas y campos críticos JSON

| Columna | Tipo SQL | NULL | Default | Claves | Significado/validación | Origen |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Catálogo / administración |
| `categoria` | `VARCHAR(70)` | No | — | — | categoria | Catálogo / administración |
| `codigo` | `VARCHAR(85)` | No | — | — | codigo | Catálogo / administración |
| `etiqueta` | `VARCHAR(160)` | No | — | — | etiqueta | Catálogo / administración |
| `orden` | `SMALLINT UNSIGNED` | No | `0` | — | orden | Catálogo / administración |
| `activo` | `TINYINT(1)` | No | `1` | — | activo | Catálogo / administración |
| `config_json` | `JSON` | Sí | — | — | Opciones configurables; para CAMPO_CRITICO contiene ruta_dato/es_critico/desactivable | Catálogo / administración |
| `updated_by` | `BIGINT UNSIGNED` | Sí | — | FK | updated by | Catálogo / administración |
| `updated_at` | `DATETIME(6)` | Sí | — | — | updated at | Catálogo / administración |

**Claves foráneas:**
- `fk_catalogo_editor`: (`updated_by`) → `usuario` (`id`).

**Índices:**
- UNIQUE KEY `uq_catalogo_categoria_codigo` (`categoria`, `codigo`).

## ubigeo — Árbol INEI: departamento/provincia/distrito

| Columna | Tipo SQL | NULL | Default | Claves | Significado/validación | Origen |
|---|---|:---:|---|---|---|---|
| `codigo` | `VARCHAR(6)` | No | — | PK | codigo | Catálogo / administración |
| `tipo` | `ENUM('DEPARTAMENTO','PROVINCIA','DISTRITO')` | No | — | — | tipo | Catálogo / administración |
| `padre_codigo` | `VARCHAR(6)` | Sí | — | FK | padre codigo | Catálogo / administración |
| `nombre` | `VARCHAR(160)` | No | — | — | nombre | Catálogo / administración |
| `activo` | `TINYINT(1)` | No | `1` | — | activo | Catálogo / administración |

**Claves foráneas:**
- `fk_ubigeo_padre`: (`padre_codigo`) → `ubigeo` (`codigo`).

**Índices:**
- KEY `ix_ubigeo_padre` (`padre_codigo`, `tipo`, `nombre`).

**CHECK:**
- `chk_ubigeo_nivel`: ` (tipo='DEPARTAMENTO' AND padre_codigo IS NULL AND CHAR_LENGTH(codigo)=2) OR (tipo='PROVINCIA' AND padre_codigo IS NOT NULL AND CHAR_LENGTH(codigo)=4) OR (tipo='DISTRITO' AND padre_codigo IS NOT NULL AND CHAR_LENGTH(codigo)=6) `.

## persona — Identidad consolidada y atributos de Persona

| Columna | Tipo SQL | NULL | Default | Claves | Significado/validación | Origen |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Ficha / sistema |
| `tipo_documento` | `VARCHAR(24)` | Sí | — | — | Único selector documental del formulario, SIN subtipo | Ficha / sistema |
| `numero_documento` | `VARCHAR(60)` | Sí | — | — | Número documental de origen, conserva ceros iniciales | Ficha / sistema |
| `numero_documento_norm` | `VARCHAR(60)` | Sí | — | — | numero documento norm | Ficha / sistema |
| `clave_identidad` | `VARCHAR(120)` | Sí | — | UK | Clave única nullable para DNI o CE verificable, OTRO sin fusión exacta | Ficha / sistema |
| `nombres` | `VARCHAR(150)` | Sí | — | — | nombres | Ficha / sistema |
| `apellido_paterno` | `VARCHAR(100)` | Sí | — | — | apellido paterno | Ficha / sistema |
| `apellido_materno` | `VARCHAR(100)` | Sí | — | — | apellido materno | Ficha / sistema |
| `fecha_nacimiento` | `DATE` | Sí | — | — | fecha nacimiento | Ficha / sistema |
| `sexo_codigo` | `VARCHAR(24)` | Sí | — | — | sexo codigo | Ficha / sistema |
| `estado_civil_codigo` | `VARCHAR(24)` | Sí | — | — | estado civil codigo | Ficha / sistema |
| `telefono` | `VARCHAR(60)` | Sí | — | — | telefono | Ficha / sistema |
| `correo` | `VARCHAR(180)` | Sí | — | — | correo | Ficha / sistema |
| `facebook_usuario` | `VARCHAR(180)` | Sí | — | — | facebook usuario | Ficha / sistema |
| `direccion` | `VARCHAR(500)` | Sí | — | — | direccion | Ficha / sistema |
| `ubigeo_dpto_codigo` | `VARCHAR(6)` | Sí | — | FK | ubigeo dpto codigo | Ficha / sistema |
| `ubigeo_prov_codigo` | `VARCHAR(6)` | Sí | — | FK | ubigeo prov codigo | Ficha / sistema |
| `ubigeo_dist_codigo` | `VARCHAR(6)` | Sí | — | FK | ubigeo dist codigo | Ficha / sistema |
| `condicion_codigo` | `VARCHAR(40)` | Sí | — | — | condicion codigo | Ficha / sistema |
| `fecha_condicion` | `DATE` | Sí | — | — | fecha condicion | Ficha / sistema |
| `es_lider_gc_intencion` | `TINYINT(1)` | No | `0` | — | es lider gc intencion | Ficha / sistema |
| `created_by` | `BIGINT UNSIGNED` | No | — | FK | created by | Ficha / sistema |
| `updated_by` | `BIGINT UNSIGNED` | Sí | — | FK | updated by | Ficha / sistema |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Ficha / sistema |
| `updated_at` | `DATETIME(6)` | Sí | — | — | updated at | Ficha / sistema |
| `version` | `INT UNSIGNED` | No | `1` | — | version | Ficha / sistema |

**Claves foráneas:**
- `fk_persona_creador`: (`created_by`) → `usuario` (`id`).
- `fk_persona_editor`: (`updated_by`) → `usuario` (`id`).
- `fk_persona_dpto`: (`ubigeo_dpto_codigo`) → `ubigeo` (`codigo`).
- `fk_persona_prov`: (`ubigeo_prov_codigo`) → `ubigeo` (`codigo`).
- `fk_persona_dist`: (`ubigeo_dist_codigo`) → `ubigeo` (`codigo`).

**Índices:**
- UNIQUE KEY `uq_persona_identidad` (`clave_identidad`).
- KEY `ix_persona_documento` (`tipo_documento`, `numero_documento_norm`).
- KEY `ix_persona_apellidos` (`apellido_paterno`, `apellido_materno`, `nombres`).

## ficha — Origen de fichas Persona/GC, autor, papel y datos JSON

| Columna | Tipo SQL | NULL | Default | Claves | Significado/validación | Origen |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Ficha / sistema |
| `persona_id` | `BIGINT UNSIGNED` | No | — | FK | persona id | Ficha / sistema |
| `presbiterio_id` | `BIGINT UNSIGNED` | No | — | FK | presbiterio id | Ficha / sistema |
| `sede_id` | `BIGINT UNSIGNED` | No | — | FK | Sede del presbiterio asociado; FK compuesta asegura padre correcto | Ficha / sistema |
| `tipo_ficha` | `ENUM('PERSONA','GC')` | No | — | — | tipo ficha | Ficha / sistema |
| `numero_ficha` | `VARCHAR(50)` | Sí | — | — | numero ficha | Ficha / sistema |
| `fecha_papel` | `DATE` | Sí | — | — | fecha papel | Ficha / sistema |
| `responsable_papel` | `VARCHAR(180)` | Sí | — | — | responsable papel | Ficha / sistema |
| `observaciones` | `TEXT` | Sí | — | — | observaciones | Ficha / sistema |
| `control_papel` | `TEXT` | Sí | — | — | control papel | Ficha / sistema |
| `datos_fuente_json` | `JSON` | Sí | — | — | Secciones papel: conyuge, responsabilidades, controles y otros originales; validación backend | Ficha / sistema |
| `posible_duplicado` | `TINYINT(1)` | No | `0` | — | posible duplicado | Ficha / sistema |
| `created_by` | `BIGINT UNSIGNED` | No | — | FK | created by | Ficha / sistema |
| `updated_by` | `BIGINT UNSIGNED` | Sí | — | FK | updated by | Ficha / sistema |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Ficha / sistema |
| `updated_at` | `DATETIME(6)` | Sí | — | — | updated at | Ficha / sistema |
| `version` | `INT UNSIGNED` | No | `1` | — | version | Ficha / sistema |

**Claves foráneas:**
- `fk_ficha_persona`: (`persona_id`) → `persona` (`id`).
- `fk_ficha_presb`: (`presbiterio_id`) → `organizacion` (`id`).
- `fk_ficha_sede`: (`sede_id`, `presbiterio_id`) → `organizacion` (`id`, `padre_id`).
- `fk_ficha_creador`: (`created_by`) → `usuario` (`id`).
- `fk_ficha_editor`: (`updated_by`) → `usuario` (`id`).

**Índices:**
- UNIQUE KEY `uq_ficha_id_tipo` (`id`, `tipo_ficha`).
- KEY `ix_ficha_presb_tipo` (`presbiterio_id`, `tipo_ficha`, `created_at`).
- KEY `ix_ficha_sede_fecha` (`sede_id`, `created_at`).
- KEY `ix_ficha_creador_fecha` (`created_by`, `created_at`).
- KEY `ix_ficha_persona` (`persona_id`).

## grupo_crecer — Datos generales de GC y vínculo con ficha líder

| Columna | Tipo SQL | NULL | Default | Claves | Significado/validación | Origen |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Ficha / sistema |
| `ficha_id` | `BIGINT UNSIGNED` | No | — | FK,UK | Ficha GC; líder derivado de ficha.persona_id, sin duplicar líder | Ficha / sistema |
| `ficha_tipo` | `ENUM('PERSONA','GC')` | No | `'GC'` | FK | ficha tipo | Ficha / sistema |
| `grupo_ya_existe` | `TINYINT(1)` | Sí | — | — | grupo ya existe | Ficha / sistema |
| `nombre` | `VARCHAR(220)` | Sí | — | — | nombre | Ficha / sistema |
| `tipo_gc` | `VARCHAR(60)` | Sí | — | — | tipo gc | Ficha / sistema |
| `estado_gc` | `VARCHAR(45)` | Sí | — | — | estado gc | Ficha / sistema |
| `fecha_inicio` | `DATE` | Sí | — | — | fecha inicio | Ficha / sistema |
| `capacidad` | `INT UNSIGNED` | Sí | — | — | capacidad | Ficha / sistema |
| `cantidad_integrantes_aprox` | `INT UNSIGNED` | Sí | — | — | Cantidad aproximada, no identidades nominales | Ficha / sistema |
| `direccion_lugar` | `VARCHAR(500)` | Sí | — | — | direccion lugar | Ficha / sistema |
| `referencia` | `VARCHAR(350)` | Sí | — | — | referencia | Ficha / sistema |
| `es_matrimonios` | `TINYINT(1)` | Sí | — | — | es matrimonios | Ficha / sistema |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Ficha / sistema |
| `updated_at` | `DATETIME(6)` | Sí | — | — | updated at | Ficha / sistema |

**Claves foráneas:**
- `fk_gc_ficha_tipo`: (`ficha_id`, `ficha_tipo`) → `ficha` (`id`, `tipo_ficha`).

**Índices:**
- UNIQUE KEY `uq_gc_ficha` (`ficha_id`).

**CHECK:**
- `chk_gc_tipo`: `ficha_tipo='GC'`.

## auditoria_evento — Actor, acción, valores antes/después

| Columna | Tipo SQL | NULL | Default | Claves | Significado/validación | Origen |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Sistema / control |
| `request_id` | `VARCHAR(75)` | No | — | — | request id | Sistema / control |
| `sesion_id` | `BIGINT UNSIGNED` | Sí | — | FK | sesion id | Sistema / control |
| `usuario_id` | `BIGINT UNSIGNED` | No | — | FK | usuario id | Sistema / control |
| `accion` | `VARCHAR(60)` | No | — | — | accion | Sistema / control |
| `recurso_tipo` | `VARCHAR(60)` | No | — | — | recurso tipo | Sistema / control |
| `recurso_id` | `BIGINT UNSIGNED` | Sí | — | — | recurso id | Sistema / control |
| `ip` | `VARBINARY(16)` | Sí | — | — | ip | Sistema / control |
| `antes_json` | `JSON` | Sí | — | — | antes json | Sistema / control |
| `despues_json` | `JSON` | Sí | — | — | despues json | Sistema / control |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Sistema / control |

**Claves foráneas:**
- `fk_audit_sesion`: (`sesion_id`) → `sesion` (`id`).
- `fk_audit_usuario`: (`usuario_id`) → `usuario` (`id`).

**Índices:**
- KEY `ix_auditoria_recurso` (`recurso_tipo`, `recurso_id`, `created_at`).
- KEY `ix_auditoria_actor` (`usuario_id`, `created_at`).

## idempotencia — Reintentos de mutación y payload

| Columna | Tipo SQL | NULL | Default | Claves | Significado/validación | Origen |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Sistema / control |
| `usuario_id` | `BIGINT UNSIGNED` | No | — | FK | usuario id | Sistema / control |
| `clave` | `VARCHAR(110)` | No | — | — | clave | Sistema / control |
| `payload_hash` | `CHAR(64)` | No | — | — | payload hash | Sistema / control |
| `response_status` | `SMALLINT UNSIGNED` | Sí | — | — | response status | Sistema / control |
| `response_json` | `JSON` | Sí | — | — | response json | Sistema / control |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Sistema / control |

**Claves foráneas:**
- `fk_idem_usuario`: (`usuario_id`) → `usuario` (`id`).

**Índices:**
- UNIQUE KEY `uq_idem_usuario_clave` (`usuario_id`, `clave`).

