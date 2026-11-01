# Verificación automatizable PCD V2.3

Ejecutar en la raíz del repositorio, sin instalar paquetes:

```bash
node scripts/validar-prototipo-v2.3.cjs
```

La verificación usa exclusivamente Node.js y comprueba **sintaxis JavaScript del HTML**, estructura del login, ausencia de ACL territorial en consulta, preservación de edición por autor, GC sin selector de conciliación, GPS único, fechas inválidas y separación de datos de demostración.

**No es una prueba E2E.** Después deben ejecutarse los escenarios [CA-101…111](../docs/05-pruebas/validacion-prototipo-v2.3.md) en el navegador para roles, navegación móvil, formularios, duplicados, filtros y reporte. **No afirmar PASS sin ejecución**; si falla el script, detener generación de React hasta corregir.

El prototipo [V2.2](../prototypes/PCD_v2.2.html) se preserva, y [V2.3](../prototypes/PCD_v2.3.html) es la versión activa de UI.