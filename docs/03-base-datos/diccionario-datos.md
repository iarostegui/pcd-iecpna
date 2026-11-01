# Diccionario de datos — resumen ejecutivo PCD V4.0

**Diccionario físico exhaustivo vigente:** [Anexo 02](anexos/anexo-02-diccionario-fisico.md), generado desde [SQL de 11 tablas](../../database/migrations/001_initial.sql). Este archivo es un **resumen** y no sustituye las 134 definiciones de columna.

| Tabla | Área | Atributos más relevantes |
|---|---|---|
| organizacion | Organización | id, tipo PRESBITERIO/SEDE, padre_id, codigo, nombre, nombre_norm, activo |
| usuario | Seguridad | id, codigo, perfil, presbiterio_id, password_hash, auth_version |
| sesion | Seguridad | id, usuario_id, presbiterio_id, sede_id, session_ref_hash, geo_estado, geo_latitud/longitud, last_seen, revocada_en |
| login_evento | Seguridad | id, codigo_intentado, resultado, ip, created_at |
| catalogo | Configuración | id, categoria, codigo, etiqueta, config_json; opciones y campos críticos |
| ubigeo | Configuración | codigo, tipo, padre_codigo, nombre, activo; INEI durante I02 |
| persona | Registro | id, tipo_documento, numero_documento, numero_documento_norm, clave_identidad, nombres, dirección, fechas y ubigeo |
| ficha | Registro | id, persona_id, presbiterio_id, sede_id, tipo_ficha, numero_ficha, datos_fuente_json, created_by, version |
| grupo_crecer | Registro | id, ficha_id, ficha_tipo GC, nombre, tipo_gc, cantidad_integrantes_aprox |
| auditoria_evento | Control | id, usuario_id, sesion_id, accion, antes_json, despues_json, created_at |
| idempotencia | Control | id, usuario_id, clave, payload_hash, response_json |

**Fuente del papel:** [diccionario de captura](../01-analisis/diccionario-captura.md) e [inventario Excel](../01-analisis/fuentes/inventario-excel-original.md). Cónyuge, responsabilidades y controles del papel van en `ficha.datos_fuente_json`, validado en PHP; no son entidades con ciclo de vida propio en PCD.

**Estado:** documento y SQL diseñados, no validados con instalación MySQL.
