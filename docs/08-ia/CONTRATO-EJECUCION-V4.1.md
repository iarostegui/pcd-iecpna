# Contrato de construcción PCD — SDD V4.1
**Propósito:** instrucciones ejecutables para IA (React/PHP/PDO/MySQL), con límites probables de error. Norma: [ALC-PCD-4.0](../01-analisis/alcance-funcional.md), [OpenSpec](../../openspec/specs/README.md), [OpenAPI](../04-integracion/openapi.yaml), [SQL](../../database/migrations/001_initial.sql). **Diseño, no prueba de ejecución**.

## 1. Instalación y catálogo fijo
1. En BD **vacía de prueba** ejecutar 001 (exactamente 11 tablas) y después 002. Se crean los presbiterios: LIM/Lima, TRU/La Libertad – Trujillo, CHI/Chimbote, IQU/Iquitos. Son identificadores internos del prototipo; sedes ficticias NO se provisionan.
2. **No implementar CRUD ni pantalla para PRESBITERIO.** `GET /presbiterios` devuelve solo nodos activos predefinidos; un nuevo presbiterio lo incorpora personal técnico mediante SQL autorizado, verificando unicidad raíz, sedes y usuarios.
3. `POST /sedes` sí está dentro de alcance. PHP verifica usuario/perfil y presbiterio activo de sesión. `nombre_norm` = trim + espacios compactados + NFKD + eliminación de diacríticos + mayúsculas (acordar/usar exactamente misma función en toda la API). Comparar nombres **dentro del mismo presbiterio**; rechazar colisión exacta UNIQUE (409); similitud no exacta solo advertencia.
4. Un presbiterio **no** puede aparecer duplicado: `nombre_raiz_norm` generado y UNIQUE. No realizar inserciones por UI que lo eludan.

## 2. Identidad documental y creación de ficha (E1-D01 APROBADA)
En el servidor; inicio de transacción PDO/InnoDB:
1. Autenticar sesión, contexto de captura y sede perteneciente al presbiterio; verificar `Idempotency-Key` por usuario + hash de payload, devolver resultado previo en reintento idéntico.
2. Validar fecha real o NULL, tipo/número de documento **sin subtipo**. Construir `clave_identidad` solo si confiable (DNI 8 dígitos preservando ceros; C.E. cuando formato oficial ya validado). Para OTRO o documento ambiguo, `clave_identidad=NULL` y solo alerta.
3. Si existe `persona.clave_identidad` exacta, **reutilizar el ID de Persona** aunque la ficha provenga de otro presbiterio. Si no existe, insertar Persona nueva. Ante carrera UNIQUE, recuperar la Persona ganadora y continuar sin duplicación. **Nunca sobrescribir atributos del consolidado por anexar otra ficha**.
4. Insertar una **ficha nueva con ID independiente** (Persona o GC según operación), contexto, `created_by` inmutable, `created_at` del servidor. Validar secciones JSON versionadas [Persona](../03-base-datos/esquemas-json/ficha-persona.schema.json) o [GC](../03-base-datos/esquemas-json/ficha-gc.schema.json), o NULL si no existen.
5. **No insertar `auditoria_evento` durante INSERT de FICHA**. La fecha/autor de creación son las únicas marcas exigidas para altas. Guardar resultado de idempotencia y hacer COMMIT; responder `persona_reutilizada` correcto.
6. `POST /personas/{id}/fichas` debe usar **la misma regla** y verificar que el ID apunta a identidad legítima/documento concordante, antes de anexar una ficha; no permitir falsificar identidad solo por conocer ID. No editar consolidado ni fichas existentes como efecto lateral.
7. Si la ficha no contiene información suficiente, respetar H-24 pendiente: no inventar un nuevo bloqueo de guardado hasta aprobación.

**Ejemplo de aceptación:** DNI 00123456 ya capturado en LIM y nueva ficha del mismo DNI en TRU → 1 Persona, 2 Fichas con autor/presbiterio distintos, Persona original idéntica, **0 eventos de auditoría de alta**, respuesta indica reutilización.

## 3. Modificación de ficha
1. GET permite consulta general dentro de funcionalidad autorizada con filtros optativos presbiterio/sede, y controles de acceso a GPS/bitácora. **La consulta no da permiso de edición**.
2. En PATCH, DIGITADOR solo si `ficha.created_by = usuario.id`; ADMIN según su perfil; REPORTES nunca. Revalidar desde PHP aunque React oculte el botón.
3. Leer versión bajo transacción; si `version` antigua devolver 409.
4. Validar el payload y **comparar cada campo** con valor actual, incluyendo campos JSON permitidos. Si no existen diferencias, no crear evento de auditoría y no alterar `created_by`.
5. Si hay diferencias, actualizar solo columnas autorizadas y `updated_at` de servidor, aumentar `version`, insertar **un** `auditoria_evento` con `usuario_id, actor_codigo, actor_perfil, sesion_id, recurso_tipo/id, ip, created_at` y mapas `antes_json/despues_json` exclusivamente de claves que cambiaron. No guardar toda la ficha; proteger PII y respetar retención H-40.
6. Confirmar operación y auditoría juntos; rollback de ambos ante error. No atribuir cambios ADMIN al autor original.

## 4. Seguridad de aceptación y producción
- UAT: contraseña DIGITADOR **común solo para pruebas**, nunca almacenada en Git o logs; hashes seguros y límite de intentos. ADMIN/REPORTES tienen secretos distintos.
- Pase a producción: sustituir credenciales compartidas de prueba por credenciales **individuales**, invalidar todas las sesiones UAT y verificar el corte con CA-120. No desplegar datos reales hasta GO técnico/institucional y H-40.
- Cookie `__Host-pcd_sid` HTTPS/HttpOnly/SameSite/Path raíz, CSRF en escrituras, expiración y revocación `auth_version`.
- Ubicación navegador una vez por sesión es **telemetría opcional no probatoria**, hora de recepción registrada por servidor, no reenviar GPS en cada ficha.
- **H-40 (decisión funcional):** REPORTES es el perfil responsable de gestionar la designación y aprobación institucional del PCD. Es una **responsabilidad organizativa**, no una autorización técnica de escritura ni un GO institucional emitido. Mantener REPORTES solo lectura. Antes de producción, conservar evidencia formal de la designación, del aprobador nominal y de la aprobación, además de los demás requisitos H-40.

## 5. Reportes e integridad
- La creación de FICHA se cuenta desde `ficha.created_at/created_by`; las **ediciones reales** se cuentan desde `auditoria_evento` por actor.
- En todas las tablas: `created_at/updated_at` de BD; diferenciarlos de `fecha_papel` de ficha.
- Ningún índice nuevo de reportes se aprueba por especulación: ejecutar consultas reales/paginadas con datos sintéticos y revisar `EXPLAIN` antes de recomendar un índice.
- Probar FK compuestas, colación y UNIQUE de columna generada en el **motor MySQL/MariaDB** real. Ningún PASS documental sustituye pruebas de motor.

## 6. No inventar
- No CRUD de presbiterios, no permiso de consulta territorial configurable, no subtipo documental, no tabla de cónyuge/responsabilidades, no registro de integrantes GC, no conciliador del papel ni flujo supervisor, no integración automática SGSP.
- E1-D02/H-23/H-24 siguen abiertas. Lo que no esté aprobado se registra como bloqueo de esa iteración, **no se deduce** de la interfaz de demostración.
- El objetivo del especialista es verificar estas reglas antes de generar código productivo, no iniciar otra etapa de ampliación de alcance.
