# PCD — Etapa 2: Seguridad, ámbitos, catálogos, calidad y modelo GC
**Fecha:** 31/10/2026 · **Auditoría de origen:** `auditoria-pcd-sdd-v3.1.zip`, `02-matriz-hallazgos.md` · **Proyecto único:** PCD, no SGSP.  
**Alcance rector:** [ALC-PCD-3.1](../01-analisis/alcance-funcional.md) y [protocolo](protocolo-auditoria-alcance.md). **Prototipo V2.2 sin cambios.**

## 1. Objetivo
Resolver o acotar H-03, H-07, H-12, H-13, H-18, H-28, H-32, H-35 y su dependencia directa **H-04**. **No** añadir supervisión, conciliación pastoral, miembros nominales de GC, flujos del SGSP ni infraestructura permanente. Priorizar dejar **I01** especificada para la IA; I02/I03 dependen además de Etapa 1.

## 2. Disposición de hallazgos (independiente del nivel asignado por auditor)
| ID | Severidad revisada | Resultado de Etapa 2 | Estado / dependencia |
|---|---|---|---|
| **H-03** | **CRÍTICO** | Mantener seis digitadores por presbiterio y su contraseña inicial común sin forzar cambio; **no publicar listado de usuarios en login productivo**, credenciales nacionales separadas, controles de intentos, sesión y revocación. | **Diseño técnico preparado; riesgo residual de suplantación pendiente de aceptación explícita**. |
| **H-04** | **ALTO** (bloquea contrato login de I01) | Catálogo de presbiterios activos público con código/nombre únicamente; la contraseña y el usuario se introducen por campo, no enumeración. | Contrato a actualizar y verificar. |
| **H-07** | **ALTO** | Definir códigos/etiquetas de las opciones **verificadas en papel**, catálogo compacto y `GET /catalogos`. Ubigeo oficial aún pendiente; no inventar códigos. | Diseño propuesto; requiere completar fuente oficial. |
| **H-12** | **CRÍTICO de privacidad** | Alinear con comportamiento del prototipo: digitador consulta datos **de fichas de su presbiterio**, edita solo **sus propias fichas**; no PII de otras sedes de otros presbiterios; ADMIN nacional; REPORTES agregados. | **Ratificación funcional E2-D01 pendiente** sobre detalle permitido en el mismo presbiterio. |
| **H-13** | **ALTO** | Normalizar documento por tipo sin perder ceros; DNI 8 dígitos, C.E. código normalizado, OTRO exige subtipo para deduplicación exacta. Dato incierto no crea clave única falsa. | Esquema técnico preparado; formatos CE/OTRO sujetos a confirmación con datos reales. |
| **H-18** | **ALTO** | Catálogo inicial de campos críticos tomado del **prototipo**, separando campos técnicos no desactivables de indicadores configurables; calcular completitud **al consultar** (no columna `marca_calidad` fija). | Propuesta técnica fundada; contraste con prototipo realizado. |
| **H-28** | **ALTO para I01** | ADMIN/REPORTES nacionales por rol, sin filas de permisos para cada presbiterio; ambos pueden elegir presbiterio inicial en login. ADMIN cambia ámbito explícitamente; REPORTES consulta informes nacionales sin requerir sede. | **Propuesta de comportamiento E2-D02 pendiente de ratificación funcional**. |
| **H-32** | **ALTO para I01** | Inactividad propuesta **45 min**, límite absoluto **12 h**, CSRF disponible desde `GET /auth/me`, revocación de sesiones al desactivar cuenta/cambiar credencial, bitácora y estado de sesión. | Diseño técnico propuesto, parámetros ajustables; validar en hosting. |
| **H-35** | **ALTO de integridad** | Eliminar redundancia de líder GC: se obtiene por `ficha_captura.persona_id`; impedir tabla GC en ficha PERSONA mediante clave/constraint compuesta, sin duplicar responsable. | Esquema SQL propuesto, **pendiente de prueba según versión MySQL/MariaDB**. |

## 3. Seguridad H-03/H-04/H-32 — diseño mínimo para I01

**Login productivo:** elegir presbiterio de un `GET /presbiterios` **público y limitado a ID/código/nombre activos**; **escribir** el código de usuario manualmente, no mostrar el desplegable demostrativo del HTML. ADMIN/REPORTES con **contraseñas distintas a la común de digitadores**, aprovisionadas por canal protegido. No publicar códigos ni claves de usuarios en APIs públicas.

**Riesgo no eliminado:** una contraseña común entre digitadores significa que una cuenta no prueba identidad física. Mitigaciones **propuestas**: 
- 5 intentos fallidos por usuario en 15 min → retardo temporal hasta vencer ventana; 20 intentos/IP en 15 min → freno temporal; controles deben evitar divulgación de cuentas y DoS injustificado. Contabilizar con `login_evento` e índices por fecha/código/IP.
- Cookies de sesión `HttpOnly`, `Secure`, `SameSite=Lax`; CSRF de sesión recuperable en `GET /auth/me`, nunca en `localStorage`.
- Sesión por inactividad: 45 min; absoluta: 12 h; revocar al logout, desactivar usuario o restablecer contraseña; registrar inicio/cierre/revocación. `auth_version` en usuario facilita invalidar cookies previas al cambio.
- No forzar cambio inicial de la contraseña común (decisión previa); considerar un mecanismo ADMIN de reset y actualización global coordinada. **El riesgo residual debe aceptarse formalmente antes de producción con datos reales.**
- No capturar GPS múltiples veces; un snapshot de ubicación por sesión opcional y no bloqueante.

## 4. Autorización H-12/H-28

**Regla mínima propuesta:**
- `DIGITADOR`: se autentica en presbiterio asignado, elige sede. Puede **consultar fichas de su presbiterio** (según demostración V2.2) y **editar solo fichas creadas por él**. Una Persona con fichas en múltiples presbiterios se presenta **solo con datos de las fichas de su ámbito**; no exponer automáticamente el consolidado global de otro presbiterio.
- `ADMIN`: cobertura nacional por **perfil**, sin necesidad de entradas `usuario_presbiterio` para todos; puede elegir/cambiar contexto de presbiterio mediante acción explícita, elegir sede para altas/ediciones y acceder a informes nacionales.
- `REPORTES`: alcance nacional por **perfil**, solo lectura **agregada**; acceso a informes sin sede obligatoria, sin edición ni datos personales o GPS detallados por defecto.
- `GET /personas/duplicados`: para digitador, informar coincidencia sin nombre/PII de otros presbiterios ni ID manipulable; no convertir coincidencia en permiso de lectura o edición.

**La amplitud de consulta nominal dentro de un mismo presbiterio requiere ratificación E2-D01; no presentar como regla empresarial ya aprobada por el usuario.** Este diseño minimiza diferencias con el prototipo.

## 5. Catálogos y documento H-07/H-13

**Catálogo inicial verificable:** tipo documento DNI, C.E., Otro (detalle obligatorio para clave inequívoca de OTRO); sexo Femenino/Masculino; estado civil Soltero(a)/Casado(a)/Otro con detalle; condición Invitado/Nuevo Creyente/Miembro; tipo GC Varones/Mujeres/Matrimonios/Mixto; estado GC Activo/En baja/Dado de baja. Los catálogos de responsabilidades se completan solo con opciones realmente presentes en el Excel. [Diseño canónico](../03-base-datos/catalogos-e-identidad.md).

**Normalización propuesta:**
- DNI → 8 dígitos decimales, se conservan ceros; clave `DNI:<8d>`.
- C.E. → eliminar espacios y guiones, letras mayúsculas; **no imponer longitud oficial no verificada** en esta etapa; clave `CE:<normalizado>` solo si sintácticamente aceptable según contrato de implementación.
- OTRO → exigir subtipo descriptivo no vacío para identificar el tipo (`PASAPORTE`, etc. solo como ejemplos, no catálogo oficial) + número; si no hay subtipo confiable **sin clave identidad**, conservar fuente sin deduplicación exacta.
- No deducir misma persona solo por nombre; coincidencias débiles son indicadores no fusiones.
- Ubigeo requiere fuente externa oficial: no inventar claves. Mantener códigos opcionales en BD y dirección/referencia; si el formulario demanda ubicación, permitir texto literal sin equipararlo a código INEI.

## 6. Calidad configurable H-18

Tomar lista inicial de campos críticos del prototipo, distinguir:
- **PERSONA:** first (nombres), last1 (apellido paterno), type (tipo documento), doc (número), birth (nacimiento), sex (sexo), pres (presbiterio), sede (sede), condition (condición).
- **GC:** personId (líder), pres, sede, type (tipo), start (inicio), capacity (capacidad).
- **Relaciones estructurales no desactivables:** pres/sede de la ficha, y líder Persona de GC. No transformar otros campos críticos en restricciones de guardado.
- `campo_critico` registra código/etiqueta/ubicación/origen/no desactivable, se audita la actualización. La completitud para reportes se calcula con **configuración vigente** y datos persistidos, sin `marca_calidad` materializada obsoleta.
- Los asteriscos del papel **no sustituyen** esta configuración. Validación técnica de fecha real independiente de criticidad.

## 7. Integridad GC H-35

Cada registro GC tiene **una sola referencia de líder**, mediante `grupo_crecer_captura.ficha_id → ficha_captura.persona_id`. Se elimina el duplicado `lider_persona_id`; se deben bloquear asociaciones GC→ficha PERSONA y responsabilidades/cónyuges→ficha GC. Propuesta SQL: columnas `ficha_tipo` constantes con CHECK y FK compuestas a `ficha_captura(id,tipo_ficha)` o `ficha_captura(id,persona_id,tipo_ficha)`. En PHP se revalida **siempre**. Compatibilidad CHECK/FK debe verificarse en servidor real.

## 8. Qué no se atenderá (alcance)

No implementar IAM corporativo, SSO, directorios externos, roles pastorales, sistema de supervisión, detección semántica de errores de transcripción, miembros nominales GC ni maestro de grupos con conciliaciones. No elevar presión al hosting con servicios residentes por controles que PHP+MySQL pueden proveer. Esto **no** exime CSRF, seguridad, integridad de ficha, control de PII, revocación o backups.

## 9. Puertas de iteración y decisiones

| Iteración | Resultado de la etapa | Puerta de GO |
|---|---|---|
| **I00** | Sigue habilitada preparación; SQL actualizado necesita ejecución real | Versiones hosting y test SQL |
| **I01** | **Diseño de implementación preparado**, sin código; contrato API+BD/UX a sincronizar | **E2-D02** (contexto nacional) y **aceptación riesgo clave común**; SQL/API test |
| **I02** | Identidad, catálogos y permisos definidos como propuesta | **E1-D01**, **E2-D01**, formatos CE/OTRO/ubigeo, API/SQL completos |
| **I03** | GC líder único propuesto | **E1-D02**, prueba FK/CHECK en hosting, referencia correcta en API |
| **I04/I05** | Visibilidad y completitud propuestas | E2-D01 y calidad objetiva verificable |
| **I06** | Protección y revocación especificadas | H-40 (privacidad, retención/backup) antes de producción |

**E2-D01:** confirmar consulta nominal de las fichas de **todo el presbiterio** por digitador (como en prototipo), con edición solo propias.  
**E2-D02:** confirmar ADMIN/REPORTES nacionales por perfil, selección explícita de presbiterio y sede operativa solo cuando corresponda.  
**E2-D03:** reconocer riesgo residual de **contraseña compartida entre digitadores**, pese a medidas compensatorias; ADMIN y REPORTES con secretos independientes.

**Estado honesto:** documentación de decisiones técnicas actualizada; implementación no iniciada y pruebas SQL/API no ejecutadas. No declarar hallazgos cerrados sin validación y autorización de los puntos E2-Dxx.

## 10. Verificación documental efectuada (sin ejecución de código)
Se comprobaron estáticamente, sobre el último contenido de GitHub:
- **23 rutas** diferentes definidas en OpenAPI, sin ruta duplicada; referencias locales a esquemas declarados.
- **17 tablas** en `001_initial.sql`, sin referencias de FK hacia tablas inexistentes por nombre; la tabla catálogo es nueva.
- Restricción de tipo GC y líder derivado declarados; semilla de `campo_critico` y valores del papel existente.
- Enlaces internos de los seis documentos E2 principales: no se detectaron rutas inexistentes.
- El archivo prototipo `prototypes/PCD_v2.2.html` conserva el mismo SHA Git anterior.

**Esta es validación ESTÁTICA limitada**: **NO** se ha ejecutado YAML lint con parser externo, SQL en MySQL/MariaDB real, API PHP, pruebas funcionales ni ensayos de concurrencia. No dar GO técnico final por la inspección estática.

## 11. Decisiones ratificadas para ejecución SDD (31/10/2026)
**H-07 / Ubigeo:** el catálogo oficial **es insumo de construcción I02**, no una ampliación ni impedimento de I00/I01. Debe registrarse procedencia, versión y jerarquía INEI antes de cerrar el componente.
**H-12 / E2-D01:** sustituir la restricción fija anterior por **`usuario.alcance_consulta = GENERAL | PRESBITERIO`**, asignable por ADMIN. GENERAL habilita consulta nacional; PRESBITERIO filtra por los presbiterios asociados. Los permisos de modificación **NO cambian** (DIGITADOR solo fichas propias, ADMIN todas, REPORTES ninguna).
**H-13:** aprobada clave documental por naturaleza/tipo/subtipo/número normalizado; DNI conserva ceros, CE no recibe límites inventados, OTRO sin subtipo no genera clave exacta.
**H-28 / E2-D02:** rol nacional ADMIN/REPORTES y cambio explícito de presbiterio por ADMIN, aprobados.
**H-03 / E2-D03:** aceptado el uso operativo de clave inicial común **con reconocimiento del riesgo de suplantación**; no se exime controles de privacidad ni la validación productiva.
**Anexos I00:** diagrama físico y diccionario columna a columna obligatorios, generados desde mismo SQL.
Los párrafos anteriores de H-12/H-28 que indiquen `pendiente de ratificación` son **históricos** y quedan reemplazados por esta resolución.
