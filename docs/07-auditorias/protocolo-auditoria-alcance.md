# AUD-PCD-01 — Protocolo obligatorio de auditoría y control del alcance
**Producto:** PCD — Plataforma de Captura de Datos IECPNA · **Norma de referencia:** [ALC-PCD-3.1](../01-analisis/alcance-funcional.md) · **Prototipo:** PCD V2.2 · **Documentación:** SDD V3.1.

## 1. Regla fundamental

La auditoría se ejecuta **dentro del alcance funcional y no funcional aprobado de PCD**, conforme al documento ALC-PCD-3.1. El auditor **sí puede cuestionar ese alcance** y alertar de lo que falta, pero debe presentarlo como **propuesta de cambio al alcance**, no como incumplimiento automático del diseño ni requisito cuya ausencia impida programar.

**No confundir PCD con SGSP:** PCD es **temporal**, dedicado a capturar dos fichas físicas (Personas y Líder GC), consultar, corregir registros autorizados, dar reportes objetivos y conservar integridad y trazabilidad. Las funcionalidades pastorales permanentes y la migración efectiva son de otro proyecto.

## 2. Fuentes y orden de revisión

1. [Alcance oficial ALC-PCD-3.1](../01-analisis/alcance-funcional.md) — inclusiones ALC-01…12 y exclusiones EXC-01…10.
2. [Reglas de negocio](../01-analisis/reglas-negocio.md) y [decisiones pendientes](../01-analisis/decisiones-pendientes.md).
3. [Diccionario de captura](../01-analisis/diccionario-captura.md) y [fuente Excel](../01-analisis/fuentes/README.md) (si no está accesible el original, declarar limitación).
4. [Prototipo PCD V2.2](../../prototypes/PCD_v2.2.html) — referencia UX/demostrativa, no autorización de ampliación.
5. [Arquitectura](../02-diseno/README.md), [modelo PCD](../03-base-datos/README.md), [API](../04-integracion/README.md), [OpenSpec](../../openspec/README.md) y [pruebas](../05-pruebas/README.md).

Distinguir siempre **decisión aprobada**, **propuesta abierta**, **modelo no probado** y **funcionalidad implementada**.

## 3. Taxonomía obligatoria de hallazgos

| Tipo de hallazgo | Qué significa | Tratamiento |
|---|---|---|
| **INCUMPLIMIENTO_EN_ALCANCE** | Un requisito ALC aprobado no tiene diseño suficiente, es contradictorio o incumple una regla. | Registrar evidencia e impacto; proponer corrección mínima dentro de alcance; severidad según riesgo. |
| **RIESGO_ESENCIAL_DE_CONTROL** | Vulnerabilidad o falla de privacidad, seguridad, integridad, respaldo o cumplimiento relacionada con datos/procesos **que PCD sí maneja**. | Evaluar sin ignorarla por temporalidad; proponer control proporcional, severidad fundamentada. |
| **CONTRADICCION_O_DECISION_PENDIENTE** | Fuentes no concuerdan o hay un E1-Dxx no resuelto. | Documentar alternativas, dependencias y responsable; no inventar aprobación. |
| **PROPUESTA_FUERA_DE_ALCANCE** | Función no prevista o explícitamente excluida (EXC-01…10) sin afectar un control esencial existente. | Se puede **cuestionar** su exclusión, pero **no tratar como defecto ni bloquear por defecto**; elevar valor/costo/riesgo al responsable. |
| **DEPENDENCIA_FUTURA_SGSP** | Migración real, gestión de grupos/personas, operaciones pastorales permanentes. | Dejar referencia para proyecto posterior, no exigir implementación en PCD. |
| **MEJORA_DOCUMENTAL** | Enlaces, nombres, explicaciones y mantenimiento. | Recomendar corrección proporcional sin inflar severidad. |

La clasificación por tipo es **independiente** de la severidad. Una propuesta excluida **no se convierte en crítica por conveniencia**. Si se alega que una exclusión genera un problema de seguridad, privacidad o integridad **dentro del alcance**, demostrar el riesgo y clasificarlo como **riesgo esencial**, no esconderlo como opcional.

## 4. Lo que la auditoría SÍ debe evaluar

- Fiel representación de **campos, secciones y opciones** de las dos fichas impresas; no inventar campos.
- Validez de tipos/fechas, guardado de incompletos, sede y presbiterio, identidad documental y autoría.
- Concurrencia, permisos horizontales, integridad relacional, trazabilidad, sesiones y auditoría técnica.
- Consultas/reportes de **datos objetivos**, calidad parametrizable y posibles coincidencias.
- Relación de Persona (líder) y datos **generales** de GC, sin integrantes nominales.
- Seguridad y protección de datos personales/religiosos y ubicación opcional, respaldo y despliegue cPanel.
- Consistencia entre SDD, interfaz V2.2, esquema SQL, API y casos de prueba; distinguir **planificado vs probado**.

## 5. Lo que NO debe exigir como requisito aprobado

- Registro nominal, identificación o gestión de integrantes de GC, ni membresía pastoral.
- Revisión de fidelidad de texto contra papel mediante OCR/IA o un módulo de supervisión. **Cada presbiterio realiza revisión manual fuera de PCD**.
- Estados de aprobación por ficha, tickets/conciliación permanente, asignación de revisores o procesos de seguimiento de correcciones.
- Maestro permanente de grupos, discipulado, asistencia, actividades, ministerios, caja, WhatsApp o CRM.
- Integración o migración operativa a SGSP durante la implementación actual.
- Infraestructura compleja o permanente no justificada en el hosting actual.

**Excepción a esta prohibición:** el auditor puede **recomendar** cualquiera de esas mejoras si justifica su impacto y solicita una decisión formal; no puede **imponerla**.

## 6. Formato obligatorio de cada observación

| Campo | Requisito |
|---|---|
| ID y título | Identificador único de hallazgo. |
| Evidencia | Archivo/ruta, líneas o captura, y comportamiento identificado; si falta fuente, decirlo. |
| **ALC/EXC** | Referencia a capacidad ALC-xx o exclusión EXC-xx; si no aplica, explicar. |
| Tipo | Una de las seis clases de la sección 3. |
| Severidad fundamentada | Crítica/Alta/Media/Baja, **solo según riesgo real** y si aplica. |
| ¿Qué se incumple? | Regla aprobada afectada, o indicar que no existe obligación por estar excluido. |
| Alternativa mínima dentro de alcance | Solución proporcionada si existe un defecto/riesgo. |
| Propuesta de ampliación (opcional) | Valor, costo/esfuerzo, riesgos y consecuencias de no adoptarla; decisión a cargo del responsable. |
| Decisión y estado | `ATENDIDA`, `ATENDIDA_SIMPLIFICADA`, `NO_SE_ATIENDE_FUERA_DE_ALCANCE`, `PENDIENTE_DECISION`, `DIFERIDA`; distinguir recomendación de aprobación. |
| Iteración impactada | I00… I06 **solo si la relación está sustentada**; mejoras opcionales no son bloqueantes automáticas. |

## 7. Regla de aprobación, respuesta y liberación

1. El auditor puede presentar tanto defectos como cuestionamientos del alcance. **No puede editar los límites aprobados** sin autorización del responsable funcional.
2. Si solicita ampliar, registrar una propuesta de cambio OpenSpec; **no actualizar directamente** requisitos vigentes, SQL, API o prototipo hasta aprobación.
3. En el informe de respuesta al auditor, cada rechazo debe identificar la exclusión EXC y la **justificación de proporcionalidad, temporalidad y valor**. También debe decir qué control existente sustituye o mitiga la necesidad.
4. No rechazar una brecha interna de seguridad, integridad o protección de datos por alegar «es temporal».
5. Para GO de una iteración, exigir corrección de defectos críticos **dentro de su alcance** y dependencias aprobadas; **no** exigir ampliaciones no aprobadas.
6. No declarar una observación «cerrada» hasta registrar evidencia documental o prueba cuando corresponda.

## 8. Ejemplos de clasificación

| Situación | Clasificación correcta |
|---|---|
| PCD acepta y guarda 31/02/1998 en DATE | **INCUMPLIMIENTO_EN_ALCANCE** (control de tipo). |
| Un nombre bien tipado JUANA no coincide con Juan en papel | **FUERA de validación automatizada de PCD**; revisión manual por presbiterio. Una IA que compare con OCR sería PROPUESTA_FUERA_DE_ALCANCE. |
| No se registra nominalmente a 12 integrantes de GC | **EXC-01**, no es defecto; solo se guarda cantidad aproximada y líder. |
| Usuario puede leer ficha personal ajena sin permiso | **RIESGO_ESENCIAL_DE_CONTROL**, evaluar aunque la aplicación sea temporal. |
| No hay tablero de conciliación ni supervisores | **EXC-03**, mejora opcional si se justifica; no es brecha de alcance. |
| Dos Personas con mismo documento provocan pérdida de ficha | **INCUMPLIMIENTO_EN_ALCANCE**, debe resolverse sin ampliar el producto. |
| No existe botón de migración SGSP | **EXC-06**, no defecto; conservar trazabilidad para migración futura. |

**Este protocolo es de aplicación obligatoria en nuevas auditorías y en la reevaluación de las observaciones H-01…H-49 existentes.**

## Actualización de criterios de auditoría V4
El auditor debe evaluar **el modelo aprobado de 11 tablas**, una tabla de catálogos, organización jerárquica y ubigeo independiente. La ausencia de una tabla por cada catálogo, de permisos territoriales de consulta o de subtipo documental **no es defecto**: son decisiones explícitas. Consultas son generales por funcionalidad con filtros opcionales por presbiterio y/o sede. No convertir sugerencias de normalización adicional en requisitos. Los riesgos esenciales H-40, CSRF, privacidad y backup sí se revisan aunque PCD sea temporal.
