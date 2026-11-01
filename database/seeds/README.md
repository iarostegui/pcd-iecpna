# Provisionamiento inicial sin secretos
- Cada presbiterio registrado debe tener exactamente seis cuentas de digitación iniciales `<PREFIJO>-DIG-01..06`. Prefijos/códigos de presbiterio se generan a partir de catálogo confirmado.
- Crear un ADMIN nacional y REPORTES nacional con **contraseñas propias distintas** de la común de digitadores (diseño de mitigación H-03, riesgo residual E2-D03 pendiente de aceptación).
- **Contraseña real compartida se suministra en instalación, jamás versionada en Git, jamás en claro en SQL.**
- El instalador PHP toma `PCD_INITIAL_PASSWORD` de configuración protegida, deriva hash seguro para cada usuario y descarta el secreto de memoria tan pronto como sea posible. **No obligar cambio en primer ingreso** según acuerdo.
- No usar ejemplos `1234` del prototipo productivamente.
- Catálogo inicial: solo presbiterios y sedes verificados; los 48 nombres no están disponibles. Las ocho sedes de Lima del documento de Consejo Directivo son indicios por homologar, no semillas definitivas.
- Registrar campaña activa con código estable; semillas idempotentes, sin tocar registros existentes.

## E2: SQL de inicialización confirmado con prototipo
[002_catalogos_calidad.sql](002_catalogos_calidad.sql) define SOLO dominios y parámetros de criticidad de campos comprobados en fichas y en el JS de PCD V2.2. No contiene catálogo oficial de sedes/ubigeo ni usuarios reales. Se ejecuta **después** de `database/migrations/001_initial.sql` en BD **de prueba**.

**No sobrescribir** decisiones ADMIN: utiliza `INSERT IGNORE` para no restablecer `campo_critico.es_critico` al re-ejecutar semillas. Los códigos ubigeo siguen pendientes DP-06 y no se inventan. Ninguna contraseña se versiona.

**V4:** [002_catalogos_calidad.sql](002_catalogos_calidad.sql) carga dominios pequeños + criticidad a la tabla **catalogo**. No existen `catalogo_valor` ni `campo_critico` como tablas; UBIGEO oficial se incorpora en I02 desde fuente INEI. No incluir usuarios reales ni credenciales en semillas.
