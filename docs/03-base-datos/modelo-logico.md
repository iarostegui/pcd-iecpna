# PCD V4.0 — Modelo lógico vigente: 11 tablas

**Fuente física:** [001_initial.sql](../../database/migrations/001_initial.sql). [Anexo ER](anexos/anexo-01-diagrama-fisico.md) · [Diccionario por columna](anexos/anexo-02-diccionario-fisico.md) · [Justificación tabla por tabla](anexos/justificacion-11-tablas.md).

| Área | Tabla | Función / vínculos |
|---|---|---|
| Organización | `organizacion` | Presbiterio (raíz) y sede (hijo) en tabla única, con referencia de parent. |
| Seguridad | `usuario` | Login, roles, contraseña hash, presbiterio operativo del digitador. **Sin tabla de permisos territoriales de consulta.** |
| Seguridad | `sesion` | Sesión segura/contexto/GPS opcional una sola vez, revocación. |
| Seguridad | `login_evento` | Accesos y fallos, incluidos intentos anónimos. |
| Configuración | `catalogo` | **Tabla de tablas**: categorías, opciones, campos críticos en `config_json`. |
| Configuración | `ubigeo` | Una sola jerarquía INEI de tres niveles; entrada de construcción I02. |
| Registro | `persona` | Identidad consolidada con **tipo + número de documento** solamente. |
| Registro | `ficha` | Fuentes de Persona/GC, sede/presbiterio, autor original, JSON de cónyuge y responsabilidades. |
| Registro | `grupo_crecer` | Ficha GC y datos agregados, líder derivado de `ficha.persona_id`. Sin miembros nominales. |
| Control | `auditoria_evento` | Antes/después, actor y sesión de ediciones. |
| Control | `idempotencia` | Evita duplicados por reintento en operaciones de escritura. |

## Restricciones y decisiones funcionales

- **Consulta:** cualquier usuario con acceso a la funcionalidad puede consultar sus registros **a nivel general**; los filtros de presbiterio/sede son **opcionales** y no definen ámbitos de permiso. No se añade `alcance_consulta` ni `usuario_presbiterio`.
- **Escritura:** DIGITADOR solo modifica fichas que creó; ADMIN tiene facultades nacionales; REPORTES no edita. La selección de presbiterio/sede de la sesión sí delimita **dónde se registra**, no qué se puede consultar.
- **Documentos:** solo `tipo_documento` y `numero_documento` de formulario. Normalizar sin perder ceros; clave exacta DNI/C.E. cuando confiable. Para OTRO sin identificación concluyente, clave identidad NULL y **posible duplicado**, no fusión automática. Sin subtipo.
- **Fuentes:** varias fichas pueden corresponder a una Persona; `datos_fuente_json` conserva valores transcritos y secciones del papel sin inventar tablas operativas para cónyuge/responsabilidades.
- **Organización:** sede `organizacion.tipo='SEDE'` y padre de tipo PRESBITERIO; FK compuesta de ficha/sesión mantiene sede-padres, PHP comprueba los tipos.
- **Ubigeo:** códigos en Persona, con validación de nivel y jerarquía en PHP; catálogo INEI cargado durante I02. No inventar códigos.
- **Calidad:** valores en CATALOGO categorías `CAMPO_CRITICO_PERSONA` y `CAMPO_CRITICO_GC`. No marcar ficha incompleta como error bloqueante ni comparar automáticamente con papel.
- **GC:** una ficha GC puede originar un registro GC y su líder es la Persona de esa ficha; prohibido crear miembros nominales.
- **Pendientes:** E1-D01 vínculo cross-presbiterio; E1-D02 grupo preexistente; H-23/H-24 reglas operativas, H-40 retención y privacidad. No asumir aprobaciones.

**Estado:** esquema nuevo inicial para construcción; **no ejecutado** contra MySQL/MariaDB. El diseño anterior de 17 tablas queda reemplazado y **no** debe utilizarse en código nuevo.
