# Prompt maestro para ejecutar en Codex / IA desarrolladora
Actúa como arquitecto y desarrollador senior de PCD (Plataforma de Captura de Datos IECPNA), **aplicación temporal independiente del SGSP**.

**MANDATO DE ALCANCE:** `docs/01-analisis/alcance-funcional.md` (ALC-PCD-3.1) define el alcance autorizado de PCD: **ALC-01…ALC-12 incluidos; EXC-01…EXC-10 excluidos**. Leerlo y citar los IDs aplicables en cada cambio. Si una especificación, auditoría o idea pide algo excluido, no programarlo: registrar la propuesta, impacto y riesgo para decisión del responsable funcional. Un auditor puede cuestionar el alcance, **no imponer ampliaciones**. Seguridad, privacidad, integridad y auditoría de funciones existentes siguen siendo obligatorias.

1. Lee en orden `README.md`, `AGENTS.md`, **`docs/01-analisis/alcance-funcional.md`**, `docs/01-analisis/`, `docs/02-diseno/`, `docs/03-base-datos/`, `docs/04-integracion/`, `openspec/specs/`, `docs/05-pruebas/`, `docs/08-ia/plan-implementacion.md`.
2. Inspecciona `prototypes/PCD_v2.3.html` y replica el diseño y los flujos aprobados con React/TypeScript/Vite. NO copias su almacenamiento local ni su autenticación simulada.
3. Implementa backend PHP 8.x modular usando PDO y MySQL InnoDB sobre hosting existente con cPanel y subdominio `pcd.unobdata.com`. Sin servicios residentes adicionales.
4. Preserva decisiones: seis digitadores por presbiterio, clave común provisionada fuera de Git sin cambio forzado, ADMIN/REPORTES nacionales, sede consistente con presbiterio, crear sede sin aprobación, guardar incompletos, número ficha opcional, GC después de Persona, duplicados, auditoría y GPS una sola vez por sesión.
5. Antes de codificar cada módulo, crea/actualiza `openspec/changes/<id>/proposal.md`, `design.md`, `specs/<capacidad>/spec.md`, `tasks.md`; luego implementa, prueba y reporta. No programes fases siguientes sin verificar las anteriores.
6. No inventes catálogo faltante ni campos de SGSP; registra decisiones pendientes. No implementes migración ahora.
7. Prueba rutas API, SQL, permisos, rendimiento y UX; adjunta comandos/resultados reales. No afirmes pruebas no ejecutadas.
8. Produce al cierre de iteración: archivos cambiados, trazabilidad RF/API/SQL/CA, comandos de prueba, resultados, bloqueos y plan inmediato.

Comienza con **I00** de `plan-implementacion.md`; implementa únicamente ese alcance en la primera iteración.

**Auditoría en cualquier iteración:** aplicar el [protocolo de auditoría y alcance](../07-auditorias/protocolo-auditoria-alcance.md); separar deficiencias reales en alcance y propuestas de ampliación, con severidad solo para riesgos o incumplimientos demostrables.

## Gate E2
Antes de ejecutar I01, lee [Etapa 2](../07-auditorias/etapa-2-arquitectura-seguridad-datos.md), [Catálogos/identidad](../03-base-datos/catalogos-e-identidad.md), [Calidad](../03-base-datos/campos-criticos.md), [Seguridad](../02-diseno/seguridad-auditoria.md) y [Preparación de iteraciones](../07-auditorias/preparacion-iteraciones.md). Las decisiones E2-D01/D02/D03 están resueltas por la versión V4; NO recuperar propuestas obsoletas de ámbito territorial de consulta ni subtipo documental. Controlar el dato de entrada, evitar listar usuarios, y **no replicar contraseñas de demo**. Mantener IA/hosting dentro del alcance temporal PCD.

## REGLAS V4.0 ANTES DE GENERAR CÓDIGO (PRIORIDAD SOBRE TODO TEXTO V3)
1. BD inicial exclusiva: [11 tablas SQL](../../database/migrations/001_initial.sql); los anexos se regeneran desde ese SQL. No recuperar 17 tablas ni añadir tablas por catálogos de 5–7 filas.
2. Únicos datos documentales `tipo_documento` y `numero_documento`. DNI/CE normalizar sin ceros inventados, OTRO solo coincidencia potencial, **sin subtipo**.
3. Acceso a funcionalidad Consulta implica **lectura total** y filtros opcionales Presbiterio/Sede; **no** ámbito territorial por usuario. DIGITADOR solo edita sus fichas, REPORTES no escribe.
4. `organizacion` = presbiterio+sede; `catalogo` = tabla de tablas (campos críticos por JSON); `ubigeo` = 1 tabla INEI por importar en I02; `sesion` integra GPS; `ficha.datos_fuente_json` conserva cónyuge/responsabilidades.
5. No modificar HTML V2.2 ni SGSP salvo pedido explícito. **No generar código productivo desde especificaciones históricas E1–E3 contradictorias**.
6. Etapa 4: [protección de datos](../07-auditorias/etapa-4-produccion-y-privacidad.md), [checklist](../06-despliegue/checklist-produccion.md). H-40 no resuelto, ejecución de SQL/PHP/React no realizada. No afirmar GO ni pruebas aprobadas.

## **ACTUALIZACIÓN V2.3 — GATE DE UI ANTES DE CONSTRUIR**
Usar **prototypes/PCD_v2.3.html** (no V2.2) como autoridad actual de UX, junto a [matriz de cambios](../02-diseno/pcd-v2.3-alineacion.md). Prototipo **V2.2 se mantiene inalterado** como histórico. Solo backend PHP/MySQL y React productivos autentican de verdad: NO copiar `DEMO_KEYS`, `localStorage`, IDs ficticios, valores de sedes ni API simulada. Consulta es general por funcionalidad con filtros presbiterio/sede, DIGITADOR edita propias; GC no concilia grupos; fecha imposible no se guarda; GPS única vez en SESION. Completar [CA-101…111](../05-pruebas/validacion-prototipo-v2.3.md) con evidencias, además de casos V4.

**Comando de control sin dependencias:** `node scripts/validar-prototipo-v2.3.cjs`. Si falla, no avanzar a I01 antes de corregir. Después ejecutar pruebas interactivas CA-101…111 y API; no declarar pruebas como realizadas por solo documentarlas.
