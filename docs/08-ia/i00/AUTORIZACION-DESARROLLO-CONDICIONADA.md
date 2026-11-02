# Autorización condicionada para continuar desarrollo — PCD IECPNA

**Fecha:** 2026-11-02  
**Ámbito:** continuación técnica de I01 con **datos exclusivamente sintéticos**.  
**Origen de autorización:** instrucción expresa del responsable funcional en conversación del proyecto («Procede», 02/11/2026), tras la revisión de I00. Este registro **no equivale a autorización institucional de producción**.

## Decisión

**GO DE DESARROLLO CONDICIONADO** para preparar y ejecutar I01 (autenticación, sesiones, contexto de presbiterio/sede y pruebas automáticas) en entornos aislados de desarrollo/CI, con datos ficticios. Se sustenta en los controles MySQL 8, PHP, bootstrap, PHPUnit, HTTP y frontend ya ejecutados en CI, ver [matriz I00](MATRIZ-I00.md).

**NO-GO** permanece para: cierre formal de I00, despliegue productivo, carga de fichas auténticas, uso de datos personales reales o religiosos, uso de GPS real en pruebas, y puesta a disposición de usuarios finales. No se altera la evaluación del especialista ni se simula su aprobación.

## Alcance permitido en I01

- Ejecutar el ciclo SDD obligatorio: `proposal.md` → `design.md` → `spec.md` (Given/When/Then) → `tasks.md` → código → pruebas y evidencias → revisión.
- Implementar únicamente el alcance aprobado de I01 en [plan](../plan-implementacion.md) y [contrato V4.1](../CONTRATO-EJECUCION-V4.1.md).
- Usar MySQL 8 de prueba, semillas sintéticas y credenciales UAT protegidas; no alterar SQL físico aprobado.
- Mantener PR independiente; no hacer merge automático ni comenzar I02.
- Informar PASS/FAIL/BLOCKED/NOT_IMPLEMENTED con evidencia de ejecución; corregir errores técnicos dentro de alcance.

## Límites que permanecen abiertos

1. **E-07/cPanel:** versiones PHP/MySQL, TLS, DocumentRoot, `.htaccess`, extensiones y política de secretos reales sin verificar.
2. **H-14:** referencia/versionado del XLSX original pendiente.
3. **H-40:** base legal, finalidad, retención, responsables y autorización institucional pendientes. **Datos reales prohibidos.**
4. **Especialista:** revisión formal pendiente.
5. **I00-06:** lint estricto OpenAPI no completado; atender como mejora técnica verificable, sin modificar contratos unilateralmente.
6. **I00-08:** pruebas de rollback/concurrencia de fichas reservadas para I02, no atribuirlas como PASS en I00.

## Puertas futuras

El GO para operar con datos reales o publicar en cPanel exigirá evidencia y aprobación específicas. Este GO condicionado no sustituye el acta definitiva de I00 ni autoriza la producción.

**Trazabilidad:** PR #3 de I00 y ejecución CI #24; mantener esta decisión referenciada desde el acta preliminar y el handoff.