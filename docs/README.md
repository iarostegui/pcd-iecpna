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

- **Fuentes:** reglas aprobadas de PCD, formularios originales y prototipo V2.2; no confundir con SGSP.
- **Estados:** distinguir definido, propuesto, pendiente y verificado; los modelos aún no representan ejecución productiva.
- **Cambios:** cuando una decisión funcional cambie, actualizar la especificación OpenSpec y los documentos afectados, además de las pruebas. Registrar temas no resueltos en [decisiones-pendientes.md](01-analisis/decisiones-pendientes.md).
