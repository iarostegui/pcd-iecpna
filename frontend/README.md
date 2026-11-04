# frontend — React + TypeScript + Vite

[↑ Índice principal](../README.md)

**Estado:** I01 implementado en desarrollo/CI. Login con presbiterio y código tecleado, selección/alta de sede,
contexto ADMIN, cierre de sesión, expiración y GPS opcional de una captura por sesión. **I02–I05
(captura, consulta, reportes, administración) no están implementados.**

## Comandos
```bash
npm ci
npm run dev       # http://localhost:5173 con proxy /api -> 127.0.0.1:8080
npm run build     # tsc -b && vite build -> dist/
npm run typecheck
npm test          # Vitest + Testing Library (jsdom)
```

## Variables de entorno
`VITE_API_BASE_URL` (por defecto `/api/v1`). Copiar `.env.example`; nunca incluir secretos.

## Pantallas I01
- **Login**: presbiterio desde `GET /presbiterios`, código de usuario como texto, contraseña vacía,
  sin listado de cuentas y errores genéricos (incluye 429).
- **Contexto**: solo sedes activas del presbiterio; búsqueda, similares y alta de sede con
  `Idempotency-Key`; para ADMIN, cambio explícito de presbiterio.
- **Espacio autenticado**: identidad, perfil, presbiterio/sede, estado de ubicación y logout. No simula
  formularios de I02–I06.
- **Expiración**: un 401 en una llamada autenticada limpia CSRF y devuelve al login con aviso; un
  temporizador usa `vence_inactividad_en`/`vence_absoluta_en` y la actividad renueva la sesión.

## Seguridad y alcance
- `src/api/client.ts` es el cliente centralizado de P01: `credentials: include`, timeout, `ApiError`
  normalizado y `X-CSRF-Token` en memoria.
- **No se usa `localStorage`** para sesión, credenciales ni coordenadas. `sessionStorage` solo guarda
  el marcador booleano `pcd_geo_solicitada` para no repetir el permiso de ubicación.
- El GPS se solicita como máximo una vez por sesión; `DENEGADA`, `ERROR` y `NO_DISPONIBLE` no bloquean.
- Los permisos se revalidan en PHP; ocultar un control en React no sustituye la autorización backend.

## Pruebas
- `src/__tests__/LoginScreen.test.tsx`: CA-101, sin listado de cuentas, campos y error genérico.
- `src/__tests__/App.test.tsx`: flujo login → sede → contexto → logout, expiración por 401,
  GPS denegado sin repetición y 409 de sede duplicada; verifica ausencia de `localStorage`.

## Despliegue
El resultado de `npm run build` es estático (`dist/`) y se publica en cPanel. **No requiere Node
residente** en el hosting. No desplegar con datos reales antes de H-40/E-07 y el GO institucional.

## Prohibiciones
No usar `localStorage` para fichas, credenciales, sesión ni coordenadas; no decidir permisos solo en
React; no implementar funcionalidades fuera de ALC-PCD ni I02–I06.

## I02 (PR #5, Draft)
- `src/components/PersonaCapture.tsx`: captura sintética Persona/Ficha integrada en Workspace al tener sede y perfil de escritura.
- `src/__tests__/PersonaCapture.test.tsx`: pruebas UI/cliente de idempotencia y ausencia de localStorage con PII.
- La UI muestra bloqueos H-24, CE y catálogo INEI sin fingir autorización productiva.
