# Modelo lógico PCD V3.1 — revisión E2 (propuesta de implementación)
**No es el modelo del SGSP.** [Alcance](../01-analisis/alcance-funcional.md) · [Documento E2](../07-auditorias/etapa-2-arquitectura-seguridad-datos.md) · [SQL propuesto](../../database/migrations/001_initial.sql).

| Tabla | Claves / relaciones esenciales | Regla relevante |
|---|---|---|
| presbiterio | id, codigo único | Activo y disponible en login público (solo id/código/nombre). |
| usuario | id, codigo único | perfil DIGITADOR/ADMIN/REPORTES, hash, auth_version para revocar. |
| usuario_presbiterio | (usuario_id,presbiterio_id) | Solo ámbito explícito de DIGITADOR; **nacional por perfil** para ADMIN/REPORTES **E2-D02 pendiente**. |
| sede | id, presbiterio_id, nombre_norm | UNIQUE presbiterio+nombre normalizado. |
| catalogo_valor | id, (dominio,codigo) único | Opciones confirmadas del papel y etiquetas versionadas; ubigeo sin fuente oficial aparte. |
| campana | id, codigo | Identifica campaña temporal; no módulos de multi-campañas. |
| sesion_captura | id, usuario_id, presbiterio_id, sede_id | session_ref_hash único; auth_version_al_ingreso, revocada_en, last_seen/fecha_login/fecha_logout. |
| login_evento | id | Log de éxito/fallo/bloqueo/logout; índice código/fecha e IP/fecha. |
| geo_sesion | sesion_id PK y FK | UNA captura/estado por sesión; reusar. |
| persona_captura | id; clave_identidad UNIQUE NULL-permitida | tipo/subtipo/documento normalizados, autor original, campos consolidado y versión. |
| ficha_captura | id, campana_id, persona_id, presbiterio_id, sede_id, tipo_ficha | snapshot por ficha; autor inmutable, control edición y trazabilidad; calidad se calcula, no marca_calidad fija. |
| conyuge_pendiente | id, ficha_id + persona_id + ficha_tipo | Solo ficha PERSONA; triple FK compuesta y CHECK de tipo. |
| persona_responsabilidad_captura | id, ficha_id + persona_id + ficha_tipo | Solo ficha PERSONA, sin rol de seguridad. |
| grupo_crecer_captura | id, ficha_id, ficha_tipo='GC' | **Líder = ficha_captura.persona_id**. Sin lider_persona_id redundante. Tipo GC protegido. |
| campo_critico | (entidad,campo_codigo) | etiqueta/ruta_dato/desactivable/es_critico; no desactivar relaciones estructurales. |
| auditoria_evento | id, sesión/usuario/geo/recurso | Antes/después para operaciones autorizadas, acceso restringido. |
| idempotencia | id, (usuario_id,clave) único | Evita guardados duplicados ante doble clic/reintento. |

## Integridad y ámbitos
- Toda ficha pertenece a sede y presbiterio consistentes mediante FK compuesta.
- El líder GC se infiere exclusivamente de Persona asociada a su ficha GC; no existe registro nominal de miembros.
- Documento DNI de ocho dígitos; CE/OTRO conservador y **no inventar validez oficial**. `clave_identidad` NULL si falta subtipo/tipo suficiente.
- Una Persona puede relacionarse con varias fichas fuente; consultar datos solo según ámbito aprobado; **E1-D01 y E2-D01 pendientes**.
- Campos críticos faltantes no bloquean: se calculan al consultar bajo configuración vigente, con fechas tipadas.
- CHECK y FK compuestas requieren **prueba en versión efectiva del hosting**, no declarar validadas aún.

## Regla funcional de ámbito de consulta aprobado (E2-D01)
`usuario.alcance_consulta` controla exclusivamente las **lecturas**: `PRESBITERIO` aplica ámbito(s) de `usuario_presbiterio`, `GENERAL` permite búsqueda nacional autenticada conforme a los privilegios del perfil. El digitador mantiene selección del presbiterio/sede operativa para registrar; el permiso GENERAL **no permite registrar ni editar fuera de permisos de escritura**. ADMIN opera nacionalmente y REPORTES solo en informes. Índice `ficha_captura(presbiterio_id, tipo_ficha, created_at)` soporta filtros por presbiterio.
