> **HISTÓRICO — NO USAR COMO REQUISITO VIGENTE:** este documento describe decisiones/análisis de PCD V3 de 31/10/2026. El responsable aprobó posteriormente **PCD V4: 11 tablas**, consulta **total por funcionalidad** con filtros presbiterio/sede, documentos **solo tipo+número sin subtipo**, tabla `catalogo`, `organizacion` jerárquica y `ubigeo` único. Las referencias a 17 tablas, `alcance_consulta`, `usuario_presbiterio`, subtipo u otras decisiones contradictorias **están reemplazadas**, sin alterar aquí el registro histórico. Fuente de verdad: [Decisiones actuales](../../docs/01-analisis/decisiones-pendientes.md), [SQL 11 tablas](../../database/migrations/001_initial.sql) y [specs vigentes](../../openspec/specs/README.md). En caso de enlaces relativos distintos, localizar los mismos documentos desde README principal.

# PCD — Etapa 3: Contratos API, pruebas y trazabilidad
**Corte:** 31/10/2026 · **Base del auditor:** `auditoria-pcd-sdd-v3.1.zip` (H-10, H-11, H-15, H-16, H-17, H-20…25, H-33/34, H-37/38/39, H-45).  
**Alcance obligatorio:** [ALC-PCD-3.1](../01-analisis/alcance-funcional.md) y [protocolo](protocolo-auditoria-alcance.md). **No se cambia el HTML V2.2 ni se desarrollan funcionalidades SGSP**.

## 1. Tratamiento de los hallazgos

| ID | Observación | Tratamiento proporcional dentro del alcance | Estado |
|---|---|---|---|
| H-10 | Faltan DTO de respuestas API | **Atender**: OpenAPI 3.2 añade estructuras Persona/Ficha/GC, páginas, usuarios, reportes y errores; éxito de operaciones tiene estructura o 204 sin body. | **Diseño actualizado**, parser OpenAPI formal y tests pendientes. |
| H-11 | Endpoints faltantes | **Atender parcialmente**: `GET /grupos/{id}`, `reiniciar-acceso`, filtros y API admin de usuarios. No crear módulos de conciliación, exportación SGSP ni administración pastoral; sedes se crean por flujo existente `POST /sedes`. | Contratos propuestos, backend pendiente. |
| H-15 | Versión GC incoherente | **Atender**: `version` de PATCH GC será `ficha_captura.version` de la ficha tipo GC; autor también proviene de esa ficha. | Criterio técnico documentado; falta prueba de concurrencia. |
| H-16 | Filtros de consulta incompletos | **Atender**: presbiterio, sede, digitador, fechas, incompletitud y posibles duplicados en `GET /personas`. Siempre aplicar ámbito GENERAL/PRESBITERIO y permisos backend. | OpenAPI actualizado; prueba pendiente. |
| H-17 | Filtros y métricas reportes | **Atender**: mismos filtros de fecha/territorio, avance y calidad; modificaciones contadas desde `auditoria_evento` por actor, no por autor de creación. | Contrato y trazabilidad documentados, cálculo aún sin implementación. |
| H-20 | GPS/PII expuestos en prototipo | **Atender seguridad**, no copiar visualización de coordenadas en consultas comunes; solo ADMIN accede a auditoría sensible. Prototipo es demostración. | Regla de diseño, pruebas IDOR pendientes. |
| H-21 | Supervisor/estados no aprobados en HTML | **No replicar**: fuera de EXC-03. No revisar ni aprobar fichas dentro de PCD. | Exclusión explícita del alcance. |
| H-22 | Reintento GPS contradice «una vez» | **No reintentar en sesión**: una captura o estado único por sesión; denegada/no disponible también cuentan como snapshot. Ante POST repetido devolver resultado previo o error consistente, no nuevo prompt. | Criterio ya aprobado, ajuste de contrato pendiente de prueba. |
| H-23 | Quién crea GC sobre Persona ajena | **Pendiente de precisión**; distinguir crear ficha GC propia (no modificar Persona) vs editar datos ajenos; observar E1-D01. No autorizar arbitrariamente a partir de ID. | Decisión funcional pendiente, afecta I03. |
| H-24 | Ficha totalmente vacía | **Pendiente de precisión**: el prototipo pide un campo identificador; la regla aprobará conservar incompletas, pero decidir mínimo contra fichas vacías. | No inventar bloqueo funcional. |
| H-25 | Nombre GC repetido | **No imponer UNIQUE sin aprobación**: advertencia de posible coincidencia; no fusionar automáticamente ni crear maestro permanente de grupos. | E1-D02 pendiente para criterio final. |
| H-33 | Gestión de usuarios y reset | **Atender**: PATCH usuario con nombre/estado/alcance de consulta/presbiterios; reinicio seguro revoca sesiones. No devolver contraseñas. | API documental, seguridad/prueba pendiente. |
| H-34 | Alta de sedes y advertencia de similares | **Atender** dentro del selector; respuesta de similitud útil, sin exigir aprobación por supervisor ni bloquear nombres parecidos legítimos. | Completar DTO y prueba de advertencia en I01. |
| H-37 | Casos sin iteración | **Atender** mediante asignación verificable de CA-42, CA-53…55 y CA-28, sin renumerar pruebas existentes. | [Matriz de ejecución](../05-pruebas/asignacion-iteraciones.md). |
| H-38 | Falta cobertura de fallos | **Atender** casos adicionales de logout, versiones, idempotencia, autorizaciones y reportes. | Casos diseñados, NO ejecutados. |
| H-39 | Falta traza RN→CU→UI→API→SQL→CA | **Atender** con matriz integral RN-01…28 con estado de decisión y dependencias. | Matriz ampliada, evidencia de pruebas pendiente. |
| H-45 | Nomenclatura de parámetros/Idempotency-Key | **Atender**: `ResourceId` en GC/fichas/usuarios y cabeceras de creación donde se necesiten. | Contrato en revisión final, sin API funcionando. |

## 2. Reglas técnicas para implementador I01–I05
1. **GENERAL/PRESBITERIO es solo consulta.** La escritura DIGITADOR continúa limitada a fichas propias, con autorización en PHP. ADMIN tiene facultades de su perfil; REPORTES solo reportes de lectura con minimización.
2. El backend debe devolver DTO compatibles con `openapi.yaml` y **un mismo esquema Error**. IDs de Personas de ámbitos ajenos se minimizan en detección de duplicados, sin convertir consulta general en acceso a GPS/bitácora privada.
3. GET /personas y reportes: filtros controlados por PHP, página/tamaño, fechas ISO. Evitar consultas sin límite.
4. Control GC con versión de su **ficha GC**, no columna inexistente en grupo. Ante cambio de versión devolver 409.
5. Para modificar usuarios, registrar actor, antes/después, revocar sesiones cuando cambia estado o permisos.
6. Captura GPS única por sesión; no re-preguntar tras denegación, cambio de sede o refresh.
7. **Excluir** módulo de conciliación, supervisor, miembros nominales GC, sincronización SGSP, OCR o revisión semántica del papel.

## 3. Verificaciones obligatorias antes de GO
- Lint estructural formal de OpenAPI, resolver todas las referencias de DTO, revisar security de cada endpoint.
- SQL inicial y semilla ejecutados contra MySQL/MariaDB real del hosting o entorno equivalente, incluyendo FK compuestas y CHECK.
- Pruebas de ámbito GENERAL/PRESBITERIO, edición ajena, 409 de versión, 409 de clave idempotente con payload distinto, 401/403/429, XSS/CSRF/IDOR.
- Reportes con datos sintéticos de varias sedes y autores, con conteo de modificaciones según actor.
- **H-40** (protección de datos/retención) y compatibilidad de despliegue corresponden a Etapa 4 antes de producción.

## 4. Pendientes no resueltos por iniciativa de la IA
**E1-D01:** vínculo cross-presbiterio de ficha a Persona; **E1-D02:** grupo existente; **H-23:** permiso de nueva ficha GC; **H-24:** mínima entrada de ficha. No alterar reglas por deducción. El Excel original binario H-14 y catálogos ubigeo **de I02** siguen como entradas documentales/técnicas, no funcionalidades nuevas.

## 5. Estado verificable
**Actualizados:** documentos, anexos y OpenAPI; **NO ejecutados:** backend PHP, frontend React, SQL en hosting, parser YAML formal, E2E y pruebas de rendimiento. El informe al auditor es acumulativo, todavía NO final.
