# Migraciones de la base PCD

[↑ database](../README.md) · [↑ Índice principal](../../README.md)

| Archivo | Alcance | Estado |
|---|---|---|
| [001_initial.sql](001_initial.sql) | Esquema inicial propuesto de PCD: presbiterios, sedes, usuarios, sesiones, Personas, fichas, GC, criticidad, auditoría e idempotencia. | **Pendiente de ejecución y pruebas en MySQL/MariaDB**. |

**Reglas de mantenimiento:** no editar silenciosamente migraciones ya aplicadas en un entorno real; versionar las siguientes como `002_*.sql`, `003_*.sql`, etc. Documentar precondiciones, cambios, validación y rollback cuando corresponda. Mantener [modelo lógico/físico](../../docs/03-base-datos/README.md) y [casos de prueba](../../docs/05-pruebas/README.md) alineados.

No incluir credenciales, información real de miembros ni datos de ubicación de usuarios en scripts versionados.

## E2 — Compatibilidad de constraints
`001_initial.sql` ahora incorpora `CHECK` y FK compuestas en la relación ficha/Persona/GC, revocación de sesiones, catálogo, identidad normalizada y calidad derivada. Se requiere **prueba de creación en versión exacta MySQL/MariaDB del hosting** antes de aprobar I00/I01; no se ha ejecutado. Para nuevos entornos ya desplegados no se aplica un `ALTER` implícito: migración futura separada tras inspección del estado real.

**ÚNICO ESQUEMA INICIAL V4:** [001_initial.sql](001_initial.sql) sustituye la propuesta anterior de 17 tablas por **11**. La migración inicial es SOLO para una BD nueva y aún requiere prueba en el motor cPanel. Una BD existente necesita procedimiento de transición y respaldo, no un RE-RUN del archivo.
