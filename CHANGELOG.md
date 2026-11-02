# Changelog documental y técnico — PCD
## 2026-11-01 — SDD V4.1 (vigente para revisión de especialista)
- Consolidación normativa [ALC-PCD-4.0](docs/01-analisis/alcance-funcional.md), conservando ALC-01…12 y EXC-01…10.
- Clave común **solo UAT**, rotación individual y revocación antes de producción.
- E1-D01 aprobado: nueva ficha de identidad documental existente, incluso entre presbiterios, sin sobrescribir consolidado; endpoint conservado.
- Los presbiterios LIM, TRU, CHI e IQU se precargan y no se agregan desde UI/API; sedes sí.
- Alta de ficha solo `created_at`/`created_by`; ediciones con auditoría **solo de campos cambiados**, actor congelado.
- 11 tablas, **146 columnas y 23 FK**, timestamps en todas, UNIQUE para presbiterio raíz, índice de limpieza idempotencia, normalización de nombres sin tildes.
- JSON Schema V1.0 cerrado para secciones de Persona/GC, contrato OpenAPI V4, validaciones CI y documentación de seguridad.
- Objetivo: preparar construcción IA, no ampliar auditorías; SQL y PHP/React productivos **NO PROBADOS**.
## 2026-11-01 — SDD V4.0 (histórico)
- Reducción de modelo previo de 17 a 11 tablas; catálogos unificados, UBIGEO jerárquico, organización presbiterio/sede.
- Consulta general por funcionalidad con filtros, sin ACL territorial por usuario; documento solo tipo y número.
- Prototipo V2.3 como referencia UI; V2.2 histórico.
## 2026-10-31 — SDD V3.1 y auditorías E1–E3 (supersedidas cuando contradicen V4.1)
- Registro de hallazgos, propuestas y decisiones previas; materiales conservados para trazabilidad, **no** como órdenes de implementación.
