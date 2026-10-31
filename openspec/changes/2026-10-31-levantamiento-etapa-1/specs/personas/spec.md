# Delta PCD Personas — propuesto
## PER-03 MODIFIED — Persona documentalmente existente
MUST guardar una nueva ficha fuente en sede autorizada aunque el documento normalizado corresponda a Persona originada en otra sede, sin duplicarla ni exponer datos privados ajenos.
### E1-CA-01
GIVEN persona documental A existente WHEN digitador B registra hoja de su sede THEN se crea nueva ficha de B vinculada a Persona, no segunda Persona ni divulgación de ficha A.
## PER-07 MODIFIED — fuentes independientes
MUST preservar snapshot literal de cada hoja y creador original, sin sobrescritura silenciosa de consolidado por fichas posteriores.
## PER-08 ADDED — valor imposible
MUST persistir literalmente datos inválidos en fuente y NULL en columna DATE/correo normalizados, con indicador de calidad y sin bloquear guardado.
### E1-CA-04
GIVEN fecha 31/02/2026 en papel WHEN se guarda THEN snapshot conserva literal y columna DATE es NULL.
## PER-09 ADDED — fidelidad al Excel
MUST capturar opciones y detalles del [inventario](../../../../../docs/01-analisis/fuentes/inventario-excel-original.md) sin convertir a bloqueantes los campos marcados con asterisco en papel.
