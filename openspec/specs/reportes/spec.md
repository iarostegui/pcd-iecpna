# PCD V4 — Consultas y reportes

## REP-01 — Avance por territorio
MUST calcular Personas y GC digitados, últimas altas/ediciones y autor con filtros por presbiterio y/o sede, digitador y período sobre `ficha`, `organizacion` y `auditoria_evento`.

## REP-02 — Calidad objetiva
MUST calcular incompletos y campos faltantes usando filas `catalogo` en categorías CAMPO_CRITICO_PERSONA/GC. Cambio por ADMIN afecta **cálculo al consultar**, sin alterar fichas.

## REP-03/09 — Consulta total por funcionalidad (H-12)
MUST permitir consulta general de TODOS los registros en funcionalidad autorizada; los filtros opcionales **Presbiterio y/o Sede** no restringen el permiso por usuario. MUST NOT crear campo `alcance_consulta` ni `usuario_presbiterio`. DIGITADOR solo modifica propias fichas, ADMIN las autorizadas por rol y REPORTES ninguna.
### Scenario
GIVEN DIGITADOR de Presbiterio A con acceso a Consulta WHEN consulta sin filtros THEN recibe página de registros nacionales de Consulta; WHEN filtra Sede B THEN obtiene solo esa sede; WHEN intenta modificar ficha ajena THEN 403.

## REP-04 — Perfil REPORTES
REPORTES MUST consultar solamente las funciones de informes permitidas, sin editar. Minimizar PII y no exponer GPS en listados.

## REP-05 — No migración SGSP
MUST NOT implementar exportación/importación directa a SGSP en la aplicación temporal.

## REP-06 — Calidad humana fuera de sistema
La fidelidad de transcripción respecto del papel se evalúa **manualmente** en cada presbiterio; no crear supervisor ni workflow de conciliación.

## REP-08 — Parámetros de criticidad
`catalogo.config_json` contiene `ruta_dato/es_critico/desactivable` con rutas validadas en lista blanca y auditoría de cambios; relaciones técnicas no desactivables.
