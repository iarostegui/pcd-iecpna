# PCD — despliegue y operación en cPanel
**Destino acordado:** `https://pcd.unobdata.com`, mismo hosting existente. La actual V2.2 estática es **solo demo**. Sustituirla por producción únicamente tras pruebas y respaldos.

## 1. Prerrequisitos
Verificar en cPanel ruta Document Root exacta, HTTPS válido y Force HTTPS; PHP 8.x compatible, OPcache, PHP-FPM si disponible y suficiente memoria, MySQL/MariaDB con InnoDB, espacio y backup. No inventar rutas por defecto.

## 2. Build
En PC de desarrollo o CI (no en cPanel): `npm ci && npm run build`; generar `frontend/dist`. Desplegar assets versionados en raíz de documento; mantener `index.html` y `assets/`. **No necesita Node residente**.

## 3. API
Crear `/api/v1` ruteada a PHP `backend/public/index.php` (configurar rewrite según Apache de hosting), colocar código y secretos fuera de webroot cuando sea posible. Evitar exponer `.env`, dumps y código fuente. Mantener mismo origen para evitar CORS complicado. HTTPS obligatorio.

## 4. Base
Crear usuario y base MySQL específicos de PCD desde cPanel con privilegios mínimos. Ejecutar `database/migrations/001_initial.sql` en BD de pruebas primero; seed únicamente con catálogos comprobados. Provisionar contraseña común inicial de forma protegida (hash PHP) **sin texto claro en SQL/Git**.

## 5. Seguridad/validación
Cookies HttpOnly/Secure/SameSite, CSRF, rate-limit, no directory listing, cabeceras, control de acceso real. Hacer pruebas de todas las rutas, PII no persistida en navegador, auditoría y GPS 1 vez/sesión.

## 6. Rendimiento
Medir carga progresiva contra baseline web existente; OPcache y caché assets; API no-store; índices. No utilizar procesos cron obligatorios, websocket ni workers residentes.

## 7. Respaldo y rollback
Backup protegido BD y versión build ANTES de reemplazar `index.html`; simulacro de restauración; despliegue reversible de activos y backend; monitorear errores 4xx/5xx. Establecer procedimiento de cierre de campaña y retención autorizada.

## Notas
El prototipo en `prototypes/` no autentica realmente y no debe usarse para fichas reales; despliegue productivo requiere backend PHP/MySQL terminado y probado.

## Validación del modelo V4 antes de despliegue
El `database/migrations/001_initial.sql` **fue reemplazado por un esquema nuevo de 11 tablas** para instalaciones desde cero. Si ya hay alguna BD con tablas anteriores **no aplicar 001 encima**, realizar respaldo, diagnóstico y migración de datos independiente. Primero ejecutar SQL y [seed](../../database/seeds/002_catalogos_calidad.sql) en base de **pruebas**; verificar `organizacion`, `catalogo`, `ubigeo`, `sesion`, FKs y CHECK; verificar su compatibilidad con versión real cPanel.

Consulta de Persona/GC de alcance total solo dentro de funcionalidad autorizada, filtros opcionales Presbiterio/Sede; backend prohíbe modificar fichas ajenas. Carga de UBIGEO es entrada I02. [Checklist GO de producción](checklist-produccion.md) condiciona H-40, restore y seguridad.
