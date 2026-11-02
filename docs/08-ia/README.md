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
| [i00/](i00/MATRIZ-I00.md) | Evidencia técnica I00: matriz de 32 controles, acta preliminar GO/NO-GO y restricciones del hosting. |

**Modo de trabajo:** leer fuentes → preparar cambio OpenSpec → implementar solo el alcance → probar → documentar evidencia → corregir. No implementar SGSP ni su proceso futuro de migración.

**Límite obligatorio de la IA:** [ALC-PCD-4.0](../01-analisis/alcance-funcional.md) define qué está incluido y excluido. Propuestas de ampliación o críticas al alcance no habilitan desarrollo sin aprobación del responsable funcional.

**Versión V4 vigente:** [modelo de 11 tablas](../03-base-datos/anexos/justificacion-11-tablas.md), [OpenSpec](../../openspec/specs/README.md), [OpenAPI](../04-integracion/openapi.yaml), [Etapa 4](../07-auditorias/etapa-4-produccion-y-privacidad.md). Nunca generar código desde un delta histórico E1/E2/E3 que contradiga V4.

[Registro de aprobaciones](APROBACIONES.md) y [entrega al especialista](../07-auditorias/ENTREGA-REVISION-ESPECIALISTA-V4.1.md). No se deben ejecutar cambios históricos E1–E3 que contradigan V4.1.

- [Contrato de ejecución V4.1](CONTRATO-EJECUCION-V4.1.md) — secuencia concreta para implementadores IA; altas sin eventos de auditoría y edición solo diferencias.
- [Aprobaciones](APROBACIONES.md) — decisiones funcionales vigentes, sin equivaler a firma institucional.
