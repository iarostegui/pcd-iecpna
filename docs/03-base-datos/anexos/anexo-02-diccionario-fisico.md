# Anexo 02 — Diccionario físico de datos PCD

Fuente: [001_initial.sql](../../../database/migrations/001_initial.sql). Generado desde CREATE TABLE del esquema propuesto. 17 tablas, 109 columnas y 28 FK. **No ejecutado en MySQL/MariaDB real**.

El significado de nombres no sustituye validación de datos; descripciones de columnas genéricas son explicaciones técnicas preliminares. Las validaciones específicas aprobadas están en reglas de negocio y documentos de E1/E2.

[Anexo 01 — Diagrama](anexo-01-diagrama-fisico.md) · [Índice de datos](../README.md)

## presbiterio — Ámbito territorial

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Identificador / relación |
| `codigo` | `VARCHAR(40)` | No | — | UK | codigo | Administración / sistema |
| `activo` | `TINYINT(1)` | No | `1` | — | activo | Administración / sistema |

**Índices:**
- UNIQUE KEY `uq_presbiterio_codigo` (`codigo`).

## usuario — Cuenta, perfil y alcance de consulta

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Identificador / relación |
| `codigo` | `VARCHAR(80)` | No | — | UK | codigo | Administración / sistema |
| `perfil` | `ENUM('DIGITADOR','ADMIN','REPORTES')` | No | — | — | perfil | Administración / sistema |
| `alcance_consulta` | `ENUM('GENERAL','PRESBITERIO')` | No | `'PRESBITERIO'` | — | GENERAL o PRESBITERIO; acceso solo lectura según permiso, no habilita edición | Administración / sistema |
| `password_hash` | `VARCHAR(255)` | No | `1` | — | password hash | Administración / sistema |
| `auth_version` | `INT UNSIGNED` | No | `1` | — | auth version; incremento en reset, cambio credencial o desactivación | Administración / sistema |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Administración / sistema |

**Índices:**
- UNIQUE KEY `uq_usuario_codigo` (`codigo`).

## usuario_presbiterio — Permisos de presbiterios

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `usuario_id` | `BIGINT UNSIGNED` | No | — | PK,FK | usuario id | Identificador / relación |
| `presbiterio_id` | `BIGINT UNSIGNED` | No | — | PK,FK | presbiterio id | Identificador / relación |

**FK:**
- `fk_up_usuario` (`usuario_id`) → `usuario` (`id`).
- `fk_up_presbiterio` (`presbiterio_id`) → `presbiterio` (`id`).

**Índices:**
- PRIMARY KEY `PRIMARY` (`usuario_id`, `presbiterio_id`).

## sede — Sede de digitación

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Identificador / relación |
| `presbiterio_id` | `BIGINT UNSIGNED` | No | — | FK | presbiterio id | Identificador / relación |
| `codigo` | `VARCHAR(50)` | No | — | — | codigo | Administración / sistema |
| `nombre_norm` | `VARCHAR(200)` | No | — | — | nombre norm | Administración / sistema |
| `activo` | `TINYINT(1)` | No | `1` | — | activo | Administración / sistema |
| `created_by` | `BIGINT UNSIGNED` | No | — | FK | created by | Administración / sistema |

**FK:**
- `fk_sede_presb` (`presbiterio_id`) → `presbiterio` (`id`).
- `fk_sede_autor` (`created_by`) → `usuario` (`id`).

**Índices:**
- UNIQUE KEY `uq_sede_presbiterio_nombre` (`presbiterio_id`, `nombre_norm`).
- UNIQUE KEY `uq_sede_ambito` (`presbiterio_id`, `id`).
- KEY `ix_sede_presbiterio_activo` (`presbiterio_id`, `activo`).

## campana — Campaña temporal

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Identificador / relación |
| `codigo` | `VARCHAR(50)` | No | — | UK | codigo | Administración / sistema |
| `estado` | `VARCHAR(30)` | No | `'ACTIVA'` | — | estado | Administración / sistema |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Administración / sistema |

**Índices:**
- UNIQUE KEY `uq_campana_codigo` (`codigo`).

## catalogo_valor — Opciones de formularios

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Identificador / relación |
| `dominio` | `VARCHAR(45)` | No | — | — | dominio | Parámetro / catálogo |
| `etiqueta` | `VARCHAR(130)` | No | `0` | — | etiqueta | Parámetro / catálogo |
| `activo` | `TINYINT(1)` | No | `1` | — | activo | Parámetro / catálogo |

**Índices:**
- UNIQUE KEY `uq_catalogo_dominio_codigo` (`dominio`, `codigo`).

## sesion_captura — Sesión PHP y contexto

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Identificador / relación |
| `usuario_id` | `BIGINT UNSIGNED` | No | — | FK | usuario id | Identificador / relación |
| `sede_id` | `BIGINT UNSIGNED` | No | — | FK | sede id | Identificador / relación |
| `ip_login` | `VARBINARY(16)` | Sí | — | — | ip login | Sistema / auditoría |
| `fecha_login` | `DATETIME(6)` | No | — | — | fecha login | Sistema / auditoría |
| `last_seen` | `DATETIME(6)` | Sí | — | — | last seen | Sistema / auditoría |
| `auth_version_al_ingreso` | `INT UNSIGNED` | No | — | — | auth version al ingreso; comparar con usuario.auth_version al autorizar | Sistema / auditoría |

**FK:**
- `fk_sc_user` (`usuario_id`) → `usuario` (`id`).
- `fk_sc_pres` (`presbiterio_id`) → `presbiterio` (`id`).
- `fk_sc_sede` (`presbiterio_id`, `sede_id`) → `sede` (`presbiterio_id`, `id`).

**Índices:**
- UNIQUE KEY `uq_sesion_ref` (`session_ref_hash`).
- KEY `ix_sesion_usuario_fecha` (`usuario_id`, `fecha_login`).

## geo_sesion — GPS único por sesión

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `sesion_id` | `BIGINT UNSIGNED` | No | — | PK,FK | PK/FK; máximo un registro por sesión | Identificador / relación |
| `estado` | `ENUM('OK','DENEGADA','NO_DISPONIBLE','ERROR')` | No | — | — | estado | Ubicación opcional |
| `latitud` | `DECIMAL(10,7)` | Sí | — | — | latitud | Ubicación opcional |
| `precision_m` | `DECIMAL(10,2)` | No | — | — | precision m | Ubicación opcional |

**FK:**
- `fk_geo_session` (`sesion_id`) → `sesion_captura` (`id`).

## login_evento — Historial de acceso

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Identificador / relación |
| `usuario_id` | `BIGINT UNSIGNED` | Sí | — | FK | usuario id | Identificador / relación |
| `codigo_intentado` | `VARCHAR(80)` | Sí | — | — | codigo intentado | Sistema / auditoría |
| `resultado` | `ENUM('OK','FALLIDO','BLOQUEADO','LOGOUT')` | No | — | — | resultado | Sistema / auditoría |
| `ip` | `VARBINARY(16)` | Sí | — | — | ip | Sistema / auditoría |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Sistema / auditoría |

**FK:**
- `fk_le_usuario` (`usuario_id`) → `usuario` (`id`).
- `fk_le_pres` (`presbiterio_id`) → `presbiterio` (`id`).

**Índices:**
- KEY `ix_login_fecha` (`created_at`).
- KEY `ix_login_codigo_fecha` (`codigo_intentado`, `created_at`).
- KEY `ix_login_ip_fecha` (`ip`, `created_at`).

## persona_captura — Identidad consolidada

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Identificador / relación |
| `tipo_documento` | `VARCHAR(24)` | Sí | — | — | tipo documento | Ficha / captura |
| `numero_documento` | `VARCHAR(60)` | Sí | — | — | numero documento | Ficha / captura |
| `clave_identidad` | `VARCHAR(220)` | Sí | — | UK | UNIQUE NULL permitido; documento confiable normalizado tipo/subtipo/número | Ficha / captura |
| `nombres` | `VARCHAR(150)` | Sí | — | — | nombres | Ficha / captura |
| `apellido_materno` | `VARCHAR(100)` | Sí | — | — | apellido materno | Ficha / captura |
| `sexo_codigo` | `VARCHAR(24)` | Sí | — | — | sexo codigo | Ficha / captura |
| `telefono` | `VARCHAR(60)` | Sí | — | — | telefono | Ficha / captura |
| `facebook_usuario` | `VARCHAR(180)` | Sí | — | — | facebook usuario | Ficha / captura |
| `ubigeo_dpto_codigo` | `VARCHAR(10)` | Sí | — | — | ubigeo dpto codigo | Ficha / captura |
| `ubigeo_dist_codigo` | `VARCHAR(10)` | Sí | — | — | ubigeo dist codigo | Ficha / captura |
| `fecha_condicion` | `DATE` | No | `0` | — | fecha condicion | Ficha / captura |
| `created_by` | `BIGINT UNSIGNED` | No | — | FK | created by | Ficha / captura |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Ficha / captura |
| `version` | `INT UNSIGNED` | No | `1` | — | version | Ficha / captura |

**FK:**
- `fk_pc_creador` (`created_by`) → `usuario` (`id`).
- `fk_pc_editor` (`updated_by`) → `usuario` (`id`).

**Índices:**
- UNIQUE KEY `uq_persona_clave_identidad` (`clave_identidad`).
- KEY `ix_persona_doc_candidato` (`tipo_documento`, `numero_documento_norm`).
- KEY `ix_persona_apellidos` (`apellido_paterno`, `apellido_materno`, `nombres`).
- KEY `ix_persona_nombres` (`nombres`).

## ficha_captura — Fichas fuente y autoría

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Identificador / relación |
| `campana_id` | `BIGINT UNSIGNED` | No | — | FK | campana id | Identificador / relación |
| `persona_id` | `BIGINT UNSIGNED` | No | — | FK | persona id | Identificador / relación |
| `presbiterio_id` | `BIGINT UNSIGNED` | No | — | FK | presbiterio id | Identificador / relación |
| `sede_id` | `BIGINT UNSIGNED` | No | — | FK | sede id | Identificador / relación |
| `tipo_ficha` | `ENUM('PERSONA','GC')` | No | — | — | tipo ficha | Ficha / captura |
| `numero_ficha` | `VARCHAR(50)` | Sí | — | — | Número de formulario opcional, no único | Ficha / captura |
| `fecha_papel` | `DATE` | Sí | — | — | fecha papel | Ficha / captura |
| `observaciones` | `TEXT` | Sí | — | — | observaciones | Ficha / captura |
| `datos_fuente_json` | `JSON` | Sí | — | — | Datos fuente aceptados; no garantiza copia fiel al papel | Ficha / captura |
| `posible_duplicado` | `TINYINT(1)` | No | `0` | — | posible duplicado | Ficha / captura |
| `created_by` | `BIGINT UNSIGNED` | No | — | FK | created by | Ficha / captura |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Ficha / captura |
| `version` | `INT UNSIGNED` | No | `1` | — | version | Ficha / captura |

**FK:**
- `fk_fc_campana` (`campana_id`) → `campana` (`id`).
- `fk_fc_persona` (`persona_id`) → `persona_captura` (`id`).
- `fk_fc_sede` (`presbiterio_id`, `sede_id`) → `sede` (`presbiterio_id`, `id`).
- `fk_fc_autor` (`created_by`) → `usuario` (`id`).
- `fk_fc_editor` (`updated_by`) → `usuario` (`id`).

**Índices:**
- KEY `ix_ficha_sede_fecha` (`sede_id`, `created_at`).
- KEY `ix_ficha_autor_fecha` (`created_by`, `created_at`).
- KEY `ix_ficha_pres_tipo` (`presbiterio_id`, `tipo_ficha`, `created_at`).
- KEY `ix_ficha_persona` (`persona_id`).
- UNIQUE KEY `uq_ficha_id_tipo` (`id`, `tipo_ficha`).
- UNIQUE KEY `uq_ficha_id_persona_tipo` (`id`, `persona_id`, `tipo_ficha`).

## conyuge_pendiente — Cónyuge declarado en papel

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Identificador / relación |
| `persona_id` | `BIGINT UNSIGNED` | No | — | FK | persona id | Identificador / relación |
| `ficha_tipo` | `ENUM('PERSONA','GC')` | No | `'PERSONA'` | FK | ficha tipo | Ficha / captura |
| `tipo_documento` | `VARCHAR(24)` | Sí | — | — | tipo documento | Ficha / captura |
| `nombre_libre` | `VARCHAR(240)` | Sí | — | — | nombre libre | Ficha / captura |

**FK:**
- `fk_con_persona` (`persona_id`) → `persona_captura` (`id`).
- `fk_con_ficha_persona` (`ficha_id`, `persona_id`, `ficha_tipo`) → `ficha_captura` (`id`, `persona_id`, `tipo_ficha`).

**CHECK:**
- `chk_con_tipo`: ficha_tipo='PERSONA'

## persona_responsabilidad_captura — Responsabilidades declaradas

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Identificador / relación |
| `persona_id` | `BIGINT UNSIGNED` | No | — | FK | persona id | Identificador / relación |
| `ficha_tipo` | `ENUM('PERSONA','GC')` | No | `'PERSONA'` | FK | ficha tipo | Ficha / captura |
| `tipo_codigo` | `VARCHAR(50)` | No | — | — | tipo codigo | Ficha / captura |
| `region_libre` | `VARCHAR(180)` | Sí | — | — | region libre | Ficha / captura |
| `descripcion` | `VARCHAR(280)` | Sí | — | — | descripcion | Ficha / captura |

**FK:**
- `fk_pr_persona` (`persona_id`) → `persona_captura` (`id`).
- `fk_pr_ficha_persona` (`ficha_id`, `persona_id`, `ficha_tipo`) → `ficha_captura` (`id`, `persona_id`, `tipo_ficha`).
- `fk_pr_sede` (`sede_id`) → `sede` (`id`).

**Índices:**
- KEY `ix_resp_persona` (`persona_id`).

**CHECK:**
- `chk_pr_tipo`: ficha_tipo='PERSONA'

## grupo_crecer_captura — Datos de GC y líder vía ficha

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Identificador / relación |
| `ficha_id` | `BIGINT UNSIGNED` | No | — | FK,UK | Ficha de tipo GC; persona_id de ficha es único líder | Identificador / relación |
| `ficha_tipo` | `ENUM('PERSONA','GC')` | No | `'GC'` | FK | ficha tipo | Ficha / captura |
| `grupo_ya_existe` | `TINYINT(1)` | Sí | — | — | grupo ya existe | Ficha / captura |
| `nombre` | `VARCHAR(220)` | Sí | — | — | nombre | Ficha / captura |
| `estado_gc` | `VARCHAR(45)` | Sí | — | — | estado gc | Ficha / captura |
| `capacidad` | `INT UNSIGNED` | Sí | — | — | capacidad | Ficha / captura |
| `direccion_lugar` | `VARCHAR(500)` | Sí | — | — | direccion lugar | Ficha / captura |
| `es_matrimonios` | `TINYINT(1)` | Sí | — | — | es matrimonios | Ficha / captura |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Ficha / captura |

**FK:**
- `fk_gc_ficha_tipo` (`ficha_id`, `ficha_tipo`) → `ficha_captura` (`id`, `tipo_ficha`).

**Índices:**
- UNIQUE KEY `uq_gc_ficha` (`ficha_id`).

**CHECK:**
- `chk_gc_tipo`: ficha_tipo='GC'

## campo_critico — Parámetros de completitud

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `entidad` | `ENUM('PERSONA','GC')` | No | — | PK | entidad | Parámetro / catálogo |
| `campo_codigo` | `VARCHAR(80)` | No | — | PK | campo codigo | Parámetro / catálogo |
| `ruta_dato` | `VARCHAR(110)` | No | `1` | — | ruta dato | Parámetro / catálogo |
| `es_critico` | `TINYINT(1)` | No | `1` | — | Afecta reporte, no impide guardar incompletos | Parámetro / catálogo |
| `updated_by` | `BIGINT UNSIGNED` | Sí | — | FK | updated by | Parámetro / catálogo |

**FK:**
- `fk_cc_user` (`updated_by`) → `usuario` (`id`).

**Índices:**
- PRIMARY KEY `PRIMARY` (`entidad`, `campo_codigo`).

**CHECK:**
- `chk_cc_no_desactivar`: desactivable=1 OR es_critico=1

## auditoria_evento — Historial de modificaciones

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Identificador / relación |
| `request_id` | `VARCHAR(75)` | No | — | — | request id | Identificador / relación |
| `geo_sesion_id` | `BIGINT UNSIGNED` | No | — | FK | geo sesion id | Identificador / relación |
| `accion` | `VARCHAR(60)` | No | — | — | accion | Sistema / auditoría |
| `recurso_id` | `BIGINT UNSIGNED` | Sí | — | — | recurso id | Identificador / relación |
| `antes_json` | `JSON` | Sí | — | — | antes json | Sistema / auditoría |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Sistema / auditoría |

**FK:**
- `fk_au_session` (`sesion_id`) → `sesion_captura` (`id`).
- `fk_au_geo` (`geo_sesion_id`) → `geo_sesion` (`sesion_id`).
- `fk_au_user` (`usuario_id`) → `usuario` (`id`).

**Índices:**
- KEY `ix_au_recurso` (`recurso_tipo`, `recurso_id`, `created_at`).
- KEY `ix_au_usuario_fecha` (`usuario_id`, `created_at`).

## idempotencia — Protección contra reintentos

| Columna | Tipo físico | NULL | Por defecto | Clave | Descripción / regla | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Identificador / relación |
| `usuario_id` | `BIGINT UNSIGNED` | No | — | FK | usuario id | Identificador / relación |
| `payload_hash` | `CHAR(64)` | No | — | — | payload hash | Sistema / auditoría |
| `response_json` | `JSON` | No | — | — | response json | Sistema / auditoría |

**FK:**
- `fk_idem_user` (`usuario_id`) → `usuario` (`id`).

**Índices:**
- UNIQUE KEY `uq_idem_user_clave` (`usuario_id`, `clave`).
