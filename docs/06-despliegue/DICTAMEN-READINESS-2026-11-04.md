# PCD IECPNA — Evaluación de publicación en hosting cPanel (04/11/2026)

**Destino documentado:** `https://pcd.unobdata.com`.

**Dictamen a esta fecha: NO-GO producción.** Se autoriza exclusivamente **preparación de paquete UAT con datos sintéticos**, sin activar el sistema en un dominio accesible a usuarios reales. La instrucción de publicar no sustituye las puertas institucionales ni las evidencias técnicas del hosting. **No se ha accedido a cPanel ni se ha realizado un despliegue remoto.**

## 1. Verificación completada fuera del hosting
- PR #5: `feature/i02-captura-personas`, I01 ya integrado en main, I02 todavía sin integrar.
- GitHub Actions [#41874307397](https://github.com/iarostegui/pcd-iecpna/actions/runs/41874307397): cuatro trabajos SUCCESS (documentos, PHP/MySQL, frontend, I00), **entorno CI distinto de cPanel**.
- H-24 validación obligatoria backend y React; H-14 referencia de Excel recibida.
- H-40: REPORTES **gestiona** la designación/aprobación institucional, pero no existe evidencia del acto formal ni aprobación de tratamiento de datos.
- I03 (ficha líder GC), I04 (edición/consulta completa), I05 (administración/reportes) **no implementadas**. El producto solicitado completo todavía no existe.
- Catálogo oficial INEI: importador preparado, pero sin carga real verificada; C.E. sin fusión automática.
- PHP 8.2 y MySQL 8 se probaron en GitHub Actions, **no en el hosting**.

## 2. Condiciones para un UAT en hosting (subdominio aislado, exclusivamente ficticios)
1. Confirmar titular de la cuenta, acceso cPanel/SSH o alternativa de implementación soportada, ruta real del Document Root y nombre de subdominio UAT **distinto del público previsto para producción**, con HTTPS funcional.
2. Verificar versión y extensiones PHP 8.2+, PDO MySQL, mbstring, Apache/LiteSpeed rewrite, InnoDB y compatibilidad del SQL 11/146/23, CHECK, FK y JSON.
3. Ubicar código `backend/src`, `backend/vendor` y secretos **fuera del directorio servido**. Exponer solo `frontend/dist` y la entrada pública de API. No asumir que enlaces simbólicos, Alias o rewrites son posibles en cPanel: comprobar con el proveedor.
4. Guardar credenciales DB y de UAT fuera de Git, de archivos públicos y de logs. SQL solo sobre **base de pruebas vacía**. No migrar ni reemplazar una BD existente sin respaldo y diagnóstico.
5. En UAT configurar `PCD_ENV=testing`, `PCD_COOKIE_SECURE=true`, sesión HTTPS, CSRF y mismo origen. Contraseñas sintéticas exclusivamente; ningún DNI/nombre/teléfono real.
6. Restringir el acceso UAT por autenticación adicional o lista de IP, prohibir indexación y respaldos públicos; aviso visible de entorno de pruebas.
7. Ejecutar smoke tests reales: `/api/v1/health`, `/api/v1/ready`, login, presbiterio/sede, CSRF, cierre, Persona/Ficha, rol REPORTES, cookies, cabeceras y rutas inexistentes. Registrar HTTP status y evidencia, sin almacenar PII.
8. Registrar backup previo y prueba de restauración **antes de sustituir contenido preexistente**. Ensayar rollback de frontend, API y base.

## 3. Puertas de producción (no sustituidas por CI)
- **PROD-01 a PROD-19** del checklist vigente; pruebas de carga 10/20/30 y restore real en hosting.
- **H-40 institucional**: formalizar aprobación, finalidad, base legal, aviso, tratamiento de datos religiosos/GPS, retención y borrado. REPORTES coordina; su perfil sigue de solo lectura.
- **Credenciales individuales** y revocación de sesiones de prueba antes de cualquier uso real.
- **I03–I05** y pruebas de integración I06 si el objetivo es publicar la plataforma PCD **completa**.
- **INEI oficial**: archivo y corte verificados, importación y recuento en DB hosting de prueba; C.E. documentado o degradación deliberada sin fusión.
- Aprobación de seguridad y validación funcional humana; el hecho de que una PR tenga CI verde no acredita GO.

## 4. Información que falta del hosting
- Proveedor/plan de alojamiento y acceso a cPanel mediante un canal autorizado; **no enviar contraseñas, claves SSH ni secretos a ChatGPT**.
- Captura o exportación de `MultiPHP Manager` / versiones PHP y MySQL/MariaDB, extensiones, `Domains` / Document Root, certificado TLS.
- Capacidad de configurar entradas PHP fuera de Document Root (o SSH/terminal/rewrite compatibles), tamaño disponible y mecanismo de copia de seguridad.

## 5. Entrega de CI para UAT
El workflow `.github/workflows/paquete-uat.yml` construye un paquete no productivo descargable como **artifact** de GitHub Actions con frontend, API PHP, vendor de producción, SQL y manifiesto SHA256. **No sube nada a cPanel ni ejecuta scripts contra un hosting**. Debe ejecutarse manualmente cuando se necesite un paquete de verificación.
