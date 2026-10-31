# Preparación de iteraciones PCD para desarrollo mediante IA
**Fecha:** 31/10/2026. Estados: LISTA_PARA_CODIFICAR / CONDICIONADA / BLOQUEADA. La documentación puede existir sin pruebas ejecutadas.

| Iteración | Estado al cierre de la Etapa 1 | Puerta de entrada antes de programar | Evidencia de salida |
|---|---|---|---|
| **I00 — Preparación** | **LISTA_PARA_CODIFICAR** (solo entorno/revisión) | `AGENTS.md`, README, arquitectura y plan; sin datos reales | Versiones hosting, CI/lint, SQL ejecutado en BD de prueba y resultado, OpenAPI lint. |
| **I01 — Login/sedes/geolocalización** | **CONDICIONADA** | Etapa 2: H-03, H-04, H-28, H-32; decisión no repetir GPS; sesión y throttling | CA-01..14,36..41 más logout/expiración, con evidencia. |
| **I02 — Personas/fichas** | **BLOQUEADA** | H-01/02/05/14 con conformidad funcional, esquema JSON, API/SQL/test sincronizados; H-07/13/18/24 dependientes | Pruebas de ficha incompleta, duplicado cross-sede sin exposición, snapshots literales, auditoría, idempotencia y rollback. |
| **I03 — GC** | **BLOQUEADA** | H-08/09 aprobados, spec y contrato GC definitivos, H-15/H-23/H-35 resueltos | Pruebas existentes/nuevos, mismo líder, candidatos a duplicado sin fusionar, capacidad vs miembros. |
| **I04 — Consulta/edición** | **BLOQUEADA** | Matriz permisos fuente vs consolidado; H-12 (alcance consulta) y anulación H-36 si se aprueba | Permisos horizontales (IDOR), edición propia, corrección ADMIN y auditoría histórica. |
| **I05 — Admin/Calidad/Reportes** | **CONDICIONADA** | Calidad parametrizable H-18 y fuentes de reportes, contratos y pruebas | Conteos reconciliados, reportes última fecha sede/autor y datos mínimos. |
| **I06 — Pruebas/despliegue** | **CONDICIONADA** | Privacidad y retención H-40, cPanel real y pruebas de restauración/carga | Informe seguridad/backup/carga, verificación de HTTPS y API real. |

## Principio de ejecución
Se puede autorizar a una IA a iniciar **I00** desde este repositorio. **No** significa que I00 se haya ejecutado. Cada iteración requiere revisión del cambio OpenSpec, pruebas y aceptación explícita antes de pasar a la siguiente. No se debe tomar como corrección aprobada lo que se registra como propuesta en Etapa 1.
