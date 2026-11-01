# PCD — Plataforma de Captura de Datos IECPNA

> **Proyecto:** aplicación temporal e independiente del SGSP para capturar fichas impresas de Personas y Líderes de Grupos Crecer.  
> **Documentación SDD:** V3.1 (análisis y diseño) · **Prototipo HTML de referencia:** V2.3 (sobre base visual V2.2) · **Dominio previsto:** `https://pcd.unobdata.com`.  
> **Estado:** documentación y prototipo disponibles; **frontend React, API PHP y MySQL productivos todavía no implementados**.

## Índice general — comenzar aquí

| Ruta | ¿Qué se encuentra aquí? | ¿Cuándo consultarla? |
|---|---|---|
| **[AGENTS.md](AGENTS.md)** | Instrucciones obligatorias para cualquier IA que programe PCD. | **Primero**, antes de modificar código o documentos. |
| **[docs/](docs/README.md)** | Índice de análisis, diseño, base de datos, API, pruebas, despliegue e instrucciones para IA. | Para navegar el cuerpo documental. |
| ↳ [01 — Análisis](docs/01-analisis/README.md) | Alcance, procesos, casos de uso, reglas, formularios, catálogos pendientes, consultas y reportes. | Entender **qué** debe hacer PCD. |
| ↳ [02 — Diseño](docs/02-diseno/README.md) | Arquitectura, componentes, seguridad, UX, requerimientos no funcionales y ADR. | Entender **cómo** se construirá. |
| ↳ [03 — Base de datos](docs/03-base-datos/README.md) | Modelos conceptual/lógico/físico, diccionario y diagrama entidad–relación. | Diseñar y validar la persistencia de PCD. |
| ↳ [04 — Integración](docs/04-integracion/README.md) | API REST/OpenAPI y estrategia **futura** de migración hacia SGSP. | Alinear frontend y backend, sin construir SGSP. |
| ↳ [05 — Pruebas](docs/05-pruebas/README.md) | Matriz de aceptación y pruebas de capacidad. | Verificar cada implementación. |
| ↳ [06 — Despliegue](docs/06-despliegue/README.md) | Instalación y operación en cPanel. | Publicar en el hosting existente. |
| ↳ [07 — Auditorías](docs/07-auditorias/README.md) | Hallazgos, levantamiento, **protocolo obligatorio de alcance** y respuesta al auditor. | Auditar **sin ampliar unilateralmente el alcance**. |
| ↳ [08 — IA](docs/08-ia/README.md) | Prompt maestro, plan iterativo, matriz de trazabilidad y definición de terminado. | Encargar el desarrollo a la IA. |
| **[openspec/](openspec/README.md)** | Especificaciones SDD vigentes por capacidad y registro de cambios futuros. | Implementar según requisitos verificables. |
| **[database/](database/README.md)** | Migraciones SQL y notas de aprovisionamiento/catálogos. | Crear y evolucionar la BD **propia de PCD**. |
| **[prototypes/](prototypes/README.md)** | Prototipo HTML [PCD V2.3 vigente](prototypes/PCD_v2.3.html), V2.2 histórica. | Validar pantalla, flujos e identidad gráfica. |
| **[frontend/](frontend/README.md)** | Directorio reservado para React + TypeScript + Vite. | Implementar la interfaz productiva. |
| **[backend/](backend/README.md)** | Directorio reservado para PHP, API REST, reglas y persistencia. | Implementar operaciones y seguridad en servidor. |
| **[tests/](tests/README.md)** | Directorio reservado para pruebas automatizadas y evidencia reproducible. | Probar la solución implementada. |

### Accesos rápidos

- **Alcance oficial y obligatorio:** **[ALC-PCD-3.1 — Qué incluye y qué excluye PCD](docs/01-analisis/alcance-funcional.md)** · [Reglas de negocio](docs/01-analisis/reglas-negocio.md) · [Decisiones pendientes](docs/01-analisis/decisiones-pendientes.md).
- **Fichas de origen:** [Diccionario de captura](docs/01-analisis/diccionario-captura.md). El **Excel original y sus ejemplos no están versionados aquí todavía**; no inventar campos o sedes para reemplazar la fuente.
- **Arquitectura y riesgos:** [Arquitectura](docs/02-diseno/arquitectura.md) · [Seguridad y auditoría](docs/02-diseno/seguridad-auditoria.md) · [RNF](docs/02-diseno/requerimientos-no-funcionales.md).
- **Diseño de datos:** [Modelo lógico](docs/03-base-datos/modelo-logico.md) · [Diagrama ER](docs/03-base-datos/diagramas/er-pcd.mmd) · [SQL inicial](database/migrations/001_initial.sql).
- **Interfaz y servicios:** [HTML V2.3](prototypes/PCD_v2.3.html) · [OpenAPI](docs/04-integracion/openapi.yaml).
- **Inicio de desarrollo IA:** [Prompt maestro](docs/08-ia/PROMPT-MAESTRO.md) · [Plan de implementación](docs/08-ia/plan-implementacion.md) · [Trazabilidad](docs/08-ia/matriz-trazabilidad.md).
- **Control de entrega:** [Casos de prueba](docs/05-pruebas/casos-prueba.md) · [Definición de terminado](docs/08-ia/definicion-terminado.md).

## Regla obligatoria para IA desarrolladora y auditoría

**El [alcance oficial ALC-PCD-3.1](docs/01-analisis/alcance-funcional.md) es el límite de evaluación y construcción.** Las auditorías pueden señalar riesgos, contradicciones o **cuestionar expresamente el alcance**, pero una funcionalidad no solicitada debe clasificarse como **propuesta de ampliación**, no como defecto de PCD ni requisito bloqueante automático. Solo el responsable funcional puede aprobar cambios de alcance. Ver [protocolo de auditoría](docs/07-auditorias/protocolo-auditoria-alcance.md).

## Descripción y alcance

PCD centralizará la digitación de fichas impresas de IECPNA mediante digitadores identificados por **presbiterio**, selección de **sede**, alta de Persona, carga opcional de Líder de Grupo Crecer una vez guardada la Persona, consulta/corrección, campos críticos configurables, reportes y auditoría con ubicación capturada **una sola vez por sesión** cuando sea posible. Las fichas incompletas y sin número impreso pueden guardarse.

PCD **no es el Sistema de Gestión y Seguimiento Pastoral (SGSP)** y no comparte su base de datos. Los registros de PCD se migrarán a SGSP **posteriormente, mediante un proyecto/proceso separado que todavía no forma parte de esta implementación**.

## Arquitectura acordada

```text
Navegador web
    └─ Frontend React + TypeScript + Vite (compilado, estático)
         └─ API REST modular PHP 8.x (mismo hosting y dominio)
              └─ Base de datos MySQL/MariaDB, motor InnoDB, propia de PCD
```

Hosting actual con cPanel, sin Node.js residente en producción, Docker, microservicios, Redis ni infraestructura adicional. El SQL y la API son **contratos de diseño propuestos**, pendientes de ejecución/pruebas en el entorno real.

## Orden de lectura obligatorio para una IA desarrolladora

1. [AGENTS.md](AGENTS.md) y este índice.
2. **[Alcance oficial de PCD](docs/01-analisis/alcance-funcional.md)** y después [Análisis](docs/01-analisis/README.md), reglas y diccionario de captura.
3. [Diseño](docs/02-diseno/README.md) y [base de datos](docs/03-base-datos/README.md).
4. [API](docs/04-integracion/README.md) y [especificaciones OpenSpec](openspec/README.md).
5. [Prototipo V2.3](prototypes/PCD_v2.3.html), como referencia **visual y de flujo, no código de autenticación ni almacenamiento para producción**.
6. [Plan de implementación](docs/08-ia/plan-implementacion.md) y [pruebas](docs/05-pruebas/README.md).

**Regla de mantenimiento:** cada carpeta documental incorpora su propio `README.md`; si se crea, mueve o retira un archivo, actualizar el índice de esa carpeta y este índice cuando corresponda. Mantener separadas las versiones **PCD HTML 2.2** y **documentación SDD 3.1**.

## Estado de auditoría — Etapa 2
[Levantamiento E2](docs/07-auditorias/etapa-2-arquitectura-seguridad-datos.md) examina H-03/04/07/12/13/18/28/32/35 con evidencias del auditor y define **propuestas** para seguridad, catálogos, identidad, sesiones, autorización y líder GC. Los modelos [SQL](database/migrations/001_initial.sql), [API](docs/04-integracion/openapi.yaml), [catálogos](docs/03-base-datos/catalogos-e-identidad.md), [criticidad](docs/03-base-datos/campos-criticos.md) y [pruebas](docs/05-pruebas/casos-prueba.md) se actualizaron. **Sin pruebas ejecutadas ni aprobación de E2-D01/D02/D03**. El prototipo HTML V2.2 permanece inalterado.

**Etapa 3 documentada:** [Informe E3](docs/07-auditorias/etapa-3-contratos-pruebas.md), [OpenAPI 3.2](docs/04-integracion/openapi.yaml), [matriz integral](docs/08-ia/matriz-trazabilidad.md) y [pruebas por iteración](docs/05-pruebas/asignacion-iteraciones.md). El prototipo 2.2 sigue igual. No se han ejecutado pruebas del backend.

## **MODELO VIGENTE PCD V4.0 — LECTURA OBLIGATORIA**
El modelo aprobado es de **11 tablas**, no 17: [justificación](docs/03-base-datos/anexos/justificacion-11-tablas.md), [SQL](database/migrations/001_initial.sql), [diagrama físico](docs/03-base-datos/anexos/anexo-01-diagrama-fisico.md) y [diccionario completo](docs/03-base-datos/anexos/anexo-02-diccionario-fisico.md). **134 columnas**.

**Consulta:** alcance total de la funcionalidad autorizada, con filtros opcionales por Presbiterio y/o Sede. **No** crear permisos territoriales para leer ni nueva tabla; edición DIGITADOR solo fichas creadas por él. **H-13:** solo tipo+número de documento (sin subtipo).

**Etapa 4:** [informe de preparación productiva](docs/07-auditorias/etapa-4-produccion-y-privacidad.md), [checklist de despliegue](docs/06-despliegue/checklist-produccion.md). H-40 privacidad y pruebas reales no cerradas. **Etapas 1–3 V3 son historial y quedan subordinadas a V4**; no programar desde sus contradicciones. El prototipo PCD V2.2 permanece intacto.

## Referencia UI/React actual — V2.3
[**Abrir prototipo PCD V2.3**](prototypes/PCD_v2.3.html) · [**Matriz de alineación con SDD V4**](docs/02-diseno/pcd-v2.3-alineacion.md) · [**Pruebas CA-101…111**](docs/05-pruebas/validacion-prototipo-v2.3.md).
**V2.2 preservado sin modificación.** La IA construye usando V2.3 para pantalla/flujo y OpenSpec V4/SQL 11 tablas para negocio e infraestructura. No copiar claves ficticias ni almacenamiento local. E1-D01/E1-D02/H-23/H-24 quedan abiertos.
