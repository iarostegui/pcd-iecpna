# database — Artefactos físicos de la base MySQL de PCD

[↑ Índice principal](../README.md)

Aquí vivirán las **migraciones y los datos de inicialización** de la base **independiente** de PCD. El análisis de entidades, claves y reglas está en [docs/03-base-datos](../docs/03-base-datos/README.md).

| Carpeta | Qué almacena |
|---|---|
| [migrations/](migrations/README.md) | Versiones SQL controladas para crear/evolucionar el esquema MySQL/MariaDB. |
| [seeds/](seeds/README.md) | Reglas y futuros scripts de provisión de usuarios, parámetros y catálogos confirmados; **sin contraseñas ni datos ficticios productivos**. |

**Advertencia:** `001_initial.sql` es un **diseño propuesto**, no prueba de instalación. Ejecutar primero en base de pruebas y validar contra versión efectiva de cPanel, índices y reglas. No almacenar volcados con datos personales.

**Inicialización E2:** [database/seeds/002_catalogos_calidad.sql](seeds/002_catalogos_calidad.sql) precarga catálogos y criticidad verificados; no contiene credenciales ni sedes. No ejecutar en BD productiva sin validar primero el esquema y las decisiones pendientes.
