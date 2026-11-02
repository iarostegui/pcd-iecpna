# scripts — Validadores y aprovisionamiento

[↑ Índice principal](../README.md)

## Validación documental
Desde la raíz del repositorio, con dependencias reproducibles:

```bash
npm ci
node scripts/validar-sdd-i00.cjs
node scripts/validar-prototipo-v2.3.cjs
```

`validar-prototipo-v2.3.cjs` no requiere paquetes; `validar-sdd-i00.cjs` usa `js-yaml` como parser formal y el `package-lock.json` fija la versión. Comprueban **sintaxis/estructura**, no MySQL ni E2E.

El prototipo [V2.2](../prototypes/PCD_v2.2.html) se preserva y [V2.3](../prototypes/PCD_v2.3.html) es la UI vigente.

## Aprovisionamiento de cuentas — `bootstrap-provision.php`
```bash
# Plan sin BD (no escribe nada)
php scripts/bootstrap-provision.php --mode=uat --dry-run

# UAT: ADMIN, REPORTES y 6 DIGITADORES por presbiterio
php scripts/bootstrap-provision.php --mode=uat

# Primer ADMIN productivo (exige PCD_ENV=production)
php scripts/bootstrap-provision.php --mode=admin-prod
php scripts/bootstrap-provision.php --mode=admin-prod --rotate
```

Variables: `PCD_DB_HOST`, `PCD_DB_PORT`, `PCD_DB_NAME`, `PCD_DB_USER`, `PCD_DB_PASSWORD`, `PCD_UAT_DIGITADOR_PASSWORD`, `PCD_ADMIN_PASSWORD`, `PCD_REPORTES_PASSWORD`.

Reglas:
- Idempotente por `usuario.codigo`; no duplica cuentas ni sobrescribe hashes existentes.
- **Argon2id obligatorio**; si PHP no lo ofrece, aborta (código 4) y no usa otro algoritmo.
- La clave común de DIGITADOR es **solo UAT**; antes de producción se rota a credenciales individuales.
- No imprime contraseñas ni hashes; las credenciales se leen del entorno.
- Códigos de salida: `0` correcto, `2` configuración, `3` BD, `4` Argon2id ausente.
