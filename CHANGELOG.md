# Changelog documental y técnico — PCD
## 2026-11-02 — P01: andamiaje ejecutable
- Backend PHP 8.2 (Composer/PSR-4 `Pcd\`), router explícito, manejo centralizado de errores, PDO utf8mb4, CSRF/CORS y `GET /api/v1/health`.
- Placeholders 501 para rutas de I01; no se implementan login, captura, consulta ni reportes.
- `scripts/bootstrap-provision.php`: 24 DIGITADORES + ADMIN + REPORTES, idempotente, Argon2id y modo separado `admin-prod`.
- Frontend React/TS/Vite con cliente HTTP centralizado, `request_id` y CSRF en memoria.
- PHPUnit y runner HTTP de 22 verificaciones con estados PASS/FAIL/BLOCKED/NOT_IMPLEMENTED.
- CI con MySQL 8 real, PHPUnit, aprovisionamiento, runner y build del frontend.
- Sin cambios en SQL, OpenAPI, prototipo V2.3 ni decisiones funcionales.
## 2026-11-01 — P00: consolidación de consistencia SDD V4.1
- Casos CA-06/25/39/40/48/58/66/67/68/69/72 alineados a H-03/H-12/H-13/E1-D01 y ALC-03, sin renumerar IDs.
- E1-CA-01 alineado a H-12: consulta general de la funcionalidad y edición solo del autor, sin restricción de visualización ajena.
- Corregidos el enlace V2.3 que apuntaba a V2.2, la numeración duplicada del alcance, E1-D01 dado por pendiente y etiquetas V4.0/134 columnas obsoletas.
- E2-D02 registrada como **APROBADA** (ALC-03); README de integración alineado a OpenAPI 4.1.
- `package-lock.json` incorporado; CI y documentación usan `npm ci`. Validadores estáticos en verde; no sustituyen pruebas de motor.
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
