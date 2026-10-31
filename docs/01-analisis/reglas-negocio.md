# Reglas aprobadas PCD V3.1 (fuente normativa de producto)
| ID | Regla | Prioridad |
|---|---|---|
| RN-01 | Login exige presbiterio del catálogo y credenciales válidas; ámbito autorizado verificado en servidor. | Crítica |
| RN-02 | Crear seis digitadores por presbiterio registrado, sin esperar catálogo completo; ADMIN y REPORTES nacionales. | Alta |
| RN-03 | Clave operativa común para digitadores sin cambio forzado al primer login; valor secreto fuera de Git, almacenamiento hash; registrar intentos. | Crítica |
| RN-04 | Después del login se elige solo Sede; lista filtrada por presbiterio y contexto persistido por sesión. | Crítica |
| RN-05 | Crear sede desde selector sin aprobación; deduplicar exacto normalizado bajo mismo presbiterio, advertir similares; permitir distinguir nombres legítimamente próximos. | Crítica |
| RN-06 | No continuar ni guardar si Sede no pertenece al Presbiterio de sesión; backend revalida siempre. | Crítica |
| RN-07 | Formulario Persona sigue campos y orden del Excel; foco inicial N.º ficha, Enter/Tab siguiente, Shift+Tab anterior; Enter no guarda. | Alta |
| RN-08 | Las fechas se ingresan DD/MM/AAAA; valores inexistentes como 31/02/1998 son rechazados como fecha. El digitador los corrige consultando la ficha o deja el campo vacío para guardar incompleto. Solo fechas reales pasan a SQL DATE. Validación en frontend y backend. | Alta |
| RN-09 | N.º ficha impresa Persona/GC es opcional y no es clave de identidad ni bloquea guardado. | Alta |
| RN-10 | Falta de datos críticos NO impide guardar; reportes detectan incompletitud; configuración editable por ADMIN durante la campaña. | Crítica |
| RN-11 | Identidad documental válida coincidente (Tipo+Número) no crea segunda Persona; conservar nueva ficha como fuente vinculada o abrir conciliación; sin documento, no deducir duplicado solo por nombre. | Crítica |
| RN-12 | Posibles coincidencias por nombres/apellidos/nacimiento generan advertencia y marca de conciliación, no bloqueo automático. | Alta |
| RN-13 | La opción de líder GC es a demanda; abrir solo después de persistir Persona, o desde Consulta posteriormente. | Crítica |
| RN-14 | GC vinculado al ID Persona; presbiterio y sede heredados de contexto, sin segunda digitación de líder. | Crítica |
| RN-15 | Un líder nuevo NO sustituye al de GC vigente en el mismo grupo; registrar nuevo GC o incidencia, sin borrar historia. | Crítica |
| RN-16 | Capacidad GC y cantidad aproximada son datos distintos; no inventar integrantes nominales. | Alta |
| RN-17 | Digitador solo modifica registros de su autoría; ADMIN modifica todos utilizando cuenta ADMIN; creador original nunca se sobrescribe. | Crítica |
| RN-18 | REPORTES es de solo lectura nacional, con filtros y minimización de datos privados. | Crítica |
| RN-19 | Geolocalizar una única vez por sesión (primer login); reutilizar misma captura en cada guardado/cambio de sede; sin solicitar repetidamente ni bloquear si no está disponible. | Crítica |
| RN-20 | Auditoría: actor, autor original, sesión, IP, fecha/hora, operación, cambios antes/después, ubicación disponible y su fecha/precisión; log de login éxitos/fallos. | Crítica |
| RN-21 | Calidad configurable afecta reportes y alertas, pero nunca elimina estructura obligatoria (usuario, sede, integridad FK). | Crítica |
| RN-22 | No revisión obligatoria de supervisor ni aprobación de nueva sede o ficha; administrar mediante indicadores. | Alta |
| RN-23 | BD PCD independiente; IDs/fechas/tipos originales se preservan para futura migración directa BD→SGSP, proceso fuera de alcance actual. | Crítica |
| RN-24 | Al guardar y pasar a la siguiente ficha se conserva presbiterio/sede y retorna foco al N.º ficha. | Alta |
| RN-25 | Todas las operaciones sensibles se validan en PHP y se confirman transaccionalmente en MySQL, con idempotencia. | Crítica |
| RN-26 | Un digitador modifica solo su ficha fuente; el autor original o ADMIN puede modificar la Persona consolidada; conservar trazabilidad y no sobrescribir por otra ficha. | Crítica |
| RN-27 | PCD valida tipos, formatos, integridad y faltantes; no verifica automáticamente si la captura reproduce literalmente el papel. La revisión de fidelidad es **manual por cada presbiterio, fuera del sistema**, sin aprobaciones ni circuito de conciliación. | Alta |
| RN-28 | GC registra solo la Persona **líder** y los datos generales del grupo. La cantidad aproximada de integrantes es resumen, no existen integrantes nominales registrados ni vínculos miembro–GC. | Crítica |

## Reglas de coincidencia — decisión derivada a validar en UAT
1. Documento completo normalizado coincidente: identidad existente; mostrar conflicto y registrar ficha como aporte / conciliación sin duplicar Persona.
2. Sin documento, nombres+apellidos+fecha nacimiento coincidentes: señal de posible duplicado; el digitador puede continuar.
3. Solo parecido fonético/textual: advertir, no prohibir. Evitar revelar registros fuera del ámbito del usuario.

## Regla de contraseña compartida: límite de identidad
La contraseña común fue solicitada expresamente; **no prueba identidad física del operador**. Deben existir cuentas personales distintas, bitácora de sesión, throttling, monitoreo y mecanismo seguro de revocación. No elevar geolocalización a prueba de identidad.

## Reglas no definidas
No inferir cuáles son exactamente las 48 sedes ni pretender que todos los datos del papel son obligatorios para guardar; ver decisiones pendientes.

## Validación técnica y calidad manual: acuerdo funcional
- Un control de fecha no permite registrar **31/02/1998**. El digitador revisa el papel o resuelve la duda fuera del sistema. Si no logra resolverla, deja la fecha vacía y guarda la ficha incompleta. No se persiste fecha inválida en el campo tipado.
- Que un nombre tenga formato textual válido **no demuestra** que se haya copiado bien: **JUANA** en lugar de **Juan**, o **JU4N4**, puede superar un control de texto. PCD no compara automáticamente contra la ficha física.
- **Cada presbiterio realizará revisiones de calidad manuales fuera de PCD**; después un digitador autorizado o ADMIN podrá corregir la ficha usando los controles existentes, con auditoría.
- Los reportes objetivos de campos críticos vacíos y coincidencias documentales se mantienen. **No** crear bandejas de revisión, supervisores, motor de detección de errores de transcripción, aprobaciones ni estados de conciliación.
