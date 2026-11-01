# PCD V4 — Etapa 4: preparación de producción, privacidad y cierre temporal

**Fecha:** 01/11/2026. **Producto:** PCD, app temporal de digitación de Personas y líderes GC. **Alcance vinculante:** [ALC-PCD](../01-analisis/alcance-funcional.md). **Diseño de datos:** [11 tablas](../03-base-datos/anexos/justificacion-11-tablas.md). Este documento **no representa evidencia de pruebas realizadas ni aprobación jurídica**.

## 1. Tratamiento de hallazgos de producción
| Hallazgo/área | Evaluación independiente | Medida mínima proporcional | Estado |
|---|---|---|---|
| **H-40 — datos personales y sensibles** | **Crítico, dentro de alcance**. Campos de condición religiosa/participación e identificación requieren tratamiento responsable. | Validar responsable de banco/tratamiento, finalidad, información/consentimiento o base habilitante legal, condiciones de acceso, plazos, ejercicio de derechos, tratamiento por proveedor, GPS y respaldos. | **Pendiente decisión institucional/jurídica**, bloqueo de producción con datos reales. |
| Instalación cPanel | Alto operativo | HTTPS, PHP y extensiones compatibles, InnoDB, hosting/capacidad disponible, rutas webroot seguras, permisos mínimos. | **Pendiente prueba en servidor real**. |
| Respaldo/restauración | Alto | Backup BD cifrado/protegido fuera de webroot, responsable, registro de restauración verificada, rollback versión frontend+API+BD. | **Pendiente prueba**. |
| Seguridad sesiones | Alto | Cookies seguras, CSRF, rate limiting, inactividad, versión de credenciales, auditoría de login. | **Diseñado**, no ejecutado. |
| Fugas por consultas generales | Alto | Consulta nacional **solo para quien tenga acceso a esa funcionalidad**, con paginación y sin GPS/credenciales/bitácora sensible; edición solo propia. | **Pendiente prueba de autorización**. |
| Integridad de 11 tablas | Alto | Probar FKs compuestas ORGANIZACION/SEDE y FICHA/GC, CHECK, SQL/seed en MySQL/MariaDB hosting. | **Pendiente prueba**. |
| Ubigeo | Insumo construcción I02 | Importar datos oficiales INEI con versión/fecha, validar jerarquía; no inventar códigos. | **Pendiente I02**, no bloquea I00/I01. |
| Cierre de campaña | Alto por datos persistentes | Acta de corte, backup final, accesos deshabilitados, destino de datos/migración posterior y eliminación/retención autorizadas. | **Pendiente responsable y fechas**, sin automatizaciones extra. |

## 2. Marco normativo — referencias y límites
- Ley peruana **N.º 29733**, protección de datos personales: https://www.gob.pe/institucion/congreso-de-la-republica/normas-legales/243470-29733
- Reglamento aprobado por **D.S. N.º 016-2024-JUS**, vigente desde marzo de 2025 según ANPD: https://www.gob.pe/institucion/anpd/normas-legales/6554453-16-2024-jus y https://www.gob.pe/institucion/anpd/campa%C3%B1as/128319-nuevo-reglamento-de-proteccion-de-datos-personales
- Las creencias/convicciones religiosas son **datos sensibles** según definición legal. PCD trata información de una congregación y condiciones pastorales: **se requiere evaluación jurídica específica** sobre legitimación, aviso, consentimiento si corresponde, acceso, transferencias/proveedor y datos de menores. **No asumir** que el consentimiento de ficha física, la relación religiosa o la vida temporal de la app cubren por sí solos todas las obligaciones.
- Definir si corresponde registrar/actualizar banco de datos, gestión de incidentes, documento formal de seguridad y otras obligaciones según organización, alcance y regulación vigente; revisión por el responsable designado. Este artefacto es **checklist técnico**, no dictamen jurídico.

## 3. Matriz de decisiones institucionales H-40 (TODAS PENDIENTES)
| Código | Decisión | Evidencia requerida para cerrar |
|---|---|---|
| PR-01 | Designar titular/responsable del tratamiento y contacto de privacidad de IECPNA. | Documento o acta aprobada. |
| PR-02 | Determinar finalidad exacta de captura y posterior uso en SGSP; base jurídica de tratamiento, transparencia y si se requiere consentimiento/formato. | Aviso/información y validación responsable. |
| PR-03 | Determinar tratamiento de datos sensibles y de menores si hay registros de niños o adolescentes. | Evaluación y salvaguardas concretas aprobadas. |
| PR-04 | Documentar servicio de hosting, encargado y transferencias/ubicación del almacenamiento según corresponda. | Contrato/condiciones y validación jurídica. |
| PR-05 | Fijar tiempo de conservación de fichas, datos personales, GPS, logs, backups y retención postcampaña. **No inventar plazos.** | Tabla formal con duraciones aprobadas. |
| PR-06 | Definir quién responde solicitudes de acceso, rectificación, oposición/eliminación según ley y canales reales. | Procedimiento escrito fuera de un nuevo módulo PCD. |
| PR-07 | Definir respuesta operativa a incidentes de seguridad y quién decide notificaciones exigibles. | Responsable, canal y pasos documentados. |
| PR-08 | Política de cierre: corte, inventario, acceso, respaldo, destino, migración futura aparte y supresión cuando proceda. | Acta de cierre y evidencias. |

**Regla:** ninguna decisión jurídica o plazo de retención se presume aprobada por la IA. La opción es un **procedimiento operativo/documental**, no agregar pantallas de gobierno de privacidad al PCD.

## 4. Checklist técnico previo a puesta en marcha
| ID | Prueba / control | Evidencia | Gate |
|---|---|---|---|
| PROD-01 | Versión real PHP/MySQL/MariaDB/Apache y soporte CHECK/FK/JSON, HTTPS | Capturas/versiones, resultado de scripts en **BD de prueba** | Obligatorio |
| PROD-02 | SQL 11 tablas + semillas catálogos, integridad de organización/ubigeo y transacciones | Logs de migración/seed, consultas de integridad | Obligatorio |
| PROD-03 | Login real, credenciales separadas ADMIN/REPORTES, CSRF/rate limit, expiración y revocación | Pruebas 401/403/429 + auditoría | Obligatorio |
| PROD-04 | Consulta general por funcionalidad y filtros presbiterio/sede, edición propia, no GPS/PII indebida | Matriz de roles, casos IDOR y paginación | Obligatorio |
| PROD-05 | GPS opcional solo una vez por sesión, denegación no bloquea | Casos de refresh/cambio sede y eventos | Obligatorio |
| PROD-06 | Backups fuera de webroot + **restauración probada** en BD de prueba y reversión build | Bitácora de backup y restore, checksums | Obligatorio |
| PROD-07 | Rendimiento con datos sintéticos, sin deteriorar hosting compartido | p95, errores, baseline, 10/20/30 sesiones | Obligatorio |
| PROD-08 | H-40 decisiones PR-01…08 documentadas y aprobadas | Acta/responsable | **Bloquea datos reales** |
| PROD-09 | Desactivación/cierre y preservación de trazabilidad | Procedimiento y ensayo sin PII real | Obligatorio |
| PROD-10 | Revisión humana de QA de fichas a cargo de presbiterios **fuera del sistema** | Procedimiento externo comunicado | No nuevo módulo |

## 5. Arquitectura operativa y minimización
- Compilar React/TS/Vite fuera de hosting; servir archivos estáticos.
- API PHP 8.x dentro del mismo origen `/api/v1`, código y secretos fuera de webroot; PDO con parámetros, usuario BD mínimo privilegio. No Node residente, colas, Redis ni microservicios.
- MySQL/MariaDB InnoDB para 11 tablas; campos sensibles solo con permisos. Evitar dumps en `public_html`; cookies `Secure/HttpOnly/SameSite`, cache no-store en endpoints privados.
- En sesión, persistir estado GPS **una vez**, no recapturar. Limitar accesos ADMIN a bitácora, sin coordenadas en reportes habituales.
- Tratamiento de información de cónyuge/responsabilidades en `ficha.datos_fuente_json` exige reglas PHP y no permite incluir datos arbitrarios fuera del papel.

## 6. Procedimiento de backup, recuperación y reversión
1. Registrar versión exacta de build, API y SQL/seed; crear respaldo previo de BD y archivos protegidos.
2. Hacer deploy de build React y PHP a directorios/versiones controlados; ejecutar scripts **solo en entorno apropiado**, no ejecutar 001 como ALTER cuando haya BD previa.
3. Ejecutar smoke tests login, selección sede, alta Persona incompleta, alta GC líder, filtros, auditoría, logout, denegación GPS.
4. Ante falla: detener escrituras, conservar logs sin PII excesiva, volver a build/API anteriores y **restaurar BD** cuando corresponda, verificando integridad; avisar al responsable.
5. Programar backup según política aprobada y probar recuperación periódica. **No afirmar que se configuró** backup o monitoreo sin evidencia.

## 7. Cierre del PCD temporal
Definir fecha de corte; obtener reportes de avance y calidad, conservar evidencia de autoría, cerrar sesiones/usuarios, proteger última copia y documentar inventario. La migración posterior a SGSP es otro proyecto; **no** se implementa integrador ni tablero de seguimiento pastoral. El tratamiento/borrado de datos y GPS se realiza solo según retención aprobada.

## 8. Respuesta al auditor — aceptación y exclusiones
**Atender:** seguridad, integridad, privacidad, respaldo, recuperación y cierre **dentro de PCD**. **No atender por sobrealcance:** SSO/gestión corporativa, supervisor de fichas, workflow de conciliación, miembros nominales GC, gestor documental u OCR, integración SGSP y operación multi-campaña permanente. H-40 **no** puede rechazarse por temporalidad.

**Estado de salida:** **DISEÑO Y CHECKLIST DOCUMENTADOS, SIN GO PRODUCTIVO**; PR-01…08 y PROD-01…09 pendientes de evidencias. No se ha conectado cPanel ni realizado restauraciones/pruebas reales.

## 9. H-46 — Recursos y rendimiento del hosting (observación original, prioridad BAJA)

**Hallazgo:** `idempotencia` sin depuración documentada, escrituras excesivas de `sesion.last_seen`, disponibilidad no verificada de Argon2id y captura de métricas sin definir. La aplicación es temporal en cPanel: **atender con correcciones pequeñas, sin sumar tablas, Redis, workers residentes ni plataforma de métricas**.

| Subtema | Acción al construir | Comprobación |
|---|---|---|
| **H-46A / idempotencia** | `idempotencia.created_at` permite eliminar respuestas antiguas de forma **acotada y configurable** tras definir ventana de reintento; ejecutar purga oportunista poco frecuente o usando mecanismo existente del hosting. **No borrar claves aún válidas** ni ejecutarla en cada consulta de ficha. | CA-112: reintento dentro de ventana devuelve el mismo resultado; después de ventana aprobada, purga limitada sin romper operaciones recientes. |
| **H-46B / sesión** | Actualizar `sesion.last_seen` de forma **limitada por intervalo** (p. ej., 60 segundos como valor técnico inicial a validar), manteniendo el cálculo de inactividad y revocación seguro. Evitar UPDATE por cada tecla o request GET. | CA-113: ráfaga de consultas no produce escritura de sesión en cada request, pero la expiración continúa funcionando. |
| **H-46C / hashing** | Verificar `PASSWORD_ARGON2ID` en la versión PHP real; usar `password_hash/password_verify`. Si Argon2id no está disponible, valorar `PASSWORD_BCRYPT` con parámetros apropiados y documentar decisión, **no** usar texto plano ni hash débil. | CA-114: usuarios reales verifican contraseña y fallos correctamente, algoritmo soportado en servidor. |
| **H-46D / medición** | Pruebas sintéticas de p95/errores y logs técnicos rotativos existentes, sin incluir PII ni GPS. Registrar hallazgos de carga en archivo de evidencia, **sin tabla nueva de métricas**. | PROD-07 y reporte de resultados con la herramienta disponible. |

**Criterio de aceptación:** H-46 se considera **atendida en diseño**, no cerrada técnicamente hasta pruebas con PHP/MySQL reales. El intervalo de purga y su retención deben ser compatibles con la política de privacidad H-40 y el comportamiento de reintento acordado. No presumir plazos de retención o infraestructura no existente.
