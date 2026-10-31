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
El [levantamiento E1](../07-auditorias/etapa-1-resolucion.md) precisa que cada ficha conserva los datos **aceptados por validaciones de tipo** y la Persona consolidada es independiente. **No se admiten fechas imposibles**; se guarda DATE real o NULL si el digitador deja el campo vacío. La revisión de fidelidad de nombres contra el papel es manual por presbiterio fuera de PCD. GC solo registra líderes y resumen de integrantes aproximados; no miembros nominales. SQL y API definitivos aún pendientes.

## Segunda etapa H-03/H-07/H-13/H-18/H-35 — reglas físicas propuestas
**SQL:** `database/migrations/001_initial.sql` ahora propone `usuario.auth_version`, `sesion_captura.auth_version_al_ingreso/revocada_en`, índice `login_evento(ip,created_at)`, catálogo compacto `catalogo_valor`, `persona_captura.tipo_documento_detalle/clave_identidad` (UNIQUE con NULL permitido), y `campo_critico.etiqueta/ruta_dato/desactivable`. La columna `marca_calidad` se elimina del esquema inicial: el reporte calcula completitud con parámetros vigentes.

**Integridad GC:** no hay `grupo_crecer_captura.lider_persona_id`; el líder se obtiene por `ficha_captura.persona_id`. FKs compuestas y `CHECK` de `ficha_tipo` evitan referenciar una ficha PERSONA como GC y viceversa; **necesitan ejecución de prueba en MySQL/MariaDB real**, sobre todo `CHECK`.

**Identidad:** no usar `UNIQUE(tipo_documento,numero_documento_norm)` para OTRO ambiguo; construir clave de identidad solo con tipo/subtipo/número suficientemente confiables y conservar ceros. [Reglas completas](catalogos-e-identidad.md).

**Calidad:** campos iniciales desde [Matriz](campos-criticos.md) del prototipo; diferenciar obligatorios técnicos de campos críticos de reporte. No crear flujo de conciliación/validación contra papel.

**Estado:** propuesta de esquema inicial todavía no probada, no migración aplicada. No usar SQL en BD productiva hasta validar hosting y decisiones pendientes.
