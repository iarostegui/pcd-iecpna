# Diseño mínimo propuesto — PCD E1
## H-01/H-02: ficha fuente y Persona
Una hoja de papel da origen a `ficha_captura` con `datos_fuente_json`, `created_by`, presbiterio y sede de la sesión. La primera ficha puede originar `persona_captura` consolidada. En fichas posteriores con documento normalizado coincidente, el backend crea otra **ficha**, sin crear Persona ni exponer datos ajenos. Transacción/idempotencia; respetar UNIQUE documental. `ficha.created_by` es autor fuente, `persona.created_by` editor autorizado del consolidado; ADMIN conserva facultad global. Cónyuge/responsabilidades proceden de la ficha con `ficha_id`, no se mezclan con otra fuente.
## H-05: inviernos del papel
La transcripción **literal** se preserva por campo en snapshot (incluso fecha, correo y número que no se pueda interpretar). Columnas tipadas reciben valores validados o NULL; alerta de calidad asociada a la ficha y sin rechazo por incompletitud.
## H-08: Grupo Crecer
Cada ficha GC conserva nombre, tipo, líder y sede y recibe identificador propio. «Grupo ya existe» es Sí/No del papel, no enlace forzoso a registro PCD. Coincidencia por nombre/sede/líder sirve para advertir, no fusionar; cambio de líder no sobrescribe historial.
## H-09: calidad temporal
Observaciones derivadas de campos críticos, inválidos y posibles duplicados, visibles en reportes y subsanables con edición auditada. **No módulo de tickets/conciliación, no aprobaciones obligatorias.**
## H-14: fuente
[Inventario contrastado](../../../docs/01-analisis/fuentes/inventario-excel-original.md) con hash y filas del Excel; pendiente cargar binario XLSX al repositorio.
**Pendiente técnico:** DTOs específicos, normalización documental H-13, estructura versionada JSON y SQL/API/ER coherentes. No declarar código listo.
