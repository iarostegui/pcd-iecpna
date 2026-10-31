# PCD — Diccionario de captura / origen papel
**Fuente:** Excel original `SGSP_Fichas_Imprimibles_A4 (2) v1. 0.xlsx`, hojas `Ficha_Persona`, `Ejemplo_Ficha_Persona`, `Ficha_Lider_GC`, `Ejemplo_Lider_GC`. Registrar incluso campos vacíos; "crítico" es configurable **para informes** y no bloquea guardado.

## Persona — mapeo físico y semántico
| ID | Campo papel | Campo propuesto PCD | Regla |
|---|---|---|---|
| P01 | N.º ficha impresa | ficha_captura.numero_ficha | Texto opcional, conserva ceros; no único |
| P02 | Tipo documento DNI/C.E./Otro | persona_captura.tipo_documento | Catálogo / Otro detallable |
| P03 | N.º documento | persona_captura.numero_documento | Texto opcional en carga, si completo normalizar |
| P04 | Nombres | persona_captura.nombres | Texto |
| P05 | Apellido paterno | persona_captura.apellido_paterno | Texto |
| P06 | Apellido materno | persona_captura.apellido_materno | Texto |
| P07 | Fecha nacimiento | persona_captura.fecha_nacimiento | DD/MM/AAAA entrada, DATE persistido |
| P08 | Sexo | persona_captura.sexo_codigo | Femenino/Masculino (papel) |
| P09 | Estado civil | persona_captura.estado_civil_codigo | Soltero/Casado/Otro |
| P10 | Teléfono | persona_captura.telefono | Texto (no entero) |
| P11 | Correo | persona_captura.correo | Validar formato si presente |
| P12 | Facebook | persona_captura.facebook_usuario | Texto |
| P13 | Dirección / referencia | persona_captura.direccion | Texto |
| P14–P16 | Departamento / Provincia / Distrito | persona_captura.ubigeo_* | Listas dependientes, catálogo oficial a importar |
| P17 | Presbiterio | ficha_captura.presbiterio_id | Heredado login, no editable |
| P18 | Sede IECPNA | ficha_captura.sede_id | Selector contexto, FK consistente |
| P19 | Condición actual | persona_captura.condicion_codigo | Invitado/Nuevo Creyente/Miembro |
| P20 | Fecha desde condición | persona_captura.fecha_condicion | Fecha |
| P21–P23 | Cónyuge/pareja: tipo/n.º/nombre | conyuge_pendiente.* | No crear Persona ficticia |
| P24–P25 | Pastor de sede / nombre de sede | persona_responsabilidad_captura.* | Rol de negocio, sede catálogo |
| P26–P27 | Presbítero / región | persona_responsabilidad_captura.* | Región NO implica presbiterio automáticamente |
| P28–P32 | Otras responsabilidades + fechas | persona_responsabilidad_captura.* | Múltiples responsabilidades |
| P33 | Observaciones | ficha_captura.observaciones | Texto |
| P34 | Fecha de llenado (papel) | ficha_captura.fecha_papel | Distinta de guardado digital |
| P35 | Responsable llenado (papel) | ficha_captura.responsable_papel | Distinto de usuario autenticado |
| P36–P39 | Control uso interno en papel | ficha_captura.control_papel | Transcribir sin convertir automáticamente a estado validado |

**Dato UI no impreso:** `es_lider_gc_intencion`: seleccionar si luego del guardado se abre GC; no da acceso técnico.

## GC — mapeo físico y semántico
| ID | Campo papel | Campo propuesto | Regla |
|---|---|---|---|
| G01 | N.º ficha GC | ficha_captura.numero_ficha | Independiente y opcional, no único |
| G02–G03 | Documento/nombre líder | grupo_crecer_captura.lider_persona_id | Se obtiene de Persona guardada; no duplicar líder |
| G04 | ¿Grupo existe? | grupo_crecer_captura.grupo_ya_existe | Sí/No; buscar asociación si Sí |
| G05 | Estado actual | grupo_crecer_captura.estado_gc | Activo/En baja/Dado de baja |
| G06 | Nombre grupo | grupo_crecer_captura.nombre | Texto |
| G07 | Tipo grupo | grupo_crecer_captura.tipo_gc | Varones/Mujeres/Matrimonios/Mixto |
| G08–G09 | Presbiterio/Sede | ficha_captura.presbiterio_id / sede_id | Heredados de sesión |
| G10 | Fecha inicio | grupo_crecer_captura.fecha_inicio | Fecha |
| G11 | Capacidad aprox. | grupo_crecer_captura.capacidad | Entero separado de asistentes |
| G12–G13 | Lugar/referencia | grupo_crecer_captura.direccion_lugar / referencia | Texto |
| G14 | Cantidad aproximada integrantes | grupo_crecer_captura.cantidad_integrantes_aprox | NO representa lista nominal |
| G15 | ¿Grupo matrimonios? | grupo_crecer_captura.es_matrimonios | Coherente con tipo |
| G16 | Observaciones | ficha_captura.observaciones | Texto |
| G17 | Fecha llenado papel | ficha_captura.fecha_papel | Fecha |
| G18 | Responsable papel | ficha_captura.responsable_papel | Texto |
| G19 | Firma del líder | **no se captura** | El papel se conserva fuera de la aplicación |
| G20–G21 | Uso interno/digitado/revisado/fecha/observación | ficha_captura.control_papel | Conservar evidencia si consta en papel |

## Separaciones semánticas obligatorias
- `ficha_captura.created_by` = digitador original; `usuario` = cuenta técnica; persona y GC son entidades diferentes.
- `fecha_papel` ≠ `created_at`, `responsable_papel` ≠ `created_by`.
- Condición pastoral ≠ estado de captura ≠ estado técnico activo/inactivo.
- Una misma Persona puede tener múltiples fichas de origen; **número impreso no identifica Persona**.
- Reglas críticas de calidad se parametrizan, no deben forzar completitud.

## Auditoría E1 — Contraste real con el Excel
La fuente física se contrastó con [inventario-excel-original.md](fuentes/inventario-excel-original.md): hojas `Ficha_Persona`, `Ejemplo_Ficha_Persona`, `Ficha_Lider_GC`, `Ejemplo_Lider_GC` y `Hoja1` vacía.
- Persona, filas 6 y 10: tipo de documento **Otro** y estado civil **Otro** requieren transcribir el detalle libre.
- Persona, filas 32–36: conservar Sí/No de responsabilidades y dos descripciones con dos fechas; no inferir el Sí/No.
- Persona, fila 20: opciones impresas Lima, Trujillo, Iquitos; el catálogo de trabajo PCD puede contener otros presbiterios ya autorizados, sin falsificar el papel.
- GC, fila 11: «¿El grupo ya existe?» es un dato de la hoja, **no** un ID de grupo PCD.
- GC, fila 33: firma solo en papel, no digitalizar.
- Marcas `*`: calidad del formulario en papel, **nunca** bloqueo a un guardado PCD incompleto.
- Campos y fechas inválidos deben conservar literal en el snapshot de ficha; la representación tipada debe ser válida o NULL.
**H-14:** contraste documental realizado; falta agregar el original XLSX en el repositorio.

## Aclaración de auditoría E1 — validación y revisión manual
- **Fecha real requerida cuando se llena:** un valor como 31/02/1998 no puede aceptarse como fecha ni enviarse al backend. El digitador consulta el origen y corrige o deja ese campo vacío (los registros incompletos sí pueden guardarse). El backend debe revalidar; SQL guarda DATE válido o NULL.
- **Texto:** un nombre ingresado con letras o caracteres admitidos puede estar mal copiado de la hoja (JUANA/Juan; JU4N4). Sin una comparación humana contra el documento físico, **PCD no puede certificar esa fidelidad**.
- La calidad de transcripción es responsabilidad de la **revisión manual externa de cada presbiterio**; la calidad calculada por PCD se limita a hechos verificables como faltantes, integridad y coincidencias documentales.
- GC **no** incluye registro nominal de integrantes ni su pertenencia; solo el líder y datos agregados del grupo. Véase [levantamiento E1](../07-auditorias/etapa-1-resolucion.md).
