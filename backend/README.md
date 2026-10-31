# backend — API PHP y reglas de negocio (pendiente de implementación)

[↑ Índice principal](../README.md)

**Estado actual:** carpeta preparada; **todavía no hay backend PHP productivo** (solo `.gitkeep`).

**Contenido previsto:** API REST ligera en **PHP 8.x**, sesiones seguras, permisos por presbiterio/rol, validaciones, búsqueda de duplicados, persistencia PDO/MySQL InnoDB, auditoría, geolocalización única por sesión, administración y generación de reportes.

**Leer antes de implementar:** [contratos API](../docs/04-integracion/README.md), [seguridad y auditoría](../docs/02-diseno/seguridad-auditoria.md), [modelo de datos](../docs/03-base-datos/README.md), [reglas de negocio](../docs/01-analisis/reglas-negocio.md) y [specs](../openspec/README.md).

**Obligatorio:** revalidar autorizaciones en cada endpoint; guardar datos y auditoría en una transacción; usar consultas preparadas, idempotencia y hash de claves. Mantener secretos fuera del Document Root y no añadir procesos residentes al hosting compartido.
