# PCD V4.0 — Decisiones pendientes y aprobadas

**Norma de alcance:** [ALC-PCD-3.1](alcance-funcional.md) actualizada con el modelo de 11 tablas. La documentación de las Etapas 1–3 anterior es **histórica** donde contradice estas decisiones.

## Decisiones aprobadas (no volver a consultar)
| ID | Decisión vigente |
|---|---|
| H-12/RN-29 | **Consulta general** en toda funcionalidad autorizada, filtros opcionales por presbiterio y/o sede. **Sin `alcance_consulta` ni `usuario_presbiterio`**. La edición permanece restringida a autor de ficha o ADMIN. |
| H-13 | Solo **tipo y número de documento** en interfaz/API/BD. DNI normalizado con ceros conservados; CE según validación aplicable; OTRO sin fusión exacta automática. **Sin subtipo**. |
| Modelo físico | **11 tablas**: organizacion, usuario, sesion, login_evento, catalogo, ubigeo, persona, ficha, grupo_crecer, auditoria_evento, idempotencia. Ver [justificación](../03-base-datos/anexos/justificacion-11-tablas.md). |
| Organización | **Una sola tabla `organizacion`** para presbiterio y sede, con relación padre/hijo. |
| Catálogos | **Una tabla `catalogo`**, incluidas las categorías de campos críticos; no tablas físicas para listas pequeñas. |
| Ubigeo | **Una tabla `ubigeo`** jerárquica; catálogo oficial INEI será **entrada de construcción I02**. |
| H-03 | Clave inicial común digitadores aceptada funcionalmente con riesgo residual de suplantación; ADMIN/REPORTES con claves distintas, bloqueo/bitácora/CSRF/revocación obligatorios. |
| H-05/H-09 | Fecha calendario imposible se rechaza o deja en blanco; la fidelidad al papel se revisa manualmente **fuera de PCD** por presbiterios. No conciliación interna. |
| H-08 | Solo líderes GC y resumen del grupo; sin miembros nominales. |
| H-28/H-32/H-35 | Perfiles nacionales, control de sesiones y líder GC único derivado de ficha, sin redundancias. |

## Pendientes reales de aprobación funcional
| ID | Pendiente | Impacto |
|---|---|---|
| **E1-D01** | Definir el procedimiento de vinculación de una nueva ficha con una Persona documentalmente existente de otro presbiterio, sin sobrescribir su consolidado. | I02 |
| **E1-D02** | Interpretar `grupo_ya_existe` en relación con GC previamente capturados sin crear maestro permanente. | I03 |
| **H-23** | Registro de una ficha GC por un digitador distinto al autor de la Persona líder, sin permiso de alterar dicha Persona. | I03 |
| **H-24** | Mínimo de entrada para impedir alta totalmente vacía y permitir fichas incompletas. | I02 |
| **H-40** | Finalidad, aviso, base legal aplicable, retención de datos/GPS/backups y responsable formal de datos. | I06/puesta en producción |

## Insumos técnicos pendientes, no ampliaciones funcionales
- **I02:** fuente/corte oficial INEI e importación en `ubigeo`; verificar formato de C.E. con reglas aplicables.
- **I00:** versión PHP/MySQL/MariaDB real de hosting; ejecutar SQL de 11 tablas y seed en entorno de pruebas; FK y CHECK.
- **H-14:** Excel binario de formularios aún no versionado en GitHub; inventario textual ya publicado.
- **DP-01/02:** no inventar las sedes faltantes; permitir alta autorizada durante captura.
- **DP-04:** volumen concurrente real desconocido, probar 10/20/30.
- **DP-08:** migración SGSP futura y **fuera de alcance**.
- **DP-09:** secretos fuera de Git y logs.
- **DP-10:** exportación opcional no aprobada.

**No suponer que un hallazgo de auditoría amplía las funcionalidades del aplicativo temporal.**
