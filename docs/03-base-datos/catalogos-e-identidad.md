# CAT-ID-PCD-3.1 — Catálogos y normalización documental
**Fuente:** campos del Excel original IECPNA y listas visibles del prototipo PCD V2.2. **No inventar valores productivos.** El catálogo de sedes/presbiterios se mantiene separado de listas funcionales.

## 1. Dominios iniciales según papel
| Dominio | Códigos propuestos | Etiquetas del papel | Observación |
|---|---|---|---|
| TIPO_DOCUMENTO | DNI / CE / OTRO | DNI / C.E. / Otro | OTRO requiere descripción libre para deduplicar; no inventar tipo |
| SEXO | F / M | Femenino / Masculino | No añadir opciones sin decisión aprobada |
| ESTADO_CIVIL | SOLTERO / CASADO / OTRO | Soltero(a) / Casado(a) / Otro | OTRO puede incluir detalle libre |
| CONDICION | INVITADO / NUEVO_CREYENTE / MIEMBRO | Invitado / Nuevo Creyente / Miembro | Datos pastorales, no rol técnico |
| TIPO_GC | VARONES / MUJERES / MATRIMONIOS / MIXTO | Varones / Mujeres / Matrimonios / Mixto | Solo líder y datos generales |
| ESTADO_GC | ACTIVO / EN_BAJA / DADO_BAJA | Activo / En baja / Dado de baja | No flujo de aprobación |
| RESPONSABILIDAD | PASTOR_SEDE / PRESBITERO / OTRA | Pastor de Sede / Presbítero / Otra responsabilidad | Dato impreso; descripción libre en OTRA |

La tabla física `catalogo_valor` es un **catálogo compacto** con dominio, código, etiqueta, activo, orden; la API ofrece `GET /catalogos` con dominios autorizados. No crear una tabla por lista sin atributos específicos. `presbiterio`, `sede` y `ubigeo` son categorías diferentes y no deben mezclarse con esta tabla.

## 2. Normalización por tipo documental (propuesta técnica E2)

**Reglas generales:** trim, eliminar separadores de presentación (espacios/guiones), letras mayúsculas, **no eliminar ceros a la izquierda**, no inferir dato faltante. La diferencia DNI/C.E./Otro es esencial para identidad.

| Tipo | Criterio para clave de deduplicación | Clave de identidad propuesta |
|---|---|---|
| DNI | Cadena resultante **exactamente 8 dígitos** | `DNI:00123456` |
| C.E. | Normalizar espacios/guiones y mayúsculas; caracteres A-Z/0-9; **longitud oficial pendiente de verificación**. Si no hay criterio suficiente, tratar como posible coincidencia, no deduplicar automáticamente. | `CE:<NORMALIZADO>` cuando sea verificable |
| OTRO | Subtipo descriptivo libre **informado y normalizable** (ej. `PASAPORTE` solo ilustrativo), más número normalizado no vacío. | `OTRO:<SUBTIPO>:<NORMALIZADO>` |
| Tipo ausente o valor inseguro | No construir clave exacta | NULL; permite identidad provisional |

Para `OTRO`, sin subtipo no se construye clave `OTRO:<NUM>` porque diferentes documentos podrían coincidir falsamente. Conservar el dato que el usuario efectivamente introdujo y fue aceptado por el tipo de control; no convertir automáticamente un DNI con 7 dígitos en 8 inventando ceros.

**BD:** `persona_captura.clave_identidad` (NULL cuando no hay documento adecuado) tiene UNIQUE y reemplaza el UNIQUE anterior `(tipo_documento,numero_documento_norm)`. `numero_documento_norm` se conserva para consultas/candidatos de coincidencia pero no es automáticamente identidad universal.

## 3. Ubigeo (pendiente)

El papel indica Departamento/Provincia/Distrito y «código INEI se completa al digitalizar». No tenemos aún el catálogo oficial versionado aquí. **No inventar códigos numéricos, combinaciones ni jerarquías.** Los campos de código podrán ser NULL; texto de ubicación/referencia de la ficha se conserva si está disponible. Cuando se confirme una fuente oficial se documentará su versión/importación o un catálogo específico, y se actualizarán API, UX y pruebas. Esta decisión **no bloquea almacenar una ficha incompleta**.

## 4. Calidad y reportes

Las claves exactas identifican una Persona consolidada cuando están bien formadas y se ha confirmado su tratamiento. Un parecido por nombre/apellidos/fecha es **posible coincidencia**, no fusionar automáticamente. La revisión de transcripción contra papel es manual por presbiterio, fuera de PCD.

## 5. Tests a ejecutar
DNI con ceros `00123456`, DNI con 7 o 9 dígitos, tipo/numero parcial, dos subtipos OTRO con mismo número, mismo OTRO con subtipo normalizado, CE con guión/case y número válido según regla que se apruebe, búsqueda cruzada sin exposición de PII.

## 6. Resolución funcional H-13 para construcción (APROBADA)
La clave de identificación documental es **`tipo + subtipo (si OTRO) + número normalizado`**; no mezclar numeraciones iguales de distinta naturaleza. DNI usa **8 dígitos sin perder ceros**; C.E. se normaliza conservando texto alfanumérico y requiere contrastar reglas documentarias aplicables durante I02, sin inventar longitudes; OTRO necesita subtipo explícito para comparación exacta. Sin documento completo/confiable, `clave_identidad=NULL`, se guarda ficha incompleta y **no se fusiona Persona por nombre**. La validez de formato es diferente de la fidelidad al papel, que se verifica manualmente por presbiterio.

**Ejemplos sin colisión:** `DNI:12345678`, `CE:12345678`, `OTRO:PASAPORTE:12345678` identifican categorías distintas. `DNI:00123456` es distinto de `DNI:12345678`.
**Pruebas I02:** ceros, longitud incorrecta, OTRO sin subtipo, tipos distintos con mismo número, variante de espacios/guiones, documento vacío y ejecución concurrente bajo UNIQUE. No se agrega módulo de conciliación.

## 7. Ubigeo — entrada obligatoria de construcción, NO decisión de alcance
**Estado aprobado:** la fuente oficial INEI será un **insumo de I02**. Antes de cerrar la construcción de formularios y datos de Persona, registrar fuente, versión/corte, jerarquía departamento→provincia→distrito, códigos y pruebas de selección; cargarlo con un script versionado sin inventar códigos. La ausencia actual del archivo no impide I00/I01, pero sí impide declarar terminada la integración de ubigeo en I02. Los campos opcionales y guardado incompleto permanecen vigentes.
