# Definition of Done — PCD
No declarar módulo terminado hasta cumplir todos:
1. Requisito y caso de uso trazados a `openspec/specs`, UI **V2.3 vigente**, API V4 y SQL de 11 tablas.
2. Login y autorización revalidados en backend; no IDOR.
3. Transacciones, UNIQUE, FK, idempotencia y no pérdida ante doble clic.
4. Autor original y auditoría antes/después conservados; GPS una vez por sesión.
5. Guardado de incompletos y número ficha opcional; criterios de calidad configurables.
6. Pruebas automatizadas con evidencia real, sin falsos PASÓ.
7. Build React compilado, sin Node en cPanel, PHP no revela secretos, backup restaurable.
8. Rendimiento medido contra web existente sin asumir capacidad.
9. Código/documentación y OpenAPI sincronizados, sin alterar SGSP.
10. Informe de riesgos residuales, incluyendo contraseña operativa común y GPS opcional.

## DoD adicional V4.0
Ninguna iteración está terminada mientras el código no use exactamente el [modelo vigente de 11 tablas](../../database/migrations/001_initial.sql), consulta general con filtros Presbiterio/Sede, documento sin subtipo y pruebas automatizadas de permisos de edición propia. Para producción cumplir [PROD-01…10](../06-despliegue/checklist-produccion.md) y H-40, sin confundir documentación con pruebas.

**Actualización de puerta I00:** ejecutar script de comprobación SDD y prototipo de forma local, y después pruebas reales SQL/PHP/MySQL + revisión de seguridad/privacidad. [Guía](I00-verificacion-estatica-y-handoff.md). La UX de referencia es V2.3, V2.2 solo histórico.
