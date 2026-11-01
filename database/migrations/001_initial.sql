-- PCD · MODELO FÍSICO SIMPLIFICADO V4.0 (11 tablas)
-- Producto temporal IECPNA. No corresponde a la base de datos del SGSP.
-- PROPUESTA PARA BASE NUEVA SIN DATOS: 001 reemplaza el diseño de 17 tablas NO desplegado.
-- No ejecutar como parche de una BD existente: preparar migración de datos si existiese.
-- Requiere MySQL 8.0.16+ o MariaDB con CHECK aplicado; validar versión real de cPanel.
-- Seguridad, auditoría, ubicaciones y datos del papel solo en el alcance de PCD.
SET NAMES utf8mb4;

-- 1. ORGANIZACION: PRESBITERIO (sin padre) o SEDE (con padre PRESBITERIO).
-- Un solo catálogo jerárquico; tipo y padre se comprueban también en PHP.
CREATE TABLE IF NOT EXISTS organizacion (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tipo ENUM('PRESBITERIO','SEDE') NOT NULL,
 padre_id BIGINT UNSIGNED NULL,
 codigo VARCHAR(50) NULL,
 nombre VARCHAR(200) NOT NULL,
 nombre_norm VARCHAR(200) NOT NULL,
 referencia VARCHAR(300) NULL,
 activo TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME(6) NOT NULL,
 UNIQUE KEY uq_org_codigo(codigo),
 UNIQUE KEY uq_org_sede_nombre(padre_id,nombre_norm),
 UNIQUE KEY uq_org_id_padre(id,padre_id),
 KEY ix_org_tipo_padre(tipo,padre_id,activo),
 CONSTRAINT fk_org_padre FOREIGN KEY(padre_id) REFERENCES organizacion(id),
 CONSTRAINT chk_org_jerarquia CHECK (
   (tipo='PRESBITERIO' AND padre_id IS NULL) OR
   (tipo='SEDE' AND padre_id IS NOT NULL)
 )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. USUARIO: un presbiterio operativo por digitador. No tabla usuario_presbiterio.
-- El permiso de consulta es el permiso a la FUNCIONALIDAD: todos sus registros
-- consultables, filtros opcionales por presbiterio y/o sede; no ámbito por usuario.
CREATE TABLE IF NOT EXISTS usuario (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 codigo VARCHAR(80) NOT NULL,
 nombre_display VARCHAR(180) NOT NULL,
 perfil ENUM('DIGITADOR','ADMIN','REPORTES') NOT NULL,
 presbiterio_id BIGINT UNSIGNED NULL,
 password_hash VARCHAR(255) NOT NULL,
 activo TINYINT(1) NOT NULL DEFAULT 1,
 auth_version INT UNSIGNED NOT NULL DEFAULT 1,
 created_at DATETIME(6) NOT NULL,
 updated_at DATETIME(6) NULL,
 UNIQUE KEY uq_usuario_codigo(codigo),
 KEY ix_user_presb(presbiterio_id),
 CONSTRAINT fk_usuario_presb FOREIGN KEY(presbiterio_id) REFERENCES organizacion(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. SESION: incluye geolocalización opcional UNA sola vez por sesión.
CREATE TABLE IF NOT EXISTS sesion (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 usuario_id BIGINT UNSIGNED NOT NULL,
 presbiterio_id BIGINT UNSIGNED NOT NULL,
 sede_id BIGINT UNSIGNED NULL,
 session_ref_hash CHAR(64) NOT NULL,
 ip_login VARBINARY(16) NULL,
 user_agent VARCHAR(255) NULL,
 fecha_login DATETIME(6) NOT NULL,
 fecha_logout DATETIME(6) NULL,
 last_seen DATETIME(6) NULL,
 revocada_en DATETIME(6) NULL,
 auth_version_al_ingreso INT UNSIGNED NOT NULL,
 geo_estado ENUM('OK','DENEGADA','NO_DISPONIBLE','ERROR') NULL,
 geo_latitud DECIMAL(10,7) NULL,
 geo_longitud DECIMAL(10,7) NULL,
 geo_precision_m DECIMAL(10,2) NULL,
 geo_capturada_en DATETIME(6) NULL,
 UNIQUE KEY uq_sesion_hash(session_ref_hash),
 KEY ix_sesion_user_fecha(usuario_id,fecha_login),
 CONSTRAINT fk_sesion_usuario FOREIGN KEY(usuario_id) REFERENCES usuario(id),
 CONSTRAINT fk_sesion_presb FOREIGN KEY(presbiterio_id) REFERENCES organizacion(id),
 CONSTRAINT fk_sesion_sede FOREIGN KEY(sede_id,presbiterio_id) REFERENCES organizacion(id,padre_id),
 CONSTRAINT chk_sesion_geo CHECK (
   (geo_estado IS NULL AND geo_capturada_en IS NULL)
   OR (geo_estado IS NOT NULL AND geo_capturada_en IS NOT NULL)
 )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. LOGIN_EVENTO: también registra intentos FALLIDOS antes de autenticar.
CREATE TABLE IF NOT EXISTS login_evento (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 usuario_id BIGINT UNSIGNED NULL,
 presbiterio_id BIGINT UNSIGNED NULL,
 codigo_intentado VARCHAR(80) NULL,
 resultado ENUM('OK','FALLIDO','BLOQUEADO','LOGOUT') NOT NULL,
 ip VARBINARY(16) NULL,
 user_agent VARCHAR(255) NULL,
 created_at DATETIME(6) NOT NULL,
 KEY ix_login_codigo_fecha(codigo_intentado,created_at),
 KEY ix_login_ip_fecha(ip,created_at),
 CONSTRAINT fk_login_usuario FOREIGN KEY(usuario_id) REFERENCES usuario(id),
 CONSTRAINT fk_login_presb FOREIGN KEY(presbiterio_id) REFERENCES organizacion(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. CATALOGO: «tabla de tablas»: lista de dominios y parámetros críticos.
-- categoria=CAMPO_CRITICO_PERSONA/GC: config_json tiene ruta_dato,
-- es_critico y desactivable; PHP usa whitelist de rutas seguras.
CREATE TABLE IF NOT EXISTS catalogo (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 categoria VARCHAR(70) NOT NULL,
 codigo VARCHAR(85) NOT NULL,
 etiqueta VARCHAR(160) NOT NULL,
 orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 activo TINYINT(1) NOT NULL DEFAULT 1,
 config_json JSON NULL,
 updated_by BIGINT UNSIGNED NULL,
 updated_at DATETIME(6) NULL,
 UNIQUE KEY uq_catalogo_categoria_codigo(categoria,codigo),
 CONSTRAINT fk_catalogo_editor FOREIGN KEY(updated_by) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. UBIGEO: un solo árbol de departamentos, provincias y distritos;
-- fuente oficial INEI como ENTRADA DE CONSTRUCCIÓN I02.
CREATE TABLE IF NOT EXISTS ubigeo (
 codigo VARCHAR(6) PRIMARY KEY,
 tipo ENUM('DEPARTAMENTO','PROVINCIA','DISTRITO') NOT NULL,
 padre_codigo VARCHAR(6) NULL,
 nombre VARCHAR(160) NOT NULL,
 activo TINYINT(1) NOT NULL DEFAULT 1,
 KEY ix_ubigeo_padre(padre_codigo,tipo,nombre),
 CONSTRAINT fk_ubigeo_padre FOREIGN KEY(padre_codigo) REFERENCES ubigeo(codigo),
 CONSTRAINT chk_ubigeo_nivel CHECK (
   (tipo='DEPARTAMENTO' AND padre_codigo IS NULL AND CHAR_LENGTH(codigo)=2) OR
   (tipo='PROVINCIA' AND padre_codigo IS NOT NULL AND CHAR_LENGTH(codigo)=4) OR
   (tipo='DISTRITO' AND padre_codigo IS NOT NULL AND CHAR_LENGTH(codigo)=6)
 )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. PERSONA: identidad consolidada; solo TIPO y NÚMERO DE DOCUMENTO.
-- DNI 8 dígitos o C.E. confiable: clave exacta; OTRO/ambiguo: NULL.
-- Jamás fusionar por nombre parecido; nunca eliminar ceros iniciales.
CREATE TABLE IF NOT EXISTS persona (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tipo_documento VARCHAR(24) NULL,
 numero_documento VARCHAR(60) NULL,
 numero_documento_norm VARCHAR(60) NULL,
 clave_identidad VARCHAR(120) NULL,
 nombres VARCHAR(150) NULL,
 apellido_paterno VARCHAR(100) NULL,
 apellido_materno VARCHAR(100) NULL,
 fecha_nacimiento DATE NULL,
 sexo_codigo VARCHAR(24) NULL,
 estado_civil_codigo VARCHAR(24) NULL,
 telefono VARCHAR(60) NULL,
 correo VARCHAR(180) NULL,
 facebook_usuario VARCHAR(180) NULL,
 direccion VARCHAR(500) NULL,
 ubigeo_dpto_codigo VARCHAR(6) NULL,
 ubigeo_prov_codigo VARCHAR(6) NULL,
 ubigeo_dist_codigo VARCHAR(6) NULL,
 condicion_codigo VARCHAR(40) NULL,
 fecha_condicion DATE NULL,
 es_lider_gc_intencion TINYINT(1) NOT NULL DEFAULT 0,
 created_by BIGINT UNSIGNED NOT NULL,
 updated_by BIGINT UNSIGNED NULL,
 created_at DATETIME(6) NOT NULL,
 updated_at DATETIME(6) NULL,
 version INT UNSIGNED NOT NULL DEFAULT 1,
 UNIQUE KEY uq_persona_identidad(clave_identidad),
 KEY ix_persona_documento(tipo_documento,numero_documento_norm),
 KEY ix_persona_apellidos(apellido_paterno,apellido_materno,nombres),
 CONSTRAINT fk_persona_creador FOREIGN KEY(created_by) REFERENCES usuario(id),
 CONSTRAINT fk_persona_editor FOREIGN KEY(updated_by) REFERENCES usuario(id),
 CONSTRAINT fk_persona_dpto FOREIGN KEY(ubigeo_dpto_codigo) REFERENCES ubigeo(codigo),
 CONSTRAINT fk_persona_prov FOREIGN KEY(ubigeo_prov_codigo) REFERENCES ubigeo(codigo),
 CONSTRAINT fk_persona_dist FOREIGN KEY(ubigeo_dist_codigo) REFERENCES ubigeo(codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. FICHA: documento de origen PERSONA o GC. DATOS_FUENTE_JSON contiene
-- secciones de cónyuge, responsabilidades y controles del formulario papel.
-- No existen entidades/gestores independientes para tales secciones.
CREATE TABLE IF NOT EXISTS ficha (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 persona_id BIGINT UNSIGNED NOT NULL,
 presbiterio_id BIGINT UNSIGNED NOT NULL,
 sede_id BIGINT UNSIGNED NOT NULL,
 tipo_ficha ENUM('PERSONA','GC') NOT NULL,
 numero_ficha VARCHAR(50) NULL,
 fecha_papel DATE NULL,
 responsable_papel VARCHAR(180) NULL,
 observaciones TEXT NULL,
 control_papel TEXT NULL,
 datos_fuente_json JSON NULL,
 posible_duplicado TINYINT(1) NOT NULL DEFAULT 0,
 created_by BIGINT UNSIGNED NOT NULL,
 updated_by BIGINT UNSIGNED NULL,
 created_at DATETIME(6) NOT NULL,
 updated_at DATETIME(6) NULL,
 version INT UNSIGNED NOT NULL DEFAULT 1,
 UNIQUE KEY uq_ficha_id_tipo(id,tipo_ficha),
 KEY ix_ficha_presb_tipo(presbiterio_id,tipo_ficha,created_at),
 KEY ix_ficha_sede_fecha(sede_id,created_at),
 KEY ix_ficha_creador_fecha(created_by,created_at),
 KEY ix_ficha_persona(persona_id),
 CONSTRAINT fk_ficha_persona FOREIGN KEY(persona_id) REFERENCES persona(id),
 CONSTRAINT fk_ficha_presb FOREIGN KEY(presbiterio_id) REFERENCES organizacion(id),
 CONSTRAINT fk_ficha_sede FOREIGN KEY(sede_id,presbiterio_id) REFERENCES organizacion(id,padre_id),
 CONSTRAINT fk_ficha_creador FOREIGN KEY(created_by) REFERENCES usuario(id),
 CONSTRAINT fk_ficha_editor FOREIGN KEY(updated_by) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. GRUPO_CRECER: ficha GC y su Persona vinculada identifican al LÍDER.
-- No hay miembros nominales, catálogo de grupos permanente ni líder duplicado.
CREATE TABLE IF NOT EXISTS grupo_crecer (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 ficha_id BIGINT UNSIGNED NOT NULL,
 ficha_tipo ENUM('PERSONA','GC') NOT NULL DEFAULT 'GC',
 grupo_ya_existe TINYINT(1) NULL,
 nombre VARCHAR(220) NULL,
 tipo_gc VARCHAR(60) NULL,
 estado_gc VARCHAR(45) NULL,
 fecha_inicio DATE NULL,
 capacidad INT UNSIGNED NULL,
 cantidad_integrantes_aprox INT UNSIGNED NULL,
 direccion_lugar VARCHAR(500) NULL,
 referencia VARCHAR(350) NULL,
 es_matrimonios TINYINT(1) NULL,
 created_at DATETIME(6) NOT NULL,
 updated_at DATETIME(6) NULL,
 UNIQUE KEY uq_gc_ficha(ficha_id),
 CONSTRAINT fk_gc_ficha_tipo FOREIGN KEY(ficha_id,ficha_tipo) REFERENCES ficha(id,tipo_ficha),
 CONSTRAINT chk_gc_tipo CHECK (ficha_tipo='GC')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. AUDITORIA_EVENTO: responsable real, operación y antes/después.
CREATE TABLE IF NOT EXISTS auditoria_evento (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 request_id VARCHAR(75) NOT NULL,
 sesion_id BIGINT UNSIGNED NULL,
 usuario_id BIGINT UNSIGNED NOT NULL,
 accion VARCHAR(60) NOT NULL,
 recurso_tipo VARCHAR(60) NOT NULL,
 recurso_id BIGINT UNSIGNED NULL,
 ip VARBINARY(16) NULL,
 antes_json JSON NULL,
 despues_json JSON NULL,
 created_at DATETIME(6) NOT NULL,
 KEY ix_auditoria_recurso(recurso_tipo,recurso_id,created_at),
 KEY ix_auditoria_actor(usuario_id,created_at),
 CONSTRAINT fk_audit_sesion FOREIGN KEY(sesion_id) REFERENCES sesion(id),
 CONSTRAINT fk_audit_usuario FOREIGN KEY(usuario_id) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. IDEMPOTENCIA: resultado reutilizable para mismo usuario/clave y payload.
CREATE TABLE IF NOT EXISTS idempotencia (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 usuario_id BIGINT UNSIGNED NOT NULL,
 clave VARCHAR(110) NOT NULL,
 payload_hash CHAR(64) NOT NULL,
 response_status SMALLINT UNSIGNED NULL,
 response_json JSON NULL,
 created_at DATETIME(6) NOT NULL,
 UNIQUE KEY uq_idem_usuario_clave(usuario_id,clave),
 CONSTRAINT fk_idem_usuario FOREIGN KEY(usuario_id) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Validaciones obligatorias en PHP: sede.tipo='SEDE', padre.tipo='PRESBITERIO',
-- usuario.presbiterio_id tipo PRESBITERIO, ubigeo.padre del nivel esperado;
-- documento formado (DNI/CE/OTRO) y control de campos JSON permitidos.
-- Consulta de funcionalidad = totalidad de registros, filtros PRESBITERIO/SEDE.
-- La CONSULTA no habilita escritura: DIGITADOR edita solo fichas propias.
-- Clave común solo digitadores; ADMIN/REPORTES secretos independientes.
-- No integrar SGSP, supervisores, miembros nominales GC ni conciliación del papel.
