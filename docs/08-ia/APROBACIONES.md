# Registro de decisiones de PCD para ejecución por IA (V4.1)
**Fuente:** instrucciones expresas del responsable funcional en conversación del proyecto. Esta tabla es evidencia de criterio funcional, **no equivale a firma jurídica/institucional ni a GO productivo**.
| Clave | Decisión funcional | Estado |
|---|---|---|
| ALC-PCD-4.0 | Aplicación temporal independiente SGSP; 12 capacidades y 10 exclusiones | APROBADA |
| H-12 | Consulta general en función autorizada; filtros presbiterio y sede, sin ACL territorial; edición propia | APROBADA |
| H-13 | Documento solo tipo/número, sin subtipo | APROBADA |
| Modelo físico | 11 tablas, una ORGANIZACION jerárquica, una CATALOGO, una UBIGEO | APROBADA |
| E1-D01 | Misma Persona por identidad documental confiable, nueva ficha entre presbiterios usando endpoint existente, consolidado no se sobrescribe | APROBADA |
| E2-D02 | ADMIN nacional cambia contexto explícitamente entre los cuatro presbiterios precargados e invalida la sede previa; REPORTES consulta agregados nacionales sin sede | APROBADA (ALC-03) |
| H-03 | Contraseña común DIGITADOR exclusivamente en UAT; rotar a credenciales individuales para producción | APROBADA, PRUEBAS PENDIENTES |
| RN-30 | LIM/TRU/CHI/IQU precargados; ninguna alta presbiterio en app, solo SQL técnico autorizado | APROBADA |
| RN-31 | Alta de ficha se registra en la ficha, sin evento auditoría. Ediciones: solo campos distintos y actor snapshot | APROBADA |
| RN-32 | Todas las tablas con created_at/updated_at | APROBADA |
| RN-33 | Quitar tildes/case/espacios al comparar nombre de sede; conservar etiqueta visible | APROBADA |
| Índices reportes | Optimizar solo con consultas/EXPLAIN reales | APROBADA |
| E1-D02 | Identificación GC preexistente | PENDIENTE |
| H-23 | Permiso de registro GC de otra Persona | PENDIENTE |\n| H-24 | Contexto presbiterio/sede y nombres, ambos apellidos, tipo/número documento y teléfono obligatorios para ficha Persona | APROBADA FUNCIONALMENTE; VERIFICACIÓN CI PENDIENTE |
| H-40 — perfil responsable | **REPORTES** gestiona la designación y la aprobación institucional del PCD; mantiene su acceso operativo **solo lectura**. No se crea un cuarto perfil ni se presume una aprobación institucional efectiva. | APROBADA COMO ASIGNACIÓN FUNCIONAL |
| H-40 PR-01…08 — evidencias institucionales | Formalización de la designación, aprobación institucional efectiva, finalidad, aviso, base legal, retención y tratamiento de datos/GPS/backups | PENDIENTE, GATE PRODUCTIVO |
| Visibilidad Github/Licencia | Repositorio público observado; cambio de visibilidad y licencia dependen del propietario | PENDIENTE DECISIÓN DEL PROPIETARIO |
**Nota:** E2-D01 (consulta general con filtros opcionales) y E2-D03 (riesgo residual de clave común) quedan cubiertas por H-12 y H-03; no se reabren. No inventar nombres de personas aprobadoras ni firmas. La aceptación institucional se adjuntará solo cuando exista autorización explícita.

Los algoritmos aprobados están consolidados en [CONTRATO-EJECUCION-V4.1](CONTRATO-EJECUCION-V4.1.md); esta lista no sustituye la revisión del especialista ni actas institucionales H-40.
