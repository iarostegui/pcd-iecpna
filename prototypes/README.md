# Prototipos PCD — IECPNA

| Archivo | Rol | Estado |
|---|---|---|
| **[PCD_v2.3.html](PCD_v2.3.html)** | **Referencia visual y de interacción para construir React/SDD V4**. Login por código escrito, consulta general con filtros, GC sin conciliación ficticia, GPS único, QA manual. | **Vigente** |
| [PCD_v2.2.html](PCD_v2.2.html) | Versión original previa a las correcciones; preserva capturas y comportamiento anterior. | **Histórico, no alterar** |

**No subir fichas reales al HTML.** La demostración funciona en el navegador con datos ficticios en `localStorage`. Las contraseñas de prueba del HTML son públicas y no constituyen autenticación.

**Para programar**: leer [matriz pantalla ↔ SDD V4](../docs/02-diseno/pcd-v2.3-alineacion.md), [OpenSpec](../openspec/specs/README.md), [OpenAPI](../docs/04-integracion/openapi.yaml), [SQL 11 tablas](../database/migrations/001_initial.sql) y [pruebas CA-101…111](../docs/05-pruebas/validacion-prototipo-v2.3.md). No copiar el almacenamiento ni el login local al backend.

**Validación reproducible:** desde la raíz `node scripts/validar-prototipo-v2.3.cjs`. Solo inspección JS/HTML, pendiente ejecutar y complementar con navegador. [Script](../scripts/validar-prototipo-v2.3.cjs).
