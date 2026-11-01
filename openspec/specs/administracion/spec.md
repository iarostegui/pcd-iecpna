# PCD V4 — Administración

## ADM-01 — ADMIN nacional
MUST crear, activar/desactivar y restablecer usuarios, administrar parámetros de calidad, consultar y corregir fichas; cada cambio se audita con actor ADMIN y revocación de sesión si aplica.

## ADM-02 — Cuentas iniciales
Seis cuentas DIGITADOR por presbiterio disponible, una ADMIN y una REPORTES nacionales. Cada digitador tiene **un** presbiterio operativo `usuario.presbiterio_id`; no tabla de relaciones territoriales. Contraseña inicial común sin cambio forzado pero hash, rate limiting y auditoría; nacionales con secretos distintos.

## ADM-03 — Campos críticos en tabla de tablas
MUST gestionar filas `catalogo` categoría CAMPO_CRITICO_PERSONA/GC, `config_json` validado. Los toggles actualizan reportes pero no bloquean guardado incompleto. No se permite desactivar relaciones técnicas ni SQL dinámico arbitrario.

## ADM-04 — Solo ADMIN escribe configuración
DIGITADOR/REPORTES no pueden escribir usuarios, catálogos críticos ni parámetros técnicos.

## ADM-05 — Revocación
Desactivar o restablecer credenciales incrementa `usuario.auth_version`; `sesion` antigua deja de autorizar. ADMIN/REPORTES nacionales por rol, sin duplicar permisos por presbiterio.

## ADM-06 — NO HAY PERMISOS DE CONSULTA POR ÁMBITO
MUST NOT construir campo `alcance_consulta`, tabla `usuario_presbiterio`, UI de selección GENERAL/PRESBITERIO ni su API. La **consulta general con filtros presbiterio/sede** se define en la propia funcionalidad; la edición conserva su autorización independiente.
