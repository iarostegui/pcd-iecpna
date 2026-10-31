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

