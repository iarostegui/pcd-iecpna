# ADR de PCD
## ADR-001 — Hosting compartido
**Aprobado:** misma infraestructura cPanel existente; `pcd.unobdata.com`. React+Vite compilado estático, PHP API liviana, MySQL InnoDB. Sin Docker, Redis, Node residente ni backend dependiente de SaaS. **Razón:** bajo costo, menor presión CPU/RAM, despliegue simple.

## ADR-002 — Sesión y geolocalización
**Aprobado:** sesión del lado servidor; presbiterio desde login y sede contextual; geolocalización 1 vez por sesión, snapshot asociado a cada guardado. No se usa GPS para autorizar. **Razón:** evitar prompts repetidos y demora por guardado.

## ADR-003 — Datos propios y migración
**Aprobado:** BD PCD independiente; preparar trazabilidad y estables IDs para proceso posterior BD→BD hacia SGSP; **sin implementar exportación de migración ahora**. **Razón:** no acoplar temporal a software permanente.

## ADR-004 — Calidad no bloqueante
**Aprobado:** campos críticos configurables por ADMIN para calidad/reportes. No bloquear registros incompletos. **Razón:** fichas impresas tienen omisiones y revisión individual no se realizará.

## ADR-005 — Cuenta compartiendo contraseña
**Decisión operativa aprobada con riesgo:** seis usuarios individuales por presbiterio, misma contraseña común sin cambio obligatorio. **Mitigación:** hash, IP, sesión, intento, GPS opcional, limitación tasa, revocación. **Limitación:** la contraseña común no verifica identidad física.

## ADR-006 — Prototipo como contrato de UX
**Aprobado:** PCD V2.2 (última identidad visual) sirve como fuente de estilo/flujo, no como código de producción. **Razón:** evita retrabajo y mantiene validaciones backend.

## ADR-007 — Seguridad mínima de una clave operativa común
Mantener decisión de clave común **solo para digitadores**; usuarios se escriben, nacionales con contraseñas independientes, bloqueo temporal, sesiones con expiración/revocación y auditoría; no cambiar automáticamente la contraseña inicial. Riesgo residual conocido y aceptado funcionalmente; controles y H-40 deben comprobarse antes de producción. [Detalle](seguridad-auditoria.md).

## ADR-008 — Ámbito nacional por perfil (propuesta E2-D02)
ADMIN/REPORTES nacionales sin filas por todos los presbiterios; ADMIN cambia contexto explícitamente, REPORTES informes nacionales sin sede. Consulta general dentro de la funcionalidad con filtros opcionales Presbiterio/Sede. H-12 vigente V4. No implementar aprobación de supervisores.

## ADR-009 — Integridad GC y calidad derivada
Líder GC a través de ficha.persona_id (referencia única), FK/CHECK compuestas, no integrantes nominales. Criticidad modificable; calidad calculada bajo configuración vigente, sin estado fijo en ficha. [SQL](../../database/migrations/001_initial.sql).

## ADR-010 — Simplificación del PCD a 11 tablas (V4, APROBADA)
La aplicación es temporal. Se eliminan tablas sin ciclo de vida propio: `usuario_presbiterio`, `campana`, `geo_sesion`, `conyuge_pendiente`, `persona_responsabilidad_captura`, `campo_critico`; se fusionan `presbiterio+sede` en ORGANIZACION, `catalogo_valor+campo_critico` en CATALOGO, geolocalización en SESION y se crea UBIGEO jerárquica propia. PERSONA, FICHA y GRUPO_CRECER son registro. [Justificación](../03-base-datos/anexos/justificacion-11-tablas.md).

## ADR-011 — Consulta total por funcionalidad, no ACL territorial (APROBADA)
Cuando un perfil tenga permiso de usar una función de Consulta, puede acceder al conjunto general de esa función y **filtrar opcionalmente por Presbiterio y/o Sede**. La sede/presbiterio de login delimita **captura**, no lectura. No `alcance_consulta` ni nuevas tablas. La escritura se autoriza separadamente: DIGITADOR solo propias, ADMIN todas, REPORTES ninguna.

## ADR-012 — H-13 sin subtipo documental (APROBADA)
Formulario/API/BD usan solo `tipo_documento` y `numero_documento`. DNI válido / CE confiable con normalización, OTRO no implica fusión exacta. `clave_identidad` técnica puede ser NULL. El texto libre adicional del papel se conserva en JSON fuente sin construir otra identidad.

## ADR-013 — Protección sensible proporcional (EN PREPARACIÓN)
[Etapa 4](../07-auditorias/etapa-4-produccion-y-privacidad.md), [PROD-01…10](../06-despliegue/checklist-produccion.md). No poner datos reales sin H-40 y pruebas.
