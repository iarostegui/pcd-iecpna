# CAT-ID-PCD-4.0 — Catálogos, identificación documental y ubigeo

**Fuentes:** [inventario de formularios](../01-analisis/fuentes/inventario-excel-original.md) y prototipo PCD V2.2. **No inventar campos ni valores de formularios.**

## Tabla de tablas CATALOGO

`catalogo(categoria, codigo, etiqueta, orden, activo, config_json)` guarda las listas pequeñas en **una sola tabla**. Categorías: TIPO_DOCUMENTO (DNI/C.E./Otro), SEXO (F/M), ESTADO_CIVIL (Soltero/Casado/Otro), CONDICION (Invitado/Nuevo Creyente/Miembro), TIPO_GC (Varones/Mujeres/Matrimonios/Mixto), ESTADO_GC (Activo/En baja/Dado de baja), RESPONSABILIDAD (Pastor Sede/Presbítero/Otra).

Las categorías `CAMPO_CRITICO_PERSONA` y `CAMPO_CRITICO_GC` se usan para reportes de completitud: `config_json={"ruta_dato":"...","es_critico":true,"desactivable":true|false}`. Se auditan cambios ADMIN, y PHP verifica rutas de una lista blanca. No se crean tablas de 5–7 filas.

## H-13 — solo tipo y número (decisión vigente)
Los controles **son exclusivamente `tipo_documento` y `numero_documento`**. No se agrega subtipo al formulario, DTO, tabla Persona ni tabla de catálogos.

| Tipo | Normalización | Coincidencia |
|---|---|---|
| DNI | Ocho dígitos; conservar ceros a izquierda; no completar con ceros inventados. | `DNI:<8 dígitos>` cuando válido, clave única. |
| C.E. | Texto alfanumérico mayúsculo; trim y separadores de presentación; validar regla documental apropiada durante I02. | `CE:<valor normalizado>` solo si confiable. |
| Otro | Conservar el número escrito; no incorporar subtipo identificador. | Posible coincidencia con mismo tipo+número; **no fusionar automáticamente**, `clave_identidad=NULL`. |
| Documento ausente/no confiable | Preservar captura incompleta y texto aceptado. | `clave_identidad=NULL` sin fusionar por nombre. |

La **descripción libre de “Otro”** que aparece en el Excel, si está presente, puede conservarse en `ficha.datos_fuente_json`, **sin crear campo subtipo de identificación ni requisito de deduplicación**. La clave de identidad es un atributo **técnico derivado**, no un control adicional para el digitador.

## UBIGEO (insumo de construcción I02)
`ubigeo(codigo, tipo, padre_codigo, nombre, activo)` es **una** tabla jerárquica para departamento/provincia/distrito. La fuente INEI, fecha/corte, validación de jerarquías y script de importación son entradas **pendientes de construcción I02**, no un motivo para inventar datos en I00/I01 ni un hallazgo de ampliación del alcance.

## ORGANIZACION
`organizacion` mantiene presbiterios y sedes como nodos jerárquicos, **separados** de CATALOGO para preservar IDs, relaciones y altas de sedes durante captura.

## Pruebas
DNI con ceros, 7/8/9 dígitos, CE con separadores, Otro con número común a DNI sin fusión, dos fichas sin documento, colisión documental concurrente; catálogos por categoría y cambios de criticidad; árbol y códigos INEI I02.
