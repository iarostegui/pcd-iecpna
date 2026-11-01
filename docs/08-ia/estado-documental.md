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

## Versión de UX vigente
**PCD_v2.3.html** está publicado como referencia React V4; **PCD_v2.2.html** permanece intacto como historial. [Informe de alineación](../02-diseno/pcd-v2.3-alineacion.md), [pruebas CA-101…111](../05-pruebas/validacion-prototipo-v2.3.md). La demostración HTML no autentica de verdad ni guarda en MySQL. Las referencias antiguas del propio archivo a V2.2 como «vigente» quedan sustituidas.

## Etapa 5 — Entrega al ciclo de implementación
[Evaluación I00 estática](I00-verificacion-estatica-y-handoff.md) y [Etapa 5](../07-auditorias/etapa-5-preparacion-i00.md) formalizan el paso de auditoría documental al ensayo técnico. El script [`validar-sdd-i00.cjs`](../../scripts/validar-sdd-i00.cjs) comprueba regresiones de modelo y OpenAPI. **No prueba MySQL, PHP, backup ni cPanel.** Versión vigente: V4, 11 tablas, 134 columnas, 23 FK, UX V2.3.
