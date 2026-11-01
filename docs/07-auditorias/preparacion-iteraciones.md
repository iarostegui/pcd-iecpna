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

## Revisión de estado tras Etapa 2 (31/10/2026)
| Iteración | Preparación lograda | Pendiente antes de GO / ejecución |
|---|---|---|
| I00 | SQL inicial E2, catálogos/semillas y OpenAPI documentados. | Verificar versión MySQL/MariaDB real, ejecutar `001_initial.sql` y semillas en BD **de prueba**, lint API y pruebas de CHECK/FK. |
| I01 | Login/contexto/sesiones/roles/auditoría documentados con CA-56…65/66/67/76. | E2-D02 contexto nacional, mitigación clave compartida, pruebas de SQL/API; producción además requiere E2-D03. |
| I02 | Normalización DNI/OTRO y catálogo, criticidad, esquema fuente. | E1-D01, E2-D01, ubigeo/CE si aplica, API de fichas y control de acceso; tests. |
| I03 | Líder GC solo vía ficha; FK/CHECK propuestas. | E1-D02 y prueba física; contratos/test funcionales. |
| I04 | Consulta por presbiterio como base del prototipo. | E2-D01 y pruebas de no exposición cross-scope. |
| I05 | Matriz críticos y cálculo dinámico. | Prueba de informes con cambios ADMIN y control nacional. |
| I06 | Sesiones/seguridad técnica previstas. | Privacidad H-40, hosting/backup/carga y pruebas de restauración. |
**Estado global:** no existen pruebas productivas ejecutadas; propuestas no equivalen a decisiones aprobadas ni release.

## Actualización tras decisiones Etapa 2
- **I00:** preparar y conservar dos anexos técnicos **generados del SQL**: diagrama físico detallado y diccionario físico por columna; verificar concordancia con versión de esquema y ejecutar SQL en motor real cuando esté disponible.
- **I01:** ámbito nacional ADMIN/REPORTES **aprobado** y credenciales separadas; `GENERAL/PRESBITERIO` se gestiona desde Administración, pero la lectura nacional no habilita edición. Hacer pruebas por matriz de roles.
- **I02:** **ubigeo INEI como entrada de construcción obligatoria**, con fuente y versión verificadas; H-13 aprobado, persistencia tipo/subtipo/número sin ceros perdidos.
- **I04:** probar que GENERAL muestra todos los presbiterios autorizados para consulta, PRESBITERIO solo los asociados, ambos con edición DIGITADOR restringida a propias fichas.
- **I06:** la aceptación funcional de clave compartida no elimina la revisión de privacidad y pruebas de controles antes de operar con datos reales.

## Etapa 3 — puertas de entrada
- I01: los contratos de login, permisos nacionales y scope configurable están definidos, pero faltan lint formal y pruebas PHP/MySQL.
- I02: DTO de Persona/ficha y filtros preparados; E1-D01 y H-24 requieren decisión, además del Excel binario y ubigeo en la construcción.
- I03: el control 409 usa la versión de ficha GC; E1-D02/H-23 y pruebas FK bloquean GO.
- I04: ámbito GENERAL/PRESBITERIO **aprobado**; falta implementar API y tests de edición solo propia.
- I05: reportes por actor de auditoría, filtros y parametrización aprobados, sin resultados probados.
- I06: H-40 protección de datos/retención y validación de hosting son parte de la siguiente etapa.

## V4 — actualizaciones obligatorias de puertas I00–I06
- **I00:** SQL de **11 tablas**, fuente [Anexo ER](../03-base-datos/anexos/anexo-01-diagrama-fisico.md), [diccionario](../03-base-datos/anexos/anexo-02-diccionario-fisico.md). Probar MySQL real antes del GO.
- **I01:** sesión con GPS dentro de `sesion`, ORGANIZACION única para presbiterios y sedes, usuario con presbiterio operativo. Sin `usuario_presbiterio`.
- **I02:** Persona y Ficha, solo tipo+número, cónyuge/responsabilidades en JSON, entrada UBIGEO INEI oficial; E1-D01 y H-24 pendientes.
- **I03:** Grupo Crecer solo líder desde ficha y datos agregados, E1-D02/H-23 pendientes.
- **I04:** **consulta total** en funcionalidad permitida con filtros presbiterio/sede, edición digitador solo propias.
- **I05:** cambios de críticos en `catalogo.config_json`, sin tabla física `campo_critico` ni ámbitos de lectura configurables.
- **I06:** [PROD-01…10](../06-despliegue/checklist-produccion.md) y [Etapa 4](etapa-4-produccion-y-privacidad.md). H-40 pendiente.

**Los estados o GO expresados antes de V4 quedan invalidados cuando supongan 17 tablas, permisos territoriales o subtipo**; las pruebas deben replanificarse.
