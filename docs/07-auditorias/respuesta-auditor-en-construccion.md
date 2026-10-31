# Respuesta al auditor PCD SDD V3.1 — REGISTRO ACUMULATIVO
**NO es el informe final.** No enviar como respuesta oficial hasta concluir todas las etapas y validar evidencias.

## Principios de respuesta
- **Se atiende** cuando elimina incoherencia o riesgo que afecta captura, integridad, seguridad o migrabilidad.
- **Se atiende simplificado** cuando la necesidad está justificada pero la solución propuesta introduce procesos no solicitados.
- **No se atiende** cuando el desarrollo sugerido añade complejidad desproporcionada a una aplicación **temporal** y no aporta beneficio al objetivo de captura. Justificación debe ser individual y demostrar control alternativo.
- **Se difiere** si requiere datos externos, validación de hosting o decisión de negocio; no equivale a rechazado.
- La vida temporal **no justifica** rechazar seguridad, protección de datos, trazabilidad, respaldos o integridad básica.

## Etapa 1: propuestas de disposición
| Hallazgo | Tratamiento propuesto | ¿Qué se atenderá? | ¿Qué no se implementará y por qué? | Estado |
|---|---|---|---|---|
| H-01 | **Atender** | Guardar ficha asociada a Persona documentalmente existente incluso fuera del ámbito, sin exponer PII ni perder la hoja. | No segundo registro Persona ni búsqueda libre entre presbiterios; duplicar datos incrementaría errores. | Diseño propuesto, API/SQL pendientes |
| H-02 | **Atender** | Fuente separada de Persona consolidada; snapshots por ficha; derechos de edición, auditoría. | No maestro histórico amplio de identidades ni flujo complejo de consolidación; temporalidad y control de alcance. | Diseño propuesto |
| H-05 | **Atender con validación de tipo** | Fecha imposible es rechazada como dato fecha; el digitador resuelve la discrepancia fuera del sistema o deja campo vacío y puede guardar incompleto. Frontend y PHP revalidan. | No crear almacenamiento alternativo de valores fecha imposibles ni un flujo de incidencias para resolverlas; no aporta valor al objetivo temporal. | **Criterio funcional aprobado**, pruebas pendientes |
| H-08 | **Atender solo alcance mínimo** | Registrar exclusivamente **líder** y datos generales del GC, con número aproximado de integrantes. Conservar pregunta de existencia del papel; detalle de identidad pendiente E1-D02. | **No registrar integrantes nominales** ni crear administración pastoral de miembros/grupos. Su información no existe en las fichas; sobrecoste sin fuente. | **Alcance funcional aprobado**, E1-D02 pendiente |
| H-09 | **No implementar revisión semántica automática** | PCD valida tipos, integridad y campos faltantes y permite corrección posterior auditada. | **Cada presbiterio revisa manualmente fuera de PCD** la fidelidad de la digitación al papel; no existe mecanismo fiable para detectar automáticamente `JUANA`/`Juan` o `JU4N4` sin comparar con el papel. No construir conciliación, tickets, supervisión o aprobaciones. | **Criterio funcional aprobado** |
| H-14 | **Atender, parcial** | Analizado Excel original, inventario de campos/opciones/filas + comprobación hash. | No inventar campos ni usar datos de ejemplo como reales. **La carga del XLSX original al repositorio sigue pendiente**, no se rechaza. | Parcial |

## Justificación reutilizable en informe final
> **Proporcionalidad y ciclo de vida:** PCD tiene una vida útil limitada a la digitación de fichas impresas y posterior migración controlada de sus datos a SGSP. Por ello se excluyen procesos administrativos y maestros permanentes que no mejoren la captura, integridad, seguridad, auditoría o calidad necesaria para migrar. Los requisitos esenciales sí se implementan con alternativas ligeras, verificables y transaccionales.

## Hallazgos fuera de Etapa 1
H-03, H-07, H-10…H-13, H-18, H-20, H-28, H-32, H-35, H-40 y restantes se decidirán por etapa. **No se consideran rechazados ni corregidos** por figurar en esta matriz.

## Aclaración de H-09 del responsable funcional (31/10/2026)
**Dos problemas diferentes:** (1) un dato imposible para el control —por ejemplo, `31/02/1998` en fecha— es **rechazado** y el digitador corrige con el origen o deja el valor vacío para guardar una ficha incompleta; (2) un texto plausible pero mal copiado —`JUANA` en vez de `Juan`, o `JU4N4`— no se verifica automáticamente en PCD. **Cada presbiterio es responsable de revisión manual externa**. Esta última verificación de calidad **no es alcance de desarrollo de PCD** por proporcionalidad y vida temporal. Se mantienen controles técnicos, seguridad e integridad.

## Criterio formal de alcance para elaborar la respuesta definitiva

El [alcance oficial ALC-PCD-3.1](../01-analisis/alcance-funcional.md) y el [protocolo de auditoría](protocolo-auditoria-alcance.md) constituyen el marco de evaluación. **Toda recomendación fuera de EXC-01…EXC-10 se analizará sin transformarla automáticamente en obligación de desarrollo**. Cuando sea razonable cuestionar el alcance, el auditor debe describir el valor y los riesgos de cambiarlo y solicitar decisión del responsable funcional, sin considerar como defecto la exclusión aprobada.

Al redactar la respuesta individual a cada observación, distinguir entre **corrección de una brecha en alcance**, **control esencial de seguridad/integridad**, **sugerencia fuera de alcance** y **dependencia futura**. La temporalidad justifica descartar estructuras permanentes que no añaden valor proporcional, pero **no** omitir controles esenciales de PCD. En particular, la revisión manual de fidelidad al papel (H-09) y la ausencia de miembros nominales GC (H-08) son decisiones de alcance **ya aprobadas**, no brechas técnicas a resolver con nuevos módulos.

## Etapa 2 — respuesta provisional por observación (NO es informe final)
| Hallazgo | Decisión de tratamiento | Justificación vinculada al alcance | Estado verificable |
|---|---|---|---|
| **H-03** | **Atender controles / aceptar riesgo residual** | Conservar contraseña común de digitadores acordada; retirar listado de cuentas en login productivo, usar claves nacionales distintas, throttling, expiración, revocación y auditoría. No agregar SSO/IAM por aplicación temporal. | Diseño SQL/API/seguridad actualizado; **E2-D03 aceptación de riesgo pendiente**. |
| **H-04** | **Atender** | Presbiterios activos deben cargarse antes de autenticar, con respuesta pública **sin usuarios**. | OpenAPI actualizado, falta prueba. |
| **H-07** | **Atender parcialmente** | Usar catálogos verificados en fichas y un catálogo compacto; no inventar ubigeo INEI ni nuevos estados pastorales. | SQL/semilla/API listos para probar; DP-06 pendiente. |
| **H-12** | **Atender, pendiente de decisión** | Seguridad dentro del alcance: no exponer datos cruzados. Se propone consulta de fichas del mismo presbiterio y edición solo propia, consistente con prototipo. | **E2-D01 pendiente**, prueba IDOR no ejecutada. |
| **H-13** | **Atender parcialmente** | Documentos con subtipos distintos de OTRO no deben colisionar. Normalizar solo cuando confiable, ceros preservados. | Esquema propuesto; CE/otros requieren validación fuente. |
| **H-18** | **Atender** | Campos críticos iniciales proceden del prototipo; la calidad cambia al modificar parámetros y no se inmoviliza en ficha. No requiere un proceso de supervisor. | SQL, semillas y matriz propuestos; pruebas no ejecutadas. |
| **H-28** | **Atender, pendiente de decisión** | ADMIN/REPORTES nacionales sin generar permisos por cada sede, ADMIN cambia contexto explícitamente y REPORTES lee informes nacionales. | **E2-D02 pendiente**, contrato API propuesto. |
| **H-32** | **Atender** | Expiración y revocación de sesión son controles esenciales aunque PCD sea temporal; CSRF recuperable tras refresh. | Diseño SQL/API listo para prueba I01, no ejecutado. |
| **H-35** | **Atender** | Líder de GC no debe estar duplicado en dos columnas; integridad de tipo de ficha protegida. No se crean integrantes nominales. | SQL modificado, **pendiente comprobar CHECK/FK** en hosting. |

**No se atenderán ampliaciones no necesarias** como SSO corporativo, gestor de identidad permanente, maestro GC con integrantes, conciliación supervisada ni infraestructura externa. Temporalidad justifica reducir complejidad, **no** omitir controles esenciales de seguridad, integridad y privacidad. [Detalle E2](etapa-2-arquitectura-seguridad-datos.md).

## Addendum aprobado del responsable funcional — Etapa 2
- **H-07**: catálogo de ubigeo será **entrada de construcción I02**; no exigir ahora completar un catálogo ajeno al diseño y no inventar jerarquías.
- **H-12**: **ATENDIDA_EN_DISEÑO** mediante permiso independiente `GENERAL/PRESBITERIO` por usuario. Consulta general es lectura nacional, con controles, y no amplía edición; revisión externa de calidad sigue manual.
- **H-13**: **ATENDIDA_EN_DISEÑO** con clave `TIPO[:SUBTIPO]:NÚMERO_NORMALIZADO` cuando confiable; documentos ambiguos no se fusionan.
- **H-28/H-32/H-35**: decisiones funcionales de Etapa 2 aprobadas; su implementación y pruebas siguen pendientes.
- **Anexos del físico:** se requieren diagramas + diccionario completos y reproducibles a partir del SQL; no basta una tabla-resumen.
- **Riesgo H-03**: aceptación funcional del esquema de clave común con controles compensatorios; riesgos de seguridad/privacidad deben verificarse antes de producción.

## Etapa 3 — respuesta en elaboración
- **H-10/15/16/17/33/37/38/39/45:** atender con DTO, filtros, matriz completa, control de versión GC por ficha, pruebas de fallos, administración y corrección de parámetros.
- **H-11:** atender operaciones de consulta/administración dentro de alcance; no crear servicios de gestión pastoral ni conciliación.
- **H-20:** impedir exposición de GPS y PII en consultas no autorizadas aunque HTML de demostración lo muestre.
- **H-21:** **NO ATENDER** módulos de supervisor, exportación SGSP y aprobaciones: EXC-03/06 y carácter temporal de PCD; mantener bitácora y permisos.
- **H-22:** un estado GPS por sesión, sin reintento dentro de la misma sesión, incluso tras denegación.
- **H-23/H-24/H-25:** documentar incertidumbres sin hacer suposiciones del negocio; no bloquear integración con estados arbitrarios, no imponer UNIQUE por nombre GC sin aprobación.
**Nota:** documentos y contratos corregidos ≠ pruebas ejecutadas. [Informe E3](etapa-3-contratos-pruebas.md).
