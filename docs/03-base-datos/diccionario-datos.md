# Diccionario de datos de PCD
Consultar `docs/01-analisis/diccionario-captura.md` para etiquetas del papel.

| Tabla | Datos obligatorios técnicos | Datos opcionales |
|---|---|---|
| presbiterio | código, nombre, activo | — |
| sede | presbiterio_id, nombre, nombre_norm, activo | código, ubicación referencial |
| usuario | código, display, perfil, password_hash, activo, auth_version | último login |
| usuario_presbiterio | usuario_id, presbiterio_id | — |
| sesion_captura | usuario_id, presbiterio_id, session_ref_hash, fecha_login, auth_version_al_ingreso | sede_id, logout, revocada_en, last_seen, IP |
| geo_sesion | sesion_id, estado, captured_at | latitud, longitud, precisión |
| login_evento | username_intentado, resultado, fecha, IP | usuario_id, presbiterio_id |
| persona_captura | ID, created_by, created_at, version | tipo_documento_detalle, clave_identidad, documento, nombre, nacimiento, sexo, contacto, ubicación, condición |
| ficha_captura | campaña_id, persona_id, presbiterio_id, sede_id, tipo, created_by, created_at, version | número papel, fecha papel, observaciones, responsable papel, control papel |
| conyuge_pendiente | persona_id | nombre/tipo/numero documento |
| persona_responsabilidad_captura | persona_id, tipo_codigo | sede_id, región, descripción, fecha desde |
| grupo_crecer_captura | ficha_id, ficha_tipo='GC'; líder = ficha_captura.persona_id | nombre, tipo, estado, fechas, capacidad, integrantes estimados, dirección |
| campo_critico | entidad, campo_codigo, etiqueta, ruta_dato, es_critico, desactivable | updated_by, updated_at |
| auditoria_evento | usuario_id, accion, recurso_tipo, created_at, request_id | sesión, geo, antes/después, IP |
| idempotencia | usuario_id, clave, payload_hash | status, response_json |

## Codificación
- Números de documentos y fichas son `VARCHAR` (no enteros).
- `latitud` `DECIMAL(10,7)` (-90..90), `longitud` `DECIMAL(10,7)` (-180..180), `precision_m` positivo. Validaciones en PHP.
- Catálogos de papel como valores normalizados / whitelists en API, ampliables por datos de catálogo externos una vez aprobados; no inventar nombres de sedes.
- `campo_critico` permite alterar **métrica de completitud**, no integridad estructural.


## Ampliación — ficha_captura.datos_fuente_json
Columna JSON opcional que guarda el conjunto de campos transcritos de cada ficha de papel. Asegura independencia de fuentes cuando distintas sedes o usuarios entregan fichas de una Persona común. El cambio de ficha no altera automáticamente el agregado Persona.

### Diccionario E2
- `catalogo_valor`: `id`, `dominio`, `codigo`, `etiqueta`, `orden`, `activo`. Solo opciones verificadas en papel.
- `persona_captura.clave_identidad` UNIQUE nullable; `tipo_documento_detalle` permite distinguir tipos OTRO sin colisiones falsas.
- `grupo_crecer_captura` no almacena doble ID del líder; la relación es vía ficha GC → Persona.
- `ficha_tipo` en auxiliares/GC protege consistencia de tipo por CHECK y FK compuesta (a validar en hosting).
- `campo_critico` define rutas evaluables y no desactivables; reporte se recalcula según parámetros.
- `sesion_captura` asocia version de autenticación, revocación y último uso; controles por PHP.

## Catálogos y fuente de verdad E2
Los códigos/etiquetas iniciales residen en `catalogo_valor`, no solo en una whitelist textual hard-coded. PHP consulta los valores activos, valida que sean dominios permitidos, y mantiene caché corta no sensible si resulta necesario. **Ubigeo INEI sigue pendiente** y no se debe inventar. `persona_captura.clave_identidad` es UNIQUE solo si la documentación proporciona clave completa confiable; `grupo_crecer_captura` no tiene `lider_persona_id` redundante.

## Anexo por columna
Este archivo es un **resumen**. El [Anexo 02](anexos/anexo-02-diccionario-fisico.md) detalla 109 columnas de las 17 tablas desde el SQL y sus FK/índices/CHECK.
