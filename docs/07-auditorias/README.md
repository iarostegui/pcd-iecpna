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

- [Etapa 3 — Contratos y pruebas](etapa-3-contratos-pruebas.md): H-10/11/15/16/17/20…25/33/34/37/38/39/45; criterios de atención y exclusiones dentro de alcance. **Sin pruebas ejecutadas**.

**Etapa 4 (V4) y modelo físico simplificado:** [preparación productiva](etapa-4-produccion-y-privacidad.md), [decisiones vigentes](../01-analisis/decisiones-pendientes.md), [justificación 11 tablas](../03-base-datos/anexos/justificacion-11-tablas.md). Los hallazgos de Etapas 1–3 conservan su historial pero **NO autorizan recuperar el modelo 17 tablas ni ampliar PCD**.

- [Etapa 5 — Preparación I00](etapa-5-preparacion-i00.md): transición de auditoría a pruebas técnicas, artefactos y GO/NO-GO. **I00 aún no ejecutada en hosting.**
