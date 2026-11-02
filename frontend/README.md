# frontend — React + TypeScript + Vite (andamiaje P01)

[↑ Índice principal](../README.md)

**Estado:** andamiaje ejecutable. Cliente HTTP centralizado, manejo uniforme de errores y preparación de CSRF. **No contiene login, captura, consulta ni reportes** (I01–I05).

## Comandos
```bash
npm ci
npm run dev       # http://localhost:5173 con proxy /api -> 127.0.0.1:8080
npm run build     # tsc -b && vite build -> dist/
npm run typecheck
```

## Variables de entorno
`VITE_API_BASE_URL` (por defecto `/api/v1`). Copiar `.env.example`; nunca incluir secretos.

## Estructura
- `src/api/client.ts`: `fetch` con `credentials: include`, timeout, `ApiError` normalizado y `X-CSRF-Token` en memoria (nunca `localStorage`).
- `src/api/types.ts`: sobres de respuesta/error compatibles con OpenAPI.
- `src/App.tsx`: consola mínima que consulta `GET /health` y muestra errores con `request_id`.

## Despliegue
El resultado de `npm run build` es estático (`dist/`) y se publica en cPanel. **No requiere Node residente** en el hosting.

## Prohibiciones
No usar `localStorage` para fichas, credenciales o coordenadas; no decidir permisos solo en React; no implementar funcionalidades fuera de ALC-PCD.
