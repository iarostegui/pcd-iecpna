---
estado: supersedido
reemplazado_por: docs/01-analisis/alcance-funcional.md
especificaciones_vigentes: openspec/specs/
---

> **ANTECEDENTE HISTÓRICO E1/E2/E3:** no utilizar este archivo para generar código ni imponer decisiones de 17 tablas, ACL territorial, subtipo documental, contraseña compartida productiva o E1-D01 pendiente. La norma vigente es **ALC-PCD-4.0/SDD V4.1**, con presbiterios fijos, E1-D01 aprobada y auditoría solo de ediciones. Para revisar el estado actual comenzar por `AGENTS.md` y el `README.md` raíz.

> **HISTÓRICO V3 (31/10/2026), NO CODIFICAR DESDE AQUÍ.** Prevalecen [SQL V4 de 11 tablas](https://github.com/iarostegui/pcd-iecpna/blob/main/database/migrations/001_initial.sql) y [decisiones vigentes](https://github.com/iarostegui/pcd-iecpna/blob/main/docs/01-analisis/decisiones-pendientes.md): consulta general por funcionalidad con filtros presbiterio/sede, documentos solo tipo+número sin subtipo, catálogo único y ubigeo separado. Cualquier regla histórica contradictoria queda sustituida.

# Delta E2 — integridad del líder GC
## GC-08 ADDED: una referencia de líder
GIVEN ficha tipo GC enlazada a Persona P WHEN se registra grupo THEN el líder se obtiene **únicamente** de ficha.persona_id, sin otro campo de líder duplicado.
## GC-09 ADDED: tipo de ficha consistente
GIVEN intento de asociar Grupo a ficha tipo PERSONA o responsabilidad/cónyuge a ficha GC WHEN guarda THEN servicio rechaza con 422 y se protege la integridad mediante FK/constraint disponible.
