# Justificación de las 11 tablas PCD

**Alcance:** [ALC-PCD-3.1](../../01-analisis/alcance-funcional.md). Aplicativo temporal de digitación, no sistema pastoral. **Modelo físico de referencia:** [SQL](../../../database/migrations/001_initial.sql).

| Tabla | Área | Función propia | Justificación de mantenerla separada |
|---|---|---|---|
| `organizacion` | Organización | Presbiterios y sedes en jerarquía, sede creada durante captura | Unifica presbiterio+sede; relaciones y altas son operativas |
| `usuario` | Seguridad | Cuentas, roles, hash y presbiterio operativo | Entidad autenticable con ciclo de vida y credenciales propias |
| `sesion` | Seguridad | Sesiones, revocación, contexto y un único GPS opcional | Control de seguridad con vencimiento y auditoría de ubicación |
| `login_evento` | Seguridad | Intentos, bloqueo y salida incluso sin usuario autenticado | Registrar intentos fallidos sin sesión, rate limiting |
| `catalogo` | Configuración/organización | Tabla de tablas de listas cortas y campos críticos JSON | Evita tabla para cada lista pequeña; configuración por categoría |
| `ubigeo` | Configuración/organización | Árbol INEI: departamento/provincia/distrito | Catálogo territorial estructurado amplio, insumo I02 |
| `persona` | Registro | Identidad consolidada y atributos de Persona | Una Persona puede estar en varias fichas, clave documental única confiable |
| `ficha` | Registro | Origen de fichas Persona/GC, autor, papel y datos JSON | Conserva fuentes independientes, cónyuge y responsabilidades en JSON |
| `grupo_crecer` | Registro | Datos generales de GC y vínculo con ficha líder | Entidades de GC diferenciadas, sin integrantes nominales |
| `auditoria_evento` | Control | Actor, acción, valores antes/después | Permite rastrear ediciones sin workflow de aprobación |
| `idempotencia` | Control | Reintentos de mutación y payload | Evita duplicar ficha o GC en doble envío |

## Consolidación desde 17 tablas

| Tablas anteriores | Destino nuevo | Decisión |
|---|---|---|
| presbiterio + sede | **organizacion** | Una jerarquía PRESBITERIO/SEDE, alta de sedes dentro del flujo. |
| usuario + usuario_presbiterio | **usuario** | Un presbiterio operativo por digitador; consulta general por funcionalidad. |
| sesion_captura + geo_sesion | **sesion** | GPS opcional una vez por sesión. |
| campana | **eliminada** | Una campaña única, configurada en el despliegue; no generar módulo multi-campaña. |
| catalogo_valor + campo_critico | **catalogo** | Tabla de tablas con `config_json` para criterios de completitud. |
| persona_captura | **persona** | Conserva entidad de identidad. |
| ficha_captura + conyuge_pendiente + persona_responsabilidad_captura | **ficha** | Cónyuge y responsabilidades como secciones JSON del papel; sin ciclo de vida autónomo. |
| grupo_crecer_captura | **grupo_crecer** | Conserva resumen GC, líder = ficha.persona_id. |
| login_evento | **login_evento** | No fusionar con auditoría porque registra intentos previos al inicio de sesión. |
| auditoria_evento | **auditoria_evento** | Conservar trazabilidad independiente. |
| idempotencia | **idempotencia** | Conservar reintentos transaccionales independientes. |
| (nueva) ubigeo | **ubigeo** | Única tabla jerárquica INEI, entrada de construcción I02; no inventar sedes ni códigos. |

**Resultado neto: 17 → 11 tablas** (17 previas, se eliminan 7 netas y se añade UBIGEO). Las decisiones eliminan tablas, no controles fundamentales. No ejecutar el SQL nuevo sobre un esquema anterior con datos sin migración expresa.
