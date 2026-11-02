# AGENTS — guía breve para agentes desarrolladores de PCD

**Regla central:** trabajar SOLO en PCD, nunca en SGSP. La migración a SGSP es futura y está fuera del alcance actual.

## 1. Alcance obligatorio
Leer **primero** [ALC-PCD-4.0](docs/01-analisis/alcance-funcional.md) y las [decisiones vigentes](docs/01-analisis/decisiones-pendientes.md). Definen ALC-01…ALC-12, EXC-01…EXC-10, roles, calidad manual y la frontera con SGSP.

Cada cambio debe trazarse a un ALC y a un requisito OpenSpec aprobado. Una propuesta fuera de alcance se registra para decisión, **no** se implementa. Si hay contradicción, reportarla antes de codificar.

## 2. Lectura mínima antes de implementar
1. [Alcance](docs/01-analisis/alcance-funcional.md), [decisiones](docs/01-analisis/decisiones-pendientes.md) y [aprobaciones](docs/08-ia/APROBACIONES.md).
2. [SQL de 11 tablas](database/migrations/001_initial.sql), [ER](docs/03-base-datos/anexos/anexo-01-diagrama-fisico.md) y [diccionario](docs/03-base-datos/anexos/anexo-02-diccionario-fisico.md).
3. [Reglas](docs/01-analisis/reglas-negocio.md), [OpenSpec vigente](openspec/specs/README.md) y [OpenAPI](docs/04-integracion/openapi.yaml).
4. [Seguridad](docs/02-diseno/seguridad-auditoria.md), [Etapa 4](docs/07-auditorias/etapa-4-produccion-y-privacidad.md) y [checklist productivo](docs/06-despliegue/checklist-produccion.md).
5. [Contrato de ejecución V4.1](docs/08-ia/CONTRATO-EJECUCION-V4.1.md) **antes del primer prompt de generación**.
6. [Prototipo V2.3](prototypes/PCD_v2.3.html) solo como UX; [casos de prueba](docs/05-pruebas/casos-prueba.md) y [protocolo de auditoría](docs/07-auditorias/protocolo-auditoria-alcance.md).

## 3. Prohibiciones vigentes
- No crear roles pastorales, módulos ni integración operativa SGSP.
- No convertir `localStorage`, el demo login ni datos de demostración en producción; V2.2 es histórico.
- No exigir número de ficha impresa, no bloquear el guardado por campos críticos faltantes, no exigir revisión de supervisor.
- No exigir consentimiento de geolocalización para guardar: **una captura por sesión**, opcional, reutilizada y no probatoria; no usarla como restricción de edición.
- No introducir frameworks o servicios residentes que sobrecarguen el hosting sin ADR aprobada; cPanel sin Node residente.
- No exponer contraseñas, coordenadas ni datos personales en Git, `localStorage`, URL, logs o respuestas no autorizadas.
- No alterar historial ni autor original: ADMIN modifica con su propia identidad.
- No afirmar pruebas exitosas sin ejecución verificable.
- No CRUD de presbiterios (LIM/TRU/CHI/IQU fijos); las sedes sí se crean con deduplicación.
- No subtipo documental, modelo de 17 tablas, `alcance_consulta`, `usuario_presbiterio` ni integrantes nominales de GC.
- No resolver por iniciativa propia E1-D02, H-23 ni H-24.

## 4. Decisiones vigentes esenciales
Resumen en [APROBACIONES.md](docs/08-ia/APROBACIONES.md):
- Login con Presbiterio + usuario tecleado + contraseña; seis DIGITADORES por presbiterio, ADMIN y REPORTES nacionales.
- Clave común de DIGITADOR solo UAT; rotación individual, hashing seguro, límite de intentos y revocación antes de producción.
- Sede dentro del presbiterio autenticado; Persona y GC integrados, GC opcional después de persistir Persona.
- Edición: DIGITADOR solo sus fichas, ADMIN según rol, REPORTES solo lectura; consulta general con filtros opcionales.
- Fichas incompletas persistibles; campos críticos configuran indicadores, no bloquean.
- Alta de ficha sin `AUDITORIA_EVENTO`; edición audita solo campos cambiados con actor y antes/después.
- E1-D01 aprobada: reutilizar Persona documental y anexar ficha sin sobrescribir el consolidado.
- 11 tablas, 146 columnas, 23 FK; `created_at/updated_at` en todas; JSON de ficha cerrado por versión.

## 5. Ciclo SDD obligatorio
`proposal.md` → `design.md` → `spec.md` Given/When/Then → `tasks.md` → código y migraciones → pruebas automáticas → trazabilidad → revisión humana. Registrar pruebas, fallos y correcciones en el PR. Las decisiones inciertas van a [decisiones-pendientes](docs/01-analisis/decisiones-pendientes.md); no inventar.

## 6. Definición de terminado
Contrato API y frontend/backend coherentes; permisos y SQL enforce; pruebas válidas y sin regresiones; auditoría transaccional; despliegue reproducible sin Node en hosting; datos de prueba ficticios; métricas verificadas antes de aceptar umbrales.

## 7. Mantenimiento de índices
Al crear, mover, renombrar o eliminar archivos o carpetas, actualizar el `README.md` de la carpeta afectada y el índice del `README.md` raíz cuando cambie una ruta principal. Cada README declara finalidad, estado real y enlaces vigentes.

## 8. Puertas y precedencia
- **Puerta I00:** `node scripts/validar-sdd-i00.cjs` y `node scripts/validar-prototipo-v2.3.cjs`; son estáticos y **no** sustituyen MySQL/PHP/E2E.
- **Precedencia V4.1:** alcance y decisiones → SQL/ER/diccionario → reglas/specs/OpenAPI → Etapa 4/checklist. Cualquier texto de E1–E3 incompatible está supersedido.
- **Reglas V4.1:** presbiterios fijos, E1-D01 aprobada, JSON Schema cerrado, índices de reportes solo con consultas/`EXPLAIN` reales.
- **H-40** sigue pendiente: prohibido usar datos reales antes del GO institucional.
- **Revisión del especialista:** no abrir una nueva auditoría formal hasta su evaluación.
