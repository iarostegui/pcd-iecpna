#!/usr/bin/env node
'use strict';
// Ejecución: node scripts/validar-prototipo-v2.3.cjs
// Solo inspección estática y sintaxis JS del HTML. No prueba navegación/BD/API.
const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');

const root = path.resolve(__dirname, '..');
const html23 = fs.readFileSync(path.join(root, 'prototypes/PCD_v2.3.html'), 'utf8');
const html22 = fs.readFileSync(path.join(root, 'prototypes/PCD_v2.2.html'), 'utf8');
const problems = [];
function check(label, predicate) {
  try { if (!predicate()) problems.push(label); }
  catch (error) { problems.push(label + ': ' + error.message); }
}
const scripts = [...html23.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/gi)];
check('Debe existir exactamente un script JavaScript en V2.3', () => scripts.length === 1);
if (scripts.length === 1) {
  try { new vm.Script(scripts[0][1], { filename: 'PCD_v2.3.inline.js' }); }
  catch (err) { problems.push('ERROR de sintaxis JS: ' + err.message); }
}
check('El archivo lleva versión V2.3', () => html23.includes('Prototipo v2.3'));
check('Se conserva V2.2 separada', () => html22.includes('Prototipo v2.2'));
check('Login por texto', () => /<input[^>]+id="userSelect"/.test(html23));
check('Login no enumera usuarios', () => !/<select[^>]+id="userSelect"/.test(html23));
check('Password sin precarga', () => /id="loginPass"/.test(html23) && !/id="loginPass"[^>]*\bvalue=/.test(html23));
check('Storage de demo aislado', () => html23.includes("pcd_iecpna_demo_v2_3") && !html23.includes('sgsp_campana_digitacion_proto_v2'));
check('No hay ACL de lectura por presbiterio', () => !html23.includes("currentUser.role!=='ADMIN'&&p.pres!==ctx.pres"));
check('Filtros generales presentes', () => html23.includes("select('filterPres'") && html23.includes("select('filterSede'"));
check('Edición por autor no eliminada', () => html23.includes("item.createdBy===currentUser.id"));
check('No hay selector GC ya digitado', () => !html23.includes('id="existingGroup"'));
check('Respuesta del papel GC conservada', () => html23.includes('¿El grupo ya existe?'));
check('No bloquear nombre GC duplicado', () => !html23.includes("toast('Ese nombre de grupo ya existe en la sede.','error')"));
check('Sin supervisor', () => !/perfil Supervisor/i.test(html23));
check('Sin GPS en detalle común de Persona', () => !html23.includes("['Coordenadas del guardado'"));
check('Una solicitud GPS por sesión', () => html23.includes("if(geoRequested){") && html23.includes('b.disabled=geoRequested||geoPending'));
check('Fecha imposible no se guarda en borrador', () => !html23.includes('else obj[k]=raw'));
check('Roles de demo con claves separadas', () => html23.includes("ADMIN:'admin-demo'") && html23.includes("REPORTE:'reportes-demo'"));
check('No se agregó subtipo documental', () => !html23.includes('tipo_documento_detalle'));
const loginCount = (html23.match(/id="loginForm"/g) || []).length;
check('Solo un login estático', () => loginCount === 1);
if (problems.length) {
  process.stderr.write('PCD V2.3: ' + problems.length + ' observación(es):\n' + problems.map(x => ' - ' + x).join('\n') + '\n');
  process.exitCode = 1;
} else {
  process.stdout.write('PCD V2.3: inspección de sintaxis/estructura OK.\n');
  process.stdout.write('IMPORTANTE: faltan pruebas interactivas navegador, API PHP y BD real.\n');
}
