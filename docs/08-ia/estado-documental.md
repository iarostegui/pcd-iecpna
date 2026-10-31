# Estado de documentación y limitaciones (31-10-2026)
## Publicado en rama main
PCD SDD V3.1: análisis, catálogo de campos, reglas negocio, diseño de componentes, arquitectura, seguridad, RNF, ER, SQL, OpenAPI, pruebas, cPanel, specs y prompt para IA. Prototipo PCD V2.2 versionado y con su identidad actualizada.

## No implica ejecución
No hay frontend React productivo, backend PHP, credenciales, base MySQL productiva ni migración SGSP; directorios de código están reservados. Modelo SQL y OpenAPI son una base para programación, pendientes de validación contra hosting y ejecución de tests.

## Fuentes y decisiones pendientes
El Excel original de las fichas y sus ejemplos está disponible como material original adjunto del trabajo, pero no se ha agregado el archivo binario al repositorio; se ha transcrito el mapeo en `diccionario-captura.md`. El catálogo completo de 48 sedes y varias decisiones operativas de hosting/retención aún no se conocen: `docs/01-analisis/decisiones-pendientes.md`.

## Riesgos
Clave común afecta atribución de identidad; se acepta por decisión operativa con mitigaciones. Coordenadas opcionales no prueban presencia en sede. Material de prototipo localStorage/demostración no es producción. No trasladar esquema físico de SGSP a PCD.

## Auditoría E2 — estado de entrega
[Etapa 2 — Seguridad/datos](../07-auditorias/etapa-2-arquitectura-seguridad-datos.md) agrega control de clave común, login sin enumeración, presbiterios públicos mínimos, sesiones con revocación, catálogo fuente, normalización condicional, criticidad dinámica y relación única GC→ficha. `database/migrations/001_initial.sql` y `docs/04-integracion/openapi.yaml` se revisaron documentalmente; también `database/seeds/002_catalogos_calidad.sql`.
- **Decisiones pendientes:** E2-D01 (consulta nominal dentro de presbiterio), E2-D02 (ADMIN/REPORTES nacionales y cambio de contexto), E2-D03 (aceptación riesgo clave común).
- **Verificación limitada realizada:** rutas OpenAPI y referencias a esquemas, nombres de tablas/FK del SQL, presencia de seed; **NO** ejecución YAML/MySQL/React/PHP ni carga.
- **Iteraciones:** I00 lista para preparar; I01 preparada para planificación pero condicionada; I02/I03/I04 no listas para codificar sin decisiones/trazabilidad Etapa 1 y Etapa 2. Prototipo PCD 2.2 inalterado.
