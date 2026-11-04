# I02 — Utilitario de importación de ubigeos

`importar-ubigeo.php` **no contiene** ni descarga el padrón. Puede importar un CSV de una descarga oficial INEI verificada **o** el archivo proporcionado por el responsable funcional, manteniendo procedencia y estado de oficialidad separados.

## Operación segura

1. Elegir fuente: (A) exportación oficial del [INEI](https://www.gob.pe/15028-consultar-codigos-estandarizados-del-inei) con versión y corte comprobados; o (B) el CSV derivado del libro **entregado para digitación** `rptUbigeo.xlsx` (registro `H-UBIGEO-2026-11-04`). No equiparar ambas procedencias.
2. Preparar CSV UTF-8 con columnas exactas `codigo,tipo,padre_codigo,nombre`; preservar los ceros iniciales. Los nodos departamento/provincia/distrito usan códigos de 2/4/6 dígitos, respectivamente.
3. Verificar su hash SHA256 con un procedimiento independiente; validar la procedencia real. El parámetro `--fuente-url` registra la URL pero **no autentica por sí solo** el archivo.
4. Ejecutar en modo de solo validación (sin escribir):\n   ```bash\n   php scripts/i02/importar-ubigeo.php \\\n     --file=/ruta/PCD_IECPNA_ubigeo_normalizado_fuente_usuario.csv \\\n     --sha256=8a3abc99898d2927d926536e4f31120b324e08e571111414846c6d2508cb650b \\\n     --fuente-tipo=usuario --referencia=H-UBIGEO-2026-11-04\n   ```\n   Para fuente **INEI efectivamente acreditada**, utilizar `--fuente-tipo=inei --fuente-url=https://... --corte=YYYY-MM-DD` con el SHA correspondiente.
5. La fuente usuario tiene **25 departamentos, 196 provincias y 1893 distritos**; la documentación cita 1892 del INEI 2026. **No alterar ni eliminar** la fila discrepante sin conciliación. Para una prueba controlada en MySQL aislado, usar el comando anterior con `--apply` **solo con autorización técnica y BD de prueba previamente aprovisionada**. La ejecución productiva está bloqueada por el script.

El proceso usa transacción; si algún registro ya existe y difiere, se cancela y **no sobrescribe**. No modificar el modelo SQL de 11 tablas ni completar jerarquías con datos inventados.

## Estado

**Se recibió un archivo de referencia del responsable funcional y se generó un CSV normalizado con SHA256; el importador ya admite expresamente esta fuente con identificador fijo y comprobación estricta de SHA. El **corte oficial INEI no está acreditado**, por lo que el importador no lo presenta como fuente oficial.** Ver [ficha documental del ubigeo](../../docs/03-base-datos/fuentes/UBIGEO-RPT-2026-11-04.md). Archivos conservados en la Biblioteca del proyecto, no en el repositorio. GitHub Actions crea una muestra completamente sintética en `/tmp` para probar el modo dry-run; no se versionan CSV ni se importa la muestra.
