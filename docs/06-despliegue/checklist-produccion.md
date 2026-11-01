# Checklist de aceptación antes de producción PCD V4.0

**No marcar PASS por existencia de documentos**. Registrar `PASS/FAIL/BLOQUEADO`, fecha, responsable, enlace a evidencia y entorno. La app temporal contiene datos personales y puede tratar información religiosa sensible.

| ID | Control exigido | Resultado | Evidencia | Dueño |
|---|---|---|---|---|
| PROD-01 | Confirmación PHP/MySQL/MariaDB + CHECK/FK/JSON/HTTPS reales | NO EJECUTADO | Pendiente | Técnico |
| PROD-02 | Crear 11 tablas, seed y verificar integridad del árbol y transacciones | NO EJECUTADO | Pendiente | Técnico |
| PROD-03 | Autenticación, contraseñas distintas para nacionales, limitación de intentos, CSRF y revocación | NO EJECUTADO | Pendiente | Técnico |
| PROD-04 | Consulta **general con filtros** por presbiterio/sede y escritura limitada | NO EJECUTADO | Pendiente | Técnico |
| PROD-05 | GPS único opcional por sesión y sin repetición tras denegación | NO EJECUTADO | Pendiente | Técnico |
| PROD-06 | Copias protegidas y restauración real verificada | NO EJECUTADO | Pendiente | Operación |
| PROD-07 | Prueba de carga 10/20/30, p95/5xx y baseline hosting | NO EJECUTADO | Pendiente | Técnico |
| PROD-08 | Decisiones jurídicas/privacidad PR-01…08 firmadas | PENDIENTE INSTITUCIONAL | Pendiente | Responsable IECPNA |
| PROD-09 | Procedimiento cierre de campaña, baja de cuentas y retención | PENDIENTE | Pendiente | Operación |
| PROD-10 | Capacitar en revisión **manual externa** papel/dato | PENDIENTE | Pendiente | Presbiterios |

**GO:** responsable técnico + funcional + responsable institucional aprueban evidencia y privacidad; sin firma PR-01…08 y sin pruebas mínimas no desplegar con datos reales. [Detalle Etapa 4](../07-auditorias/etapa-4-produccion-y-privacidad.md).
