# PCD — backend PHP (andamiaje P01)

[↑ Índice principal](../README.md)

**Estado:** andamiaje ejecutable P01. Router, sobre JSON, errores, PDO, CSRF/CORS y aprovisionamiento. **No implementa login, captura, consulta ni reportes** (I01–I05).

## Requisitos
- PHP 8.2 con `pdo_mysql`, `mbstring`, `json`.
- Composer 2.
- MySQL 8 / MariaDB compatible con `database/migrations/001_initial.sql`.

## Instalación
```bash
cd backend
composer install
cp .env.example .env   # editar localmente; .env está ignorado por Git
```

## Ejecutar en desarrollo
```bash
php -S 127.0.0.1:8080 -t backend/public backend/public/router.php
curl -s http://127.0.0.1:8080/api/v1/health
```

## Endpoints del andamiaje
| Método y ruta | Estado |
|---|---|
| `GET /api/v1/health` | **Liveness**: 200 si el proceso HTTP responde; no consulta MySQL. |
| `GET /api/v1/ready` | **Readiness**: 200 `database.status=up`; 503 `ok=false` si MySQL no responde. |
| `POST /api/v1/auth/login` | **501** placeholder de I01. |
| `GET /api/v1/auth/me` | **501** placeholder de I01. |
| `POST /api/v1/auth/logout` | **501** con CSRF obligatorio (token de I01). |
| `GET /api/v1/presbiterios` | **501** placeholder de catálogos. |

Los errores usan el sobre de OpenAPI: `{"error":{"code","message"},"request_id"}`. Las respuestas operativas no exponen entorno, credenciales, DSN, rutas internas ni trazas.

## Estructura
- `public/index.php`: front controller; `public/.htaccess` para Apache/cPanel.
- `src/Http`: request, response, router y error handler.
- `src/Database`: PDO con `utf8mb4`, prepares reales y transacciones.
- `src/Security` y `src/Http/Middleware`: CSRF y CORS.
- `src/Provisioning`: plan y ejecución idempotente (Argon2id).
- `tests/`: PHPUnit unitario e integración (esta última requiere MySQL).

## Pruebas
```bash
cd backend
vendor/bin/phpunit --configuration phpunit.xml
```
La integración de aprovisionamiento se ejecuta solo si existe `PCD_DB_HOST`; en local sin MySQL queda omitida.

## Despliegue cPanel
DocumentRoot del subdominio → `backend/public`. Sin Node ni procesos residentes. Configurar variables de entorno en el panel y mantener `.env` fuera del repositorio.
