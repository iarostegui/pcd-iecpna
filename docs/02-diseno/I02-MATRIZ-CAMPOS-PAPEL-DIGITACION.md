# I02 — Matriz de correspondencia de captura con ficha física H-14

**Fuente aprobada por responsable funcional:** `SGSP_Fichas_Imprimibles_A4 (2).xlsx`, hoja `Ficha_Persona`. El formato se **entregó para toma de datos** y el PCD debe capturarlo fielmente. Ejemplos en `Ejemplo_Ficha_Persona` son ilustrativos, **no datos de producción**. La ficha Líder GC corresponde a I03.

**Regla de obligatoriedad operativa H-24:** presbiterio y sede de la sesión; nombres, ambos apellidos, tipo y número de documento y teléfono. No copiar automáticamente todos los asteriscos del papel a validaciones digitales. Todos los demás campos siguen opcionales para fidelidad a la captura incompleta.

| Ubicación en Ficha_Persona | Dato del papel | Campo PCD de entrada | Almacenamiento | Control I02 |
|---|---|---|---|---|
| G5 | N.º ficha | `numero_ficha` | `ficha.numero_ficha` | Opcional; conserva texto |
| C6 / G6 | Tipo / N.º documento | `tipo_documento`, `numero_documento` | `persona` | Obligatorio; DNI 8 con ceros; CE alfanumérico sin fusión; OTRO sin fusión |
| A7 / A8 / E8 | Nombres / apellidos | `nombres`, `apellido_paterno`, `apellido_materno` | `persona` | Obligatorios H-24 |
| A9 | Fecha nacimiento | `fecha_nacimiento` | `persona` | Fecha real u omitida |
| E9 | Sexo | `sexo_codigo` | `persona` / `catalogo.SEXO` | Selector real F/M |
| A10 | Estado civil | `estado_civil_codigo` | `persona` / `catalogo.ESTADO_CIVIL` | Selector real |
| A13 / E13 | Teléfono / correo | `telefono`, `correo` | `persona` | Teléfono obligatorio; correo opcional y validado |
| A14 | Facebook usuario | `facebook_usuario` | `persona` | Opcional |
| A15 | Dirección / referencia | `direccion` | `persona` | Opcional |
| A16–A17 | Departamento, provincia, distrito | `ubigeo_dpto_codigo`, `ubigeo_prov_codigo`, `ubigeo_dist_codigo` | `persona` + jerarquía `ubigeo` | Códigos de 2/4/6, selecciones dependientes y FK lógica |
| A20 | Presbiterio y sede IECPNA | **Contexto autenticado**, no texto arbitrario | `ficha.presbiterio_id/sede_id` | Ambos obligatorios, sede perteneciente al presbiterio |
| A21 | Invitado / Nuevo Creyente / Miembro | `condicion_codigo` | `persona` / `catalogo.CONDICION` | Selector real |
| A22 | Desde qué fecha | `fecha_condicion` | `persona` | Opcional; fecha válida |
| A25–A26 | Cónyuge: documento / nombres | `conyuge.*` | `ficha.datos_fuente_json` versión 1.0 | Sin crear otra Persona ni tabla |
| A29–A36 | Pastor sede, presbítero, otras responsabilidades / fechas | `responsabilidades.*` | `ficha.datos_fuente_json` versión 1.0 | Flags, descripciones y fechas |
| A37 | Observaciones | `observaciones` | `ficha.observaciones` | Opcional |
| A42 | Fecha llenado / responsable | `fecha_papel`, `responsable_papel` | `ficha` | Distinta de fecha y autor del alta digital |
| A45 | Ficha completa / Falta información / Persona validada | `control_papel` JSON TEXT | `ficha.control_papel` | **Transcripción de marcas físicas**; no aprueba formalmente la Persona. Completa y falta información son excluyentes. |
| A45–A46 | Digitado/revisado por / fecha / observación interna | `created_by`, `created_at`; observación en `observaciones` | `ficha` | Autoría de digitación automática; no se inventa rol de revisor o proceso de validación |

**Campos suplementarios del contrato PCD:** `es_lider_gc_intencion` y otras referencias funcionales existentes se mantienen sin crear tablas ajenas. No son nuevos requisitos obligatorios H-24.

## Datos de ubicación H-UBIGEO-2026-11-04

- Archivo físico fuente geográfica `rptUbigeo.xlsx`, hoja `Ubigeo_Separado`, suministrado por el responsable del proyecto. **No acreditado como corte oficial INEI**.
- CSV normalizado y hash: `8a3abc99898d2927d926536e4f31120b324e08e571111414846c6d2508cb650b`.
- Referencia Git reproducible no personal: `database/referencias/ubigeo-20261104/parte-{01,02,03}.b64` (CSV comprimido xz y codificado base64). La reconstrucción se comprueba por SHA256 antes de importar.
- Registros: **25 departamentos, 196 provincias, 1893 distritos**. Si se confirma posteriormente diferencia con INEI, documentar reconciliación específica sin alterar arbitrariamente códigos. **No usar etiqueta «INEI oficial» en UI hasta acreditar procedencia.**

## Carné de Extranjería (CE): decisión conservadora y verificable

La consulta oficial Migraciones utiliza **número de CE y otros datos** para verificar identidad, no basta con validar longitud. La plataforma Migraciones indica incluso que existen CE cuyo número empieza con `N`; por tanto, no se supone regla exclusiva de nueve dígitos.

- Servicio oficial: https://sel.migraciones.gob.pe/servmig-valreg/VerificarCE
- Orientación 2026: https://www.gob.pe/institucion/migraciones/noticias/1357963-ciudadano-extranjero-conoce-la-vigencia-de-tu-carne-de-extranjeria
- Manual de carga SUNAT T-Registro (regla de esa plataforma, **no comprobación de titularidad de Migraciones**): https://www.sunat.gob.pe/orientacion/pvs/registro/manual/mu-0441-pvs-T-Registro_RM_170_2023_TR.pdf

**Implementación I02:** admitir CE en texto alfanumérico (de 1 a 11 caracteres), preservar ceros iniciales, y emitir aviso. **No generar `clave_identidad` para CE ni fusionar Personas por coincidencia hasta una verificación de identidad confiable.** Se usa el máximo 11 solo como control de entrada operativo conservador, sin presentar la longitud como certificación de Migraciones. Ante divergencia documentada debe revisarse validación; no rechazar por falta de consulta remota una ficha legible dentro del contrato.

## Límites de cierre

- Esta matriz acredita correspondencia **funcional/documental** del formulario Persona, no GO productivo.
- La preservación de contenido en fichas de presbiterios distintos aplica E1-D01: una Persona consolidada existente **no se sobreescribe** al anexar fichas; se preservan los datos fuente de cada ficha según los campos admitidos.
- Sin H-40 institucional, credenciales individuales, revisión de hosting y pruebas I06, **ninguna autorización de datos reales ni producción**.
