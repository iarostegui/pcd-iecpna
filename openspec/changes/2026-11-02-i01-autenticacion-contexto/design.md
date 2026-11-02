# I01 — Diseño técnico

## 1. Principios

1. **Backend autoriza, React solo presenta.** Toda regla de perfil, presbiterio, sede y sesión se revalida en
   PHP contra MySQL; ocultar un control en React no sustituye el control.
2. **Sin cambios de modelo.** Se usan exactamente las tablas/columnas de `database/migrations/001_initial.sql`.
   No se agregan tablas, columnas, índices ni migraciones.
3. **Sesión de servidor.** La cookie contiene solo un identificador aleatorio; en `sesion` se guarda
   `session_ref_hash = SHA-256(cookie)`. El token CSRF vive en la sesión PHP de servidor, nunca en
   `localStorage` ni en la URL.
4. **Contratos OpenAPI.** Se respetan rutas, DTO, estados 200/201/204/400/401/403/409/422/429. Las únicas
   extensiones son propiedades opcionales aditivas en `AuthMe`/`Sede` para la UX y se documentan en
   `docs/04-integracion/openapi.yaml`.

## 2. Componentes backend

| Componente | Responsabilidad |
|---|---|
| `Support\TextNormalizer` | NFKD + eliminación de diacríticos + mayúsculas + espacios compactados (RN-33). Fallback ASCII documentado si `intl` no está disponible. |
| `Support\Json` | Canonicalización para hash de idempotencia y decodificación segura. |
| `Support\Validation` | Validación de cuerpos/query con errores 422 uniformes. |
| `Support\Ip` | IP de `REMOTE_ADDR` (sin confiar en cabeceras de proxy) y `user_agent` acotado. |
| `Security\PasswordHasher` | Argon2id obligatorio en producción; fallback bcrypt de costo 12 solo en desarrollo/CI cuando no exista Argon2id (`PCD_ALLOW_BCRYPT_FALLBACK=true`). Verificación con `password_verify`. |
| `Security\PhpSession` | Cookie `HttpOnly`, `SameSite=Lax`, `Secure` en HTTPS/producción, `Path=/`; `session_regenerate_id(true)` al autenticar. |
| `Security\Csrf` | Token por sesión, comparación `hash_equals`, rotación al login y destrucción al logout. |
| `Repository\UsuarioRepository` | Búsqueda por código/perfil/estado/`auth_version`; sin exponer hash en respuestas. |
| `Repository\OrganizacionRepository` | Presbiterios activos, sedes por padre, validación jerárquica y alta con UNIQUE. |
| `Repository\SesionRepository` | Persistencia, búsqueda por hash, `last_seen` acotado, contexto, logout/revocación. |
| `Repository\LoginEventoRepository` | Registro OK/FALLIDO/BLOQUEADO/LOGOUT y conteos por código/IP en ventana. |
| `Repository\IdempotenciaRepository` | Resultado reutilizable por usuario + clave + hash de payload. |
| `Repository\AuditoriaRepository` | Evento de cambio de contexto ADMIN, sin PII innecesaria. |
| `Service\AuthService` | Credenciales, bloqueo, creación/validación de sesión, revocación, contexto, GPS y DTO `AuthMe`. |
| `Service\SedeService` | Listado, similitud, alta normalizada, deduplicación e idempotencia. |
| `Controller\AuthController` | `login`, `me`, `logout`, `ubicacion`. |
| `Controller\OrganizacionController` | `GET /presbiterios`, `GET/POST /sedes`. |
| `Controller\ContextoController` | `PUT /contexto/presbiterio`, `PUT /contexto/sede`. |

## 3. Sesión, cookie y CSRF

- Nombre de cookie: `__Host-pcd_sid` en producción; `pcd_dev_sid`/`pcd_test_sid` en desarrollo/CI para poder
  usar HTTP local. `PCD_COOKIE_SECURE` fuerza `Secure`; en producción es obligatorio.
- Al autenticar: se revoca cualquier sesión previa del mismo navegador, se regenera el ID, se rota el token
  CSRF y se inserta una fila `sesion` con `session_ref_hash`, `fecha_login`, `last_seen`, IP, user-agent y
  `auth_version_al_ingreso`.
- En cada request autenticado: se busca por hash, se valida logout/revocación, cuenta activa,
  `auth_version`, inactividad (45 min) y vida absoluta (12 h). La inactividad se mide contra `last_seen`.
- `last_seen` se actualiza como máximo una vez cada `PCD_SESSION_TOUCH_SECONDS` (60 s por defecto) con un
  `UPDATE ... WHERE last_seen IS NULL OR last_seen < ?`, cumpliendo H-46/CA-113.
- Errores de sesión: 401 `session_expired` (inactividad/absoluta) o `session_revoked` (cuenta/versión), sin
  datos del usuario. La cookie se invalida.
- Logout: auth + CSRF, marca `fecha_logout`/`revocada_en`, registra `login_evento` LOGOUT, destruye la sesión
  PHP y responde 204.
- Orden de controles en escrituras: **401 autenticación → 403 CSRF → 403 autorización → 422 validación**.

## 4. Login y mitigación de fuerza bruta

- Entrada: `presbiterio_id` activo, `usuario` (trim + mayúsculas, 1…80) y `password` (sin trim, 1…200).
- Mensaje único `invalid_credentials` para usuario inexistente, contraseña incorrecta, presbiterio ajeno o
  cuenta inactiva. Se ejecuta `password_verify` también cuando el usuario no existe, contra un hash ficticio
  generado con el mismo algoritmo, para reducir diferencias de tiempo.
- Límites por ventana de 15 min: 5 fallos por código de cuenta y 20 por IP. Al superar el umbral se registra
  `BLOQUEADO` y se responde 429 genérico. La ventana es temporal: no hay bloqueo permanente.
- Cada intento se registra en `login_evento` con código intentado, usuario resuelto (si existe), presbiterio,
  resultado, IP y user-agent. Nunca se registra la contraseña.
- Éxito: `login_evento` OK + `sesion` + `AuthMe`. Fallo: `login_evento` FALLIDO + 401/429 genérico.

## 5. Organización, sedes y contexto

- `GET /presbiterios` es público y devuelve solo `id`, `codigo`, `nombre` de nodos activos. No lista usuarios.
- `GET /sedes` exige sesión y devuelve solo sedes activas cuyo `padre_id` es el presbiterio de la sesión.
  `q` filtra con la misma normalización; `similares` advierte coincidencias no exactas.
- `POST /sedes` exige sesión, CSRF, `Idempotency-Key` y perfil DIGITADOR/ADMIN. Normaliza, rechaza duplicado
  exacto con 409 (incluida la carrera UNIQUE), crea con 201 y devuelve `advertencia` si hay similitud.
  La idempotencia devuelve la misma respuesta para la misma clave+payload y 409 si la clave cambia de payload.
- `PUT /contexto/sede`: valida sede activa y padre igual al presbiterio de la sesión; 403 si no pertenece.
  Actualiza solo `sesion.sede_id` y conserva el presbiterio.
- `PUT /contexto/presbiterio`: solo ADMIN; valida presbiterio activo; actualiza presbiterio y anula sede;
  registra un evento de auditoría con solo las claves cambiadas. DIGITADOR/REPORTES reciben 403.
- REPORTES no crea sedes ni cambia contexto; solo lectura.

## 6. Georreferenciación

- `POST /auth/ubicacion` exige sesión y CSRF. Acepta `estado`, coordenadas opcionales y `capturada_en`.
- Solo se persiste la primera captura de la sesión. Si ya existe `geo_estado`, responde 409 con la ubicación
  original en `details`; no sobrescribe.
- `OK` exige latitud/longitud válidas; `DENEGADA`, `ERROR` y `NO_DISPONIBLE` guardan `geo_capturada_en` con la
  hora de recepción del servidor y coordenadas NULL. El CHECK de `sesion` se respeta.
- No se devuelven coordenadas en `/auth/me` ni en DTO nominales. `AuthMe` solo expone `geo_estado` para que
  React no repita la solicitud tras recargar.
- React solicita la ubicación como máximo una vez por sesión y guarda únicamente un marcador booleano en
  `sessionStorage`; nunca coordenadas, credenciales ni sesión.

## 7. Frontend React/TS/Vite

- `LoginScreen`: presbiterio desde `GET /presbiterios`, código de usuario como `<input type="text">`,
  contraseña vacía, sin listado de cuentas, estados de carga/error y mensaje específico de 429.
- `ContextScreen`: presbiterio de la sesión; selector/buscador de sedes; alta de sede con preaviso de
  similitudes; para ADMIN, cambio explícito de presbiterio.
- `Workspace`: identidad del usuario autenticado, perfil, presbiterio/sede, estado GPS, cierre de sesión.
  No simula formularios de I02–I06; informa que las capacidades de captura pertenecen a iteraciones posteriores.
- `App`: arranque con `GET /auth/me`, restauración de CSRF en memoria, detección de 401 y expiración,
  temporizador de sesión, cliente HTTP centralizado de P01 (`credentials: include`, sin `localStorage`).
- Accesibilidad: etiquetas asociadas, `aria-live` para estados, foco visible, mensajes de error con
  `role="alert"`.

## 8. API implementada en I01

| Ruta | Auth | CSRF | Resultado |
|---|---|---|---|
| `GET /health`, `GET /ready` | No | No | Se mantienen de P01. |
| `POST /auth/login` | No | No | 200 `AuthMe`; 401; 422; 429. |
| `GET /auth/me` | Sí | No | 200 `AuthMe`; 401. |
| `POST /auth/logout` | Sí | Sí | 204; 401/403. |
| `POST /auth/ubicacion` | Sí | Sí | 200 `Location`; 409; 401/403/422. |
| `GET /presbiterios` | No | No | 200 `PresbiterioPublico[]`. |
| `GET /sedes` | Sí | No | 200 `BusquedaSedes`; 401. |
| `POST /sedes` | Sí | Sí | 201 `Sede`; 409; 401/403/422. |
| `PUT /contexto/presbiterio` | ADMIN | Sí | 200 `AuthMe`; 401/403/422. |
| `PUT /contexto/sede` | Sí | Sí | 200 `AuthMe`; 401/403/422. |

Las rutas de I02–I06 permanecen en 501 y no se amplían.

## 9. Pruebas

1. **PHPUnit unitario:** normalización, validación, hashing, política de sesión, canonicalización, errores.
2. **PHPUnit integración MySQL:** esquema, cuentas, contexto, sedes, throttling y revocación cuando
   `PCD_DB_*` está disponible.
3. **Runner HTTP I01 (`tests/api/i01.php`):** ejecuta CA-01…CA-14, CA-56…CA-60, CA-61…CA-67,
   CA-76, CA-84, CA-91, CA-92, CA-94, CA-101/109/110 (parte API), CA-113 y los escenarios negativos
   obligatorios. Reporta PASS/FAIL/BLOCKED y escribe evidencia JSON.
4. **Vitest + Testing Library:** login sin enumeración, flujo de contexto, alta/duplicado de sede, expiración,
   GPS denegado y ausencia de `localStorage`.
5. **Build React:** `npm run build`.
6. **Validadores SDD:** `npm test` y verificación documental. No sustituyen MySQL ni HTTP.

## 10. Riesgos y límites

- El entorno local de desarrollo puede carecer de `intl` y Argon2id; CI usa MySQL 8 y PHP con las extensiones
  declaradas. Se documenta el fallback bcrypt solo para desarrollo/CI sin Argon2id.
- La sesión PHP usa el almacenamiento de sesiones del hosting (filesystem). No se crean tablas adicionales.
- No se valida cPanel, TLS ni backups (E-07/H-40); I01 queda en desarrollo/CI con datos sintéticos.
- No se implementan I02–I06 ni se resuelven E1-D02/H-23/H-24.
