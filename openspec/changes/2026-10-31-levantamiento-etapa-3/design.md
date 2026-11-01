> **HISTÓRICO V3 (31/10/2026), NO CODIFICAR DESDE AQUÍ.** Prevalecen [SQL V4 de 11 tablas](https://github.com/iarostegui/pcd-iecpna/blob/main/database/migrations/001_initial.sql) y [decisiones vigentes](https://github.com/iarostegui/pcd-iecpna/blob/main/docs/01-analisis/decisiones-pendientes.md): consulta general por funcionalidad con filtros presbiterio/sede, documentos solo tipo+número sin subtipo, catálogo único y ubigeo separado. Cualquier regla histórica contradictoria queda sustituida.

# Diseño de contratos y pruebas E3
- **DTO:** PersonaResumen, FichaDetalle, PersonaGuardada, GrupoDetalle, UsuarioPublico/Patch, ReporteAvance/Calidad, páginas y Error; [OpenAPI](../../../docs/04-integracion/openapi.yaml) es contrato de intercambio.
- **Consulta:** permiso GENERAL/PRESBITERIO en usuario, filtro backend; edición digitador solo autor, ADMIN global, REPORTES lectura. Nunca exponer GPS en DTO de Persona.
- **GC:** los PATCH usan ficha_captura.version; autoría igual a ficha.created_by; grupo_crecer_captura no tiene columna líder ni versión.
- **Duplicados:** documento confiable coincide, sin segunda Persona, los datos textuales no se fusionan por similitud. E1-D01 aún pendiente.
- **Reportes:** filtros presbiterio/sede/digitador/fecha; altas desde fichas por creador, modificaciones por auditoria_evento.actor. No certificar fidelidad con papel.
- **GPS:** única captura o estado por sesión sin reintento; error/idempotencia debe documentarse sin recapturar.
- **Administración:** cambio de alcance de consulta y desactivación de cuentas con revocación de sesión; restablecer acceso sin devolver secretos.
- **Fuera de alcance:** no aprobación de supervisor, no maestro permanente GC ni enlaces SGSP.
