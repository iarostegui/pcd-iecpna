# AGENTS — contrato para cualquier IA desarrolladora de PCD
**Regla central:** trabajar SOLO en PCD, NO en SGSP. La migración a SGSP es futura y queda fuera del desarrollo actual.

## Límite de producto — cumplimiento obligatorio
**Documento normativo de alcance:** [`docs/01-analisis/alcance-funcional.md`](docs/01-analisis/alcance-funcional.md) (ALC-PCD-3.1). Contiene **objetivos, inclusiones ALC-01…ALC-12, exclusiones EXC-01…EXC-10, roles, calidad manual y frontera con SGSP**.

Toda IA —desarrolladora o auditora— debe **leerlo antes** de interpretar requisitos y evaluar hallazgos. El prototipo vigente V2.3 muestra UX demostrativa, pero **no autoriza funcionalidades fuera del alcance oficial**.

**Al auditar:** utilizar el [protocolo de control de alcance](docs/07-auditorias/protocolo-auditoria-alcance.md). Es legítimo cuestionar el alcance y proponer mejoras; no lo es registrar la ausencia de funciones excluidas como incumplimiento ni alterar la planificación sin aprobación. Los riesgos esenciales de seguridad, protección de datos e integridad dentro de PCD sí requieren evaluación y control proporcional.

**Al programar:** cada cambio debe trazarse a ALC y al requisito OpenSpec aprobado. Una propuesta fuera de alcance se registra para decisión, **no** se implementa automáticamente. Si hay contradicción, reportarla antes de codificar; no inventar una nueva decisión.

## Lectura obligatoria antes de codificar
1. `README.md` y **`docs/01-analisis/alcance-funcional.md` (obligatorio primero)**;
2. `docs/01-analisis/alcance-funcional.md`, `reglas-negocio.md`, `procesos-negocio.md`, `casos-uso.md`;
3. `docs/02-diseno/`, `docs/03-base-datos/`, `docs/04-integracion/`;
4. `openspec/specs/<capacidad>/spec.md`;
5. `prototypes/PCD_v2.3.html`, como patrón visual y orden de formulario;
6. `docs/05-pruebas/`.

## Prohibiciones
- Preservar PCD V2.2 como histórico; usar V2.3 para construir, sin convertir localStorage o demo login en producción.
- No crear roles pastorales ni módulos de SGSP.
- No usar 48 sedes inventadas ni dar por oficiales los datos de demostración.
- No exigir número de ficha impresa, no bloquear guardado por campos críticos faltantes, no exigir revisión de supervisor.
- No exigir consentimiento de geolocalización para guardar; **una captura por sesión** como máximo, reutilizada.
- No usar geolocalización como restricción de edición.
- No introducir framework o servicio residente que sobrecargue hosting, sin ADR aprobada.
- No exponer contraseñas, coordenadas ni datos personales en Git, `localStorage`, URL, logs de consola o respuestas no autorizadas.
- No alterar historial ni autor original al editar; ADMIN modifica con su propia identidad.
- No afirmar pruebas exitosas sin ejecución verificable.

## Decisiones vigentes esenciales
- Login solicita Presbiterio + Usuario + Contraseña; seis digitadores iniciales por presbiterio existente, ADMIN nacional, REPORTES nacional.
- Contraseña **inicial común y no forzada a cambiar**, por decisión operativa; almacenar solo hashes Argon2id/bcrypt, mantener límite de intentos y auditoría; valor real se provisiona fuera del repositorio.
- Tras login seleccionar **solo Sede** dentro del Presbiterio; conservar última sede válida por usuario; crear nueva sede al vuelo, sin aprobación, con deduplicación.
- Registro Persona y GC **integrados en la navegación**, pero **GC se abre después de persistir Persona** y es opcional.
- Digitador edita registros de su autoría; ADMIN ve/edita todos; REPORTES solo consulta agregados y calidad autorizada.
- Fichas incompletas son persistibles; campos críticos configurables en tiempo de operación para **indicadores**, no para bloquear.
- Auditoría por guardado: actor, creador original, antes/después, IP, fecha, sesión y ubicación capturada al login cuando exista.
- PCD usa BD propia; SGSP no forma parte del alcance de implementación.

## Ciclo SDD obligatorio
Por capacidad: `proposal.md` (cambio) → `design.md` → `spec.md` Given/When/Then → `tasks.md` → código y migraciones → pruebas automáticas → verificación de trazabilidad → revisión humana. Registrar pruebas, fallos y correcciones en PR. Las decisiones inciertas se registran en `docs/01-analisis/decisiones-pendientes.md`; no inventar.

## Definición de terminado
Contrato API y frontend/backend coherentes; permisos y SQL enforce; pruebas válidas y sin regresiones; auditoría transaccional; despliegue reproducible sin Node en hosting; datos de prueba ficticios; métricas verificadas en hosting antes de aceptar umbrales.

## Mantenimiento del índice y READMEs
Al crear, mover, renombrar o eliminar archivos o carpetas, actualizar el `README.md` de la carpeta afectada y el índice del `README.md` raíz cuando cambie una ruta principal. Cada README describe finalidad, estado (propuesto/vigente/ejecutado) y enlaces reales a su contenido. Evitar enlaces rotos y no inventar pruebas realizadas.

## Controles E2 y límite para programación
Antes de I01/I02 leer [decisiones vigentes V4](docs/01-analisis/decisiones-pendientes.md), [modelo de 11 tablas](docs/03-base-datos/anexos/justificacion-11-tablas.md) y [seguridad](docs/02-diseno/seguridad-auditoria.md). El login requiere código de usuario escrito; la organización se consulta por `GET /presbiterios` según contrato OpenAPI V4 y entrega solo datos públicos de presbiterios activos; las consultas operativas son generales para perfiles con acceso y permiten filtrar por presbiterio/sede; edición del digitador solo sobre fichas propias. ADMIN/REPORTES usan secretos distintos, sesiones con CSRF/expiración/revocación y límites de intentos. E2-D01/D02/D03 quedaron resueltas en V4; siguen pendientes pruebas de motor/API. No crear miembros nominales GC.

## **REGLA DE PRECEDENCIA V4.0 — OBLIGATORIA PARA AGENTES**
Antes de planificar, generar código, auditar o proponer rediseños leer en este orden:
1. [Alcance oficial](docs/01-analisis/alcance-funcional.md) y [decisiones vigentes](docs/01-analisis/decisiones-pendientes.md).
2. [SQL 11 tablas](database/migrations/001_initial.sql), [ER](docs/03-base-datos/anexos/anexo-01-diagrama-fisico.md), [diccionario físico](docs/03-base-datos/anexos/anexo-02-diccionario-fisico.md).
3. [Reglas](docs/01-analisis/reglas-negocio.md), [OpenSpec vigente](openspec/specs/README.md), [OpenAPI V4](docs/04-integracion/openapi.yaml).
4. [Etapa 4](docs/07-auditorias/etapa-4-produccion-y-privacidad.md) y [gates](docs/06-despliegue/checklist-produccion.md).

**NO PROGRAMAR:** tablas antiguas de 17 (sesion_captura, geo_sesion, etc.), subtipo documental, alcance_consulta, permisos de lectura por presbiterio, integrantes nominales GC, módulos SGSP. **SÍ:** consulta total por funcionalidad con filtros opcionales Presbiterio/Sede, edición solo de autor, 11 tablas, tabla CATALOGO y UBIGEO independiente.

Los deltas `openspec/changes/2026-10-31-levantamiento-etapa-1`, `etapa-2`, `etapa-3` son antecedentes; todo texto incompatible con V4 está **supersedido**, no es orden de construcción. Nuevos cambios desde la versión V4 y con evidencia de SQL/test. No declarar PASS sin ejecutar pruebas reales.

## REGLA V2.3 — FASE UI ANTES DE GENERAR REACT
**Fuente única de UX vigente:** [PCD_v2.3.html](prototypes/PCD_v2.3.html) y [matriz](docs/02-diseno/pcd-v2.3-alineacion.md). V2.2 es histórica. La nueva maqueta **no elimina** las decisiones pendientes E1-D01/E1-D02/H-23/H-24; cualquier solución allí requiere aprobación. No copiar simulación de passwords, roles, datos, GPS ni localStorage a producción. Validar [CA-101…111](docs/05-pruebas/validacion-prototipo-v2.3.md).

## Puerta documental I00
Ejecutar `node scripts/validar-sdd-i00.cjs` y `node scripts/validar-prototipo-v2.3.cjs` en el clon antes de programar. Seguir [I00 — handoff](docs/08-ia/I00-verificacion-estatica-y-handoff.md). Detectar referencias al modelo antiguo; no modificar el prototipo V2.2 ni crear funcionalidades SGSP.
