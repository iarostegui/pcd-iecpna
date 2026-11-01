# PCD — Estado documental vigente 01/11/2026 (V4.0)

## Decisiones funcionales y estructura vigentes
- [Alcance oficial](../01-analisis/alcance-funcional.md): plataforma **temporal** de captura de fichas Persona/Líder GC, no SGSP.
- [Modelo físico inicial de 11 tablas](../../database/migrations/001_initial.sql): **134 columnas y 23 FK**, tabla jerárquica ORGANIZACION, tabla de tablas CATALOGO, UBIGEO INEI (entrada I02), seguridad y registro simplificados.
- [Anexo ER](../03-base-datos/anexos/anexo-01-diagrama-fisico.md), [diccionario](../03-base-datos/anexos/anexo-02-diccionario-fisico.md) y [justificación](../03-base-datos/anexos/justificacion-11-tablas.md) sincronizados con SQL.
- [OpenAPI V4](../04-integracion/openapi.yaml), [OpenSpec vigente](../../openspec/specs/README.md) y [trazabilidad](matriz-trazabilidad.md) actualizados documentalmente.
- **Consulta total** en funcionalidad autorizada, filtros opcionales Presbiterio/Sede, **sin** restricciones territoriales de lectura por usuario ni tabla asociativa.
- Identificación por **tipo y número** exclusivamente; sin subtipo.
- Calidad: reportes de faltantes automáticos, revisión de fidelidad papel↔transcripción manual externa por presbiterios.
- Líder GC único y resumen agregado, sin integrantes nominales.

## Etapa 4 preparada, no ejecutada
[Informe E4](../07-auditorias/etapa-4-produccion-y-privacidad.md), [checklist productivo](../06-despliegue/checklist-produccion.md). PR-01…08 de datos personales/sensibles quedan sin decisión institucional. No se han ejecutado pruebas MySQL/Apache/PHP, recuperación de backups, E2E ni carga real.

## Pendientes
E1-D01, E1-D02, H-23, H-24; H-14 Excel binario; fuente/corte INEI durante I02; ambiente cPanel real; H-40 privacidad/retención. **No hay frontend React ni backend PHP productivos implementados**.

## Documentación histórica
Textos de análisis/auditorías y cambios E1/E2/E3 de fecha 31/10/2026 se preservan como **antecedentes**; cualquier mención a 17 tablas, `alcance_consulta`, subtipo documental o permisos territoriales **ha sido anulada** por decisiones V4.0. El prototipo V2.2 no se modificó.
