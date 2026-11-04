# Diseño I02 — PCD V4.1

## Arquitectura
Rutas HTTP -> AuthMiddleware (sesión/CSRF) -> PersonaController -> PersonaService (validaciones, transacciones) -> PDO InnoDB (persona, ficha, idempotencia). Catálogos y ubigeos solo lectura autorizada.

## Identidad y doble captura
- DNI: 8 caracteres numéricos; mantener ceros iniciales. Clave exacta `DNI:<número>`.
- CE y OTRO: conservar literalmente y normalizar para alerta, sin fusión automática no aprobada.
- Si existe clave confiable: reutilizar Persona y crear nueva ficha con `presbiterio_id/sede_id/created_by` propios. Jamás actualizar Persona existente al adjuntar ficha.
- Carrera de unicidad: transacción, UNIQUE de `clave_identidad`; recuperar ID ganador, no modificar su consolidado.
- `POST /personas/{id}/fichas`: verificar la coincidencia documental con Persona (no solo ID); rechazar discrepancias.

## Fichas
`tipo_ficha='PERSONA'`; no requiere número de ficha; fecha papel es distinta de created_at. `datos_fuente_json` admite esquema 1.0, conyuge y responsabilidades exclusivamente; JSON inválido o propiedades desconocidas -> 422. No insertar evento de auditoría en alta.

## Seguridad
Rol REPORTES solo lectura, DIGITADOR/ADMIN alta; contexto de sede obligatorio, comprobado contra presbiterio; no datos reales, secretos, GPS ni PII en logs/URL. Lectura nacional dentro de funcionalidad autorizada. H-24 bloquea definir umbral mínimo; no deducirlo de UI.

## Fuente INEI
Fuente oficial encontrada: https://www.gob.pe/15028-consultar-codigos-estandarizados-del-inei y https://www.datosabiertos.gob.pe/dataset/ubigeos-c%C3%B3digos-de-ubicaci%C3%B3n-geogr%C3%A1fica-instituto-nacional-de-estad%C3%ADstica-e-inform%C3%A1tica-inei. Antes de importar, fijar corte, comprobar jerarquías y SHA256 del archivo de entrada. No importar datos externos sin esas comprobaciones.

## Pruebas
PHPUnit unitarias para normalización, fechas/JSON, HTTP integrado sobre MySQL 8 en CI, reintento idempotente, rollback, lectura autorizada y concurrencia. La verificación de la regla H-24 queda BLOCKED.