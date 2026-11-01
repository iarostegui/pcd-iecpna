# Plan de implementación PCD V4.0 — fuente vigente para SDD

**Alcance:** [ALC-PCD](../01-analisis/alcance-funcional.md), aplicación **temporal** independiente de SGSP. Base física [11 tablas SQL](../../database/migrations/001_initial.sql), [anexos](../03-base-datos/anexos/README.md) y [specs](../../openspec/specs/README.md). El prototipo HTML V2.2 es referencia de UX **sin cambios**.

## Reglas obligatorias para la IA
1. **Consulta general en la funcionalidad autorizada** con filtros opcionales presbiterio y/o sede; no hay `alcance_consulta` ni tabla para ACL territorial.
2. Digitador edita exclusivamente fichas creadas por él; ADMIN ejerce funciones nacionales, REPORTES solo lectura.
3. H-13: únicamente tipo+número documento. Sin subtipo. DNI válido y CE confiable generan clave técnica; OTRO coincidencia potencial sin fusión automática.
4. BD simplificada: `organizacion` (presbiterio+sede), `catalogo` (tabla de tablas), `ubigeo` INEI, `persona`, `ficha`, `grupo_crecer`, `usuario`, `sesion` (GPS), `login_evento`, `auditoria_evento`, `idempotencia`.
5. Cónyuge/responsabilidades se conservan como `ficha.datos_fuente_json` validado; no tablas/CRUD independientes.
6. Fechas imposibles rechazadas o vacías; se guarda incompleto; comparación con papel se revisa manualmente por presbiterios.
7. No generar integración SGSP, integrantes nominales GC, aprobación de supervisor ni canal WhatsApp.

## Ciclo de trabajo por iteración SDD
**Plan → especificación → implementación frontend/backend → pruebas automatizadas → revisión de consistencia SQL/API/UX → auditoría de resultados → correcciones → aceptación humana**. Cada etapa conserva evidencia por commit y casos de prueba por ID; jamás declarar PASS sin ejecutar.

| Iteración | Alcance funcional / técnico | Productos esperados | Puerta para iniciar |
|---|---|---|---|
| **I00 — Infraestructura técnica** | Confirmar hosting PHP/MySQL, estructura repo, ejecutar SQL 11 tablas y seed en **BD de prueba**; comprobar constraints; generar diagramas/diccionario. | Esquema instalado en test, scripts y reporte de compatibilidad, anexos sincronizados. | Insumos hosting y acceso BD de pruebas. |
| **I01 — Autenticación, contexto** | Login por presbiterio (usuario tecleado), cookie/CSRF, bloqueo, revocación, GPS 1 vez en `sesion`; organización con presbiterios/sedes jerárquicos y alta sede. | React Login/selector, PHP Auth/Org, pruebas CA-01…14 y CA-91/92/94. | I00 SQL probado; credenciales protegidas. |
| **I02 — Captura Persona** | Persona/Ficha, tipo+número documento, incompletos, JSON fuente cónyuge/responsabilidades, idempotencia, ubigeo INEI importado. | Formulario React, PHP Persona/Ficha, test duplicados/concurrencia/formatos/JSON. | **E1-D01 + H-24** y fuente INEI/CE aplicable aprobados. |
| **I03 — Líder GC** | Ficha GC con referencia Persona líder, datos agregados, versión ficha; no miembros nominales. | React GC, PHP GC, pruebas FK, líder y 409. | **E1-D02 + H-23**, I02. |
| **I04 — Consulta/edición** | Consulta nacional de función con filtros opcionales presbiterio/sede/fecha/digitador; autor de ficha controla edición. | React búsqueda y detalle, PHP list/get/patch, CA-77/78/97/98. | I02 y permisos backend test. |
| **I05 — Admin/reportes** | Usuarios, catálogos críticos, informes de avance/calidad/productividad, auditoría. | React/Admin, PHP, cálculos sobre `catalogo`, CA-79/85/93/88. | I04/BD, pruebas de métricas/actor real. |
| **I06 — Integración/producción** | Pruebas carga/seguridad, backup restore, despliegue cPanel y cierre, datos personales sensibles. | [Checklist PROD-01…10](../06-despliegue/checklist-produccion.md), evidencia y decisión GO. | H-40 PR-01…08 institucional, pruebas y backups verificadas. |

## Decisiones e insumos abiertos
- **E1-D01:** vincular ficha a Persona existente entre presbiterios.
- **E1-D02:** GC «ya existe» declarado en papel.
- **H-23:** ficha GC por digitador distinto del creador Persona.
- **H-24:** umbral mínimo de ficha no totalmente vacía.
- **H-40:** responsable, finalidad, base jurídica, aviso, retención y cierre.
- Fuente oficial INEI y corte para I02; catálogo de sedes incompleto pero hay alta de sedes; versión real hosting y carga por probar.

**Estado real:** SQL, OpenAPI, anotaciones y anexos están en GitHub; **NO hay frontend/backend productivos**, ni pruebas de motor, integración, restore o carga ejecutadas.

## Gate V2.3 (UX aprobada como referencia, no implementación)
**Referencia interactiva vigente:** [PCD_v2.3.html](../../prototypes/PCD_v2.3.html). [Cambios por pantalla](../02-diseno/pcd-v2.3-alineacion.md). I01 login por usuario escrito, claves separadas en producción, GPS único; I02 Persona tipo+número, fecha imposible rechazada, calidad externa; I03 GC sin selector adicional; I04 consulta nacional con filtros, lectura independiente de edición; I05 reportes generales. Probar **CA-101…111**. E1-D01/E1-D02/H-23/H-24 siguen pendientes, no quedan resueltos por la maqueta.

**H-46 integrado en I01/I06:** controlar frecuencia de actualización de sesión, validación de hashing PHP, limpieza acotada de idempotencia y métricas p95 sin servicio adicional ni tablas. [Etapa 4](../07-auditorias/etapa-4-produccion-y-privacidad.md) · [CA-112…114](../05-pruebas/casos-prueba.md).
