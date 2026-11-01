#!/usr/bin/env node
'use strict';
// Comprobación ESTÁTICA, sin dependencias externas. No ejecuta SQL ni parsea YAML formalmente.
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const read = rel => fs.readFileSync(path.join(root, rel), 'utf8');
const sql = read('database/migrations/001_initial.sql');
const api = read('docs/04-integracion/openapi.yaml');
const dict = read('docs/03-base-datos/anexos/anexo-02-diccionario-fisico.md');
const er = read('docs/03-base-datos/anexos/anexo-01-diagrama-fisico.md');
const rules = read('docs/01-analisis/decisiones-pendientes.md');
const errors = [], checks = [];
function ensure(ok, msg) { checks.push({ok:!!ok,msg}); if(!ok) errors.push(msg); }
function unique(xs) { return new Set(xs).size === xs.length; }
const names = [...sql.matchAll(/^CREATE TABLE IF NOT EXISTS (\w+)\s*\(/gm)].map(m => m[1]);
const wanted = ['organizacion','usuario','sesion','login_evento','catalogo','ubigeo','persona','ficha','grupo_crecer','auditoria_evento','idempotencia'];
ensure(names.length === 11 && unique(names) && wanted.every(n => names.includes(n)), 'SQL debe incluir 11 tablas vigentes sin duplicados.');
for (const old of ['usuario_presbiterio', 'alcance_consulta', 'tipo_documento_detalle', 'conyuge_pendiente', 'campo_critico (', 'geo_sesion (']) {
 ensure(!names.includes(old) && !/^\s*(alcance_consulta|tipo_documento_detalle)\s+(ENUM|VARCHAR|CHAR)/m.test(sql), 'SQL retiene estructura V3 eliminada: ' + old);
}
ensure(/CREATE TABLE IF NOT EXISTS organizacion/.test(sql) && /padre_id/.test(sql), 'Falta organización jerárquica.');
ensure(/CREATE TABLE IF NOT EXISTS ubigeo/.test(sql) && /padre_codigo/.test(sql), 'Falta UBIGEO jerárquico.');
ensure(/CREATE TABLE IF NOT EXISTS ficha/.test(sql) && /datos_fuente_json\s+JSON/.test(sql), 'Falta origen JSON de ficha.');
ensure(/CREATE TABLE IF NOT EXISTS sesion/.test(sql) && /geo_estado/.test(sql), 'Falta GPS único en sesión.');
ensure(/CREATE TABLE IF NOT EXISTS catalogo/.test(sql) && /config_json/.test(sql), 'Falta tabla de tablas.');
ensure(/clave_identidad/.test(sql) && !/\bsubtipo_documento\b/.test(sql), 'Clave documental con dos campos.');
ensure(/consulta general|Consulta general|Consulta total|consulta total/.test(rules), 'La norma de consulta total debe constar en decisiones.');
const dictionaryTables = [...dict.matchAll(/^## (\w+) —/gm)].map(m=>m[1]);
const dictionaryColumns = (dict.match(/^\| `[^|]+` \|/gm)||[]).length;
ensure(dictionaryTables.length===11 && unique(dictionaryTables) && wanted.every(n=>dictionaryTables.includes(n)), 'Diccionario no cubre 11 tablas.');
ensure(dictionaryColumns===134, 'Diccionario físico no declara exactamente 134 columnas: '+dictionaryColumns);
ensure(er.includes('11 tablas') && er.includes('134 columnas'), 'ER no corresponde al esquema de 11 tablas.');
const routes = [...api.matchAll(/^  (\/[^:\n]+):$/gm)].map(m=>m[1]);
const schemas = [...api.matchAll(/^    (\w+):$/gm)].map(m=>m[1]);
const refs = [...api.matchAll(/\$ref: '#\/components\/schemas\/(\w+)'/g)].map(m=>m[1]);
ensure(unique(routes), 'Rutas OpenAPI duplicadas.');
ensure(refs.every(r=>schemas.includes(r)), 'OpenAPI contiene referencias schema no declaradas.');
ensure(api.includes('version: 4.0.0') && api.includes('openapi: 3.0.3'), 'OpenAPI debe estar en versión V4.');
for(const obsolete of ['alcance_consulta','tipo_documento_detalle','usuario_presbiterio']) ensure(!api.includes(obsolete), 'OpenAPI todavía usa campo/tabla obsoleto: '+obsolete);
ensure(fs.existsSync(path.join(root,'prototypes/PCD_v2.3.html')), 'Prototipo V2.3 no está versionado.');
ensure(fs.existsSync(path.join(root,'docs/03-base-datos/anexos/justificacion-11-tablas.md')), 'Falta justificación física 11 tablas.');
for(const check of checks) console.log((check.ok?'OK':'FAIL')+' - '+check.msg);
console.log('I00 ESTÁTICO: '+checks.filter(x=>x.ok).length+'/'+checks.length+' controles. Tablas='+names.length+', columnas diccionario='+dictionaryColumns+', rutas OpenAPI='+routes.length);
if(errors.length) { console.error('Errores: '+errors.join('; ')); process.exitCode=1; }
else console.log('I00 estáticamente consistente. PENDIENTES: parser YAML real, SQL MySQL/MariaDB y hosting.');
