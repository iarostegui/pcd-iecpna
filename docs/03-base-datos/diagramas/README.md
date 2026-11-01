# Diagramas del modelo de datos de PCD

[↑ Diseño de datos](../README.md) · [↑ Índice principal](../../../README.md)

Almacena diagramas fuente **editables** del modelo PCD y, en el futuro, sus exportaciones gráficas versionadas.

| Archivo | Uso |
|---|---|
| [er-pcd.mmd](er-pcd.mmd) | Diagrama entidad–relación en Mermaid para visualizar las relaciones principales de PCD. |

La referencia de atributos y claves es el [modelo lógico](../modelo-logico.md) y el [SQL inicial](../../../database/migrations/001_initial.sql). Si cambian cardinalidades, actualizar estos tres elementos conjuntamente; no sustituir el modelo de PCD por el del SGSP.

**Etapa 2:** el modelo ER debe representar GC→ficha de tipo GC, cuya Persona es el único líder; el detalle de restricciones se encuentra en [modelo lógico](../modelo-logico.md) y [SQL](../../../database/migrations/001_initial.sql).

## Modelo físico editable
[er-pcd-fisico.mmd](er-pcd-fisico.mmd) muestra las tablas y campos desde el script SQL; se publica con el [Anexo 01](../anexos/anexo-01-diagrama-fisico.md) y [Anexo 02](../anexos/anexo-02-diccionario-fisico.md). El anterior `er-pcd.mmd` queda como representación conceptual resumida.

**Modelo vigente V4:** [ER físico de 11 tablas](er-pcd-fisico.mmd) con [Anexo 01](../anexos/anexo-01-diagrama-fisico.md) y [Anexo 02](../anexos/anexo-02-diccionario-fisico.md). Todo SQL previo de 17 tablas reemplazado.
