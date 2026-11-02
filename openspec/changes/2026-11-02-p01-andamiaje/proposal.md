# P01 — Andamiaje ejecutable (propuesta)

## Motivo
P00 dejó la especificación V4.1 consistente. Antes de I00/I01 se necesita una base técnica ejecutable que demuestre el contrato HTTP, la configuración por entorno, el aprovisionamiento idempotente y un runner de pruebas reproducible, sin implementar procesos funcionales completos.

## Objetivo
Construir un andamiaje PHP 8.2 + React/TS/Vite que:
- arranque por HTTP y responda con el sobre JSON de OpenAPI;
- centralice errores, CSRF, CORS y PDO con consultas preparadas;
- aprovisione ADMIN, REPORTES y seis DIGITADORES por presbiterio con Argon2id e idempotencia;
- ejecute PHPUnit y un runner HTTP con estados PASS/FAIL/BLOCKED/NOT_IMPLEMENTED;
- compile el frontend sin Node residente en el hosting.

## Alcance
- Backend: `backend/` (Composer, PSR-4 `Pcd\` → `src/`).
- Frontend: `frontend/` (React, TypeScript, Vite, Fetch).
- Aprovisionamiento: `scripts/bootstrap-provision.php`.
- Pruebas: `backend/tests/`, `tests/api/run.php`.
- CI: jobs de documentación, backend con MySQL 8 y frontend.

## Exclusiones
- No implementa login, captura, consulta, reportes ni auditoría funcional (I01–I05).
- No modifica SQL, OpenAPI, prototipo V2.3 ni decisiones funcionales.
- No resuelve E1-D02, H-23 ni H-24.
- No usa datos personales reales ni contraseñas en Git.

## Criterio de aceptación
Los seis artefactos de P01 existen, el backend responde por HTTP, el frontend compila, PHPUnit y el runner se ejecutan con evidencia, y CI queda en verde. Las pruebas dependientes de MySQL solo son PASS cuando se ejecutan contra el motor real.
