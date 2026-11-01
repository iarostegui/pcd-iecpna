# Modelo conceptual vigente — PCD V4 (independiente de SGSP)

## Objetos de negocio y relaciones
- **Organización:** `organizacion` con nodo PRESBITERIO 1:N nodo SEDE, padre/hijo.
- **Actor y sesión:** `usuario` DIGITADOR con un presbiterio operativo, ADMIN/REPORTES nacionales; Usuario 1:N `sesion`, con GPS opcional por sesión, 1:N eventos de `login_evento`.
- **Datos capturados:** `persona` 1:N `ficha` (fuentes de Persona/GC). `grupo_crecer` 1:1 con ficha de tipo GC, líder `ficha.persona_id`. Sin miembros nominales.
- **Catálogos:** `catalogo` es tabla de tablas; datos de cónyuge/responsabilidades dentro de `ficha.datos_fuente_json`; `ubigeo` es árbol propio INEI.
- **Control:** `auditoria_evento` actor real y antes/después, `idempotencia` evita doble insert.

## Decisiones
Consulta **total por funcionalidad** con filtros opcionales de presbiterio/sede; ningún permiso de lectura territorial. Solo tipo+número de documento; número de ficha opcional. Credenciales y edición de fichas siguen reglas por rol/autor. Calidad automática de incompletitud, revisión manual externa al papel. La BD PCD no se integra ahora con SGSP.

[ER físico](anexos/anexo-01-diagrama-fisico.md) · [Modelo lógico de 11 tablas](modelo-logico.md) · [Diccionario completo](anexos/anexo-02-diccionario-fisico.md).
