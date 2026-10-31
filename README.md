# PCD — Plataforma de Captura de Datos IECPNA
**Estado:** documentación de análisis/diseño SDD V3.1 · **Prototipo visual/funcional vigente:** PCD V2.2 (con identidad gráfica actualizada) · **No es el SGSP.**

## Finalidad
Aplicación web **temporal e independiente** para digitalizar fichas impresas de **Personas** y **Líderes de Grupos Crecer** de IECPNA, con digitadores por presbiterio, registro de sedes, calidad, consultas, auditoría y reportes. Su base MySQL es **propia de PCD**. Los datos migrarán **posteriormente** a SGSP mediante un proceso de migración independiente, aún no implementado.

## Fuentes y jerarquía
1. Decisiones de PCD aprobadas y registradas en `docs/01-analisis/reglas-negocio.md`.
2. Fichas originales del Excel `SGSP_Fichas_Imprimibles_A4 (2) v1. 0.xlsx` (fuente de **campos en papel**, disponible como adjunto original de trabajo; **no está aún versionada en este repositorio**).
3. Prototipo `prototypes/PCD_v2.2.html` (referencia de UX, **no seguro para datos reales**).
4. Especificaciones SDD `openspec/specs/`, contratos `docs/04-integracion/openapi.yaml`, diseño físico `database/migrations/001_initial.sql`.
5. Diseño SGSP: solamente referencia conceptual de compatibilidad para futura migración; **no modificar ni implementar SGSP**.

## Arquitectura aprobada
React + TypeScript + Vite compilado como estáticos, API PHP 8.x modular con PDO, MySQL/MariaDB + InnoDB, hosting Linux **existente** con cPanel, dominio `https://pcd.unobdata.com`. Sin servicios residentes Node.js, Docker, Redis, microservicios, colas externas ni gastos adicionales. Objetivos de rendimiento sujetos a pruebas en hosting real.

## Índice SDD
- `AGENTS.md`: instrucciones obligatorias al agente.
- `docs/01-analisis/`: alcance, proceso, reglas, casos de uso y catálogo de campos.
- `docs/02-diseno/`: arquitectura, RNF, seguridad, UI y decisiones técnicas.
- `docs/03-base-datos/`: modelo conceptual/lógico/físico, diccionario y ER.
- `docs/04-integracion/`: API REST/OpenAPI y política de futura migración.
- `docs/05-pruebas/`: pruebas funcionales, seguridad y rendimiento.
- `docs/06-despliegue/`: manual cPanel.
- `openspec/specs/`: obligaciones SDD por capacidad; `openspec/changes/`: futuros cambios.
- `database/migrations/`, `database/seeds/`: artefactos SQL versionados.
- `prototypes/PCD_v2.2.html`: prototipo inalterado.

## Estado de implementación
**Aún sin backend ni frontend productivos.** Documentos y esquema SQL son diseño propuesto para implementación y pruebas, no evidencia de funcionamiento. No publicar `prototypes/` como producción ni usar claves de demostración.

