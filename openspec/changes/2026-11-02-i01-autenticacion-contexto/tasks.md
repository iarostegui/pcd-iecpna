# I01 — Tareas y verificación

## 0. Puertas previas

- [x] Rama `feature/i01-autenticacion-contexto` activa y árbol limpio al inicio.
- [x] Leídos `AGENTS.md`, alcance, decisiones/aprobaciones V4.1, contrato de ejecución, plan I00–I06,
      OpenAPI, SQL físico, seguridad, casos de prueba, prototipo V2.3 y autorización condicionada de I00.
- [x] Confirmado que I00 solo autoriza desarrollo/CI con datos sintéticos; no habilita producción.
- [x] Confirmado modelo de 11 tablas/146 columnas/23 FK y ausencia de cambios de esquema.

## 1. SDD

- [x] `proposal.md` con motivo, alcance, exclusiones y trazabilidad.
- [x] `design.md` con arquitectura, sesión, CSRF, organización, GPS, frontend y riesgos.
- [x] `spec.md` con escenarios Given/When/Then y vínculo a CA.
- [x] `tasks.md` con actividades y evidencia.

## 2. Backend

- [x] Utilidades de normalización NFKD, validación, JSON canónico e IP.
- [x] Hashing Argon2id con fallback bcrypt documentado solo para desarrollo/CI sin soporte.
- [x] Repositorios de usuario, organización, sesión, `login_evento`, idempotencia y auditoría.
- [x] Servicio de autenticación con bloqueo 5/cuenta y 20/IP en 15 min.
- [x] Persistencia de sesión, cookie segura, regeneración, CSRF, inactividad 45 min y absoluta 12 h.
- [x] Revocación por `auth_version`, cuenta inactiva y logout real.
- [x] `GET /presbiterios` público y mínimo.
- [x] `GET /sedes` por presbiterio y búsqueda normalizada/similar.
- [x] `POST /sedes` con CSRF, idempotencia, normalización, 409 exacto y advertencia por similitud.
- [x] `PUT /contexto/sede` con validación de pertenencia.
- [x] `PUT /contexto/presbiterio` solo ADMIN, anulación de sede y auditoría.
- [x] `POST /auth/ubicacion` una vez por sesión con estados y sin exponer coordenadas.
- [x] Actualizar `App` y controladores; conservar 501 en rutas de I02–I06.

## 3. Frontend

- [x] Login con presbiterio, usuario escrito, contraseña vacía y sin listado de cuentas.
- [x] Selección/búsqueda de sede, alta con preaviso de similitud y manejo de 409.
- [x] Contexto autenticado, identidad, perfil, presbiterio/sede y logout.
- [x] Manejo de expiración/401 y limpieza de CSRF.
- [x] GPS opcional una vez por sesión con estados y sin `localStorage`.
- [x] Estados de carga y errores accesibles.
- [x] Cliente HTTP centralizado de P01 extendido solo con lo necesario.

## 4. Pruebas obligatorias

- [x] CA-01…CA-14.
- [x] CA-56…CA-60.
- [x] CA-91, CA-92 y CA-94.
- [x] CA-61…CA-67, CA-76, CA-84, CA-109, CA-110, CA-113, CA-114 y CA-123 como cobertura adicional I01.
- [x] Casos negativos: login incorrecto, presbiterio incorrecto, usuario inactivo, bloqueo, revocación,
      CSRF, sesión expirada, acceso sin autenticación, sede ajena, alta/duplicidad, concurrencia y GPS denegado.
- [x] Casos UX V2.3 aplicables (CA-101, CA-109, CA-110 y flujo React login/contexto).
- [ ] PHPUnit ejecutado contra MySQL 8 real en CI (local: MariaDB 11.8 de prueba).
- [x] Runner HTTP I01 ejecutado contra MySQL real de prueba, con evidencia JSON.
- [x] Vitest/Testing Library ejecutado y `npm run build` correcto.
- [x] Validadores SDD (`npm test`) ejecutados.

## 5. Cierre documental y publicación

- [x] OpenAPI actualizado solo con extensiones aditivas y respuestas coherentes.
- [x] READMEs, CHANGELOG, estado documental y matriz de trazabilidad actualizados.
- [ ] Commit en `feature/i01-autenticacion-contexto`.
- [ ] Push y evidencia de GitHub Actions.
- [ ] Pull Request Draft hacia `main`, sin merge.
- [ ] Informe final con PASS/FAIL/BLOCKED, defectos corregidos y restricciones pendientes.

## 6. Evidencia ejecutada (local, 02/11/2026)

| Comando / entorno | Resultado | Estado |
|---|---|---|
| `npm test` (raíz) | validadores SDD y prototipo | PASS |
| `cd backend && vendor/bin/phpunit` | 64 pruebas / 154 aserciones; integración con MariaDB 11.8 local | PASS |
| `php tests/api/i01.php --json=...` | 36 PASS / 0 FAIL / 0 BLOCKED; CA obligatorios y cobertura I01 | PASS |
| `php tests/api/run.php` | 23 PASS / 0 FAIL / 0 BLOCKED | PASS |
| `cd frontend && npm test` | 8 pruebas UX (login, contexto, expiración, GPS, 409, REPORTES) | PASS |
| `cd frontend && npm run build` | Vite build correcto | PASS |
| `php -l` sobre `src/tests/scripts` | sin errores de sintaxis | PASS |
| MySQL 8 / PHP 8.2 con Argon2id y CI | pendiente de ejecución remota | PENDIENTE |

**Nota:** el entorno local usa MariaDB 11.8 y PHP 8.2 sin Argon2id (fallback bcrypt costo 12 para
desarrollo). La evidencia válida de MySQL 8/PHP 8.2/Argon2id debe ser la ejecución de GitHub Actions;
no se declara PASS de motor MySQL 8 hasta obtenerla.
