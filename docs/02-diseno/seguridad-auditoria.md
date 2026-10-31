# SEC-PCD-3.1 — seguridad/ámbitos (Etapa 2)
**Control proporcional a PCD temporal; no SGSP.** [Alcance](../01-analisis/alcance-funcional.md) · [Auditoría E2](../07-auditorias/etapa-2-arquitectura-seguridad-datos.md).

## Cuentas
**DIGITADOR:** seis cuentas por presbiterio; contraseña inicial **común**, sin cambio forzado (decisión operativa); cuentas distintas identificables por usuario. Suplantación posible: **riesgo residual pendiente de aceptación E2-D03** antes de producción.
**ADMIN** y **REPORTES** nacionales: **contraseñas individuales distintas** de la clave común de digitadores, provisionadas fuera de Git. ADMIN puede editar todo, REPORTES sólo informes/consultas agregadas.
El formulario **productivo** muestra input libre para código de usuario, **nunca** lista desplegable de cuentas, aunque el prototipo V2.2 demuestre lo contrario. La consulta pública de presbiterios solo expone códigos/nombres activos.

## Controles concretos de login (propuesta)
- `password_hash` / `password_verify` en PHP, hash seguro con `PASSWORD_DEFAULT` o Argon2id si está disponible en hosting. Nunca contraseña real en SQL/Git/logs.
- Parámetros iniciales modificables por configuración protegida: máximo **5 intentos fallidos por cuenta** o **20 por IP** en **15 min** → retardo/bloqueo temporal sin revelar si el usuario existe. Registrar `login_evento` con IP/código/fecha. Evitar bloquear permanentemente a usuarios detrás de una IP compartida.
- `PHPSESSID` de sesión rotado en login; cookie `HttpOnly; Secure; SameSite=Lax` bajo HTTPS.
- **Expiración por inactividad 45 min** y **límite absoluto 12 h** desde login, ambos controlados en backend.
- CSRF aleatorio por sesión: `GET /auth/me` devuelve token CSRF solamente en sesión autenticada; React lo conserva en memoria y reconsulta tras recarga; cada POST/PATCH/PUT/DELETE lo verifica.
- `sesion_captura.session_ref_hash` conserva hash de identificador de sesión PHP (nunca ID claro); `auth_version_al_ingreso` se compara contra `usuario.auth_version` en cada operación. Desactivar/reset incrementa versión, revoca sesiones abiertas; `revocada_en` permite invalidar explícitamente. Logout cierra/rota cookie y audita.
- `last_seen` actualizado con frecuencia moderada y control de tiempo inactivo en sesión PHP; no generar escrituras innecesarias por cada recurso.

## Roles, presbiterio y sede (decisiones funcionales aprobadas E2-D01/D02)
- DIGITADOR: inicia sesión en un presbiterio autorizado. La consulta depende de **usuario.alcance_consulta**: `PRESBITERIO` filtra por presbiterios de `usuario_presbiterio`; `GENERAL` permite consultar registros nacionales para controles autorizados. **En cualquier ámbito edita solo las fichas que creó**. El backend aplica el filtro; ninguna autorización se decide solo en React.
- ADMIN y REPORTES: alcance nacional por rol; ADMIN edita conforme a sus facultades y cambia explícitamente contexto; REPORTES es de solo lectura para informes, con minimización de PII y GPS por defecto.
- **Consulta `GENERAL` no autoriza actualizar fichas ajenas ni administrar usuarios**. Las consultas nominales permitidas se registran según controles de privacidad; no se revelan coordenadas de otras sesiones en búsquedas comunes. `GET /personas/duplicados` no es una vía de enumeración pública de documentos.

## Geolocalización (sin cambio funcional)
Una solicitud opcional de `navigator.geolocation.getCurrentPosition` por sesión tras autenticación; persistir `geo_sesion` una vez, recuperarla tras refresh y reutilizar en auditorías. No bloquear si denegada, no usar GPS como prueba de presencia ni autorización.

## Auditoría
`login_evento` registra éxitos, fallos, bloqueo, logout con fecha, usuario solicitado, presbiterio e IP sin contraseñas. `auditoria_evento` registra actor, recurso, ID, antes/después, sesión/IP, georreferencia de sesión si existe. `created_by` inmutable al editar. ADMIN es actor de sus correcciones; REPORTES no ve coordenadas o datos nominales por defecto.

## Privacidad / puesta en producción
No PII en `localStorage`, URLs ni errores. Acceso con HTTPS; logs de aplicación minimizados. Retención de GPS, backups, aviso/información al titular y responsabilidades de tratamiento **siguen pendientes H-40**, condiciones previas a operar con datos reales. **Una app temporal no puede omitir protección de datos.**

## H-13 — claves documentales (regla aprobada)
Identidad exacta: DNI con 8 dígitos, preservando ceros; CE texto normalizado bajo verificación I02; OTRO subtipo obligatorio para comparación exacta. Si los datos documentales son ambiguos se conserva la captura sin clave documental para fusión, evitando falsos positivos. La coincidencia por nombre se limita a señalamiento de posible duplicidad. [Detalle](../03-base-datos/catalogos-e-identidad.md).
