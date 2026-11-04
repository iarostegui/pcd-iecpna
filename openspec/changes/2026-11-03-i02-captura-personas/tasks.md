# I02 — Tareas, estado y evidencias

- [x] Identificar base I01, contratos y modelo físico inmutable.
- [x] Registrar H-24, CE e INEI como puertas separadas, sin inventar decisiones.
- [x] Crear proposal/design/spec/tasks para trazabilidad SDD.
- [x] Backend persona/ficha + validación + idempotencia, con pruebas unitarias/integración.
- [x] Frontend de captura integrado a Workspace I01, con pruebas Vitest/build.
- [x] Probar en MySQL 8 CI y corregir errores (run 41756308611, I02-HTTP 19 PASS).
- [x] Matriz de evidencias por IDs y PR Draft #5.
- [x] H-24 — **APROBADA FUNCIONALMENTE**: presbiterio/sede + nombres, ambos apellidos, tipo/número documento y teléfono; código/tests incluidos.
- [x] CE — captura conservadora sin clave de identidad confiable, sin fusión; fuentes Migraciones/SUNAT y limitación documentadas. Integración con validación remota **fuera del alcance I02**.
- [x] UBIGEO — fuente del usuario H-UBIGEO-2026-11-04 (sin corte INEI acreditado), SHA256 y 2114 códigos importados en MySQL CI; reimportación idempotente, consultas jerárquicas y rechazo de código ajeno PASS. La acreditación INEI externa se mantiene pendiente para producción.
- [ ] GO producción — **NO AUTORIZADO** (H-40, cPanel, especialista).

I02 lista para revisión/merge técnico después de CI verde; el GO productivo continúa bloqueado por H-40, hosting e I03–I06.
- [x] Utilitario de validación/importación controlada INEI con SHA256 y dry-run; **fuente recibida cargada en MySQL de CI, sin afirmar importación oficial INEI**.
