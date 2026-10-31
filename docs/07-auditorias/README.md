# 07 — Auditorías SDD de PCD
[↑ Índice general](../../README.md)

Se conservan **los hallazgos, decisiones y respuestas** a auditorías de documentación de PCD. Nunca incluir auditorías del SGSP en este repositorio.

| Documento | Uso | Estado |
|---|---|---|
| **[protocolo-auditoria-alcance.md](protocolo-auditoria-alcance.md)** | **Instrucción obligatoria para el auditor**: validar dentro del alcance, separar defectos y propuestas externas, y justificar cuestionamientos. | **Vigente** para nuevas auditorías y reevaluación de hallazgos previos. |
| [etapa-1-resolucion.md](etapa-1-resolucion.md) | Levantamiento H-01, H-02, H-05, H-08, H-09 y H-14, con solución mínima y evidencia. | **Propuesta técnica fundamentada**, pendiente de conformidad funcional en las decisiones identificadas. |
| [respuesta-auditor-en-construccion.md](respuesta-auditor-en-construccion.md) | Matriz acumulativa para informar qué se corrige, qué se simplifica y qué no se atenderá, con justificación. | **Borrador**, no es el informe final de cierre. |
| [preparacion-iteraciones.md](preparacion-iteraciones.md) | Puertas de entrada I00–I06, bloqueos y evidencias; no confundir documentación con pruebas. | **Plan de trabajo**, ninguna iteración de producto ejecutada. |

**Fuente del informe de auditoría:** archivo aportado `auditoria-pcd-sdd-v3.1.zip` con 7 documentos, conservado en la conversación de trabajo; el ZIP no ha sido añadido a GitHub. Para rastreabilidad, cada decisión referencia el ID H-xx.

**Antes de clasificar hallazgos:** leer [ALC-PCD-3.1](../01-analisis/alcance-funcional.md). Un auditor puede **cuestionar el alcance** y proponer ampliación, pero no exigir funcionalidad fuera de él ni declararla bloqueante automáticamente; no desatender por ello riesgos de seguridad, integridad o privacidad.

| [etapa-2-arquitectura-seguridad-datos.md](etapa-2-arquitectura-seguridad-datos.md) | H-03/H-04/H-07/H-12/H-13/H-18/H-28/H-32/H-35; seguridad, catálogo, contexto, calidad e integridad GC con decisiones E2 abiertas. | **Diseño técnico en GitHub, pruebas reales y conformidad pendientes**. |
