# PCD V4 — Autenticación y sesión (vigente)

## AUTH-01 — Login por presbiterio
MUST solicitar presbiterio de `organizacion.tipo=PRESBITERIO`, código usuario escrito y contraseña. DIGITADOR solo ingresa a su presbiterio operativo `usuario.presbiterio_id`; ADMIN/REPORTES nacionales por rol. La consulta general no altera contexto de digitación.
### Scenario
GIVEN digitador asignado a Presbiterio A WHEN intenta iniciar sesión bajo B THEN responder 401 sin revelar credencial.

## AUTH-02 — Cuentas iniciales
MUST habilitar seis digitadores por presbiterio y una cuenta nacional ADMIN y REPORTES. Digitadores comparten contraseña inicial sin cambio forzado; cuentas nacionales con claves **distintas** de digitadores; hashes protegidos y secretos fuera del repositorio. Riesgo residual de suplantación reconocido.

## AUTH-03 — Geolocalización opcional única
MUST intentar geolocalización como máximo una vez por sesión, guardar estado y resultado en columnas `sesion.geo_*` y NO solicitar nuevamente después de refresh/cambio sede. La denegación no bloquea captura.
### Scenario
GIVEN sesión con geo_estado=DENEGADA WHEN se registran 10 fichas THEN no se vuelven a solicitar coordenadas y cada auditoría referencia la sesión original.

## AUTH-04 — Cierre y revocación
MUST invalidar sesión al logout/desactivación/reset de credenciales, comparar `sesion.auth_version_al_ingreso` con `usuario.auth_version`; registrar eventos en `login_evento`, cambios en `auditoria_evento`.

## AUTH-05 — Catálogo público mínimo
`GET /presbiterios` público expone SOLO ID/código/nombre activos de `organizacion`; nunca lista de usuarios.

## AUTH-06/07 — Medidas compensatorias
Propuesta de parámetros operativos 5 fallos/cuenta o 20/IP en 15 min → control temporal sin enumeración/DoS; sesión cookie HttpOnly/Secure/SameSite=Lax, HTTPS y CSRF.

## AUTH-08 — Vigencia
Inactividad 45 minutos y absoluto 12 horas como defaults de diseño sujetos a prueba. `GET /auth/me` devuelve CSRF y contexto, sin datos GPS sensibles.

## AUTH-09 — Contexto nacional
ADMIN puede seleccionar explícitamente presbiterio/sede para escribir; REPORTES consulta informes nacionales sin obligar sede, solo lectura. Sin tabla de autorizaciones de lectura territorial.

**BD:** usuario, sesion, login_evento, organizacion, auditoria_evento.
