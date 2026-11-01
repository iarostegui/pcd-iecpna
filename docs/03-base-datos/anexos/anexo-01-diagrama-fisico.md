# Anexo 01 — Diagrama físico PCD V4.0 (11 tablas)

**Fuente de verdad:** [migración inicial SQL](../../../database/migrations/001_initial.sql). **11 tablas, 134 columnas, 23 FK**. Producto temporal PCD (no SGSP).

[Diccionario completo](anexo-02-diccionario-fisico.md) · [Fuente editable Mermaid](../diagramas/er-pcd-fisico.mmd)

## Mapa de entidades

```mermaid
erDiagram
  ORGANIZACION {
    BIGINT_UNSIGNED id PK
  }
  USUARIO {
    BIGINT_UNSIGNED id PK
  }
  SESION {
    BIGINT_UNSIGNED id PK
  }
  LOGIN_EVENTO {
    BIGINT_UNSIGNED id PK
  }
  CATALOGO {
    BIGINT_UNSIGNED id PK
  }
  UBIGEO {
    VARCHAR_6 codigo PK
  }
  PERSONA {
    BIGINT_UNSIGNED id PK
  }
  FICHA {
    BIGINT_UNSIGNED id PK
  }
  GRUPO_CRECER {
    BIGINT_UNSIGNED id PK
  }
  AUDITORIA_EVENTO {
    BIGINT_UNSIGNED id PK
  }
  IDEMPOTENCIA {
    BIGINT_UNSIGNED id PK
  }
  ORGANIZACION ||--o{ ORGANIZACION : fk_org_padre
  ORGANIZACION ||--o{ USUARIO : fk_usuario_presb
  USUARIO ||--o{ SESION : fk_sesion_usuario
  ORGANIZACION ||--o{ SESION : fk_sesion_presb
  ORGANIZACION ||--o{ SESION : fk_sesion_sede
  USUARIO ||--o{ LOGIN_EVENTO : fk_login_usuario
  ORGANIZACION ||--o{ LOGIN_EVENTO : fk_login_presb
  USUARIO ||--o{ CATALOGO : fk_catalogo_editor
  UBIGEO ||--o{ UBIGEO : fk_ubigeo_padre
  USUARIO ||--o{ PERSONA : fk_persona_creador
  USUARIO ||--o{ PERSONA : fk_persona_editor
  UBIGEO ||--o{ PERSONA : fk_persona_dpto
  UBIGEO ||--o{ PERSONA : fk_persona_prov
  UBIGEO ||--o{ PERSONA : fk_persona_dist
  PERSONA ||--o{ FICHA : fk_ficha_persona
  ORGANIZACION ||--o{ FICHA : fk_ficha_presb
  ORGANIZACION ||--o{ FICHA : fk_ficha_sede
  USUARIO ||--o{ FICHA : fk_ficha_creador
  USUARIO ||--o{ FICHA : fk_ficha_editor
  FICHA ||--o{ GRUPO_CRECER : fk_gc_ficha_tipo
  SESION ||--o{ AUDITORIA_EVENTO : fk_audit_sesion
  USUARIO ||--o{ AUDITORIA_EVENTO : fk_audit_usuario
  USUARIO ||--o{ IDEMPOTENCIA : fk_idem_usuario
```

## Diagrama detallado de atributos y relaciones

```mermaid
erDiagram
  ORGANIZACION {
    BIGINT_UNSIGNED id PK
    ENUM tipo
    BIGINT_UNSIGNED padre_id FK
    VARCHAR_50 codigo UK
    VARCHAR_200 nombre
    VARCHAR_200 nombre_norm
    VARCHAR_300 referencia
    TINYINT_1 activo
    DATETIME_6 created_at
  }
  USUARIO {
    BIGINT_UNSIGNED id PK
    VARCHAR_80 codigo UK
    VARCHAR_180 nombre_display
    ENUM perfil
    BIGINT_UNSIGNED presbiterio_id FK
    VARCHAR_255 password_hash
    TINYINT_1 activo
    INT_UNSIGNED auth_version
    DATETIME_6 created_at
    DATETIME_6 updated_at
  }
  SESION {
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
    ENUM geo_estado
    DECIMAL_10_7 geo_latitud
    DECIMAL_10_7 geo_longitud
    DECIMAL_10_2 geo_precision_m
    DATETIME_6 geo_capturada_en
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
  CATALOGO {
    BIGINT_UNSIGNED id PK
    VARCHAR_70 categoria
    VARCHAR_85 codigo
    VARCHAR_160 etiqueta
    SMALLINT_UNSIGNED orden
    TINYINT_1 activo
    JSON config_json
    BIGINT_UNSIGNED updated_by FK
    DATETIME_6 updated_at
  }
  UBIGEO {
    VARCHAR_6 codigo PK
    ENUM tipo
    VARCHAR_6 padre_codigo FK
    VARCHAR_160 nombre
    TINYINT_1 activo
  }
  PERSONA {
    BIGINT_UNSIGNED id PK
    VARCHAR_24 tipo_documento
    VARCHAR_60 numero_documento
    VARCHAR_60 numero_documento_norm
    VARCHAR_120 clave_identidad UK
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
    VARCHAR_6 ubigeo_dpto_codigo FK
    VARCHAR_6 ubigeo_prov_codigo FK
    VARCHAR_6 ubigeo_dist_codigo FK
    VARCHAR_40 condicion_codigo
    DATE fecha_condicion
    TINYINT_1 es_lider_gc_intencion
    BIGINT_UNSIGNED created_by FK
    BIGINT_UNSIGNED updated_by FK
    DATETIME_6 created_at
    DATETIME_6 updated_at
    INT_UNSIGNED version
  }
  FICHA {
    BIGINT_UNSIGNED id PK
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
  GRUPO_CRECER {
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
  AUDITORIA_EVENTO {
    BIGINT_UNSIGNED id PK
    VARCHAR_75 request_id
    BIGINT_UNSIGNED sesion_id FK
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
  ORGANIZACION ||--o{ ORGANIZACION : fk_org_padre
  ORGANIZACION ||--o{ USUARIO : fk_usuario_presb
  USUARIO ||--o{ SESION : fk_sesion_usuario
  ORGANIZACION ||--o{ SESION : fk_sesion_presb
  ORGANIZACION ||--o{ SESION : fk_sesion_sede
  USUARIO ||--o{ LOGIN_EVENTO : fk_login_usuario
  ORGANIZACION ||--o{ LOGIN_EVENTO : fk_login_presb
  USUARIO ||--o{ CATALOGO : fk_catalogo_editor
  UBIGEO ||--o{ UBIGEO : fk_ubigeo_padre
  USUARIO ||--o{ PERSONA : fk_persona_creador
  USUARIO ||--o{ PERSONA : fk_persona_editor
  UBIGEO ||--o{ PERSONA : fk_persona_dpto
  UBIGEO ||--o{ PERSONA : fk_persona_prov
  UBIGEO ||--o{ PERSONA : fk_persona_dist
  PERSONA ||--o{ FICHA : fk_ficha_persona
  ORGANIZACION ||--o{ FICHA : fk_ficha_presb
  ORGANIZACION ||--o{ FICHA : fk_ficha_sede
  USUARIO ||--o{ FICHA : fk_ficha_creador
  USUARIO ||--o{ FICHA : fk_ficha_editor
  FICHA ||--o{ GRUPO_CRECER : fk_gc_ficha_tipo
  SESION ||--o{ AUDITORIA_EVENTO : fk_audit_sesion
  USUARIO ||--o{ AUDITORIA_EVENTO : fk_audit_usuario
  USUARIO ||--o{ IDEMPOTENCIA : fk_idem_usuario
```

**Notas:** ORGANIZACION combina presbiterio/sede con jerarquía. CATALOGO combina listas y configuración de campos críticos. UBIGEO es una tabla jerárquica específica. FICHA guarda secciones de cónyuge/responsabilidades como JSON; PERSONA mantiene identidad separada. SESION integra geolocalización opcional. El líder GC se obtiene desde FICHA.persona_id. No hay subtipo documental ni tabla de ámbito de consulta.

**Pendiente:** probar CHECK y FK compuestas en el motor de hosting y regenerar anexos después de modificar el SQL.
