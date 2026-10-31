# 08 — Instrucciones para IA desarrolladora de PCD

[↑ docs](../README.md) · [↑ Índice principal](../../README.md)

Organiza el **contrato de trabajo** para una IA que implemente PCD respetando SDD, pruebas y decisiones aprobadas. Leer primero el [AGENTS.md](../../AGENTS.md) raíz.

| Archivo | Qué establece |
|---|---|
| [PROMPT-MAESTRO.md](PROMPT-MAESTRO.md) | Instrucción inicial reutilizable para comenzar el desarrollo de PCD por iteraciones. |
| [plan-implementacion.md](plan-implementacion.md) | Orden I00–I06: preparación, autenticación, Persona, GC, consulta, administración, calidad y despliegue. |
| [matriz-trazabilidad.md](matriz-trazabilidad.md) | Relación requisito ↔ pantalla ↔ API ↔ tabla ↔ prueba. |
| [definicion-terminado.md](definicion-terminado.md) | Condiciones obligatorias antes de declarar terminada una función. |
| [estado-documental.md](estado-documental.md) | Inventario del estado documental, limitaciones y riesgos abiertos. |

**Modo de trabajo:** leer fuentes → preparar cambio OpenSpec → implementar solo el alcance → probar → documentar evidencia → corregir. No implementar SGSP ni su proceso futuro de migración.

**Límite obligatorio de la IA:** [ALC-PCD-3.1](../01-analisis/alcance-funcional.md) define qué está incluido y excluido. Propuestas de ampliación o críticas al alcance no habilitan desarrollo sin aprobación del responsable funcional.
