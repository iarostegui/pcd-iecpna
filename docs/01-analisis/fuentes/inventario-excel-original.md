# Inventario verificado del Excel de fichas IECPNA
**Fuente revisada físicamente:** `SGSP_Fichas_Imprimibles_A4 (2) v1. 0.xlsx`, 24.408 bytes. SHA-256 **`f3ca0022e9c6f66fc69ee8c56ac8f8a40726dcbc587c79cb0fb564474e639fae`**.
Se identificaron **5 hojas**: `Ficha_Persona` (A1:G47), `Ejemplo_Ficha_Persona` (A1:G48), `Ficha_Lider_GC` (A1:G40), `Ejemplo_Lider_GC` (A1:G40), `Hoja1` vacía. Los datos de las hojas *Ejemplo* son ficticios y no deben provisionarse como datos reales.

## Ficha_Persona — etiquetas y opciones según fila Excel
| Fila | Sección / etiqueta literal resumida | Opciones y notas |
|---|---|---|
| 5 | 1. DATOS PERSONALES; N.° Ficha | La ficha impresa puede no tener numeración; PCD permite vacío. |
| 6 | Documento*; N.° documento* | ☐ DNI ☐ C.E. ☐ Otro: ______. |
| 7 | Nombres* | Texto. |
| 8 | Apellido paterno*; Apellido materno | Apellido materno **no marcado** con *. |
| 9 | Fecha nacimiento*; Sexo* | DD/MM/AAAA; ☐ Femenino ☐ Masculino. |
| 10 | Estado civil* | ☐ Soltero(a) ☐ Casado(a) ☐ Otro: ______. |
| 12–17 | CONTACTO Y DIRECCIÓN | F13 Teléfono/Correo; F14 Facebook usuario; F15 Dirección/referencia*; F16 Departamento*/Provincia*; F17 Distrito*, nota: código INEI se completa al digitalizar. |
| 19–22 | DATOS DE LA IGLESIA | F20 Presbiterio* ☐ Lima ☐ Trujillo ☐ Iquitos; Sede-IECPNA*; F21 Actualmente es* ☐ Invitado ☐ Nuevo Creyente ☐ Miembro; F22 Desde qué fecha*. |
| 24–26 | CÓNYUGE/PAREJA (solo si corresponde) | F25 Documento ☐ DNI ☐ C.E. ☐ Otro y N.°; F26 Nombres y apellidos. |
| 28–36 | RESPONSABILIDAD EN LA IGLESIA (si corresponde) | F29 instrucciones; F30 ☐ Pastor de Sede + Nombre de Sede; F31 ☐ Presbítero + Región; F32 ¿Tiene alguna responsabilidad? ☐ Sí ☐ No; F33 Escriba solo si Sí; F34 Desde qué fecha; F35 Otra responsabilidad; F36 Desde qué fecha. |
| 37–42 | OBSERVACIONES y llenado | F37 OBSERVACIONES (espacio libre); F42 Fecha de llenado; Responsable. |
| 44–46 | USO INTERNO — CONTROL DE DIGITALIZACIÓN | F45 ☐ Ficha completa ☐ Falta información ☐ Persona validada, Digitado/revisado por; F46 Fecha, Observación. |

## Ficha_Lider_GC — etiquetas y opciones según fila Excel
| Fila | Sección / etiqueta literal resumida | Opciones y notas |
|---|---|---|
| 5 | 1. DATOS DEL LÍDER; N.° Ficha | Numeración opcional. |
| 6–8 | Documento*; N.° documento*; Nombres y apellidos* | ☐ DNI ☐ C.E. ☐ Otro; F8 exige que el líder figure previamente en la Ficha Persona. |
| 10–15 | DATOS DEL GRUPO CRECER | F11 ¿El grupo ya existe?* ☐ Sí ☐ No; Actualmente está* ☐ Activo ☐ En baja ☐ Dado de baja; F12 Nombre del grupo*; F13 Tipo* ☐ Varones ☐ Mujeres ☐ Matrimonios ☐ Mixto; F14 Presbiterio*/Sede*; F15 Fecha de inicio* y Capacidad aproximada*. |
| 17–20 | LUGAR HABITUAL DE REUNIÓN | F18 Dirección/lugar; F20 Referencia. |
| 22–24 | INTEGRANTES | F23 **solo resumen; lista nominal del grupo se entrega en otro formato**; F24 Cantidad aproximada; ¿Es grupo de matrimonios? ☐ Sí ☐ No. |
| 26–33 | OBSERVACIONES y llenado | F26 Observaciones (espacio); F32 Fecha de llenado/Responsable; F33 Firma del líder (opcional en **papel**, NO digitalizada en PCD). |
| 35–38 | USO INTERNO — CONTROL DE DIGITALIZACIÓN | F36 ☐ Ficha completa ☐ Falta información ☐ Persona validada, Digitado/revisado por; F37 Fecha/Observación; F38 el código GC **se asigna al digitalizar**. |

## Distinciones esenciales para PCD
1. Los `*` son marcas de completitud del **formulario físico**, **no hacen obligatoria la captura digital** (regla de guardar incompletos).
2. Fecha y responsable **del papel** son independientes de fecha de guardado y digitador autenticado.
3. “Persona validada” en **control interno del papel** no equivale a revisión automática ni aprobación de supervisor PCD.
4. Ficha de GC exige **Persona digital previamente guardada**, pero el número y firma de ficha GC son independientes.
5. “¿El grupo ya existe?” indica un hecho declarado en la ficha **no identidad demostrada** del registro PCD.
6. Ubigeo INEI se completa al digitalizar (no necesariamente aparece escrito en el papel).
7. Los ejemplos están claramente etiquetados como **ejemplos**; no usarlos para poblar la BD productiva.
8. El nombre SGSP en el encabezado/pie del Excel forma parte de la **fuente original**, sin implicar que los softwares sean iguales.

## Brechas detectadas frente al anterior diccionario PCD
- El formulario físico separa **tipo “Otro” de estado civil** y **tipo “Otro” de documento**; registrar literalmente sus descripciones (anterior diccionario no especifica ambos controles).
- “¿Tiene alguna responsabilidad? Sí/No” es un dato explícito F32 y **no debe inferirse solo de texto de responsabilidad**.
- En responsabilidades hay dos campos independientes de descripción y fecha (F33/34 y F35/36).
- Control interno del papel tiene varias marcas y fechas; no equiparar a aprobación de PCD.
- El presbiterio impreso Persona ofrece solo Lima, Trujillo, Iquitos; en PCD el catálogo de acceso puede incluir Chimbote: se conserva el texto original y se usa la selección operacional del usuario sin inventar una marca en papel.
- El dato GC “Firma del líder” **no se captura**; registrar ausencia de digitalización, no cargar imágenes ni OCR.
- Los ejemplos de hojas contienen datos ficticios cuya presencia no implica campos nuevos.

**Estado de H-14:** contraste directo del contenido Excel **realizado** y documentado. **Falta cargar el archivo XLSX binario al repositorio**; sin ello no declarar H-14 totalmente cerrado para la IA.
