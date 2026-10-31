# Preparación de iteraciones PCD para desarrollo mediante IA
**Fecha:** 31/10/2026. Estados: LISTA_PARA_CODIFICAR / CONDICIONADA / BLOQUEADA. La documentación puede existir sin pruebas ejecutadas.

| Iteración | Estado al cierre de la Etapa 1 | Puerta de entrada antes de programar | Evidencia de salida |
|---|---|---|---|
| **I00 — Preparación** | **LISTA_PARA_CODIFICAR** (solo entorno/revisión) | `AGENTS.md`, README, arquitectura y plan; sin datos reales | Versiones hosting, CI/lint, SQL ejecutado en BD de prueba y resultado, OpenAPI lint. |
| **I01 — Login/sedes/geolocalización** | **CONDICIONADA** | Etapa 2: H-03, H-04, H-28, H-32; decisión no repetir GPS; sesión y throttling | CA-01..14,36..41 más logout/expiración, con evidencia. |
| **I02 — Personas/fichas** | **BLOQUEADA** | H-01/02/05/14 con conformidad funcional, esquema JSON, API/SQL/test sincronizados; H-07/13/18/24 dependientes | Pruebas de ficha incompleta, fechas válidas o vacías y rechazo de fechas imposibles, duplicado cross-sede sin exposición, auditoría, idempotencia y rollback. |
| **I03 — GC** | **BLOQUEADA** | Alcance GC **solo líderes aprobado**; E1-D02 pendiente, spec y contrato GC definitivos, H-15/H-23/H-35 resueltos | Pruebas existentes/nuevos, mismo líder, candidatos a duplicado sin fusionar, capacidad vs miembros. |
| **I04 — Consulta/edición** | **BLOQUEADA** | Matriz permisos fuente vs consolidado; H-12 (alcance consulta) y anulación H-36 si se aprueba | Permisos horizontales (IDOR), edición propia, corrección ADMIN y auditoría histórica. |
| **I05 — Admin/Calidad/Reportes** | **CONDICIONADA** | Calidad parametrizable H-18 y fuentes de reportes, contratos y pruebas | Conteos reconciliados, reportes última fecha sede/autor y datos mínimos. |
| **I06 — Pruebas/despliegue** | **CONDICIONADA** | Privacidad y retención H-40, cPanel real y pruebas de restauración/carga | Informe seguridad/backup/carga, verificación de HTTPS y API real. |

## Principio de ejecución
Se puede autorizar a una IA a iniciar **I00** desde este repositorio. **No** significa que I00 se haya ejecutado. Cada iteración requiere revisión del cambio OpenSpec, pruebas y aceptación explícita antes de pasar a la siguiente. No se debe tomar como corrección aprobada lo que se registra como propuesta en Etapa 1.

## Calidad manual (decisión aprobada)
La **revisión de fidelidad del texto con el papel** corresponde a cada presbiterio, **fuera de PCD**. No condicionar ninguna iteración a construir motor de detección de errores de transcripción, colas, supervisores o conciliación. Sí exigir validación de tipos, autorización, guardado incompleto y auditoría. **H-05 y H-09 tienen definición funcional cerrada, pero implementación y pruebas aún pendientes.**

## Puerta transversal de alcance (aplicable I00–I06)

**Cada iteración debe trazarse a una capacidad ALC-01…ALC-12** del [documento oficial](../01-analisis/alcance-funcional.md). Una observación que pida implementar EXC-01…EXC-10 debe figurar como **propuesta de ampliación**, no como bloqueo de la iteración, salvo que denuncie una deficiencia esencial de seguridad/privacidad/integridad en las funciones autorizadas. Auditoría y agente no pueden aprobar unilateralmente un nuevo módulo. Ver [protocolo](protocolo-auditoria-alcance.md).
