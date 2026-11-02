# I01 — Especificación verificable (Given/When/Then)

**Capacidades:** autenticación, sesión, contexto operativo, organización/sedes y geolocalización opcional.
**Contratos:** [specs vigentes](../../../specs/README.md), [OpenAPI](../../../docs/04-integracion/openapi.yaml),
[SQL de 11 tablas](../../../database/migrations/001_initial.sql),
[seguridad](../../../docs/02-diseno/seguridad-auditoria.md) y
[contrato V4.1](../../../docs/08-ia/CONTRATO-EJECUCION-V4.1.md).

## AUTH-01 — Login con presbiterio, usuario tecleado y contraseña

- **I01-AUTH-01.1** GIVEN un DIGITADOR activo asignado al presbiterio A WHEN envía su código y contraseña
  correctos con el presbiterio A THEN recibe 200 con `AuthMe`, cookie de sesión y evento `OK` en
  `login_evento` (CA-01, CA-57).
- **I01-AUTH-01.2** GIVEN una solicitud sin `presbiterio_id` WHEN se procesa el login THEN responde 422 y no
  crea sesión ni cookie (CA-01).
- **I01-AUTH-01.3** GIVEN un DIGITADOR del presbiterio A WHEN intenta login con el presbiterio B THEN responde
  401 con el mismo mensaje que una contraseña incorrecta, sin revelar que la cuenta existe (CA-02, CA-57).
- **I01-AUTH-01.4** GIVEN un usuario inactivo WHEN intenta login THEN responde 401 genérico y registra el
  intento fallido sin crear sesión (CA-05).
- **I01-AUTH-01.5** GIVEN una cuenta inexistente y una existente con contraseña incorrecta WHEN se comparan
  las respuestas THEN ambas tienen el mismo estado, código y mensaje (CA-57, CA-84).

## AUTH-02 — Cuentas, secretos y hashing

- **I01-AUTH-02.1** GIVEN el seed y el aprovisionamiento UAT WHEN se consulta MySQL THEN existen 24
  DIGITADORES activos (6 por presbiterio), 1 ADMIN y 1 REPORTES (CA-03, CA-04, CA-115).
- **I01-AUTH-02.2** GIVEN la contraseña común UAT WHEN se revisa `usuario.password_hash` THEN no aparece en
  texto claro; el hash es Argon2id o, solo en desarrollo/CI sin soporte, bcrypt fuerte documentado
  (CA-06, CA-114).
- **I01-AUTH-02.3** GIVEN las credenciales de DIGITADOR, ADMIN y REPORTES WHEN se intenta usar la clave de un
  perfil en otro THEN el login falla y los hashes son distintos (CA-58).
- **I01-AUTH-02.4** GIVEN una respuesta de login, error o log WHEN se inspecciona THEN no contiene contraseña,
  hash, token CSRF ni coordenadas (CA-47, CA-124).

## AUTH-03 — Sesión, CSRF, expiración y revocación

- **I01-AUTH-03.1** GIVEN un login exitoso WHEN se inspecciona la cookie THEN es `HttpOnly`, `SameSite=Lax`,
  `Path=/` y `Secure` en HTTPS/producción; el identificador se regenera al autenticar (CA-36, AUTH-06/07).
- **I01-AUTH-03.2** GIVEN una sesión autenticada WHEN se consulta `GET /auth/me` THEN devuelve usuario,
  perfil, presbiterio, sede, token CSRF y vencimientos, sin coordenadas (CA-63, CA-110).
- **I01-AUTH-03.3** GIVEN una escritura autenticada WHEN falta el token CSRF o es inválido THEN responde 403
  `csrf_invalid` y no modifica datos (CA-63).
- **I01-AUTH-03.4** GIVEN una sesión sin actividad por más de 45 min WHEN se usa THEN responde 401
  `session_expired`, la sesión queda revocada y se exige nuevo login (CA-61).
- **I01-AUTH-03.5** GIVEN una sesión con más de 12 h desde `fecha_login` WHEN se usa THEN responde 401
  `session_expired` aunque haya actividad reciente (CA-62).
- **I01-AUTH-03.6** GIVEN un usuario desactivado o con `auth_version` incrementado WHEN usa una sesión
  anterior THEN responde 401 `session_revoked` y la sesión queda revocada (CA-64, CA-65, CA-76).
- **I01-AUTH-03.7** GIVEN varios GET sucesivos en la misma sesión WHEN se revisa `sesion.last_seen` THEN no se
  actualiza en cada request; la inactividad sigue funcionando (CA-113).
- **I01-AUTH-03.8** GIVEN una sesión autenticada WHEN se ejecuta logout con CSRF THEN responde 204, registra
  `LOGOUT`, marca `fecha_logout`/`revocada_en` y `GET /auth/me` responde 401 (CA-63).
- **I01-AUTH-03.9** GIVEN una ruta autenticada WHEN se accede sin cookie THEN responde 401 antes de evaluar
  CSRF o autorización (CA-84).

## AUTH-04 — Límites de intentos

- **I01-AUTH-04.1** GIVEN 5 fallos del mismo código de cuenta en 15 min WHEN se intenta un sexto login THEN
  responde 429 sin revelar si la cuenta existe y registra `BLOQUEADO` en `login_evento` (CA-07, CA-59).
- **I01-AUTH-04.2** GIVEN 20 fallos desde la misma IP en 15 min WHEN se intenta otro login THEN responde 429;
  la mitigación es temporal y no bloquea permanentemente una IP compartida (CA-60).
- **I01-AUTH-04.3** GIVEN una cuenta existente y una inexistente WHEN ambas superan el umbral THEN las
  respuestas 429 son indistinguibles (CA-59).

## AUTH-05 — Catálogo público mínimo

- **I01-AUTH-05.1** GIVEN una solicitud anónima a `GET /presbiterios` WHEN responde THEN devuelve solo
  `id`, `codigo` y `nombre` de LIM/TRU/CHI/IQU activos, sin usuarios ni datos personales (CA-56, CA-115).
- **I01-AUTH-05.2** GIVEN el contrato vigente WHEN se revisan rutas THEN no existe POST/PATCH/DELETE de
  presbiterios (RN-30).

## AUTH-06 — Contexto ADMIN y REPORTES

- **I01-AUTH-06.1** GIVEN un ADMIN autenticado WHEN ejecuta `PUT /contexto/presbiterio` con un presbiterio
  activo THEN la sesión cambia de presbiterio, la sede queda NULL y se registra auditoría con las claves
  cambiadas (CA-66).
- **I01-AUTH-06.2** GIVEN un DIGITADOR o REPORTES WHEN intenta cambiar el presbiterio THEN responde 403
  (CA-66, CA-67).
- **I01-AUTH-06.3** GIVEN un REPORTES autenticado WHEN intenta crear sede o cambiar contexto THEN responde
  403; su acceso es de solo lectura (CA-67, CA-111).

## CAT-01/02/03/08/09 — Organización y sedes

- **I01-CAT-01.1** GIVEN una sesión en el presbiterio A WHEN consulta `GET /sedes` THEN solo recibe sedes
  activas con padre A (CA-08).
- **I01-CAT-01.2** GIVEN una sesión en el presbiterio A WHEN intenta `PUT /contexto/sede` con una sede del
  presbiterio B THEN responde 403 y no modifica la sesión (CA-09, CA-92).
- **I01-CAT-01.3** GIVEN una sede nueva válida WHEN se crea con CSRF e idempotencia THEN responde 201, queda
  disponible y se puede seleccionar sin aprobación (CA-10).
- **I01-CAT-02.1** GIVEN una sede existente WHEN se crea otra con el mismo nombre normalizado (tildes,
  mayúsculas o espacios distintos) en el mismo presbiterio THEN responde 409 y no inserta duplicado
  (CA-11, CA-116, CA-123).
- **I01-CAT-02.2** GIVEN un nombre solo similar WHEN se crea la sede THEN responde 201 con advertencia y no
  bloquea un nombre legítimamente distinto (CA-12).
- **I01-CAT-02.3** GIVEN dos creaciones simultáneas del mismo nombre normalizado WHEN ambas llegan THEN solo
  una crea la sede; la otra recibe 409 (CA-13).
- **I01-CAT-02.4** GIVEN dos claves de idempotencia distintas y el mismo payload WHEN se reintenta THEN no se
  duplica; con la misma clave y payload distinto responde 409 (CA-112).
- **I01-CAT-03.1** GIVEN una sesión con sede A WHEN selecciona sede B del mismo presbiterio THEN conserva el
  presbiterio y no vuelve a solicitar GPS (CA-14, CA-87).
- **I01-CAT-03.2** GIVEN una sede de otro presbiterio WHEN se intenta usar en una ficha THEN el esquema
  `fk_ficha_sede(sede_id,presbiterio_id)` impide la inserción (CA-92).

## AUTH-03 — Geolocalización opcional

- **I01-GEO-01** GIVEN una sesión sin `geo_estado` WHEN React solicita ubicación una vez THEN puede guardar
  `OK`, `DENEGADA`, `ERROR` o `NO_DISPONIBLE` en la única fila `sesion` (CA-36, CA-94).
- **I01-GEO-02** GIVEN una sesión con `geo_estado` registrado WHEN se envía otra ubicación THEN responde 409
  con la primera captura y no sobrescribe; el mismo login no vuelve a pedirla (CA-38, CA-39, CA-94, CA-109).
- **I01-GEO-03** GIVEN una ubicación denegada o no disponible WHEN se registran operaciones posteriores THEN
  no se bloquean y no se solicitan coordenadas por cada ficha (CA-39, CA-87).
- **I01-GEO-04** GIVEN una respuesta autenticada normal WHEN se inspecciona THEN no expone latitud, longitud
  ni precisión; solo `geo_estado` para evitar repetir la solicitud (CA-110, CA-124).
- **I01-GEO-05** GIVEN una coordenada WHEN se guarda THEN es telemetría autodeclarada, con hora de recepción
  del servidor; no se usa como prueba de autoría ni restricción de acceso (ALC-10, CA-124).

## UI — Pantallas y comportamiento React

- **I01-UI-01** GIVEN la pantalla de login WHEN se abre THEN no hay listado de cuentas; el código es texto y
  la contraseña está vacía (CA-101).
- **I01-UI-02** GIVEN credenciales inválidas WHEN se envía el formulario THEN se muestra un error genérico sin
  enumerar cuentas; con 429 se informa el límite temporal (CA-57, CA-59).
- **I01-UI-03** GIVEN un login exitoso sin sede WHEN se muestra el contexto THEN solo aparecen sedes del
  presbiterio y se puede crear una nueva o seleccionar una existente (CA-08, CA-10).
- **I01-UI-04** GIVEN un error 409 de sede WHEN se intenta crear THEN se muestra el duplicado sin crear otra
  sede; una similitud se muestra como advertencia (CA-11, CA-12).
- **I01-UI-05** GIVEN un usuario autenticado WHEN se muestra el espacio de trabajo THEN se ven su identidad,
  perfil, presbiterio/sede y la acción de cerrar sesión; no se simulan funcionalidades I02–I06.
- **I01-UI-06** GIVEN una sesión expirada o revocada WHEN cualquier llamada responde 401 THEN se vuelve al
  login con mensaje de expiración y se limpia el CSRF en memoria (CA-61, CA-62, CA-65).
- **I01-UI-07** GIVEN cualquier pantalla WHEN se inspecciona el navegador THEN no hay sesión, credenciales ni
  coordenadas en `localStorage`; el CSRF solo vive en memoria (CA-47).
- **I01-UI-08** GIVEN un GPS autorizado, denegado o no disponible WHEN termina el intento THEN no se habilita
  un reintento en la misma sesión ni se solicita de nuevo al cambiar de sede (CA-109, CA-87).

## Criterios transversales

- **I01-CT-01** Toda respuesta de error usa `{error:{code,message,details?},request_id}` sin trazas ni PII
  (CA-84).
- **I01-CT-02** No se agregan tablas/columnas; se mantienen 11 tablas, 146 columnas y 23 FK (CA-91, CA-122).
- **I01-CT-03** No se implementan I02–I06, SGSP, E1-D02/H-23/H-24 ni CRUD de presbiterios.
- **I01-CT-04** Las pruebas obligatorias se ejecutan contra MySQL 8 real; sin ejecución no se declara PASS.
