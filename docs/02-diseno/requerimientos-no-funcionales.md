# RNF-PCD-4.0 · Requerimientos no funcionales y verificaciones
| ID | Dimensión | Criterio de aceptación / medición |
|---|---|---|
| RNF-01 | Plataforma | Ejecutar en cPanel existente con frontend compilado, PHP 8.x y MySQL/MariaDB InnoDB; sin Node residente. |
| RNF-02 | Respuesta inicial | Objetivo p95 ≤2 s en conexión de referencia, **por medir en hosting real**. |
| RNF-03 | API guardar | Objetivo p95 ≤1 s en carga prevista, sin contar espera de geolocalización; medir en pruebas. |
| RNF-04 | Duplicidad | Objetivo p95 ≤500 ms para búsqueda indexada, medido sin cache engañosa. |
| RNF-05 | Concurrencia | Probar 10/20/30 usuarios simulados; preservar rendimiento web institucional; umbral real sujeto a baseline. |
| RNF-06 | Consistencia | Transacciones InnoDB para datos relacionados + auditoría; rollback completo en fallos. |
| RNF-07 | Identidad | Clave técnica única NULL-permitida para documento DNI/CE confiable por tipo+número, sin subtipo y sin fusionar automáticamente OTRO; duplicados de fichas sin duplicar Persona. |
| RNF-08 | Seguridad | HTTPS, autenticación real, hash de contraseñas, CSRF, throttling/login, sin secretos Git. |
| RNF-09 | Autorización | Permiso de funcionalidad comprobado en backend; consulta total y filtros opcionales Presbiterio/Sede, escritura por autor/ADMIN comprobada en backend. |
| RNF-10 | Privacidad | Sin PII en localStorage, URLs ni consola; reportes mínimos y acceso GPS restringido. |
| RNF-11 | Auditoría | Creador inmutable, actor editor, antes/después, IP, hora, sesión, GPS disponible; fallos logueados. |
| RNF-12 | Ubicación | Pedir como máximo una captura por sesión, reutilizable tras refresh; denegación no bloqueante. |
| RNF-13 | Resiliencia | Idempotencia en POST; evitar doble clic y duplicado ante reintento. |
| RNF-14 | Accesibilidad | Teclado Enter/Tab + foco visible, etiquetas y errores; móvil 320px y escritorio, zoom 200%. |
| RNF-15 | Datos | UTF8MB4, fechas reales, campos críticos parametrizados y almacenables incompletos. |
| RNF-16 | BD | Índices por documento, sede, fechas, autor y claves foráneas de consistencia. |
| RNF-17 | Respaldo | Backup automatizable y restore de prueba antes de campaña. |
| RNF-18 | Mantenibilidad | Separación frontend/API/dominio/persistencia, lint/test, API OpenAPI y scripts de despliegue. |
| RNF-19 | Seguridad sesión | Expiración configurable, invalidación al logout, cookies protegidas, registro de acceso. |
| RNF-20 | Fallos | Mensajes claros sin datos SQL; no perder lo tecleado en error de red; reintento idempotente. |
| RNF-21 | Catálogos | Cache de catálogos no sensibles con versión; invalidación en nueva sede. |
| RNF-22 | Migrabilidad | IDs PCD estables; no acoplar esquemas o credenciales de SGSP. |
| RNF-23 | Rendimiento BD | Paginación server-side y consultas limitadas; no cargar todos los registros a la vez. |
| RNF-24 | Cierre | Protocolo de desactivación, backup, retención aprobada y borrado controlado. |

**No son cifras garantizadas:** RNF-02 a 05 son metas a verificar en el hosting actual. Preparar informe p50/p95, errores, CPU/conexiones si cPanel ofrece información, y baseline del sitio institucional.


**RNF-V4-01:** Crear **11 tablas** en motor MySQL/MariaDB real, comprobar CHECK/FK y no habilitar producción sin pruebas. **RNF-V4-02:** consentimiento/base y retención H-40 validados por IECPNA según [Etapa 4](../07-auditorias/etapa-4-produccion-y-privacidad.md). **RNF-V4-03:** inyección/CSRF/IDOR, consulta global con filtros, edición solo propias; no coordenadas en listados. **RNF-V4-04:** restablecer backup y realizar rollback con evidencias.

## RNF-OPT-46 — Recursos mínimos de hosting
En producción no escribir sesión por cada GET/tecla; limitar `last_seen` por intervalo con expiración efectiva. Retirar idempotencias antiguas tras ventana aprobada, sin afectar reintentos recientes. Comprobar Argon2id en PHP y alternativa segura; medir p95 y 5xx de manera sintética sin tabla extra ni servicio de monitoreo residente. **Verificar en I01/I06, no asumir implementación.**
