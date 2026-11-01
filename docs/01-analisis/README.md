# 01 — Análisis funcional de PCD

[↑ docs](../README.md) · [↑ Índice principal](../../README.md)

Define **qué necesidad resuelve PCD**, qué datos se transcriben de los formularios impresos, quién opera y con qué reglas. No contiene la lógica física de programación.

| Archivo | Contenido y finalidad |
|---|---|
| **[alcance-funcional.md](alcance-funcional.md)** | **Documento normativo ALC-PCD-3.1**: objetivos, funcionalidades ALC-01…12, exclusiones EXC-01…10, responsabilidades, control de calidad y límites obligatorios para IA/auditoría. |
| [procesos-negocio.md](procesos-negocio.md) | Pasos operativos: preparación, login, sede, Persona, GC, corrección, reportes y cierre. Distingue negocio de automatización. |
| [reglas-negocio.md](reglas-negocio.md) | Reglas funcionales aprobadas; identidad, duplicados, sedes, auditoría, geolocalización y permisos. |
| [casos-uso.md](casos-uso.md) | Actores, precondiciones, acciones y excepciones de los flujos principales. |
| [diccionario-captura.md](diccionario-captura.md) | Mapeo de campos de las dos fichas impresas a atributos PCD y sus criterios de captura. |
| [reportes-consultas.md](reportes-consultas.md) | Consultas, filtros, avance por sede, productividad y calidad de datos. |
| [decisiones-pendientes.md](decisiones-pendientes.md) | Datos o criterios que todavía no están confirmados y que la IA **no debe inventar**. |

**Fuente externa aún no versionada:** el Excel original de fichas Persona y Líder GC con sus ejemplos. El diccionario es la transcripción de trabajo; no sustituye una validación final contra el Excel. El diseño visual se encuentra en [PCD V2.2](../../prototypes/PCD_v2.2.html).

**Para cambios:** revisar primero las reglas; actualizar la especificación por capacidad en [OpenSpec](../../openspec/README.md).

## Fuentes contrastadas
La transcripción de las fichas impresas y los datos del Excel verificado están en [fuentes/](fuentes/README.md). **El XLSX binario aún no se ha añadido a GitHub**.

**Para cualquier auditoría:** aplicar primero el [alcance oficial](alcance-funcional.md) y el [protocolo de auditoría](../07-auditorias/protocolo-auditoria-alcance.md). Proponer ampliaciones es válido, pero no son incumplimientos hasta que el responsable funcional apruebe el cambio.

**ALCANCE V4:** [Decisiones vigentes](decisiones-pendientes.md), [reglas](reglas-negocio.md), [captura de fichas](diccionario-captura.md), [modelo simplificado](../03-base-datos/anexos/justificacion-11-tablas.md). Consulta general con filtros y documento tipo+número; no usar viejas asociaciones territoriales.
