# docs — Documentación de análisis, diseño y ejecución de PCD

[↑ Volver al índice principal](../README.md)

Aquí se guardan **especificaciones explicativas** y decisiones técnicas de **PCD**, no código de producción. El detalle obligatorio y verificable por capacidad se mantiene en [OpenSpec](../openspec/README.md).

## Índice de áreas documentales

| Directorio | Uso | Punto de entrada |
|---|---|---|
| [01-analisis/](01-analisis/README.md) | **Alcance oficial ALC-PCD-3.1**, procesos, reglas, casos de uso, fichas, reportes y pendientes. | **[Alcance oficial](01-analisis/alcance-funcional.md)** |
| [02-diseno/](02-diseno/README.md) | Arquitectura, módulos, RNF, seguridad, auditoría, UX y ADR. | [Arquitectura](02-diseno/arquitectura.md) |
| [03-base-datos/](03-base-datos/README.md) | Modelo conceptual/lógico/físico, relaciones, atributos y ER. | [Modelo lógico](03-base-datos/modelo-logico.md) |
| [04-integracion/](04-integracion/README.md) | Contratos API y notas de migración futura a SGSP. | [OpenAPI](04-integracion/openapi.yaml) |
| [05-pruebas/](05-pruebas/README.md) | Escenarios de aceptación y ensayo de carga en hosting compartido. | [Casos de prueba](05-pruebas/casos-prueba.md) |
| [06-despliegue/](06-despliegue/README.md) | Publicación cPanel, operación, respaldo y cierre de campaña. | [Instalación cPanel](06-despliegue/instalacion-cpanel.md) |
| [07-auditorias/](07-auditorias/README.md) | Hallazgos, levantamiento, **protocolo de alcance** y respuesta al auditor. | [Protocolo de auditoría](07-auditorias/protocolo-auditoria-alcance.md) |
| [08-ia/](08-ia/README.md) | Instrucciones para agentes IA, trazabilidad y orden de implementación. | [Prompt maestro](08-ia/PROMPT-MAESTRO.md) |

## Criterios documentales

- **Fuentes:** alcance y decisiones V4 de PCD, fichas originales (el XLSX aún pendiente en GitHub) y prototipo **V2.3 vigente**; V2.2 solo histórico. No confundir con SGSP.
- **Estados:** distinguir definido, propuesto, pendiente y verificado; los modelos aún no representan ejecución productiva.
- **Cambios:** cuando una decisión funcional cambie, actualizar la especificación OpenSpec y los documentos afectados, además de las pruebas. Registrar temas no resueltos en [decisiones-pendientes.md](01-analisis/decisiones-pendientes.md).

## Etapa 3
[Contratos y pruebas](07-auditorias/etapa-3-contratos-pruebas.md) documenta H-10/11/15/16/17/20…25/33/34/37/38/39/45 sin ampliar el alcance; la siguiente etapa tratará riesgos/operación productiva H-40 y pendientes.

## Documento normativo V4
[11 tablas y motivo de cada una](03-base-datos/anexos/justificacion-11-tablas.md) · [Anexo 01 ER](03-base-datos/anexos/anexo-01-diagrama-fisico.md) · [Anexo 02 diccionario](03-base-datos/anexos/anexo-02-diccionario-fisico.md) · [Etapa 4](07-auditorias/etapa-4-produccion-y-privacidad.md) · [Checklist I06](06-despliegue/checklist-produccion.md). **Los informes E1–E3 anteriores son históricos si contradicen V4.**
