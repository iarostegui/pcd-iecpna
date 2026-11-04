# Plan de implementación PCD V4.1 — fuente vigente para SDD

**Alcance:** [ALC-PCD](../01-analisis/alcance-funcional.md), aplicación **temporal** independiente de SGSP. Base física [11 tablas SQL](../../database/migrations/001_initial.sql), [anexos](../03-base-datos/anexos/README.md) y [specs](../../openspec/specs/README.md). El prototipo HTML V2.3 es la referencia de UX, y V2.2 queda histórico e inalterado.

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
| **I02 — Captura Persona** | Persona/Ficha, tipo+número documento, incompletos, JSON fuente cónyuge/responsabilidades, idempotencia, ubigeo INEI importado. | Formulario React, PHP Persona/Ficha, test duplicados/concurrencia/formatos/JSON. | **H-24** y fuente INEI/CE aplicable; E1-D01 ya aprobado. |
| **I03 — Líder GC** | Ficha GC con referencia Persona líder, datos agregados, versión ficha; no miembros nominales. | React GC, PHP GC, pruebas FK, líder y 409. | **E1-D02 + H-23**, I02. |
| **I04 — Consulta/edición** | Consulta nacional de función con filtros opcionales presbiterio/sede/fecha/digitador; autor de ficha controla edición. | React búsqueda y detalle, PHP list/get/patch, CA-77/78/97/98. | I02 y permisos backend test. |
| **I05 — Admin/reportes** | Usuarios, catálogos críticos, informes de avance/calidad/productividad, auditoría. | React/Admin, PHP, cálculos sobre `catalogo`, CA-79/85/93/88. | I04/BD, pruebas de métricas/actor real. |
| **I06 — Integración/producción** | Pruebas carga/seguridad, backup restore, despliegue cPanel y cierre, datos personales sensibles. | [Checklist PROD-01…10](../06-despliegue/checklist-produccion.md), evidencia y decisión GO. | H-40 PR-01…08 institucional, pruebas y backups verificadas. |

## Decisiones e insumos abiertos
- **E1-D01 APROBADO:** reutilizar Persona y agregar ficha fuente entre presbiterios vía endpoint existente.
- **E1-D02:** GC «ya existe» declarado en papel.
- **H-23:** ficha GC por digitador distinto del creador Persona.
- **H-24 aprobada:** presbiterio y sede de sesión más nombres, apellidos paterno y materno, tipo/número documento y teléfono.
- **H-40:** responsable, finalidad, base jurídica, aviso, retención y cierre.
- Fuente oficial INEI y corte para I02; catálogo de sedes incompleto pero hay alta de sedes; versión real hosting y carga por probar.

**Estado real:** SQL, OpenAPI, anotaciones y anexos están en GitHub. **I01 está implementado en desarrollo/CI** con autenticación, sesión, organización/sedes, contexto y GPS opcional, pruebas PHPUnit/HTTP/UX y build React; I02 implementada en PR #5, pendiente de cierre técnico; I03–I05 siguen pendientes. **NO hay frontend/backend productivos** ni pruebas de restore/carga ejecutadas; E-07/H-14/H-40 y revisión del especialista siguen abiertos.

## Gate V2.3 (UX aprobada como referencia, no implementación)
**Referencia interactiva vigente:** [PCD_v2.3.html](../../prototypes/PCD_v2.3.html). [Cambios por pantalla](../02-diseno/pcd-v2.3-alineacion.md). I01 login por usuario escrito, claves separadas en producción, GPS único; I02 Persona tipo+número, fecha imposible rechazada, calidad externa; I03 GC sin selector adicional; I04 consulta nacional con filtros, lectura independiente de edición; I05 reportes generales. Probar **CA-101…111**. **E1-D01 está aprobada**; E1-D02/H-23 siguen pendientes y no quedan resueltos por la maqueta.

**H-46 integrado en I01/I06:** controlar frecuencia de actualización de sesión, validación de hashing PHP, limpieza acotada de idempotencia y métricas p95 sin servicio adicional ni tablas. [Etapa 4](../07-auditorias/etapa-4-produccion-y-privacidad.md) · [CA-112…114](../05-pruebas/casos-prueba.md).

## Etapa 5 — Preparación técnica I00
La [guía I00](I00-verificacion-estatica-y-handoff.md) incluye script [`validar-sdd-i00.cjs`](../../scripts/validar-sdd-i00.cjs) y checklist de pruebas reales. Generar evidencia del motor real antes de declarar I00 terminada; evitar repetir propuestas fuera de alcance y no depender de E1-D02/H-23/H-24 para comenzar I00 (E1-D01 aprobada).

## Criterios técnicos obligatorios ratificados 01/11/2026
- Los cuatro presbiterios **LIM/TRU/CHI/IQU** están en seed y no son CRUD. Sedes sí se agregan por usuario autorizado; comparar sin tildes.
- La contraseña común se utiliza solo **pruebas/aceptación**, y antes de producción es obligatoria la **rotación individual** sin exponer secretos. 
- No auditar `INSERT` de ficha: conservar autor/fecha en ficha. Auditoría de PATCH solo campos diferentes con antes/después, snapshot de actor, timestamps.
- `created_at` y `updated_at` presentes en todas las tablas. Índices para reportes nuevos dependen de consulta real y `EXPLAIN`, no añadirlos preventivamente.
- E1-D01 está **aprobado**, conservar endpoint `POST /personas/{id}/fichas` y `persona_reutilizada`; no sobrescribir consolidado al anexar.
- Validación JSON Schema estricta de secciones opcionales de ficha; no crear tablas de cónyuge.
- El objetivo del próximo especialista es **evitar ambigüedades para generar código**, no completar más etapas documentales.

**Guía directa para IA:** [Contrato de ejecución V4.1](CONTRATO-EJECUCION-V4.1.md) y [entrega al especialista](../07-auditorias/ENTREGA-REVISION-ESPECIALISTA-V4.1.md), antes de desarrollar. La CI estática ha pasado al menos en ejecución 41368871153, pero no se ha probado el motor ni el producto.
