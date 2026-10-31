-- PCD V3.1 · modelo propio; propuesta inicial MySQL 8/MariaDB reciente, ejecutar primero en BD de prueba.
-- NO incluye datos reales ni contraseñas. Antes de ejecutar: validar versión real cPanel.
SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS presbiterio (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 codigo VARCHAR(40) NOT NULL, nombre VARCHAR(180) NOT NULL,
 activo TINYINT(1) NOT NULL DEFAULT 1,
 UNIQUE KEY uq_presbiterio_codigo(codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 codigo VARCHAR(80) NOT NULL, nombre_display VARCHAR(180) NOT NULL,
 perfil ENUM('DIGITADOR','ADMIN','REPORTES') NOT NULL,
 password_hash VARCHAR(255) NOT NULL, activo TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME(6) NOT NULL, updated_at DATETIME(6) NULL,
 UNIQUE KEY uq_usuario_codigo(codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario_presbiterio (
 usuario_id BIGINT UNSIGNED NOT NULL,
 presbiterio_id BIGINT UNSIGNED NOT NULL,
 PRIMARY KEY(usuario_id,presbiterio_id),
 CONSTRAINT fk_up_usuario FOREIGN KEY(usuario_id) REFERENCES usuario(id),
 CONSTRAINT fk_up_presbiterio FOREIGN KEY(presbiterio_id) REFERENCES presbiterio(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sede (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 presbiterio_id BIGINT UNSIGNED NOT NULL,
 codigo VARCHAR(50) NULL, nombre_oficial VARCHAR(200) NOT NULL,
 nombre_norm VARCHAR(200) NOT NULL, ubicacion_referencia VARCHAR(300) NULL,
 activo TINYINT(1) NOT NULL DEFAULT 1,
 created_by BIGINT UNSIGNED NULL, created_at DATETIME(6) NOT NULL,
 UNIQUE KEY uq_sede_presbiterio_nombre(presbiterio_id,nombre_norm),
 UNIQUE KEY uq_sede_ambito(presbiterio_id,id),
 KEY ix_sede_presbiterio_activo(presbiterio_id,activo),
 CONSTRAINT fk_sede_presb FOREIGN KEY(presbiterio_id) REFERENCES presbiterio(id),
 CONSTRAINT fk_sede_autor FOREIGN KEY(created_by) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS campana (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 codigo VARCHAR(50) NOT NULL, nombre VARCHAR(180) NOT NULL,
 estado VARCHAR(30) NOT NULL DEFAULT 'ACTIVA',
 created_at DATETIME(6) NOT NULL,
 UNIQUE KEY uq_campana_codigo(codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sesion_captura (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 usuario_id BIGINT UNSIGNED NOT NULL, presbiterio_id BIGINT UNSIGNED NOT NULL,
 sede_id BIGINT UNSIGNED NULL, session_ref_hash CHAR(64) NOT NULL,
 ip_login VARBINARY(16) NULL, user_agent VARCHAR(255) NULL,
 fecha_login DATETIME(6) NOT NULL, fecha_logout DATETIME(6) NULL,
 last_seen DATETIME(6) NULL,
 UNIQUE KEY uq_sesion_ref(session_ref_hash),
 KEY ix_sesion_usuario_fecha(usuario_id,fecha_login),
 CONSTRAINT fk_sc_user FOREIGN KEY(usuario_id) REFERENCES usuario(id),
 CONSTRAINT fk_sc_pres FOREIGN KEY(presbiterio_id) REFERENCES presbiterio(id),
 CONSTRAINT fk_sc_sede FOREIGN KEY(presbiterio_id,sede_id) REFERENCES sede(presbiterio_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS geo_sesion (
 sesion_id BIGINT UNSIGNED PRIMARY KEY,
 estado ENUM('OK','DENEGADA','NO_DISPONIBLE','ERROR') NOT NULL,
 latitud DECIMAL(10,7) NULL, longitud DECIMAL(10,7) NULL,
 precision_m DECIMAL(10,2) NULL, capturada_en DATETIME(6) NOT NULL,
 CONSTRAINT fk_geo_session FOREIGN KEY(sesion_id) REFERENCES sesion_captura(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_evento (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 usuario_id BIGINT UNSIGNED NULL, presbiterio_id BIGINT UNSIGNED NULL,
 codigo_intentado VARCHAR(80) NULL,
 resultado ENUM('OK','FALLIDO','BLOQUEADO','LOGOUT') NOT NULL,
 ip VARBINARY(16) NULL, user_agent VARCHAR(255) NULL,
 created_at DATETIME(6) NOT NULL,
 KEY ix_login_fecha(created_at),
 KEY ix_login_codigo_fecha(codigo_intentado,created_at),
 CONSTRAINT fk_le_usuario FOREIGN KEY(usuario_id) REFERENCES usuario(id),
 CONSTRAINT fk_le_pres FOREIGN KEY(presbiterio_id) REFERENCES presbiterio(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS persona_captura (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tipo_documento VARCHAR(24) NULL, numero_documento VARCHAR(60) NULL,
 numero_documento_norm VARCHAR(60) NULL,
 nombres VARCHAR(150) NULL, apellido_paterno VARCHAR(100) NULL,
 apellido_materno VARCHAR(100) NULL, fecha_nacimiento DATE NULL,
 sexo_codigo VARCHAR(24) NULL, estado_civil_codigo VARCHAR(24) NULL,
 telefono VARCHAR(60) NULL, correo VARCHAR(180) NULL,
 facebook_usuario VARCHAR(180) NULL, direccion VARCHAR(500) NULL,
 ubigeo_dpto_codigo VARCHAR(10) NULL, ubigeo_prov_codigo VARCHAR(10) NULL,
 ubigeo_dist_codigo VARCHAR(10) NULL, condicion_codigo VARCHAR(40) NULL,
 fecha_condicion DATE NULL, es_lider_gc_intencion TINYINT(1) NOT NULL DEFAULT 0,
 created_by BIGINT UNSIGNED NOT NULL, updated_by BIGINT UNSIGNED NULL,
 created_at DATETIME(6) NOT NULL, updated_at DATETIME(6) NULL,
 version INT UNSIGNED NOT NULL DEFAULT 1,
 UNIQUE KEY uq_persona_documento(tipo_documento,numero_documento_norm),
 KEY ix_persona_apellidos(apellido_paterno,apellido_materno,nombres),
 KEY ix_persona_nombres(nombres),
 CONSTRAINT fk_pc_creador FOREIGN KEY(created_by) REFERENCES usuario(id),
 CONSTRAINT fk_pc_editor FOREIGN KEY(updated_by) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ficha_captura (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 campana_id BIGINT UNSIGNED NOT NULL,
 persona_id BIGINT UNSIGNED NOT NULL,
 presbiterio_id BIGINT UNSIGNED NOT NULL,
 sede_id BIGINT UNSIGNED NOT NULL,
 tipo_ficha ENUM('PERSONA','GC') NOT NULL,
 numero_ficha VARCHAR(50) NULL,
 fecha_papel DATE NULL, responsable_papel VARCHAR(180) NULL,
 observaciones TEXT NULL, control_papel TEXT NULL,
 datos_fuente_json JSON NULL, -- snapshot original por ficha
 marca_calidad VARCHAR(30) NOT NULL DEFAULT 'INCOMPLETA',
 posible_duplicado TINYINT(1) NOT NULL DEFAULT 0,
 created_by BIGINT UNSIGNED NOT NULL, updated_by BIGINT UNSIGNED NULL,
 created_at DATETIME(6) NOT NULL, updated_at DATETIME(6) NULL,
 version INT UNSIGNED NOT NULL DEFAULT 1,
 KEY ix_ficha_sede_fecha(sede_id,created_at),
 KEY ix_ficha_autor_fecha(created_by,created_at),
 KEY ix_ficha_pres_tipo(presbiterio_id,tipo_ficha,created_at),
 KEY ix_ficha_persona(persona_id),
 CONSTRAINT fk_fc_campana FOREIGN KEY(campana_id) REFERENCES campana(id),
 CONSTRAINT fk_fc_persona FOREIGN KEY(persona_id) REFERENCES persona_captura(id),
 CONSTRAINT fk_fc_sede FOREIGN KEY(presbiterio_id,sede_id) REFERENCES sede(presbiterio_id,id),
 CONSTRAINT fk_fc_autor FOREIGN KEY(created_by) REFERENCES usuario(id),
 CONSTRAINT fk_fc_editor FOREIGN KEY(updated_by) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS conyuge_pendiente (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 persona_id BIGINT UNSIGNED NOT NULL, ficha_id BIGINT UNSIGNED NOT NULL,
 tipo_documento VARCHAR(24) NULL, numero_documento VARCHAR(60) NULL,
 nombre_libre VARCHAR(240) NULL,
 CONSTRAINT fk_con_persona FOREIGN KEY(persona_id) REFERENCES persona_captura(id),
 CONSTRAINT fk_con_ficha FOREIGN KEY(ficha_id) REFERENCES ficha_captura(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS persona_responsabilidad_captura (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 persona_id BIGINT UNSIGNED NOT NULL, ficha_id BIGINT UNSIGNED NOT NULL,
 tipo_codigo VARCHAR(50) NOT NULL, sede_id BIGINT UNSIGNED NULL,
 region_libre VARCHAR(180) NULL,
 descripcion VARCHAR(280) NULL, fecha_desde DATE NULL,
 KEY ix_resp_persona(persona_id),
 CONSTRAINT fk_pr_persona FOREIGN KEY(persona_id) REFERENCES persona_captura(id),
 CONSTRAINT fk_pr_ficha FOREIGN KEY(ficha_id) REFERENCES ficha_captura(id),
 CONSTRAINT fk_pr_sede FOREIGN KEY(sede_id) REFERENCES sede(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS grupo_crecer_captura (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 ficha_id BIGINT UNSIGNED NOT NULL,
 lider_persona_id BIGINT UNSIGNED NOT NULL,
 grupo_ya_existe TINYINT(1) NULL,
 nombre VARCHAR(220) NULL, tipo_gc VARCHAR(60) NULL,
 estado_gc VARCHAR(45) NULL, fecha_inicio DATE NULL,
 capacidad INT UNSIGNED NULL, cantidad_integrantes_aprox INT UNSIGNED NULL,
 direccion_lugar VARCHAR(500) NULL, referencia VARCHAR(350) NULL,
 es_matrimonios TINYINT(1) NULL,
 created_at DATETIME(6) NOT NULL, updated_at DATETIME(6) NULL,
 UNIQUE KEY uq_gc_ficha(ficha_id),
 KEY ix_gc_lider(lider_persona_id),
 CONSTRAINT fk_gc_ficha FOREIGN KEY(ficha_id) REFERENCES ficha_captura(id),
 CONSTRAINT fk_gc_lider FOREIGN KEY(lider_persona_id) REFERENCES persona_captura(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS campo_critico (
 entidad ENUM('PERSONA','GC') NOT NULL,
 campo_codigo VARCHAR(80) NOT NULL, es_critico TINYINT(1) NOT NULL DEFAULT 1,
 updated_by BIGINT UNSIGNED NULL, updated_at DATETIME(6) NULL,
 PRIMARY KEY(entidad,campo_codigo),
 CONSTRAINT fk_cc_user FOREIGN KEY(updated_by) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS auditoria_evento (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 request_id VARCHAR(75) NOT NULL, sesion_id BIGINT UNSIGNED NULL,
 geo_sesion_id BIGINT UNSIGNED NULL, usuario_id BIGINT UNSIGNED NOT NULL,
 accion VARCHAR(60) NOT NULL, recurso_tipo VARCHAR(60) NOT NULL,
 recurso_id BIGINT UNSIGNED NULL, ip VARBINARY(16) NULL,
 antes_json JSON NULL, despues_json JSON NULL,
 created_at DATETIME(6) NOT NULL,
 KEY ix_au_recurso(recurso_tipo,recurso_id,created_at),
 KEY ix_au_usuario_fecha(usuario_id,created_at),
 CONSTRAINT fk_au_session FOREIGN KEY(sesion_id) REFERENCES sesion_captura(id),
 CONSTRAINT fk_au_geo FOREIGN KEY(geo_sesion_id) REFERENCES geo_sesion(sesion_id),
 CONSTRAINT fk_au_user FOREIGN KEY(usuario_id) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS idempotencia (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 usuario_id BIGINT UNSIGNED NOT NULL, clave VARCHAR(110) NOT NULL,
 payload_hash CHAR(64) NOT NULL, response_status SMALLINT UNSIGNED NULL,
 response_json JSON NULL, created_at DATETIME(6) NOT NULL,
 UNIQUE KEY uq_idem_user_clave(usuario_id,clave),
 CONSTRAINT fk_idem_user FOREIGN KEY(usuario_id) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

