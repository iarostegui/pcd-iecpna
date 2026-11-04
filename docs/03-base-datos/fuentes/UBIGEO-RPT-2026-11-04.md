# PCD IECPNA — Fuente de datos de ubigeos para implementación I02

**Estado de la fuente:** `RECIBIDA DEL RESPONSABLE FUNCIONAL / VALIDADA ESTRUCTURALMENTE / CORTE OFICIAL INEI NO ACREDITADO`.

La referencia de digitación geográfica de I02 es el archivo entregado `rptUbigeo.xlsx`. Se conserva **íntegro** en la Biblioteca de archivos del proyecto, sin modificar la copia original. También se generaron una vista normalizada y un SQL de carga para **entornos de prueba**, mantenidos junto al original.

## 1. Custodia documental (archivos persistentes)
Ruta Biblioteca de ChatGPT: `/IAC - Sistema de Iglesias/PCD IECPNA/Ubigeos/`

| Archivo | Función |
|---|---|
| `rptUbigeo.xlsx` | **Fuente recibida**: libro original, hoja `Ubigeo_Separado`, columnas de departamento/provincia/distrito. |
| `PCD_IECPNA_ubigeo_normalizado_fuente_usuario.csv` | Derivado UTF-8 de 4 columnas (`codigo,tipo,padre_codigo,nombre`) compatible con el importador I02. |
| `PCD_IECPNA_ubigeo_fuente_usuario.sql` | Derivado SQL orientado a base **vacía de pruebas**, con niveles padre antes que hijos; no ejecutar en producción sin GO. |
| `PCD_IECPNA_ubigeo_manifest.json` | Integridad: recuentos, SHA256 y tratamiento de la estructura de entrada. |

Se prefirió conservar los archivos en la Biblioteca del proyecto en lugar de incorporar el Excel o CSV al repositorio GitHub. Los controles de I00 `S-07` impiden versionar arbitrariamente archivos de datos: no relajar controles de privacidad por conveniencia. Esta ficha de procedencia SÍ forma parte de la documentación versionada.

## 2. Recuento y correspondencia con el modelo

| Nivel | Número de filas | Código derivado | Padre |
|---|---:|---|---|
| Departamento | **25** | dos dígitos: `DD` | `NULL` |
| Provincia | **196** | cuatro dígitos: `DDPP` | `DD` |
| Distrito | **1893** | seis dígitos: `DDPPDD` | `DDPP` |
| **Total** | **2114** | `VARCHAR(6)` preservando ceros | FK jerárquica |

- Hoja de origen `Ubigeo_Separado`: **2114 filas de datos**, seis columnas.
- El valor textual `8` en los campos subordinados de filas agrupadoras significa **nivel no aplicable**; no es un ubigeo y no debe convertirse en código.
- Se conservaron tildes y denominaciones originales, sin sustituirlas por nombres externos.
- Verificación automática: **0 códigos duplicados, 0 longitudes malformadas, 0 padres ausentes, 0 contradicciones de nombres**.
- La salida coincide estructuralmente con la tabla vigente `ubigeo(codigo,tipo,padre_codigo,nombre)`. No se amplía el diseño físico 11/146/23.

## 3. Huellas SHA256

```text
rptUbigeo.xlsx
3aced41b041d4c03fea57d1437adb0579467aa8fbc19dcaf02217ef6b9b67a36

PCD_IECPNA_ubigeo_normalizado_fuente_usuario.csv
8a3abc99898d2927d926536e4f31120b324e08e571111414846c6d2508cb650b

PCD_IECPNA_ubigeo_fuente_usuario.sql
fae428a20118be3840473f8e0b3d442e427ce44a7fe32400380a6d51c7dfdbdb
```

## 4. Procedencia y discrepancia de corte

**El archivo adjunto no contiene, de forma comprobada, una fecha de corte o versión INEI oficial.** Se trata de un insumo proporcionado por el responsable del sistema. No debe anunciarse como copia oficial INEI sin contrastarlo con el servicio de códigos estandarizados.

Referencias externas para corroboración:
- INEI (canal primario): https://www.gob.pe/15028-consultar-codigos-estandarizados-del-inei
- INEI/datos abiertos (recurso anterior): https://www.datosabiertos.gob.pe/dataset/ubigeos-c%C3%B3digos-de-ubicaci%C3%B3n-geogr%C3%A1fica-instituto-nacional-de-estad%C3%ADstica-e-inform%C3%A1tica-inei
- INEI, proyecciones al 30 de junio de 2026 (publicación 31/12/2025): https://www.gob.pe/institucion/inei/informes-publicaciones/6894980-peru-

**Advertencia de conciliación:** la publicación INEI de proyecciones para 2018–2026 menciona **1892 distritos**, mientras el archivo recibido contiene **1893**. La diferencia de una unidad requiere comparar **códigos concretos y versiones**, no eliminar arbitrariamente una fila del Excel. El nombre del distrito adicional y su vigencia deben documentarse antes de acreditar oficialmente el catálogo.

## 5. Instrucción de implementación (I02)

1. Tratar este libro como **fuente de referencia operativa recibida**. Mantener intacto el archivo original, la normalización y el manifiesto.
2. El CSV normalizado es compatible con `scripts/i02/importar-ubigeo.php`; el proceso valida jerarquías y SHA256, y por defecto ejecuta `dry-run`.
3. Para importar al MySQL de **pruebas**, verificar compatibilidad del corte y origen, ejecutar dry-run documentado, comprobar 25/196/1893 y revisar anomalías. El SQL derivado **no** es un parche seguro sobre una tabla parcialmente poblada.
4. No cargar en producción, no publicar datos reales de Personas, no declarar I02 aprobado por la sola existencia de archivos. Mantener PR #5 en Draft hasta evidencia del catálogo importado en MySQL de prueba y validación de C.E.
5. Al cerrar I02, incorporar evidencia de versión, conteos, consulta jerárquica por API y pruebas de regresión.

**Decisión vigente:** el dataset entregado se incorpora al repositorio documental **por referencia persistente** y debe utilizarse para continuar I02. Su oficialidad/versión queda expresamente diferenciada de la validación estructural.
