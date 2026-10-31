# Migraciones de la base PCD

[↑ database](../README.md) · [↑ Índice principal](../../README.md)

| Archivo | Alcance | Estado |
|---|---|---|
| [001_initial.sql](001_initial.sql) | Esquema inicial propuesto de PCD: presbiterios, sedes, usuarios, sesiones, Personas, fichas, GC, criticidad, auditoría e idempotencia. | **Pendiente de ejecución y pruebas en MySQL/MariaDB**. |

**Reglas de mantenimiento:** no editar silenciosamente migraciones ya aplicadas en un entorno real; versionar las siguientes como `002_*.sql`, `003_*.sql`, etc. Documentar precondiciones, cambios, validación y rollback cuando corresponda. Mantener [modelo lógico/físico](../../docs/03-base-datos/README.md) y [casos de prueba](../../docs/05-pruebas/README.md) alineados.

No incluir credenciales, información real de miembros ni datos de ubicación de usuarios en scripts versionados.
