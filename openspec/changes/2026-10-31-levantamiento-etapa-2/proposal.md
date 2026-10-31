# Proposal E2 — PCD: controles mínimos dentro del alcance
Auditoría H-03/04/07/12/13/18/28/32/35. Objetivo: permitir que I01 se implemente de forma coherente y segura sobre React/PHP/MySQL, y que I02/I03 no hereden ambigüedades de identidad/calidad.
- Incluir: credenciales y sesiones seguras, endpoint público de presbiterios, consulta dentro de ámbito, acceso nacional por rol, catalogación verificada, normalización documental, criticidad por reporte, líder GC sin referencia duplicada.
- No incluir: roles pastorales SGSP, IAM empresarial/SSO, conciliación, OCR, revisión humana dentro de PCD, miembros nominales GC, nuevos servicios residentes.
- Un riesgo residual de contraseña común sigue vigente, requiere aceptación explícita para producción.
- E2-D01 consulta de todas las fichas del propio presbiterio; E2-D02 operación de contexto nacional; E2-D03 aceptación riesgo de clave común.
- Criterio de cierre: API, SQL, seguridad, modelo lógico/ER, SDD y pruebas coherentes; prueba de hosting todavía no ejecutada.
