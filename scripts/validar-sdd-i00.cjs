#!/usr/bin/env node
'use strict';
/* PCD I00: validator de contrato SDD V4.1.
   No ejecuta SQL: verifica DDL sintáctico, columnas del diccionario, rutas/refs YAML y estructuras JSON.
   Requiere npm install; este PASS no sustituye pruebas reales MySQL/PHP. */
const fs=require('node:fs'),path=require('node:path'),yaml=require('js-yaml');
const root=path.resolve(__dirname,'..'), read=f=>fs.readFileSync(path.join(root,f),'utf8');
const errors=[], report=(ok,msg)=>{console.log((ok?'OK':'FAIL')+' '+msg);if(!ok)errors.push(msg);};
const sql=read('database/migrations/001_initial.sql');
const md=read('docs/03-base-datos/anexos/anexo-02-diccionario-fisico.md');
const api=yaml.load(read('docs/04-integracion/openapi.yaml'));
const seed=read('database/seeds/002_catalogos_calidad.sql');
function splitSql(s) {
  let arr=[],current='',depth=0,quoted=false;
  for(const c of s) {
    if(c==="'")quoted=!quoted;
    if(!quoted&&c==='(')depth++;
    if(!quoted&&c===')')depth--;
    if(c===','&&!quoted&&depth===0){if(current.trim())arr.push(current.trim());current='';}
    else current+=c;
  }
  if(current.trim())arr.push(current.trim());
  return arr;
}
const tables=new Map();
for(const match of sql.matchAll(/CREATE TABLE IF NOT EXISTS (\w+) \(\n([\s\S]*?)\n\) ENGINE=/g)){
 const name=match[1],lines=match[2].split('\n').filter(x=>!x.trim().startsWith('--')).join('\n');
 const fields=new Map();let fks=0,idx=0,checks=0;
 for(const raw of splitSql(lines)){
  const item=raw.replace(/\s+/g,' ').trim();
  let col=item.match(/^(\w+) (BIGINT UNSIGNED|INT UNSIGNED|SMALLINT UNSIGNED|ENUM\([^)]*\)|VARCHAR\(\d+\)|CHAR\(\d+\)|DECIMAL\([^)]+\)|DATETIME\(\d+\)|VARBINARY\(\d+\)|TINYINT\(\d+\)|DATE|JSON|TEXT|INT)(?=\s|$)/i);
  if(col){fields.set(col[1],{type:col[2],nullable:!/\bNOT NULL\b|\bPRIMARY KEY\b/.test(item.slice(col[0].length))});continue;}
  if(/^CONSTRAINT \w+ FOREIGN KEY\b/.test(item)){fks++;continue;}
  if(/^(PRIMARY KEY|UNIQUE KEY|KEY)\b/.test(item)){idx++;continue;}
  if(/^CONSTRAINT \w+ CHECK\b/.test(item)){checks++;continue;}
  throw Error('SQL no reconocible '+name+': '+item.slice(0,140));
 }
 tables.set(name,{fields,fks,idx,checks});
}
const desired=['organizacion','usuario','sesion','login_evento','catalogo','ubigeo','persona','ficha','grupo_crecer','auditoria_evento','idempotencia'];
report(tables.size===11&&desired.every(x=>tables.has(x)),'DDL: 11 tablas vigentes, sin tablas eliminadas');
const ncol=[...tables.values()].reduce((a,t)=>a+t.fields.size,0);
const nfk=[...tables.values()].reduce((a,t)=>a+t.fks,0);
report(ncol===146&&nfk===23,'DDL: 146 columnas y 23 FK');
for(const [t,data] of tables)report(data.fields.has('created_at')&&data.fields.has('updated_at'),t+' timestamps completos');
const headings=[...md.matchAll(/^## (\w+) —/gm)].map(x=>x[1]);
report(headings.length===tables.size&&headings.every(x=>tables.has(x)),'Diccionario: mismas 11 tablas');
let dictTable='',dictRows=new Map(),dictCols=0;for(const line of md.split('\n')){
 let title=line.match(/^## (\w+) —/);if(title){dictTable=title[1];dictRows.set(dictTable,new Map());continue;}
 let field=line.match(/^\| \`(\w+)\` \| \`([^|]+)\` \| (No|Sí) \|/);if(!field)continue;
 dictRows.get(dictTable)?.set(field[1],{type:field[2],nullable:field[3]==='Sí'});dictCols++;
}
report(dictCols===ncol,'Diccionario: '+ncol+' filas físicas');
for(const [table,data] of tables){
 const target=dictRows.get(table)||new Map();
 const ok=target.size===data.fields.size&&[...data.fields].every(([name,col])=>{const got=target.get(name);return got&&got.type===col.type&&got.nullable===col.nullable;});
 report(ok,'DDL vs diccionario: '+table+' nombres, tipos, nulabilidad');
}
report(api.openapi==='3.0.3'&&api.info.version==='4.1.0','OpenAPI versión definida');
report(api.paths&&typeof api.paths==='object'&&Object.keys(api.paths).length>=24,'API endpoints presentes');
report(api.paths['/presbiterios']&&Object.keys(api.paths['/presbiterios']).every(x=>['get','parameters','summary','description','servers'].includes(x)),'Presbiterios solo lectura');
const schemas=api.components.schemas||{},refs=[];
function walk(x){if(!x||typeof x!=='object')return;if(x.$ref)refs.push(x.$ref);for(const [k,v] of Object.entries(x))if(k!=='$ref')walk(v);}
walk(api);
function resolve(ref){if(!ref.startsWith('#/'))return true;const keys=ref.slice(2).split('/').map(x=>x.replace(/~1/g,'/').replace(/~0/g,'~'));let v=api;for(const k of keys){if(v===null||v===undefined||!Object.prototype.hasOwnProperty.call(v,k))return false;v=v[k];}return true;}
report(refs.every(resolve),'OpenAPI: todas las referencias locales apuntan al componente exacto');
report(Boolean(schemas.FichaPersonaFuente&&schemas.FichaGCFuente),'OpenAPI: DTO fuente cerrados');
for(const type of ['persona','gc']){
 const js=JSON.parse(read('docs/03-base-datos/esquemas-json/ficha-'+type+'.schema.json'));
 report(js.additionalProperties===false&&js.properties.schema_version.const==='1.0','JSON Schema '+type+' cerrado/versionado');
 const spec=schemas[type==='persona'?'FichaPersonaFuente':'FichaGCFuente'];
 report(Object.keys(js.properties).sort().join(',')===Object.keys(spec.properties).sort().join(','),'JSON vs OpenAPI '+type+' propiedades');
}
for(const code of ['LIM','TRU','CHI','IQU'])report(seed.includes("'"+code+"'"),'Seed presbiterio '+code);
report(/uq_org_presb_nombre\(nombre_raiz_norm\)/.test(sql),'UNIQUE presbiterio raíz');
report(/ix_idem_created_at\(created_at\)/.test(sql),'Índice idempotencia para limpieza');
report(!Object.keys(api.paths).includes('/admin/presbiterios'),'No se añadieron endpoints de alta presbiterio');
console.log('Validación estática I00 V4.1: '+(errors.length?'FALLÓ':'OK')+'. MySQL/Hosting/E2E siguen PENDIENTES.');
if(errors.length)process.exitCode=1;
