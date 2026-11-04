# I02 — Utilitario de importación de ubigeos

`importar-ubigeo.php` **no contiene** ni descarga un padrón INEI. Prepara una importación controlada de un archivo CSV derivado de una descarga **oficial verificada**.

## Operación segura

1. Obtener la exportación completa desde el [INEI](https://www.gob.pe/15028-consultar-codigos-estandarizados-del-inei); documentar versión y corte.
2. Preparar CSV UTF-8 con columnas exactas `codigo,tipo,padre_codigo,nombre`; preservar los ceros iniciales. Los nodos departamento/provincia/distrito usan códigos de 2/4/6 dígitos, respectivamente.
3. Verificar su hash SHA256 con un procedimiento independiente; validar la procedencia real. El parámetro `--fuente-url` registra la URL pero **no autentica por sí solo** el archivo.
4. Ejecutar `php scripts/i02/importar-ubigeo.php --file=... --sha256=... --fuente-url=... --corte=YYYY-MM-DD` en modo de solo validación, sin importar.
5. Solo tras revisar estructura, fuente, corte y autorización en entorno aislado, añadir `--apply`. La ejecución productiva está bloqueada por el script.

El proceso usa transacción; si algún registro ya existe y difiere, se cancela y **no sobrescribe**. No modificar el modelo SQL de 11 tablas ni completar jerarquías con datos inventados.

## Estado

**Se recibió un archivo de referencia del responsable funcional y se generó un CSV normalizado con SHA256; el corte y la correspondencia con el archivo oficial del INEI aún requieren corroboración.** Ver [ficha documental del ubigeo](../../docs/03-base-datos/fuentes/UBIGEO-RPT-2026-11-04.md). Archivos conservados en la Biblioteca del proyecto, no en el repositorio. GitHub Actions crea una muestra completamente sintética en `/tmp` para probar el modo dry-run; no se versionan CSV ni se importa la muestra.
