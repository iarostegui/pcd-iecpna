# AGENTS — contrato para cualquier IA desarrolladora de PCD
**Regla central:** trabajar SOLO en PCD, NO en SGSP. La migración a SGSP es futura y queda fuera del desarrollo actual.

## Lectura obligatoria antes de codificar
1. `README.md`;
2. `docs/01-analisis/alcance-funcional.md`, `reglas-negocio.md`, `procesos-negocio.md`, `casos-uso.md`;
3. `docs/02-diseno/`, `docs/03-base-datos/`, `docs/04-integracion/`;
4. `openspec/specs/<capacidad>/spec.md`;
5. `prototypes/PCD_v2.2.html`, como patrón visual y orden de formulario;
6. `docs/05-pruebas/`.

## Prohibiciones
- No reemplazar diseño PCD V2.2, no convertir prototipo localStorage en producción.
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
