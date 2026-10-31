# Plan de desarrollo por IA — implementación PCD (no SGSP)
## I00 — Preparación
Confirmar versiones cPanel; crear rama de trabajo, configurar CI/lint/tests, validar SQL de prueba y contrato. No usar datos reales.

## I01 — Seguridad/contexto
Backend PHP login, sesión, roles, usuario_presbiterio, sedes, geolocalización snapshot único, logs; React Login y selector. Pruebas CA-01..14 y CA-36..41.

## I02 — Persona
Capturar campos del Excel, componentes React desde V2.2; lookup documento, datos incompletos, ficha opcional, idempotencia, auditoría y control de concurrencia. Pruebas CA-15..28.

## I03 — Grupo Crecer
GC solo tras Persona o desde Consulta; grupo existente/nuevo, líder persistido; CA-29..31.

## I04 — Consulta y edición
Buscar, filtrar, editar solo propios, Admin todos; historial, paginación, permisos; CA-32..35,43.

## I05 — Administración/calidad/reportes
Usuarios por presbiterio, criticidad dinámica, reportes por presbiterio/sede/usuario/fecha; CA-03,21,44,45.

## I06 — Seguridad/performance/despliegue
Completar IDOR/CSRF/XSS, backups, REST/OpenAPI, pruebas carga, publicación en cPanel; CA-46..52.

## Control
Cada iteración: actualizar specs y código → ejecutar pruebas unitarias/API/E2E → reportar evidencia y diffs → correcciones → aprobación humana del lote. No modificar SGSP ni publicar prototipo como real. El build producción no necesita Node en hosting.

## Auditoría E1: puertas de entrada
La [matriz de preparación](../07-auditorias/preparacion-iteraciones.md) precisa estados. **I00 está habilitada solo para comenzar tareas técnicas**; I01 requiere cierre de seguridad/sesiones, I02/I03/I04 requieren aprobar deltas E1 y sincronizar API, SQL, casos de prueba. Las propuestas de diseño no equivalen a GO productivo.

## Aclaraciones funcionales aprobadas de la auditoría E1
En I02, validar fechas reales en React y PHP; si valor imposible, exigir corregir o vaciar el campo, pudiendo guardar incompleto. No persistir fechas imposibles con tipo fecha ni crear mecanismo de excepción. La revisión de fidelidad `JUANA`/Juan o `JU4N4` se hará **manualmente por presbiterio, fuera de PCD**, no existe motor/flujo de conciliación. I03 **solo registra líderes de GC y datos generales**, nunca integrantes nominales. E1-D01/E1-D02 y sincronización SQL/API/test siguen pendientes para I02/I03.

## E2 — plan de iteración I01 listo para preparar en modo Plan, no para publicarlo sin decisiones
1. Crear cambio OpenSpec I01 con controles AUTH-05…AUTH-09 y CAT-01…03; especificar el comportamiento de E2-D02 o registrarlo expresamente pendiente.
2. Confirmar mecanismo de sesión cookie/CSRF, login público de presbiterios y campo de usuario **escrito** (no desplegable). ADMIN/REPORTES con contraseñas individuales.
3. Programar solo tras decisión: PHP login/logout/me, throttling, sesiones 45m/12h y revocación, `usuario_presbiterio` y contexto sede, geo_sesion única por sesión; React pantalla/selector siguiendo V2.2.
4. **Pruebas obligatorias:** CA-01…14, CA-36…41, CA-56…67 y CA-76 (incluyendo 401, 403, 429, CSRF y sesiones revocadas).
5. Antes de declarar I01 LISTA: ejecutar SQL inicial y semilla en versión real MySQL/MariaDB, lint OpenAPI y evidencia de pruebas API/E2E. No afirmar ejecución sin evidencias.
6. E2-D01 de consulta nominal se reserva principalmente a I04, E2-D03 es aceptación de riesgo antes de producción, no obliga a implantar infraestructura fuera de alcance.
Ver [Etapa 2](../07-auditorias/etapa-2-arquitectura-seguridad-datos.md) y [casos](../05-pruebas/casos-prueba.md).

## Actualización posterior: decisiones aprobadas
H-12 se construirá con `alcance_consulta` por usuario (GENERAL/PRESBITERIO), **independiente de permisos de modificación**. H-13 validación de identidad DNI/CE/OTRO aprobada; fuente INEI del ubigeo se incorporará durante I02 como **entrada de construcción**. I00 debe crear **dos anexos del modelo físico sincronizados con SQL** y verificar columna por columna. H-28 y controles H-03/H-32/H-35 aceptados en diseño, pendientes de ejecución y pruebas.

## Entregables del diseño físico I00
Se generan [Anexo 01](../03-base-datos/anexos/anexo-01-diagrama-fisico.md) y [Anexo 02](../03-base-datos/anexos/anexo-02-diccionario-fisico.md) directamente desde el SQL; no se ha ejecutado aún el SQL en un servidor real.

## Etapa 3 registrada
[Informe E3](../07-auditorias/etapa-3-contratos-pruebas.md), [OpenAPI 3.2](../04-integracion/openapi.yaml), [trazabilidad RN→CU→UI→API→SQL→CA](matriz-trazabilidad.md) y [asignación de pruebas](../05-pruebas/asignacion-iteraciones.md). I01 se planifica con contratos más completos; I02/I03 dependen de E1-D01/E1-D02 y decisiones H-23/H-24. **No desarrollar módulos extra por observaciones fuera de ALC-PCD.**

## Ajuste RN-29 aprobado
La consulta `GENERAL/PRESBITERIO` es un atributo explícito de cuenta (`usuario.alcance_consulta`), independiente de la edición. [Spec REP-09](../../openspec/specs/reportes/spec.md) y [ADM-06](../../openspec/specs/administracion/spec.md). I04 debe probar CA-77/78; I05 cambia permisos de lectura, audita y revalida sesiones.
