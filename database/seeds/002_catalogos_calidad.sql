-- PCD 11 tablas; ejecutar después de database/migrations/001_initial.sql.
-- Basado en etiquetas de los formularios IECPNA y prototipo V2.2.
-- No contiene usuarios, sedes, contraseñas, datos de prueba reales ni UBIGEO INEI.
-- INSERT IGNORE evita sobrescribir la configuración modificada por ADMIN.
INSERT IGNORE INTO catalogo (categoria,codigo,etiqueta,orden,activo) VALUES
('TIPO_DOCUMENTO','DNI','DNI',10,1),
('TIPO_DOCUMENTO','CE','C.E.',20,1),
('TIPO_DOCUMENTO','OTRO','Otro',30,1),
('SEXO','F','Femenino',10,1),('SEXO','M','Masculino',20,1),
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
-- En CAMPOS CRITICOS, la categoría identifica el tipo de ficha.
-- «es_critico» puede desactivarse solo cuando «desactivable»: true.
INSERT IGNORE INTO catalogo
(categoria,codigo,etiqueta,orden,activo,config_json) VALUES
('CAMPO_CRITICO_PERSONA','first','Nombres',10,1,'{"ruta_dato":"persona.nombres","es_critico":true,"desactivable":true}'),
('CAMPO_CRITICO_PERSONA','last1','Apellido paterno',20,1,'{"ruta_dato":"persona.apellido_paterno","es_critico":true,"desactivable":true}'),
('CAMPO_CRITICO_PERSONA','type','Tipo documento',30,1,'{"ruta_dato":"persona.tipo_documento","es_critico":true,"desactivable":true}'),
('CAMPO_CRITICO_PERSONA','doc','N.º documento',40,1,'{"ruta_dato":"persona.numero_documento","es_critico":true,"desactivable":true}'),
('CAMPO_CRITICO_PERSONA','birth','Fecha nacimiento',50,1,'{"ruta_dato":"persona.fecha_nacimiento","es_critico":true,"desactivable":true}'),
('CAMPO_CRITICO_PERSONA','sex','Sexo',60,1,'{"ruta_dato":"persona.sexo_codigo","es_critico":true,"desactivable":true}'),
('CAMPO_CRITICO_PERSONA','pres','Presbiterio',70,1,'{"ruta_dato":"ficha.presbiterio_id","es_critico":true,"desactivable":false}'),
('CAMPO_CRITICO_PERSONA','sede','Sede',80,1,'{"ruta_dato":"ficha.sede_id","es_critico":true,"desactivable":false}'),
('CAMPO_CRITICO_PERSONA','condition','Condición pastoral',90,1,'{"ruta_dato":"persona.condicion_codigo","es_critico":true,"desactivable":true}'),
('CAMPO_CRITICO_GC','personId','Líder',10,1,'{"ruta_dato":"ficha.persona_id","es_critico":true,"desactivable":false}'),
('CAMPO_CRITICO_GC','pres','Presbiterio',20,1,'{"ruta_dato":"ficha.presbiterio_id","es_critico":true,"desactivable":false}'),
('CAMPO_CRITICO_GC','sede','Sede',30,1,'{"ruta_dato":"ficha.sede_id","es_critico":true,"desactivable":false}'),
('CAMPO_CRITICO_GC','type','Tipo GC',40,1,'{"ruta_dato":"grupo_crecer.tipo_gc","es_critico":true,"desactivable":true}'),
('CAMPO_CRITICO_GC','start','Fecha inicio',50,1,'{"ruta_dato":"grupo_crecer.fecha_inicio","es_critico":true,"desactivable":true}'),
('CAMPO_CRITICO_GC','capacity','Capacidad',60,1,'{"ruta_dato":"grupo_crecer.capacidad","es_critico":true,"desactivable":true}');
