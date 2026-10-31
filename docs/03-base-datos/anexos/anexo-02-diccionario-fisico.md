# Anexo 02 — Diccionario físico completo de PCD

**Origen:** [SQL inicial](../../../database/migrations/001_initial.sql). Generado recorriendo todas las definiciones de columna y constraint: **17 tablas, 165 columnas, 28 claves foráneas**. Es documentación del esquema **propuesto**, no evidencia de ejecución en MySQL.

[Diagrama físico](anexo-01-diagrama-fisico.md) · [Índice de datos](../README.md)

## presbiterio — Presbiterios autorizados

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Referencia interna |
| `codigo` | `VARCHAR(40)` | No | — | UK | codigo | Catálogo/administración |
| `nombre` | `VARCHAR(180)` | No | — | — | nombre | Catálogo/administración |
| `activo` | `TINYINT(1)` | No | `1` | — | activo | Catálogo/administración |

**Índices:**
- UNIQUE KEY `uq_presbiterio_codigo` (`codigo`).

## usuario — Usuarios, roles, ámbitos de consulta

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Referencia interna |
| `codigo` | `VARCHAR(80)` | No | — | UK | codigo | Catálogo/administración |
| `nombre_display` | `VARCHAR(180)` | No | — | — | nombre display | Catálogo/administración |
| `perfil` | `ENUM('DIGITADOR','ADMIN','REPORTES')` | No | — | — | perfil | Catálogo/administración |
| `alcance_consulta` | `ENUM('GENERAL','PRESBITERIO')` | No | `'PRESBITERIO'` | — | GENERAL o PRESBITERIO para consulta; nunca modifica escritura | Catálogo/administración |
| `password_hash` | `VARCHAR(255)` | No | — | — | password hash | Catálogo/administración |
| `activo` | `TINYINT(1)` | No | `1` | — | activo | Catálogo/administración |
| `auth_version` | `INT UNSIGNED` | No | `1` | — | auth version | Catálogo/administración |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Catálogo/administración |
| `updated_at` | `DATETIME(6)` | Sí | — | — | updated at | Catálogo/administración |

**Índices:**
- UNIQUE KEY `uq_usuario_codigo` (`codigo`).

## usuario_presbiterio — Asignación territorial

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `usuario_id` | `BIGINT UNSIGNED` | No | — | PK,FK | usuario id | Referencia interna |
| `presbiterio_id` | `BIGINT UNSIGNED` | No | — | PK,FK | presbiterio id | Referencia interna |

**FK:**
- `fk_up_usuario`: (`usuario_id`) → `usuario` (`id`).
- `fk_up_presbiterio`: (`presbiterio_id`) → `presbiterio` (`id`).

**Índices:**
- PRIMARY KEY `PRIMARY` (`usuario_id`, `presbiterio_id`).

## sede — Sedes

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Referencia interna |
| `presbiterio_id` | `BIGINT UNSIGNED` | No | — | FK | presbiterio id | Referencia interna |
| `codigo` | `VARCHAR(50)` | Sí | — | — | codigo | Catálogo/administración |
| `nombre_oficial` | `VARCHAR(200)` | No | — | — | nombre oficial | Catálogo/administración |
| `nombre_norm` | `VARCHAR(200)` | No | — | — | nombre norm | Catálogo/administración |
| `ubicacion_referencia` | `VARCHAR(300)` | Sí | — | — | ubicacion referencia | Catálogo/administración |
| `activo` | `TINYINT(1)` | No | `1` | — | activo | Catálogo/administración |
| `created_by` | `BIGINT UNSIGNED` | Sí | — | FK | created by | Catálogo/administración |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Catálogo/administración |

**FK:**
- `fk_sede_presb`: (`presbiterio_id`) → `presbiterio` (`id`).
- `fk_sede_autor`: (`created_by`) → `usuario` (`id`).

**Índices:**
- UNIQUE KEY `uq_sede_presbiterio_nombre` (`presbiterio_id`, `nombre_norm`).
- UNIQUE KEY `uq_sede_ambito` (`presbiterio_id`, `id`).
- KEY `ix_sede_presbiterio_activo` (`presbiterio_id`, `activo`).

## campana — Campaña de digitación

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Referencia interna |
| `codigo` | `VARCHAR(50)` | No | — | UK | codigo | Catálogo/administración |
| `nombre` | `VARCHAR(180)` | No | — | — | nombre | Catálogo/administración |
| `estado` | `VARCHAR(30)` | No | `'ACTIVA'` | — | estado | Catálogo/administración |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Catálogo/administración |

**Índices:**
- UNIQUE KEY `uq_campana_codigo` (`codigo`).

## catalogo_valor — Catálogos verificados

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Referencia interna |
| `dominio` | `VARCHAR(45)` | No | — | — | dominio | Catálogo/administración |
| `codigo` | `VARCHAR(65)` | No | — | — | codigo | Catálogo/administración |
| `etiqueta` | `VARCHAR(130)` | No | — | — | etiqueta | Catálogo/administración |
| `orden` | `SMALLINT UNSIGNED` | No | `0` | — | orden | Catálogo/administración |
| `activo` | `TINYINT(1)` | No | `1` | — | activo | Catálogo/administración |

**Índices:**
- UNIQUE KEY `uq_catalogo_dominio_codigo` (`dominio`, `codigo`).

## sesion_captura — Sesión de autenticación

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Referencia interna |
| `usuario_id` | `BIGINT UNSIGNED` | No | — | FK | usuario id | Referencia interna |
| `presbiterio_id` | `BIGINT UNSIGNED` | No | — | FK | presbiterio id | Referencia interna |
| `sede_id` | `BIGINT UNSIGNED` | Sí | — | FK | sede id | Referencia interna |
| `session_ref_hash` | `CHAR(64)` | No | — | UK | session ref hash | Sistema |
| `ip_login` | `VARBINARY(16)` | Sí | — | — | ip login | Sistema |
| `user_agent` | `VARCHAR(255)` | Sí | — | — | user agent | Sistema |
| `fecha_login` | `DATETIME(6)` | No | — | — | fecha login | Sistema |
| `fecha_logout` | `DATETIME(6)` | Sí | — | — | fecha logout | Sistema |
| `last_seen` | `DATETIME(6)` | Sí | — | — | last seen | Sistema |
| `revocada_en` | `DATETIME(6)` | Sí | — | — | revocada en | Sistema |
| `auth_version_al_ingreso` | `INT UNSIGNED` | No | — | — | auth version al ingreso | Sistema |

**FK:**
- `fk_sc_user`: (`usuario_id`) → `usuario` (`id`).
- `fk_sc_pres`: (`presbiterio_id`) → `presbiterio` (`id`).
- `fk_sc_sede`: (`presbiterio_id`, `sede_id`) → `sede` (`presbiterio_id`, `id`).

**Índices:**
- UNIQUE KEY `uq_sesion_ref` (`session_ref_hash`).
- KEY `ix_sesion_usuario_fecha` (`usuario_id`, `fecha_login`).

## geo_sesion — GPS por sesión

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `sesion_id` | `BIGINT UNSIGNED` | No | — | PK,FK | Una localización opcional por sesión | Referencia interna |
| `estado` | `ENUM('OK','DENEGADA','NO_DISPONIBLE','ERROR')` | No | — | — | estado | Catálogo/administración |
| `latitud` | `DECIMAL(10,7)` | Sí | — | — | latitud | Catálogo/administración |
| `longitud` | `DECIMAL(10,7)` | Sí | — | — | longitud | Catálogo/administración |
| `precision_m` | `DECIMAL(10,2)` | Sí | — | — | precision m | Catálogo/administración |
| `capturada_en` | `DATETIME(6)` | No | — | — | capturada en | Catálogo/administración |

**FK:**
- `fk_geo_session`: (`sesion_id`) → `sesion_captura` (`id`).

## login_evento — Accesos y bloqueos

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Referencia interna |
| `usuario_id` | `BIGINT UNSIGNED` | Sí | — | FK | usuario id | Referencia interna |
| `presbiterio_id` | `BIGINT UNSIGNED` | Sí | — | FK | presbiterio id | Referencia interna |
| `codigo_intentado` | `VARCHAR(80)` | Sí | — | — | codigo intentado | Sistema |
| `resultado` | `ENUM('OK','FALLIDO','BLOQUEADO','LOGOUT')` | No | — | — | resultado | Sistema |
| `ip` | `VARBINARY(16)` | Sí | — | — | ip | Sistema |
| `user_agent` | `VARCHAR(255)` | Sí | — | — | user agent | Sistema |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Sistema |

**FK:**
- `fk_le_usuario`: (`usuario_id`) → `usuario` (`id`).
- `fk_le_pres`: (`presbiterio_id`) → `presbiterio` (`id`).

**Índices:**
- KEY `ix_login_fecha` (`created_at`).
- KEY `ix_login_codigo_fecha` (`codigo_intentado`, `created_at`).
- KEY `ix_login_ip_fecha` (`ip`, `created_at`).

## persona_captura — Persona consolidada

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Referencia interna |
| `tipo_documento` | `VARCHAR(24)` | Sí | — | — | tipo documento | Ficha impresa/digitación |
| `tipo_documento_detalle` | `VARCHAR(100)` | Sí | — | — | Subtipo de documento OTRO | Ficha impresa/digitación |
| `numero_documento` | `VARCHAR(60)` | Sí | — | — | numero documento | Ficha impresa/digitación |
| `numero_documento_norm` | `VARCHAR(60)` | Sí | — | — | numero documento norm | Ficha impresa/digitación |
| `clave_identidad` | `VARCHAR(220)` | Sí | — | UK | Clave documental normalizada única cuando existe; admite NULL | Ficha impresa/digitación |
| `nombres` | `VARCHAR(150)` | Sí | — | — | nombres | Ficha impresa/digitación |
| `apellido_paterno` | `VARCHAR(100)` | Sí | — | — | apellido paterno | Ficha impresa/digitación |
| `apellido_materno` | `VARCHAR(100)` | Sí | — | — | apellido materno | Ficha impresa/digitación |
| `fecha_nacimiento` | `DATE` | Sí | — | — | fecha nacimiento | Ficha impresa/digitación |
| `sexo_codigo` | `VARCHAR(24)` | Sí | — | — | sexo codigo | Ficha impresa/digitación |
| `estado_civil_codigo` | `VARCHAR(24)` | Sí | — | — | estado civil codigo | Ficha impresa/digitación |
| `telefono` | `VARCHAR(60)` | Sí | — | — | telefono | Ficha impresa/digitación |
| `correo` | `VARCHAR(180)` | Sí | — | — | correo | Ficha impresa/digitación |
| `facebook_usuario` | `VARCHAR(180)` | Sí | — | — | facebook usuario | Ficha impresa/digitación |
| `direccion` | `VARCHAR(500)` | Sí | — | — | direccion | Ficha impresa/digitación |
| `ubigeo_dpto_codigo` | `VARCHAR(10)` | Sí | — | — | ubigeo dpto codigo | Ficha impresa/digitación |
| `ubigeo_prov_codigo` | `VARCHAR(10)` | Sí | — | — | ubigeo prov codigo | Ficha impresa/digitación |
| `ubigeo_dist_codigo` | `VARCHAR(10)` | Sí | — | — | ubigeo dist codigo | Ficha impresa/digitación |
| `condicion_codigo` | `VARCHAR(40)` | Sí | — | — | condicion codigo | Ficha impresa/digitación |
| `fecha_condicion` | `DATE` | Sí | — | — | fecha condicion | Ficha impresa/digitación |
| `es_lider_gc_intencion` | `TINYINT(1)` | No | `0` | — | es lider gc intencion | Ficha impresa/digitación |
| `created_by` | `BIGINT UNSIGNED` | No | — | FK | created by | Ficha impresa/digitación |
| `updated_by` | `BIGINT UNSIGNED` | Sí | — | FK | updated by | Ficha impresa/digitación |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Ficha impresa/digitación |
| `updated_at` | `DATETIME(6)` | Sí | — | — | updated at | Ficha impresa/digitación |
| `version` | `INT UNSIGNED` | No | `1` | — | version | Ficha impresa/digitación |

**FK:**
- `fk_pc_creador`: (`created_by`) → `usuario` (`id`).
- `fk_pc_editor`: (`updated_by`) → `usuario` (`id`).

**Índices:**
- UNIQUE KEY `uq_persona_clave_identidad` (`clave_identidad`).
- KEY `ix_persona_doc_candidato` (`tipo_documento`, `numero_documento_norm`).
- KEY `ix_persona_apellidos` (`apellido_paterno`, `apellido_materno`, `nombres`).
- KEY `ix_persona_nombres` (`nombres`).

## ficha_captura — Ficha fuente

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Referencia interna |
| `campana_id` | `BIGINT UNSIGNED` | No | — | FK | campana id | Referencia interna |
| `persona_id` | `BIGINT UNSIGNED` | No | — | FK | persona id | Referencia interna |
| `presbiterio_id` | `BIGINT UNSIGNED` | No | — | FK | presbiterio id | Referencia interna |
| `sede_id` | `BIGINT UNSIGNED` | No | — | FK | sede id | Referencia interna |
| `tipo_ficha` | `ENUM('PERSONA','GC')` | No | — | — | tipo ficha | Ficha impresa/digitación |
| `numero_ficha` | `VARCHAR(50)` | Sí | — | — | Identificador del papel opcional y repetible | Ficha impresa/digitación |
| `fecha_papel` | `DATE` | Sí | — | — | fecha papel | Ficha impresa/digitación |
| `responsable_papel` | `VARCHAR(180)` | Sí | — | — | responsable papel | Ficha impresa/digitación |
| `observaciones` | `TEXT` | Sí | — | — | observaciones | Ficha impresa/digitación |
| `control_papel` | `TEXT` | Sí | — | — | control papel | Ficha impresa/digitación |
| `datos_fuente_json` | `JSON` | Sí | — | — | datos fuente json | Ficha impresa/digitación |
| `posible_duplicado` | `TINYINT(1)` | No | `0` | — | posible duplicado | Ficha impresa/digitación |
| `created_by` | `BIGINT UNSIGNED` | No | — | FK | created by | Ficha impresa/digitación |
| `updated_by` | `BIGINT UNSIGNED` | Sí | — | FK | updated by | Ficha impresa/digitación |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Ficha impresa/digitación |
| `updated_at` | `DATETIME(6)` | Sí | — | — | updated at | Ficha impresa/digitación |
| `version` | `INT UNSIGNED` | No | `1` | — | version | Ficha impresa/digitación |

**FK:**
- `fk_fc_campana`: (`campana_id`) → `campana` (`id`).
- `fk_fc_persona`: (`persona_id`) → `persona_captura` (`id`).
- `fk_fc_sede`: (`presbiterio_id`, `sede_id`) → `sede` (`presbiterio_id`, `id`).
- `fk_fc_autor`: (`created_by`) → `usuario` (`id`).
- `fk_fc_editor`: (`updated_by`) → `usuario` (`id`).

**Índices:**
- KEY `ix_ficha_sede_fecha` (`sede_id`, `created_at`).
- KEY `ix_ficha_autor_fecha` (`created_by`, `created_at`).
- KEY `ix_ficha_pres_tipo` (`presbiterio_id`, `tipo_ficha`, `created_at`).
- KEY `ix_ficha_persona` (`persona_id`).
- UNIQUE KEY `uq_ficha_id_tipo` (`id`, `tipo_ficha`).
- UNIQUE KEY `uq_ficha_id_persona_tipo` (`id`, `persona_id`, `tipo_ficha`).

## conyuge_pendiente — Cónyuge en ficha

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Referencia interna |
| `persona_id` | `BIGINT UNSIGNED` | No | — | FK | persona id | Referencia interna |
| `ficha_id` | `BIGINT UNSIGNED` | No | — | FK | ficha id | Referencia interna |
| `ficha_tipo` | `ENUM('PERSONA','GC')` | No | `'PERSONA'` | FK | ficha tipo | Ficha impresa/digitación |
| `tipo_documento` | `VARCHAR(24)` | Sí | — | — | tipo documento | Ficha impresa/digitación |
| `numero_documento` | `VARCHAR(60)` | Sí | — | — | numero documento | Ficha impresa/digitación |
| `nombre_libre` | `VARCHAR(240)` | Sí | — | — | nombre libre | Ficha impresa/digitación |

**FK:**
- `fk_con_persona`: (`persona_id`) → `persona_captura` (`id`).
- `fk_con_ficha_persona`: (`ficha_id`, `persona_id`, `ficha_tipo`) → `ficha_captura` (`id`, `persona_id`, `tipo_ficha`).

**CHECK:**
- `chk_con_tipo`: ficha_tipo='PERSONA'

## persona_responsabilidad_captura — Responsabilidades en ficha

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Referencia interna |
| `persona_id` | `BIGINT UNSIGNED` | No | — | FK | persona id | Referencia interna |
| `ficha_id` | `BIGINT UNSIGNED` | No | — | FK | ficha id | Referencia interna |
| `ficha_tipo` | `ENUM('PERSONA','GC')` | No | `'PERSONA'` | FK | ficha tipo | Ficha impresa/digitación |
| `tipo_codigo` | `VARCHAR(50)` | No | — | — | tipo codigo | Ficha impresa/digitación |
| `sede_id` | `BIGINT UNSIGNED` | Sí | — | FK | sede id | Referencia interna |
| `region_libre` | `VARCHAR(180)` | Sí | — | — | region libre | Ficha impresa/digitación |
| `descripcion` | `VARCHAR(280)` | Sí | — | — | descripcion | Ficha impresa/digitación |
| `fecha_desde` | `DATE` | Sí | — | — | fecha desde | Ficha impresa/digitación |

**FK:**
- `fk_pr_persona`: (`persona_id`) → `persona_captura` (`id`).
- `fk_pr_ficha_persona`: (`ficha_id`, `persona_id`, `ficha_tipo`) → `ficha_captura` (`id`, `persona_id`, `tipo_ficha`).
- `fk_pr_sede`: (`sede_id`) → `sede` (`id`).

**Índices:**
- KEY `ix_resp_persona` (`persona_id`).

**CHECK:**
- `chk_pr_tipo`: ficha_tipo='PERSONA'

## grupo_crecer_captura — Grupo Crecer sin miembros nominales

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Referencia interna |
| `ficha_id` | `BIGINT UNSIGNED` | No | — | FK,UK | Referencia a ficha GC; líder derivado de ficha.persona_id | Referencia interna |
| `ficha_tipo` | `ENUM('PERSONA','GC')` | No | `'GC'` | FK | ficha tipo | Ficha impresa/digitación |
| `grupo_ya_existe` | `TINYINT(1)` | Sí | — | — | grupo ya existe | Ficha impresa/digitación |
| `nombre` | `VARCHAR(220)` | Sí | — | — | nombre | Ficha impresa/digitación |
| `tipo_gc` | `VARCHAR(60)` | Sí | — | — | tipo gc | Ficha impresa/digitación |
| `estado_gc` | `VARCHAR(45)` | Sí | — | — | estado gc | Ficha impresa/digitación |
| `fecha_inicio` | `DATE` | Sí | — | — | fecha inicio | Ficha impresa/digitación |
| `capacidad` | `INT UNSIGNED` | Sí | — | — | capacidad | Ficha impresa/digitación |
| `cantidad_integrantes_aprox` | `INT UNSIGNED` | Sí | — | — | Resumen numérico, sin miembros nominales | Ficha impresa/digitación |
| `direccion_lugar` | `VARCHAR(500)` | Sí | — | — | direccion lugar | Ficha impresa/digitación |
| `referencia` | `VARCHAR(350)` | Sí | — | — | referencia | Ficha impresa/digitación |
| `es_matrimonios` | `TINYINT(1)` | Sí | — | — | es matrimonios | Ficha impresa/digitación |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Ficha impresa/digitación |
| `updated_at` | `DATETIME(6)` | Sí | — | — | updated at | Ficha impresa/digitación |

**FK:**
- `fk_gc_ficha_tipo`: (`ficha_id`, `ficha_tipo`) → `ficha_captura` (`id`, `tipo_ficha`).

**Índices:**
- UNIQUE KEY `uq_gc_ficha` (`ficha_id`).

**CHECK:**
- `chk_gc_tipo`: ficha_tipo='GC'

## campo_critico — Campos críticos

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `entidad` | `ENUM('PERSONA','GC')` | No | — | PK | entidad | Catálogo/administración |
| `campo_codigo` | `VARCHAR(80)` | No | — | PK | campo codigo | Catálogo/administración |
| `etiqueta` | `VARCHAR(150)` | No | — | — | etiqueta | Catálogo/administración |
| `ruta_dato` | `VARCHAR(110)` | No | — | — | ruta dato | Catálogo/administración |
| `desactivable` | `TINYINT(1)` | No | `1` | — | desactivable | Catálogo/administración |
| `es_critico` | `TINYINT(1)` | No | `1` | — | Parámetro de reporte, no bloqueo de captura | Catálogo/administración |
| `updated_by` | `BIGINT UNSIGNED` | Sí | — | FK | updated by | Catálogo/administración |
| `updated_at` | `DATETIME(6)` | Sí | — | — | updated at | Catálogo/administración |

**FK:**
- `fk_cc_user`: (`updated_by`) → `usuario` (`id`).

**Índices:**
- PRIMARY KEY `PRIMARY` (`entidad`, `campo_codigo`).

**CHECK:**
- `chk_cc_no_desactivar`: desactivable=1 OR es_critico=1

## auditoria_evento — Auditoría de cambios

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Referencia interna |
| `request_id` | `VARCHAR(75)` | No | — | — | request id | Referencia interna |
| `sesion_id` | `BIGINT UNSIGNED` | Sí | — | FK | sesion id | Referencia interna |
| `geo_sesion_id` | `BIGINT UNSIGNED` | Sí | — | FK | geo sesion id | Referencia interna |
| `usuario_id` | `BIGINT UNSIGNED` | No | — | FK | usuario id | Referencia interna |
| `accion` | `VARCHAR(60)` | No | — | — | accion | Sistema |
| `recurso_tipo` | `VARCHAR(60)` | No | — | — | recurso tipo | Sistema |
| `recurso_id` | `BIGINT UNSIGNED` | Sí | — | — | recurso id | Referencia interna |
| `ip` | `VARBINARY(16)` | Sí | — | — | ip | Sistema |
| `antes_json` | `JSON` | Sí | — | — | antes json | Sistema |
| `despues_json` | `JSON` | Sí | — | — | despues json | Sistema |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Sistema |

**FK:**
- `fk_au_session`: (`sesion_id`) → `sesion_captura` (`id`).
- `fk_au_geo`: (`geo_sesion_id`) → `geo_sesion` (`sesion_id`).
- `fk_au_user`: (`usuario_id`) → `usuario` (`id`).

**Índices:**
- KEY `ix_au_recurso` (`recurso_tipo`, `recurso_id`, `created_at`).
- KEY `ix_au_usuario_fecha` (`usuario_id`, `created_at`).

## idempotencia — Prevención de reintentos

| Columna | Tipo SQL | Nulo | Valor por defecto | PK/FK/UK | Significado y validación | Procedencia |
|---|---|:---:|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | PK | id | Referencia interna |
| `usuario_id` | `BIGINT UNSIGNED` | No | — | FK | usuario id | Referencia interna |
| `clave` | `VARCHAR(110)` | No | — | — | clave | Sistema |
| `payload_hash` | `CHAR(64)` | No | — | — | payload hash | Sistema |
| `response_status` | `SMALLINT UNSIGNED` | Sí | — | — | response status | Sistema |
| `response_json` | `JSON` | Sí | — | — | response json | Sistema |
| `created_at` | `DATETIME(6)` | No | — | — | created at | Sistema |

**FK:**
- `fk_idem_user`: (`usuario_id`) → `usuario` (`id`).

**Índices:**
- UNIQUE KEY `uq_idem_user_clave` (`usuario_id`, `clave`).

