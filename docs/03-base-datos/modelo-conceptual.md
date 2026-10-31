# Modelo conceptual independiente PCD
**PCD no comparte la BD del SGSP.** Entidades clave: `Presbiterio` agrupa `Sede`; `Usuario` tiene `UsuarioPresbiterio` (ámbitos) y `Sesion`; `PersonaCaptura` puede tener varias `FichaCaptura`; un `GrupoCrecerCaptura` se registra mediante ficha GC y vincula líder a Persona; `Responsabilidad` y `ConyugePendiente` conservan datos del papel; `CriticidadCampo` parametriza informes; `GeoSesion` es captura única asociada a sesión; `AuditoriaEvento` conserva operaciones; `LoginEvento` intentos.

## Cardinalidades
- Presbiterio 1:N Sede.
- Usuario N:M Presbiterio vía UsuarioPresbiterio (digitadores iniciales típicamente 1, ADMIN/REPORTES cobertura general).
- Usuario 1:N Sesión; Sesión 0:1 GeoSesion; Sesión 1:N AuditoriaEvento.
- Persona 1:N FichaCaptura de tipo PERSONA (para soportar varias fichas de origen).
- FichaCaptura 1:0..N Responsabilidad; Persona 1:0..N ConyugePendiente.
- FichaCaptura de tipo GC 1:0..1 GrupoCrecerCaptura; GrupoCrecerCaptura N:1 Persona (líder).
- Una ficha (Persona o GC) tiene siempre presbiterio+sede de origen y `created_by` inmutable.

## Decisiones y advertencias
- Los documentos pueden faltar; UNIQUE (tipo,número_normalizado) aplica **solo cuando ambos están presentes**.
- N.º ficha es opcional, no índice único.
- La pertenencia GC nominal no se modela desde la cantidad aproximada.
- Un cambio de líder requiere **nuevo GC** según regla pastoral de referencia, no actualización destructiva.

