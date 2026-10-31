-- PCD E2 — dominios y campos críticos por defecto extraídos del papel y prototipo V2.2.
-- Ejecutar UNA VEZ tras 001_initial.sql en BD de pruebas; sin sedes, usuarios, passwords ni PII.
-- INSERT IGNORE protege la configuración modificada posteriormente por ADMIN.
INSERT IGNORE INTO catalogo_valor (dominio,codigo,etiqueta,orden,activo) VALUES
('TIPO_DOCUMENTO','DNI','DNI',10,1),
('TIPO_DOCUMENTO','CE','C.E.',20,1),
('TIPO_DOCUMENTO','OTRO','Otro',30,1),
('SEXO','F','Femenino',10,1),
('SEXO','M','Masculino',20,1),
('ESTADO_CIVIL','SOLTERO','Soltero(a)',10,1),
('ESTADO_CIVIL','CASADO','Casado(a)',20,1),
('ESTADO_CIVIL','OTRO','Otro',30,1),
('CONDICION','INVITADO','Invitado',10,1),
('CONDICION','NUEVO_CREYENTE','Nuevo Creyente',20,1),
('CONDICION','MIEMBRO','Miembro',30,1),
('TIPO_GC','VARONES','Varones',10,1),
('TIPO_GC','MUJERES','Mujeres',20,1),
('TIPO_GC','MATRIMONIOS','Matrimonios',30,1),
('TIPO_GC','MIXTO','Mixto',40,1),
('ESTADO_GC','ACTIVO','Activo',10,1),
('ESTADO_GC','EN_BAJA','En baja',20,1),
('ESTADO_GC','DADO_BAJA','Dado de baja',30,1),
('RESPONSABILIDAD','PASTOR_SEDE','Pastor de Sede',10,1),
('RESPONSABILIDAD','PRESBITERO','Presbítero',20,1),
('RESPONSABILIDAD','OTRA','Otra responsabilidad',30,1);

-- Rutas de datos explicadas en docs/03-base-datos/campos-criticos.md.
-- No se usan como nombres SQL arbitrarios para construir consultas: whitelist en PHP.
INSERT IGNORE INTO campo_critico(entidad,campo_codigo,etiqueta,ruta_dato,desactivable,es_critico) VALUES
('PERSONA','first','Nombres','persona.nombres',1,1),
('PERSONA','last1','Apellido paterno','persona.apellido_paterno',1,1),
('PERSONA','type','Tipo de documento','persona.tipo_documento',1,1),
('PERSONA','doc','N.º de documento','persona.numero_documento',1,1),
('PERSONA','birth','Fecha de nacimiento','persona.fecha_nacimiento',1,1),
('PERSONA','sex','Sexo','persona.sexo_codigo',1,1),
('PERSONA','pres','Presbiterio','ficha.presbiterio_id',0,1),
('PERSONA','sede','Sede','ficha.sede_id',0,1),
('PERSONA','condition','Condición pastoral','persona.condicion_codigo',1,1),
('GC','personId','Líder registrado','ficha.persona_id',0,1),
('GC','pres','Presbiterio','ficha.presbiterio_id',0,1),
('GC','sede','Sede','ficha.sede_id',0,1),
('GC','type','Tipo GC','grupo.tipo_gc',1,1),
('GC','start','Fecha de inicio','grupo.fecha_inicio',1,1),
('GC','capacity','Capacidad','grupo.capacidad',1,1);
