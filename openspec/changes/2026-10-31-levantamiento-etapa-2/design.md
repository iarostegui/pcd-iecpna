> **HISTÓRICO — NO USAR COMO REQUISITO VIGENTE:** este documento describe decisiones/análisis de PCD V3 de 31/10/2026. El responsable aprobó posteriormente **PCD V4: 11 tablas**, consulta **total por funcionalidad** con filtros presbiterio/sede, documentos **solo tipo+número sin subtipo**, tabla `catalogo`, `organizacion` jerárquica y `ubigeo` único. Las referencias a 17 tablas, `alcance_consulta`, `usuario_presbiterio`, subtipo u otras decisiones contradictorias **están reemplazadas**, sin alterar aquí el registro histórico. Fuente de verdad: [Decisiones actuales](../../docs/01-analisis/decisiones-pendientes.md), [SQL 11 tablas](../../database/migrations/001_initial.sql) y [specs vigentes](../../openspec/specs/README.md). En caso de enlaces relativos distintos, localizar los mismos documentos desde README principal.

# E2 — Diseño propuesto para implementación PHP/MySQL
## Autenticación
1. `GET /presbiterios` sin cookie, respuesta limitada a identificadores/nombres activos.
2. Usuario **escrito** en un input, no enumeración de cuentas; contraseña de digitadores común sin cambio inicial obligado; ADMIN y REPORTES con secretos individuales.
3. Por intentos fallidos (15 min por usuario/IP) aplicar límites iniciales propuestos 5/20; sin revelar existencia de cuenta; usar `login_evento` e índices IP/fecha/código.
4. Sesión de servidor con inactividad 45 min, absoluto 12 h, cookies seguras; `auth_version` revoca por reset/cambio de cuenta; `revocada_en` revoca sesiones, logout invalida cookie.
5. `GET /auth/me` permite recuperar CSRF y contexto sin mostrar contraseña/GPS detallado; ubicación GPS una vez/sesión opcional.

## Presbiterio/ámbito
DIGITADOR se limita al presbiterio del login y fichas de ese ámbito, edita solo las propias; ADMIN/REPORTES nacionales por perfil sin filas para cada presbiterio. ADMIN puede cambiar presbiterio mediante acción explícita; REPORTES reporta nacional sin sede. **E2-D01/D02 pendientes de aprobación funcional**.

## Identidad
`clave_identidad` UNIQUE (nullable) calculada por tipo+subtipo+número normalizado. DNI de 8 dígitos; CE/OTRO con criterios documentados y pendientes oficiales; OTRO sin subtipo no se fusiona. [Documento](../../../../docs/03-base-datos/catalogos-e-identidad.md).

## Calidad
Catálogo `campo_critico` con columna `desactivable`; semillas según prototipo; no persistir marca fija de calidad, cálculo al consultar. [Matriz](../../../../docs/03-base-datos/campos-criticos.md).

## GC
Única referencia de líder: `ficha_captura.persona_id`. Ficha GC debe ser tipo GC para asociar `grupo_crecer_captura`; responsabilidades y cónyuge solo en ficha PERSONA. Validación transaccional PHP + FK compuesta/CHECK donde sea compatible con servidor cPanel. No integrantes nominales.

## Fuera del alcance
No proceso automático de conciliación de texto con papel ni SSO; revisión manual presbiterial externa. No se implementa SGSP.
