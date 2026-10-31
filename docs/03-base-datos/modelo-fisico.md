# Modelo físico PCD (MySQL/MariaDB)
Script: `database/migrations/001_initial.sql`. Motor InnoDB, UTF8MB4; PK `BIGINT UNSIGNED AUTO_INCREMENT`, FECHAS DATETIME(6)/DATE y DECIMAL(10,7) para coordenadas; jamás FLOAT para IDs.

## Normas
- SQL parametrizado con PDO y prepared statements; clave foránea para todas las referencias esenciales.
- Índice compuesto `(tipo_documento,numero_documento_norm)` UNIQUE (se permiten NULL).
- Sede UNIQUE `(presbiterio_id,nombre_norm)`; `nombre_norm` se calcula en la aplicación y se revalida en transacción.
- Índices búsquedas: `persona(apellidos,nombres)`, `ficha(sede_id,created_at)`, `ficha(created_by,created_at)`, `auditoria(recurso,id)`, `login_evento(fecha)`.
- Concurrencia: `version` en fichas y entidades editables; PATCH exige versión y devuelve 409 si antigua.
- Idempotencia: clave por acción/recurso enviada en header `Idempotency-Key`; misma clave+payload → mismo resultado.
- Fechas del papel son distintas de fecha de operación.
- Para mantener integridad de ficha GC ↔ Persona/Grupo se requiere validación en servicios, además de FK.
- GPS por sesión, referencia a `geo_sesion` desde auditoría; datos y fecha de captura disponibles para cada guardado. No recapturar.
- Credenciales (incluso comunes) solo hash; establecer valor real seguro por instalador y **no en scripts Git**.

## Estado
**Modelo de implementación propuesto y versionado**, sujeto a ejecutar en MySQL/MariaDB exactos de cPanel, EXPLAIN, pruebas de concurrencia y revisión del diccionario. No existe todavía backend implementado.


## Snapshot de fuente y autorización
`ficha_captura.datos_fuente_json` preserva datos originales de cada formulario para no perder discrepancias si dos fichas corresponden a una Persona. DIGITADOR modifica solo sus propias fichas con `version`/auditoría; la Persona consolidada la modifica su creador o ADMIN. Esto preserva origen para migración futura.

## Hallazgos E1: modelo en discusión
El [levantamiento E1](../07-auditorias/etapa-1-resolucion.md) propone conservar el literal de cada ficha en `datos_fuente_json` y mantener la Persona consolidada por separado; no fusionar registros GC sobre coincidencias débiles. Los cambios SQL definitivos están **pendientes**, no considerar este documento validación productiva.
