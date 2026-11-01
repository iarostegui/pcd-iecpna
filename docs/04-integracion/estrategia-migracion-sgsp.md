# PCD → SGSP: estrategia futura, NO implementación actual
## Decisión
PCD tiene BD temporal MySQL propia; posteriormente se ejecutará un proceso **separado** de migración directa de BD PCD a BD SGSP. No ofrecer botón actual «Migrar», no conectar ahora a BD SGSP ni suponer esquema destino.

## Requisitos de preparación de datos
1. Mantener IDs inmutables `persona.id`, `ficha.id`, `grupo_crecer.id`.
2. Conservar usuario, sede/presbiterio original, fecha papel y fecha de digitación.
3. Documentos normalizados y alertas de duplicidad, sin eliminar fichas fuente.
4. Guardar condición pastoral separada de estado técnico.
5. Responsabilidades pastorales como datos, no roles técnicos.
6. Cónyuge pendiente si no existe Persona vinculada.
7. Tipo, capacidad, integrantes aproximados GC sin inventar miembros nominales.
8. Campos incompletos y su condición crítica configurable deben poder identificarse.

## Diseño futuro ETL
Homologar catálogos origen/destino, evaluar duplicados/colisiones transversales, establecer tabla de correspondencia IDs y marca de migración, simular y reconciliar resultados; rollback/compensación documentados. **No diseñar columnas SGSP por inferencia**.


**V4:** origen simplificado de 11 tablas: `organizacion` jerárquica, `persona`, `ficha`, `grupo_crecer`, catálogos y ubigeo. Cónyuge/responsabilidades en JSON fuente, sin subtipo documental ni permisos territoriales de lectura. Este texto NO autoriza implementar ETL ahora.
