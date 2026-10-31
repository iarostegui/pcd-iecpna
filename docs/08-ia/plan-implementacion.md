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
