# Plan de rendimiento PCD en hosting compartido
## Objetivo
Evitar que campaña degrade sitio actual de UNOB/iglesia, sin sacrificar consistencia.

## Escenarios
- Calentar frontend estático, cargar catálogos una vez.
- 10 usuarios simultáneos → login/sede, buscar 1 documento y guardar Persona.
- 20 usuarios → 70% Personas, 15% GC, 10% Consulta, 5% Alta sede; ráfagas y doble clic.
- 30 usuarios → estrés controlado en horario no crítico, parar si aparecen 5xx, saturación o deterioro web institucional.
- Volumen 5.000 personas/GC simulados, sin datos reales; índices/EXPLAIN.
- React build: registrar JS/CSS comprimidos y latencia navegable en 320/390/768/1440px.

## Métricas
p50/p95/p99 de login, lookup, guardar, consulta; tasa de errores; duración de transacción; queries lentas; tamaño payload; métricas disponibles en cPanel. Baseline antes/durante/después del sitio web existente.

## Umbrales iniciales propuestos, no garantizados
Primer render p95 ≤ 2 s; validar duplicado p95 ≤ 500 ms; guardar Persona/GC p95 ≤ 1 s; revisar capacidad en entorno real, renegociar si el hosting no sostiene metas sin dañar la web. No saltar a nube ni microservicios automáticamente: medir y optimizar consultas/assets primero.

## Evidencia
Informe versionado por commit/hash, entorno, fecha, configuración, datos sintéticos, herramienta, fallos y comparativo. No afirmar metas superadas sin ejecución.

