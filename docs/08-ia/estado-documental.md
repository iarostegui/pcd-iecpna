# PCD — Estado documental vigente 02/11/2026 (V4.1 + I01)

## Decisiones funcionales y estructura vigentes
- [Alcance oficial](../01-analisis/alcance-funcional.md): plataforma **temporal** de captura de fichas Persona/Líder GC, no SGSP.
- [Modelo físico inicial de 11 tablas](../../database/migrations/001_initial.sql): **146 columnas y 23 FK**, tabla jerárquica ORGANIZACION, tabla de tablas CATALOGO, UBIGEO INEI (entrada I02), seguridad y registro simplificados.
- [Anexo ER](../03-base-datos/anexos/anexo-01-diagrama-fisico.md), [diccionario](../03-base-datos/anexos/anexo-02-diccionario-fisico.md) y [justificación](../03-base-datos/anexos/justificacion-11-tablas.md) sincronizados con SQL.
- [OpenAPI V4](../04-integracion/openapi.yaml), [OpenSpec vigente](../../openspec/specs/README.md) y [trazabilidad](matriz-trazabilidad.md) actualizados documentalmente.
- **Consulta total** en funcionalidad autorizada, filtros opcionales Presbiterio/Sede, **sin** restricciones territoriales de lectura por usuario ni tabla asociativa.
- Identificación por **tipo y número** exclusivamente; sin subtipo.
- Calidad: reportes de faltantes automáticos, revisión de fidelidad papel↔transcripción manual externa por presbiterios.
- Líder GC único y resumen agregado, sin integrantes nominales.

## I01 implementado en desarrollo/CI (02/11/2026)
- Autenticación real contra MySQL con `password_verify`, presbiterio + código tecleado, bloqueo 5/cuenta y 20/IP en 15 min, `login_evento`.
- Sesión persistida en `sesion` (hash de cookie, `last_seen`, `auth_version`, GPS), cookie HttpOnly/SameSite/Secure, CSRF, inactividad 45 min y absoluta 12 h, logout y revocación.
- `GET /presbiterios` público y mínimo; sedes del presbiterio, alta normalizada RN-33 con 409 e idempotencia; contexto ADMIN explícito; REPORTES solo lectura.
- React I01: login sin listado de cuentas, selección/alta de sede, contexto autenticado, expiración, GPS opcional una vez por sesión y ausencia de `localStorage`.
- Evidencia: `npm test` (SDD), PHPUnit 70/179 local (la CI de la ronda se registra en el PR), runner I01 42/42 PASS y build React. CI [run 41456246321](https://github.com/iarostegui/pcd-iecpna/actions/runs/41456246321): jobs `documentos`, `backend`, `frontend` e `i00` en verde, MySQL 8.0.46/PHP 8.2.35. **No es GO productivo; E-07, H-14, H-40 y revisión del especialista siguen abiertos.**

## Correcciones técnicas I01-O01…O03 (02/11/2026)
- **O01:** cubo de intentos por `(usuario_id o codigo_intentado) + presbiterio_id`; los fallos en un presbiterio no bloquean la cuenta en otro. IP sigue global. Pruebas HTTP y de repositorio.
- **O02:** captura GPS con `UPDATE ... WHERE geo_estado IS NULL` atómico; concurrencia 200/409 sin sobrescritura. Pruebas HTTP concurrentes y de dos conexiones MySQL.
- **O03:** `PhpSession::cookieName()`/`cookieOptions()`, `__Host-pcd_sid` + `Secure` en producción, `HttpOnly`, `SameSite=Lax`, `Path=/`, sin `Domain`; no se confía en cabeceras de proxy. TLS/cPanel real: BLOCKED (E-07).
- Evidencia de la ronda: local PHPUnit 70/179, runner I01 42/42 PASS, frontend 8/8 y build; CI [run 41456246321](https://github.com/iarostegui/pcd-iecpna/actions/runs/41456246321) con MySQL 8.0.46/PHP 8.2.35: I01 42/42, PHPUnit 70/178 (1 omitida), I00 31 PASS/0 FAIL/1 BLOCKED y frontend 8/8.

## Etapa 4 preparada, no ejecutada
[Informe E4](../07-auditorias/etapa-4-produccion-y-privacidad.md), [checklist productivo](../06-despliegue/checklist-produccion.md). PR-01…08 de datos personales/sensibles quedan sin decisión institucional. No se han ejecutado pruebas MySQL/Apache/PHP, recuperación de backups, E2E ni carga real.

## Pendientes
E1-D02, H-23, H-24; H-14 Excel binario; fuente/corte INEI durante I02; ambiente cPanel real; H-40 privacidad/retención. **P01 dejó el andamiaje (API PHP 8.2 + React/TS/Vite); I01 añade autenticación, sesiones y contexto operativo en desarrollo/CI. I02–I05 siguen pendientes y no existe backend/frontend productivo.**

## Documentación histórica
Textos de análisis/auditorías y cambios E1/E2/E3 de fecha 31/10/2026 se preservan como **antecedentes**; cualquier mención a 17 tablas, `alcance_consulta`, subtipo documental o permisos territoriales **ha sido anulada** por decisiones V4.1. El prototipo V2.2 no se modificó.

## Versión de UX vigente
**PCD_v2.3.html** está publicado como referencia React V4; **PCD_v2.2.html** permanece intacto como historial. [Informe de alineación](../02-diseno/pcd-v2.3-alineacion.md), [pruebas CA-101…111](../05-pruebas/validacion-prototipo-v2.3.md). La demostración HTML no autentica de verdad ni guarda en MySQL. Las referencias antiguas del propio archivo a V2.2 como «vigente» quedan sustituidas.

## Etapa 5 — Entrega al ciclo de implementación
[Evaluación I00 estática](I00-verificacion-estatica-y-handoff.md) y [Etapa 5](../07-auditorias/etapa-5-preparacion-i00.md) formalizan el paso de auditoría documental al ensayo técnico. I00 ejecutó **MySQL 8.0.46 en CI**: 11 tablas, 146 columnas, 23 FK, UNIQUE/CHECK, idempotencia, seed y aprovisionamiento Argon2id; ver [matriz](i00/MATRIZ-I00.md) y [acta](i00/ACTA-PRELIMINAR-I00.md). **El cierre sigue NO-GO** por cPanel no verificado, H-14 y H-40.

## Alineamiento para revisión profesional V4.1
[Entrega resumida](../07-auditorias/ENTREGA-REVISION-ESPECIALISTA-V4.1.md). E1-D01 **aprobada**; presbiterios de seed fijos y no administrables desde la app; contraseña común solo aceptación, rotación individual para producción; creación de ficha sin evento auditoría y edición solo campos alterados; nombres sin tildes para duplicados; JSON Schema estricto; 11 tablas, 146 columnas, 23 FK. [Aprobaciones](APROBACIONES.md) son criterios funcionales, no firmas jurídicas.
CI y validador SDD son revisión estructural, no acreditan motor MySQL/PHP/React. H-40 y visibilidad/LICENCIA del repositorio requieren decisión institucional/propietario.

## Verificación SDD automatizada — evidencia
Run [41443882284](https://github.com/iarostegui/pcd-iecpna/actions/runs/41443882284): jobs `documentos`, `backend`, `frontend` e `i00` en verde; MySQL 8 real, PHPUnit 43/115, runner 20/0/0/3 y verificador I00 30 PASS/0 FAIL/1 BLOCKED/1 NOT_IMPLEMENTED. No cubre cPanel, backups ni H-40. [Contrato IA](CONTRATO-EJECUCION-V4.1.md); [paquete especialista](../07-auditorias/ENTREGA-REVISION-ESPECIALISTA-V4.1.md).
