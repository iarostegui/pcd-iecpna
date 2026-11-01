# 02 — Diseño técnico y de experiencia PCD

[↑ docs](../README.md) · [↑ Índice principal](../../README.md)

Explica **cómo** se implementará PCD sobre el **hosting compartido existente**, con arquitectura ligera y controles efectivos. No es código ejecutable.

| Archivo | Qué contiene |
|---|---|
| [arquitectura.md](arquitectura.md) | Distribución navegador → React estático → PHP REST → MySQL InnoDB; responsabilidades de capas. |
| [diseno-componentes.md](diseno-componentes.md) | Propuesta de módulos frontend/backend, componentes, responsabilidades y secuencias. |
| [requerimientos-no-funcionales.md](requerimientos-no-funcionales.md) | Criterios medibles de velocidad, consistencia, privacidad, seguridad, disponibilidad y mantenibilidad. |
| [seguridad-auditoria.md](seguridad-auditoria.md) | Autenticación, permisos, sesiones, login log, autoría de modificaciones y geolocalización de sesión. |
| [ux-prototipo.md](ux-prototipo.md) | Pantallas y reglas UX basadas en PCD HTML V2.2, incluyendo navegación con Enter/Tab y responsive. |
| [decisiones-arquitectura.md](decisiones-arquitectura.md) | ADR: infraestructura, sesión, separación de bases, calidad configurable y decisiones operativas. |

**Relaciones:** modelo de datos en [03-base-datos](../03-base-datos/README.md); endpoints en [04-integracion](../04-integracion/README.md); interfaz de referencia en [prototypes/](../../prototypes/README.md).

**Estado:** diseño V3.1; cualquier meta de rendimiento sigue pendiente de prueba sobre el hosting real.

**MODELO V4 vigente:** [11 tablas](../03-base-datos/modelo-logico.md), [seguridad y consulta general](seguridad-auditoria.md), [Etapa 4](../07-auditorias/etapa-4-produccion-y-privacidad.md). La fuente de implementación es SQL V4, no diagramas históricos.

**Referencia de diseño vigente:** [PCD V2.3](../../prototypes/PCD_v2.3.html), [matriz de cambios](pcd-v2.3-alineacion.md) y [pruebas CA-101…111](../05-pruebas/validacion-prototipo-v2.3.md). V2.2 queda histórica.
