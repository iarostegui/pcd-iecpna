# I01 — Autenticación y contexto operativo (propuesta)

**Iteración:** I01 · **Rama:** `feature/i01-autenticacion-contexto` · **Base:** P00/P01 aprobados e I00 con GO de desarrollo condicionado a datos sintéticos.

## 1. Motivo

P01 entregó un andamiaje ejecutable sin procesos funcionales. I01 es la primera capacidad funcional del plan
[I00–I06](../../../docs/08-ia/plan-implementacion.md): permitir el acceso real a PCD, fijar el contexto de
presbiterio/sede, registrar la sesión y habilitar la geolocalización opcional de una sola captura por sesión.
Sin I01 ninguna capacidad posterior (Persona, GC, consulta, reportes) puede autenticarse ni heredar contexto.

## 2. Objetivo

Implementar de extremo a extremo, con datos exclusivamente sintéticos:

- login con presbiterio activo, código de usuario **tecleado** y contraseña verificada contra MySQL;
- sesiones persistentes en `sesion`, cookie segura, CSRF, expiración por inactividad/absoluta, revocación por
  `auth_version` y logout real;
- mitigación de fuerza bruta por cuenta e IP con bitácora en `login_evento`;
- consulta pública de presbiterios fijos y consulta/selección de sedes del presbiterio autenticado;
- alta de sede con normalización RN-33, deduplicación exacta (409) y advertencia por similitud;
- cambio explícito de contexto de presbiterio para ADMIN y exclusión de escritura para REPORTES;
- geolocalización opcional una vez por sesión, con estados `OK`, `DENEGADA`, `ERROR` y `NO_DISPONIBLE`,
  sin bloquear operaciones ni usarse como prueba de autoría;
- pantallas React de login, contexto, sede, alta de sede, usuario autenticado, cierre y expiración.

## 3. Alcance funcional y trazabilidad

| Requisito vigente | Contrato / caso |
|---|---|
| AUTH-01 login por presbiterio y usuario tecleado | OpenAPI `POST /auth/login`; CA-01, CA-02, CA-57 |
| AUTH-02 cuentas iniciales y secretos separados | CA-03, CA-04, CA-06, CA-58 |
| AUTH-03 GPS opcional único | OpenAPI `POST /auth/ubicacion`; CA-36…CA-39, CA-76, CA-87, CA-94, CA-109 |
| AUTH-04 cierre y revocación | CA-61…CA-65; contrato V4.1 §4 |
| AUTH-05 catálogo público mínimo | OpenAPI `GET /presbiterios`; CA-56 |
| AUTH-06/07 límites y CSRF | CA-07, CA-59, CA-60, CA-63; SEC-PCD-4.1 |
| AUTH-08 vigencia de sesión | CA-61, CA-62, CA-113; H-32/H-46 |
| AUTH-09 contexto nacional ADMIN/REPORTES | OpenAPI `PUT /contexto/presbiterio`; CA-66, CA-67 |
| AUTH-10 presbiterios fijos | CA-115; RN-30 |
| AUTH-11 rotación productiva | CA-120; se mantiene como gate, no se resuelve en I01 |
| CAT-01/02/03/08/09 organización y sedes | OpenAPI `GET/POST /sedes`, `PUT /contexto/sede`; CA-08…CA-14, CA-92, CA-116, CA-123 |
| ALC-10 ubicación y bitácora | CA-41, CA-47, CA-110, CA-124 |

## 4. Decisiones respetadas

- Se mantiene el modelo físico de **11 tablas, 146 columnas y 23 FK**; no se crean tablas ni columnas.
- LIM/TRU/CHI/IQU son fijos y no tienen CRUD; el alta de sede sí está en alcance.
- No se resuelven E1-D02, H-23 ni H-24; no se implementa I02–I06 ni SGSP.
- No se usan datos personales reales, coordenadas reales ni credenciales en Git.
- La geolocalización es telemetría autodeclarada: no prueba autoría ni restringe acceso.
- Se conserva el contrato V4.1 y se permiten únicamente extensiones **aditivas y opcionales** en DTO de
  `AuthMe` para que React pueda mostrar contexto y evitar repetir la solicitud GPS tras recargar. No cambia
  rutas, códigos de estado ni semántica existente.

## 5. Exclusiones

- Captura Persona/Ficha, Grupos Crecer, consulta de fichas, reportes, administración de usuarios y catálogos
  críticos: pertenecen a I02–I05.
- Resolución de E1-D02/H-23/H-24 y cualquier flujo de supervisor, conciliación o integrantes nominales.
- Cambios de visibilidad/licencia del repositorio, cPanel real, backups o carga de datos reales (H-40/E-07).
- Nuevas tablas, columnas, índices de reportes o migraciones distintas de `001`/`002`.

## 6. Criterio de aceptación

I01 se considera verificable cuando:

1. los artefactos SDD existen y enlazan requisitos, OpenAPI, SQL y casos de prueba;
2. PHPUnit y el runner HTTP I01 se ejecutan contra MySQL 8 real sin FAIL;
3. las pruebas UX automatizadas de React se ejecutan y el build compila;
4. los casos obligatorios CA-01…CA-14, CA-56…CA-60, CA-91, CA-92 y CA-94 tienen resultado explícito;
5. el commit se publica en la rama y se abre PR Draft hacia `main`, sin merge.

Cualquier imposibilidad de ejecutar un caso se reporta `BLOCKED`, nunca `PASS`.
