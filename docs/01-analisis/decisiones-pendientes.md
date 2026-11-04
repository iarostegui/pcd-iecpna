# PCD V4.1 — Decisiones pendientes y aprobadas

**Norma de alcance:** [ALC-PCD-4.0](alcance-funcional.md) actualizada con el modelo de 11 tablas. La documentación de las Etapas 1–3 anterior es **histórica** donde contradice estas decisiones.

## Decisiones aprobadas (no volver a consultar)
| ID | Decisión vigente |
|---|---|
| H-12/RN-29 | **Consulta general** en toda funcionalidad autorizada, filtros opcionales por presbiterio y/o sede. **Sin `alcance_consulta` ni `usuario_presbiterio`**. La edición permanece restringida a autor de ficha o ADMIN. |
| H-13 | Solo **tipo y número de documento** en interfaz/API/BD. DNI normalizado con ceros conservados; CE según validación aplicable; OTRO sin fusión exacta automática. **Sin subtipo**. |
| E1-D01 | **APROBADO:** reutilización de Persona documentalmente coincidente entre presbiterios usando `POST /personas/{id}/fichas`; crear nueva ficha sin alterar consolidado ni fichas previas. | 
| Auditoría de ficha | Alta solo en `ficha.created_at/created_by`; editar registra exclusivamente campos alterados (antes/después) en `auditoria_evento`. | 
| Presbiterios | Fijos iniciales LIM Lima, TRU La Libertad – Trujillo, CHI Chimbote, IQU Iquitos. No alta por aplicación; solo SQL autorizado. Sedes sí tienen alta desde PCD. | 
| Modelo físico | **11 tablas**: organizacion, usuario, sesion, login_evento, catalogo, ubigeo, persona, ficha, grupo_crecer, auditoria_evento, idempotencia. Ver [justificación](../03-base-datos/anexos/justificacion-11-tablas.md). |
| Organización | **Una sola tabla `organizacion`** para presbiterio y sede, con relación padre/hijo. |
| Catálogos | **Una tabla `catalogo`**, incluidas las categorías de campos críticos; no tablas físicas para listas pequeñas. |
| Ubigeo | **Una tabla `ubigeo`** jerárquica; catálogo oficial INEI será **entrada de construcción I02**. |
| H-03 | Clave común de digitadores **solo pruebas/UAT**; **pase productivo exige rotación a credenciales individuales** antes de GO. ADMIN/REPORTES con secretos distintos, bloqueo/CSRF/revocación. |
| H-05/H-09 | Fecha calendario imposible se rechaza o deja en blanco; la fidelidad al papel se revisa manualmente **fuera de PCD** por presbiterios. No conciliación interna. |
| H-08 | Solo líderes GC y resumen del grupo; sin miembros nominales. |
| H-28/H-32/H-35 | Perfiles nacionales, control de sesiones y líder GC único derivado de ficha, sin redundancias. |
| H-14 — formulario de origen | **CONFIRMADO:** `SGSP_Fichas_Imprimibles_A4 (2).xlsx` es el formato efectivamente entregado para la toma de datos en papel y debe servir como referencia de digitación del PCD (Persona y Líder GC). La regla H-24 de obligatoriedad del sistema prevalece frente a las marcas del impreso; conservar todos los datos de origen correspondientes sin inventar campos. No subir el Excel a un repositorio público. |
| H-24 — mínimo de captura Persona | **APROBADO:** presbiterio y sede del contexto, nombres, apellido paterno, apellido materno, tipo y número de documento y teléfono de contacto obligatorios. Los demás datos de Persona pueden estar incompletos, según contratos vigentes. La validación corresponde a API y formulario. |\n| H-40 — asignación de responsabilidad | **APROBADO FUNCIONALMENTE:** el perfil nacional REPORTES es responsable de gestionar la designación institucional y la aprobación institucional vinculadas al uso de PCD y tratamiento de datos. Esto **no concede permisos de escritura operativa**, ni constituye por sí solo un acto de aprobación institucional o GO de producción. |

## Pendientes reales de aprobación funcional
| ID | Pendiente | Impacto |
|---|---|---|
| **E1-D02** | Interpretar `grupo_ya_existe` en relación con GC previamente capturados sin crear maestro permanente. | I03 |
| **H-23** | Registro de una ficha GC por un digitador distinto al autor de la Persona líder, sin permiso de alterar dicha Persona. | I03 |

| **H-40 (restante)** | El perfil responsable de la gestión de designación y aprobación es **REPORTES** (decisión funcional aprobada). Queda pendiente identificar al titular/persona institucional, documentar el acto de designación y la aprobación efectiva, finalidad, aviso, base legal aplicable y retención de datos/GPS/backups. **Sin GO productivo.** | I06/puesta en producción |

## Insumos técnicos pendientes, no ampliaciones funcionales
- **I02:** fuente/corte oficial INEI e importación en `ubigeo`; verificar formato de C.E. con reglas aplicables.
- **I00:** versión PHP/MySQL/MariaDB real de hosting; ejecutar SQL de 11 tablas y seed en entorno de pruebas; FK y CHECK.
- **H-14:** confirmada la versión de referencia del Excel físico entregado para toma de datos/digitación. Su contenido está disponible como adjunto del proyecto; **no se versionará el binario en repositorio público** sin revisión de privacidad/licencia. La trazabilidad de campos debe comprobarse en I02 e I03.
- **DP-01/02:** no inventar las sedes faltantes; permitir alta autorizada durante captura.
- **DP-04:** volumen concurrente real desconocido, probar 10/20/30.
- **DP-08:** migración SGSP futura y **fuera de alcance**.
- **DP-09:** secretos fuera de Git y logs.
- **DP-10:** exportación opcional no aprobada.

**No suponer que un hallazgo de auditoría amplía las funcionalidades del aplicativo temporal.**

**Registro técnico aprobado adicional:** todas las 11 tablas incorporan `created_at` y `updated_at`; normalización NFKD sin tildes para nombres de sedes, mayúsculas y espacios, índices nuevos de reportes únicamente tras EXPLAIN; JSON de cónyuge y responsabilidades con versión y contrato cerrado; el repositorio seguirá público hasta que su propietario autorice cambiar visibilidad, por lo que se prohíbe subir secretos/PII.
