# Anexo 01 — Diagrama físico de PCD

**Fuente única:** [SQL](../../../database/migrations/001_initial.sql); **17 tablas, 165 columnas, 28 FK**. BD temporal propia de PCD, no SGSP.

[Anexo 02: Diccionario físico](anexo-02-diccionario-fisico.md) · [Fuente Mermaid editable](../diagramas/er-pcd-fisico.mmd)

## Vista global
```mermaid
erDiagram
  PRESBITERIO {
    BIGINT_UNSIGNED id PK
  }
  USUARIO {
    BIGINT_UNSIGNED id PK
  }
  USUARIO_PRESBITERIO {
    BIGINT_UNSIGNED usuario_id PK,FK
    BIGINT_UNSIGNED presbiterio_id PK,FK
  }
  SEDE {
    BIGINT_UNSIGNED id PK
  }
  CAMPANA {
    BIGINT_UNSIGNED id PK
  }
  CATALOGO_VALOR {
    BIGINT_UNSIGNED id PK
  }
  SESION_CAPTURA {
    BIGINT_UNSIGNED id PK
  }
  GEO_SESION {
    BIGINT_UNSIGNED sesion_id PK,FK
  }
  LOGIN_EVENTO {
    BIGINT_UNSIGNED id PK
  }
  PERSONA_CAPTURA {
    BIGINT_UNSIGNED id PK
  }
  FICHA_CAPTURA {
    BIGINT_UNSIGNED id PK
  }
  CONYUGE_PENDIENTE {
    BIGINT_UNSIGNED id PK
  }
  PERSONA_RESPONSABILIDAD_CAPTURA {
    BIGINT_UNSIGNED id PK
  }
  GRUPO_CRECER_CAPTURA {
    BIGINT_UNSIGNED id PK
  }
  CAMPO_CRITICO {
    ENUM entidad PK
    VARCHAR_80 campo_codigo PK
  }
  AUDITORIA_EVENTO {
    BIGINT_UNSIGNED id PK
  }
  IDEMPOTENCIA {
    BIGINT_UNSIGNED id PK
  }
  USUARIO ||--o{ USUARIO_PRESBITERIO : fk_up_usuario
  PRESBITERIO ||--o{ USUARIO_PRESBITERIO : fk_up_presbiterio
  PRESBITERIO ||--o{ SEDE : fk_sede_presb
  USUARIO ||--o{ SEDE : fk_sede_autor
  USUARIO ||--o{ SESION_CAPTURA : fk_sc_user
  PRESBITERIO ||--o{ SESION_CAPTURA : fk_sc_pres
  SEDE ||--o{ SESION_CAPTURA : fk_sc_sede
  SESION_CAPTURA ||--o| GEO_SESION : fk_geo_session
  USUARIO ||--o{ LOGIN_EVENTO : fk_le_usuario
  PRESBITERIO ||--o{ LOGIN_EVENTO : fk_le_pres
  USUARIO ||--o{ PERSONA_CAPTURA : fk_pc_creador
  USUARIO ||--o{ PERSONA_CAPTURA : fk_pc_editor
  CAMPANA ||--o{ FICHA_CAPTURA : fk_fc_campana
  PERSONA_CAPTURA ||--o{ FICHA_CAPTURA : fk_fc_persona
  SEDE ||--o{ FICHA_CAPTURA : fk_fc_sede
  USUARIO ||--o{ FICHA_CAPTURA : fk_fc_autor
  USUARIO ||--o{ FICHA_CAPTURA : fk_fc_editor
  PERSONA_CAPTURA ||--o{ CONYUGE_PENDIENTE : fk_con_persona
  FICHA_CAPTURA ||--o{ CONYUGE_PENDIENTE : fk_con_ficha_persona
  PERSONA_CAPTURA ||--o{ PERSONA_RESPONSABILIDAD_CAPTURA : fk_pr_persona
  FICHA_CAPTURA ||--o{ PERSONA_RESPONSABILIDAD_CAPTURA : fk_pr_ficha_persona
  SEDE ||--o{ PERSONA_RESPONSABILIDAD_CAPTURA : fk_pr_sede
  FICHA_CAPTURA ||--o{ GRUPO_CRECER_CAPTURA : fk_gc_ficha_tipo
  USUARIO ||--o{ CAMPO_CRITICO : fk_cc_user
  SESION_CAPTURA ||--o{ AUDITORIA_EVENTO : fk_au_session
  GEO_SESION ||--o{ AUDITORIA_EVENTO : fk_au_geo
  USUARIO ||--o{ AUDITORIA_EVENTO : fk_au_user
  USUARIO ||--o{ IDEMPOTENCIA : fk_idem_user
```

## Diagrama con todos los campos y FK
```mermaid
erDiagram
  PRESBITERIO {
    BIGINT_UNSIGNED id PK
    VARCHAR_40 codigo UK
    VARCHAR_180 nombre
    TINYINT_1 activo
  }
  USUARIO {
    BIGINT_UNSIGNED id PK
    VARCHAR_80 codigo UK
    VARCHAR_180 nombre_display
    ENUM perfil
    ENUM alcance_consulta
    VARCHAR_255 password_hash
    TINYINT_1 activo
    INT_UNSIGNED auth_version
    DATETIME_6 created_at
    DATETIME_6 updated_at
  }
  USUARIO_PRESBITERIO {
    BIGINT_UNSIGNED usuario_id PK,FK
    BIGINT_UNSIGNED presbiterio_id PK,FK
  }
  SEDE {
    BIGINT_UNSIGNED id PK
    BIGINT_UNSIGNED presbiterio_id FK
    VARCHAR_50 codigo
    VARCHAR_200 nombre_oficial
    VARCHAR_200 nombre_norm
    VARCHAR_300 ubicacion_referencia
    TINYINT_1 activo
    BIGINT_UNSIGNED created_by FK
    DATETIME_6 created_at
  }
  CAMPANA {
    BIGINT_UNSIGNED id PK
    VARCHAR_50 codigo UK
    VARCHAR_180 nombre
    VARCHAR_30 estado
    DATETIME_6 created_at
  }
  CATALOGO_VALOR {
    BIGINT_UNSIGNED id PK
    VARCHAR_45 dominio
    VARCHAR_65 codigo
    VARCHAR_130 etiqueta
    SMALLINT_UNSIGNED orden
    TINYINT_1 activo
  }
  SESION_CAPTURA {
    BIGINT_UNSIGNED id PK
    BIGINT_UNSIGNED usuario_id FK
    BIGINT_UNSIGNED presbiterio_id FK
    BIGINT_UNSIGNED sede_id FK
    CHAR_64 session_ref_hash UK
    VARBINARY_16 ip_login
    VARCHAR_255 user_agent
    DATETIME_6 fecha_login
    DATETIME_6 fecha_logout
    DATETIME_6 last_seen
    DATETIME_6 revocada_en
    INT_UNSIGNED auth_version_al_ingreso
  }
  GEO_SESION {
    BIGINT_UNSIGNED sesion_id PK,FK
    ENUM estado
    DECIMAL_10_7 latitud
    DECIMAL_10_7 longitud
    DECIMAL_10_2 precision_m
    DATETIME_6 capturada_en
  }
  LOGIN_EVENTO {
    BIGINT_UNSIGNED id PK
    BIGINT_UNSIGNED usuario_id FK
    BIGINT_UNSIGNED presbiterio_id FK
    VARCHAR_80 codigo_intentado
    ENUM resultado
    VARBINARY_16 ip
    VARCHAR_255 user_agent
    DATETIME_6 created_at
  }
  PERSONA_CAPTURA {
    BIGINT_UNSIGNED id PK
    VARCHAR_24 tipo_documento
    VARCHAR_100 tipo_documento_detalle
    VARCHAR_60 numero_documento
    VARCHAR_60 numero_documento_norm
    VARCHAR_220 clave_identidad UK
    VARCHAR_150 nombres
    VARCHAR_100 apellido_paterno
    VARCHAR_100 apellido_materno
    DATE fecha_nacimiento
    VARCHAR_24 sexo_codigo
    VARCHAR_24 estado_civil_codigo
    VARCHAR_60 telefono
    VARCHAR_180 correo
    VARCHAR_180 facebook_usuario
    VARCHAR_500 direccion
    VARCHAR_10 ubigeo_dpto_codigo
    VARCHAR_10 ubigeo_prov_codigo
    VARCHAR_10 ubigeo_dist_codigo
    VARCHAR_40 condicion_codigo
    DATE fecha_condicion
    TINYINT_1 es_lider_gc_intencion
    BIGINT_UNSIGNED created_by FK
    BIGINT_UNSIGNED updated_by FK
    DATETIME_6 created_at
    DATETIME_6 updated_at
    INT_UNSIGNED version
  }
  FICHA_CAPTURA {
    BIGINT_UNSIGNED id PK
    BIGINT_UNSIGNED campana_id FK
    BIGINT_UNSIGNED persona_id FK
    BIGINT_UNSIGNED presbiterio_id FK
    BIGINT_UNSIGNED sede_id FK
    ENUM tipo_ficha
    VARCHAR_50 numero_ficha
    DATE fecha_papel
    VARCHAR_180 responsable_papel
    TEXT observaciones
    TEXT control_papel
    JSON datos_fuente_json
    TINYINT_1 posible_duplicado
    BIGINT_UNSIGNED created_by FK
    BIGINT_UNSIGNED updated_by FK
    DATETIME_6 created_at
    DATETIME_6 updated_at
    INT_UNSIGNED version
  }
  CONYUGE_PENDIENTE {
    BIGINT_UNSIGNED id PK
    BIGINT_UNSIGNED persona_id FK
    BIGINT_UNSIGNED ficha_id FK
    ENUM ficha_tipo FK
    VARCHAR_24 tipo_documento
    VARCHAR_60 numero_documento
    VARCHAR_240 nombre_libre
  }
  PERSONA_RESPONSABILIDAD_CAPTURA {
    BIGINT_UNSIGNED id PK
    BIGINT_UNSIGNED persona_id FK
    BIGINT_UNSIGNED ficha_id FK
    ENUM ficha_tipo FK
    VARCHAR_50 tipo_codigo
    BIGINT_UNSIGNED sede_id FK
    VARCHAR_180 region_libre
    VARCHAR_280 descripcion
    DATE fecha_desde
  }
  GRUPO_CRECER_CAPTURA {
    BIGINT_UNSIGNED id PK
    BIGINT_UNSIGNED ficha_id FK,UK
    ENUM ficha_tipo FK
    TINYINT_1 grupo_ya_existe
    VARCHAR_220 nombre
    VARCHAR_60 tipo_gc
    VARCHAR_45 estado_gc
    DATE fecha_inicio
    INT_UNSIGNED capacidad
    INT_UNSIGNED cantidad_integrantes_aprox
    VARCHAR_500 direccion_lugar
    VARCHAR_350 referencia
    TINYINT_1 es_matrimonios
    DATETIME_6 created_at
    DATETIME_6 updated_at
  }
  CAMPO_CRITICO {
    ENUM entidad PK
    VARCHAR_80 campo_codigo PK
    VARCHAR_150 etiqueta
    VARCHAR_110 ruta_dato
    TINYINT_1 desactivable
    TINYINT_1 es_critico
    BIGINT_UNSIGNED updated_by FK
    DATETIME_6 updated_at
  }
  AUDITORIA_EVENTO {
    BIGINT_UNSIGNED id PK
    VARCHAR_75 request_id
    BIGINT_UNSIGNED sesion_id FK
    BIGINT_UNSIGNED geo_sesion_id FK
    BIGINT_UNSIGNED usuario_id FK
    VARCHAR_60 accion
    VARCHAR_60 recurso_tipo
    BIGINT_UNSIGNED recurso_id
    VARBINARY_16 ip
    JSON antes_json
    JSON despues_json
    DATETIME_6 created_at
  }
  IDEMPOTENCIA {
    BIGINT_UNSIGNED id PK
    BIGINT_UNSIGNED usuario_id FK
    VARCHAR_110 clave
    CHAR_64 payload_hash
    SMALLINT_UNSIGNED response_status
    JSON response_json
    DATETIME_6 created_at
  }
  USUARIO ||--o{ USUARIO_PRESBITERIO : fk_up_usuario
  PRESBITERIO ||--o{ USUARIO_PRESBITERIO : fk_up_presbiterio
  PRESBITERIO ||--o{ SEDE : fk_sede_presb
  USUARIO ||--o{ SEDE : fk_sede_autor
  USUARIO ||--o{ SESION_CAPTURA : fk_sc_user
  PRESBITERIO ||--o{ SESION_CAPTURA : fk_sc_pres
  SEDE ||--o{ SESION_CAPTURA : fk_sc_sede
  SESION_CAPTURA ||--o| GEO_SESION : fk_geo_session
  USUARIO ||--o{ LOGIN_EVENTO : fk_le_usuario
  PRESBITERIO ||--o{ LOGIN_EVENTO : fk_le_pres
  USUARIO ||--o{ PERSONA_CAPTURA : fk_pc_creador
  USUARIO ||--o{ PERSONA_CAPTURA : fk_pc_editor
  CAMPANA ||--o{ FICHA_CAPTURA : fk_fc_campana
  PERSONA_CAPTURA ||--o{ FICHA_CAPTURA : fk_fc_persona
  SEDE ||--o{ FICHA_CAPTURA : fk_fc_sede
  USUARIO ||--o{ FICHA_CAPTURA : fk_fc_autor
  USUARIO ||--o{ FICHA_CAPTURA : fk_fc_editor
  PERSONA_CAPTURA ||--o{ CONYUGE_PENDIENTE : fk_con_persona
  FICHA_CAPTURA ||--o{ CONYUGE_PENDIENTE : fk_con_ficha_persona
  PERSONA_CAPTURA ||--o{ PERSONA_RESPONSABILIDAD_CAPTURA : fk_pr_persona
  FICHA_CAPTURA ||--o{ PERSONA_RESPONSABILIDAD_CAPTURA : fk_pr_ficha_persona
  SEDE ||--o{ PERSONA_RESPONSABILIDAD_CAPTURA : fk_pr_sede
  FICHA_CAPTURA ||--o{ GRUPO_CRECER_CAPTURA : fk_gc_ficha_tipo
  USUARIO ||--o{ CAMPO_CRITICO : fk_cc_user
  SESION_CAPTURA ||--o{ AUDITORIA_EVENTO : fk_au_session
  GEO_SESION ||--o{ AUDITORIA_EVENTO : fk_au_geo
  USUARIO ||--o{ AUDITORIA_EVENTO : fk_au_user
  USUARIO ||--o{ IDEMPOTENCIA : fk_idem_user
```

**Advertencia:** líder GC se obtiene de `ficha_captura.persona_id`, sin integrantes nominales. La lectura `GENERAL/PRESBITERIO` no altera derechos de modificación. Modelo sin ejecución MySQL real.
