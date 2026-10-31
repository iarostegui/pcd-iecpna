# Provisionamiento inicial sin secretos
- Cada presbiterio registrado debe tener exactamente seis cuentas de digitación iniciales `<PREFIJO>-DIG-01..06`. Prefijos/códigos de presbiterio se generan a partir de catálogo confirmado.
- Crear un ADMIN nacional y REPORTES nacional.
- **Contraseña real compartida se suministra en instalación, jamás versionada en Git, jamás en claro en SQL.**
- El instalador PHP toma `PCD_INITIAL_PASSWORD` de configuración protegida, deriva hash seguro para cada usuario y descarta el secreto de memoria tan pronto como sea posible. **No obligar cambio en primer ingreso** según acuerdo.
- No usar ejemplos `1234` del prototipo productivamente.
- Catálogo inicial: solo presbiterios y sedes verificados; los 48 nombres no están disponibles. Las ocho sedes de Lima del documento de Consejo Directivo son indicios por homologar, no semillas definitivas.
- Registrar campaña activa con código estable; semillas idempotentes, sin tocar registros existentes.

