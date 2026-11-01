# PCD V4.0 — Modelo físico vigente

**Único SQL inicial autorizado para una base nueva:** [database/migrations/001_initial.sql](../../database/migrations/001_initial.sql). **11 tablas y 134 columnas**. El SQL anterior de 17 tablas fue sustituido antes de desplegarse; si se descubre una BD ya creada, **no** ejecutar 001 encima: preparar migración/backup específico.

## Productos entregables
- [**Anexo 01 — ER físico completo**](anexos/anexo-01-diagrama-fisico.md), fuente editable [Mermaid](diagramas/er-pcd-fisico.mmd).
- [**Anexo 02 — Diccionario por columna**](anexos/anexo-02-diccionario-fisico.md), con tipos, claves, índices, restricciones y default.
- [**Justificación de 11 tablas**](anexos/justificacion-11-tablas.md): pertenencia a Registro, Seguridad, Configuración o Control y motivo de persistencia.
- [Seed de listas y criticidad](../../database/seeds/002_catalogos_calidad.sql).

## Reglas de diseño
Una tabla `organizacion` para presbiterios/sedes, una `catalogo` para listas pequeñas y parámetros críticos y una `ubigeo` jerárquica INEI; `persona`, `ficha`, `grupo_crecer` son las tres entidades de captura. `sesion` integra el estado GPS. No existen `usuario_presbiterio`, `alcance_consulta`, `tipo_documento_detalle`, `campana`, `geo_sesion`, `conyuge_pendiente`, `persona_responsabilidad_captura` ni `campo_critico` como tablas.

**Auditoría y validación:** CHECK/FK compuestas pendientes de prueba en motor real, más revisión de árboles ORGANIZACION/UBIGEO en PHP y transacciones para ficha+auditoría+idempotencia. No anunciar compatibilidad productiva sin evidencias.
