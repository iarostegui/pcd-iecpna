# Design E4 — Controles de instalación y privacidad PCD V4

1. **Entorno**: React Vite compilado estático; PHP REST mismo origen, PDO, MySQL/MariaDB InnoDB; variables seguras fuera de webroot, sin Node ejecutando permanentemente.
2. **BD**: 11 tablas; una tabla ORGANIZACION, CATALOGO, UBIGEO; Persona/Ficha/GC; sesión contiene ubicación. No ALTER implícito sobre BD anterior.
3. **Consulta**: filtros opcionales por Presbiterio/Sede, autorización por funcionalidad; edición solo propias y ADMIN.
4. **Seguridad**: cookie protegida, CSRF, límite de intentos, expiración/revocación, auditoría, 429, pruebas IDOR/XSS y datos en tránsito.
5. **Datos sensibles**: no almacenar capturas en navegador ni registrar PII en logs, definir responsable y base legal; GPS de acceso limitado con tiempo de conservación aprobado.
6. **Backup**: fuera de webroot, restauración ensayada en ambiente aislado; revertir build/API de forma coordinada con BD si falla.
7. **Cierre**: acta de corte, revocación de cuentas, respaldos finales y disposición posterior conforme a retención aprobada. Migración SGSP no se desarrolla.
8. **Legal**: Ley 29733 / Reglamento DS 016-2024-JUS; validación jurídica y responsabilidades PR-01…08 antes de usar datos reales.

**No declarar implementado ninguno de estos controles hasta adjuntar evidencia de pruebas.**
