# Datos fuente JSON — versión 1.0
- [Persona](ficha-persona.schema.json): cónyuge, responsabilidades opcionales del formulario papel, sin tabla propia.
- [Grupo Crecer](ficha-gc.schema.json): la ficha GC conserva detalles normales en las columnas `grupo_crecer`, por lo que JSON solo versiona el formato cuando se usa.
- En `ficha.datos_fuente_json` el objeto tiene `schema_version="1.0"`; se admite NULL para ausencia completa de secciones. Al cambiar formato publicar v2; jamás reinterpretar silenciosamente capturas anteriores.
- Las fechas del JSON deben ser reales ISO `YYYY-MM-DD` o nulas. PHP valida esquema, campos extra, tamaño y tipos; React refleja los mismos controles.
- Los datos fuente no duplican `ficha.fecha_papel`, `ficha.control_papel`, `persona` ni las columnas de `grupo_crecer`.
- Estos esquemas definen persistencia; los campos de entrada están en [OpenAPI](../../04-integracion/openapi.yaml) y deben contrastarse con el Excel original. No constituye módulo adicional.
