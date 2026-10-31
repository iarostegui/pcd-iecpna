# Decisiones pendientes no bloqueantes y evidencias faltantes
| ID | Tema | Situación actual | Tratamiento sin inventar |
|---|---|---|---|
| DP-01 | Catálogo oficial de sedes | Parcial, algunas sedes Lima identificadas; **no hay lista completa de 48** | Precargar solo confirmadas, permitir alta desde selector, no inventar |
| DP-02 | Nombre oficial y correspondencia región/presbiterio | Lima, La Libertad–Trujillo, Chimbote, Iquitos usados en prototipo; estructura oficial parcial | Seeds explícitamente provisionales, homologación |
| DP-03 | Hosting | Dominio `pcd.unobdata.com` creado, PHP/MySQL/límites no confirmados | Compatibilidad con 8.x/InnoDB; validar en cPanel |
| DP-04 | Concurrencia | No se conoce número real de digitadores simultáneos | Escenarios de prueba 10/20/30; medir, no prometer |
| DP-05 | Retención de GPS/logs | Finalidad definida, plazo no cerrado | Minimización, acceso restrictivo, retención pendiente aprobación |
| DP-06 | Catálogo completo de ubigeo/responsabilidades | Fuente parcial; no usar valores ficticios productivos | Importar catálogo oficial cuando esté disponible |
| DP-07 | Duplicado documental en distinta sede | Decisión: misma Persona, fichas de origen conservadas; UX de conciliación necesita prueba con equipo | Mostrar conflicto sin exponer PII ajena, registrar fuente sin duplicar |
| DP-08 | Migración a SGSP | Se hará BD→BD después; esquema objetivo SGSP aún por homologar | No implementar ahora; conservar IDs y trazabilidad |
| DP-09 | Contraseña común real | Requerida sin cambio forzado; valor no autorizado en documentos | Provisionar fuera de Git (hash), rotación administrativa sin forzar usuario |
| DP-10 | Exportación de reportes | Deseable; no requisito para migración | Implementar solo si se aprueba y se valida minimización |
| DP-11 | Referencia original del Excel en GitHub | Documento fuente existe como archivo de conversación, no repositorio | Registrar nombre exacto; carga binaria pendiente si se desea versionar |

## Criterio
Ningún DP autoriza al agente a crear datos falsos ni a cambiar funcionalidades del prototipo aprobado.

## E1 — Puntos para conformidad del responsable funcional
- E1-D01: adjuntar automáticamente la ficha cuando el documento pertenece a Persona registrada incluso en otro presbiterio, sin exponer PII ni autorizar edición del consolidado.
- E1-D02: «grupo ya existe» se captura como Sí/No del papel; no construir identidad maestra permanente de GC dentro de PCD.
- E1-D03: **CERRADA FUNCIONALMENTE**; revisión manual fuera de PCD por presbiterio, sin workflow de supervisor, sistema de tickets ni conciliación.
- E1-D04: agregar el Excel binario exacto a GitHub; inventario y SHA ya documentados.
Ver [resolución E1](../07-auditorias/etapa-1-resolucion.md).

## Decisiones confirmadas sobre H-05, H-08 y H-09
- **H-05 — APROBADA:** no ingresar ni persistir fechas imposibles; el digitador corrige con el origen o deja fecha vacía para guardar incompleto. No se implementa persistencia especial de fechas erróneas como si fueran fechas válidas.
- **H-08 — ALCANCE APROBADO:** solo Personas líderes y datos generales de los Grupos Crecer; no integrantes nominales. Sigue pendiente **E1-D02**, interpretación exacta de «grupo ya existe».
- **H-09 / E1-D03 — APROBADA:** cada presbiterio hace **revisión manual de fidelidad al papel fuera de PCD**. No detector automático de errores en nombres, revisiones asignadas, supervisores ni conciliación dentro de PCD.
- **E1-D01 — PENDIENTE:** falta conformidad explícita para vinculación automática de fichas duplicadas entre presbiterios.

## Etapa 2 — decisiones y riesgos pendientes (31/10/2026)
- **E2-D01 — PENDIENTE DE CONFORMIDAD FUNCIONAL:** se propone que el DIGITADOR consulte fichas del propio presbiterio (como prototipo V2.2), pero solo edite sus fichas. En Personas con fuentes transversales, nunca exponer datos personales de otros presbiterios. Evitar inventar restricciones mayores o menores sin aprobación.
- **E2-D02 — PENDIENTE DE CONFORMIDAD FUNCIONAL:** ADMIN/REPORTES nacionales por rol sin filas por todos los presbiterios; ambos ingresan con presbiterio inicial, ADMIN puede cambiar **explícitamente** presbiterio/sede y REPORTES consulta nacional sin sede.
- **E2-D03 — PENDIENTE DE ACEPTACIÓN DE RIESGO:** contraseña común inicial **solo entre digitadores**, cuentas nacionales con claves distintas, usuario no enumerado, límites de intentos y revocación. La auditoría por nombre de cuenta no demuestra identidad física si la clave es compartida.
- **E2-T04 — PENDIENTE DE ENSAYO:** `CHECK` y FK compuestas en MySQL/MariaDB real de cPanel, versión del servidor, expiración/cookies, carga y rendimiento. No declarar SQL validado.
- **DP-06 continúa PENDIENTE:** fuentes oficiales de ubigeo y formato documental C.E.; código INEI nunca inventado.
Referencias: [Etapa 2](../07-auditorias/etapa-2-arquitectura-seguridad-datos.md), [catálogos e identidad](../03-base-datos/catalogos-e-identidad.md) y [calidad](../03-base-datos/campos-criticos.md).

## Cierre de decisiones de Etapa 2 según responsable funcional (31/10/2026)
- **DP-06 / Ubigeo — RECLASIFICADO:** el catálogo oficial INEI se recibirá/cargará como **entrada de construcción en I02** (fuente/versiones/códigos/jerarquía y pruebas). Ya no es cuestionamiento de alcance. No inventar datos.
- **E2-D01 / H-12 — APROBADO CON MODIFICACIÓN:** permiso de **consulta** configurable por usuario: `GENERAL` (nacional) o `PRESBITERIO` (solo presbiterios asignados); su objetivo es control de lectura. **Nunca concede escritura adicional**. Digitador solo modifica sus fichas; ADMIN puede todas; REPORTES sigue solo lectura.
- **E2-D02 / H-28 — APROBADO:** ADMIN y REPORTES nacionales por perfil, cambio explícito de contexto operativo para ADMIN y reportes nacionales sin sede para REPORTES.
- **E2-D03 / H-03 — ACEPTACIÓN FUNCIONAL DE RIESGO RESIDUAL:** se conserva la contraseña inicial común para digitadores, con controles compensatorios y secretos independientes para ADMIN/REPORTES. Seguridad/privacidad y pruebas reales siguen siendo puerta de producción, no se declara el riesgo mitigado por completo.
- **H-13 — APROBADO:** clave documental por tipo+subtipo+valor normalizado, DNI 8 dígitos conservando ceros, CE conservador sujeto a verificación de formato en I02, OTRO con subtipo, NULL cuando ambiguo.
- **Anexos físicos — OBLIGATORIOS I00:** diagrama detallado y diccionario **columna a columna**, derivados de `001_initial.sql`.
**SIGUEN ABIERTOS:** E1-D01 (vinculación automática entre presbiterios), E1-D02 (interpretación del GC preexistente), H-40 condiciones legales/de retención y compatibilidad real de hosting.
