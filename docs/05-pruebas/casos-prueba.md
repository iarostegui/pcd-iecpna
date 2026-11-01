# PCD V3.1 — Matriz de casos de prueba
Cada caso debe reportar: estado `NO_EJECUTADO/PASÓ/FALLÓ/BLOQUEADO`, evidencia real, comando, fecha, usuario de prueba y entorno. **No dar por ejecutado sin hacerlo.**

| ID | Prueba GIVEN / WHEN / THEN | Prioridad |
|---|---|---|
| CA-01 | Login sin presbiterio → rechaza | P0 |
| CA-02 | Digitador intenta ingresar con presbiterio ajeno → rechaza sin revelar cuenta | P0 |
| CA-03 | Seis digitadores por presbiterio configurado → existen activos | P0 |
| CA-04 | ADMIN y REPORTES → cobertura nacional según rol | P0 |
| CA-05 | Usuario inactivo → login rechazado | P0 |
| CA-06 | Contraseña inicial común hash en BD → sin texto claro ni cambio forzado | P0 |
| CA-07 | Exceso intentos login → throttling y auditoría | P0 |
| CA-08 | Selección de sede → solo sedes de presbiterio | P0 |
| CA-09 | Intentar sede fuera del presbiterio vía API → 403 | P0 |
| CA-10 | Sede nueva no duplicada → se crea y selecciona sin aprobación | P0 |
| CA-11 | Nombre normalizado igual (IECPNA/acento/espacios) → 409 | P0 |
| CA-12 | Nombre solo similar → advertencia, puede crear sede legítima | P1 |
| CA-13 | Dos creaciones simultáneas misma sede → solo una | P0 |
| CA-14 | Cambiar sede conserva presbiterio, nunca reautoriza GPS | P0 |
| CA-15 | Nueva Persona → foco inicial N.º ficha | P0 |
| CA-16 | Enter/Tab avanza, Shift+Tab retrocede, Enter no guarda | P0 |
| CA-17 | Fecha válida DD/MM/AAAA → ISO en API | P0 |
| CA-18 | Fecha imposible (31/02/1998; 29/02 en año no bisiesto) → rechazar valor como fecha, permitir corregir o dejar vacío y guardar incompleto | P0 |
| CA-19 | Persona sin número de ficha → guardar correctamente | P0 |
| CA-20 | Persona con críticos incompletos → guardar y reportar faltantes | P0 |
| CA-21 | Configurar criticidad vía ADMIN → cambia reporte no validez del guardado | P0 |
| CA-22 | Documento existente → alerta, no segunda Persona | P0 |
| CA-23 | Dos POST simultáneos documento → UNIQUE impide segundo registro | P0 |
| CA-24 | Persona sin documento y nombre parecido → avisar posible duplicado, permitir | P1 |
| CA-25 | Duplicado entre presbiterios → no exponer datos fuera de ámbito | P0 |
| CA-26 | Doble clic guardar / timeout reintento misma clave → una sola ficha | P0 |
| CA-27 | Persona marcada líder GC antes de guardarla → no crea GC | P0 |
| CA-28 | Persona guardada → abrir GC ya vinculado | P0 |
| CA-29 | Persona existente en Consulta → registrar GC después | P0 |
| CA-30 | Capacidad GC vs integrantes estimados → persistir separados | P0 |
| CA-31 | Cambio de líder en GC existente → no sustituye destructivamente | P0 |
| CA-32 | Digitador edita su ficha → permitido, creador se mantiene | P0 |
| CA-33 | Digitador edita ficha ajena → 403 | P0 |
| CA-34 | ADMIN edita ficha ajena → permitido, actor ADMIN, creador inalterado | P0 |
| CA-35 | REPORTES intenta POST/PATCH → 403 | P0 |
| CA-36 | Iniciar sesión → 1 solicitud GPS opcional, datos/estado en UNA fila sesion (sin tabla geo_sesion) | P0 |
| CA-37 | Guardar 10 Personas+GC → misma ubicación de sesión sin nuevos prompts | P0 |
| CA-38 | Refrescar página → misma ubicación almacenada, no nuevo prompt | P0 |
| CA-39 | Denegar GPS → guardar, auditoría indica DENEGADA | P0 |
| CA-40 | Cada alta/edición → auditoría con sesión, IP, actor y geo/estado | P0 |
| CA-41 | Login exitoso/fallido → log separado, sin contraseña | P0 |
| CA-42 | Falla auditoría durante escritura → rollback transacción | P0 |
| CA-43 | Consulta con 5.000 fichas → paginación, filtros y permiso | P1 |
| CA-44 | Reporte avance sede incluye último día digitado y autor | P0 |
| CA-45 | Reporte calidad identifica campos críticos faltantes y duplicidad | P0 |
| CA-46 | React DOM XSS/SQL injection/CSRF/IDOR → no vulnerabilidades básicas | P0 |
| CA-47 | localStorage/URL/logs → no documentos, credenciales ni coordenadas | P0 |
| CA-48 | HTML V2.2 y React en 320/390/768/1440px → sin pérdida de controles | P1 |
| CA-49 | Backup y restauración a BD aislada → datos íntegros | P0 |
| CA-50 | Prueba carga y baseline web actual → informe p95 sin degradación no aceptable | P0 |
| CA-51 | Scripts SQL se ejecutan sin error en MySQL/MariaDB real | P0 |
| CA-52 | Ninguna API ofrece migración/exportación SGSP | P0 |

| CA-53 | Persona existente de DIG-A, nueva ficha de DIG-B → dos fichas con snapshots independientes | P0 |
| CA-54 | DIG-B edita su ficha → permitido, no afecta identidad Persona de DIG-A | P0 |
| CA-55 | DIG-B intenta PATCH Persona creada por DIG-A → 403; ADMIN autorizado | P0 |

## Casos propuestos E1 (NO EJECUTADOS)
| ID | Comprobación | Iteración |
|---|---|---|
| E1-CA-01 | Documento existente de otra sede: crear ficha nueva, no segunda Persona y no mostrar PII ajena | I02 |
| E1-CA-02 | Crear primera ficha genera fuente y Persona; segunda ficha divergente no sobrescribe Persona | I02 |
| E1-CA-03 | Autores distintos editan solo sus fichas; ADMIN conserva autoría | I02/I04 |
| E1-CA-04 | Fecha imposible no se admite; digitador corrige o deja fecha vacía; PHP vuelve a comprobar y DATE solo tiene fecha válida o NULL | I02 |
| E1-CA-05 | Diferencia JUANA/Juan o JU4N4 no se considera detección automática de transcripción; revisión manual presbiterial externa | I02/I04 |
| E1-CA-06 | GC ya existe=Sí sin referencia previa → nueva ficha registrada | I03 |
| E1-CA-07 | GC nombre parecido / líder diferente: advertencia sin fusión | I03 |
| E1-CA-08 | Reporte de faltantes/duplicados objetivos sin cola de conciliación, aprobación ni revisión textual; edición posterior auditada | I04/I05 |
| E1-CA-09 | Inventario de campos coincide con las hojas del Excel, sin inventar dato | I02/I03 |
| E1-CA-10 | Doble clic y concurrencia al adjuntar ficha no producen doble alta | I02 |
**Estado: 10 pruebas diseñadas, ninguna ejecutada.**

## Casos derivados de aclaración funcional (NO EJECUTADOS)
- **E1-CA-11:** formulario GC con 12 integrantes aproximados registra el líder y conteo, **no** 12 Personas/relaciones de miembros.
- **E1-CA-12:** no existe indicador falso «transcripción textual verificada» para `JUANA`/Juan o `JU4N4`; la revisión del papel es manual y externa.
- **E1-CA-13:** API rechaza directamente una fecha calendario imposible (422); permite fecha ausente/NULL.
**No se ha ejecutado ninguna prueba aquí.**

## Etapa 2 — casos complementarios de diseño (NO EJECUTADOS)
| ID | Criterio verificable / escenario | Iteración | Dependencia |
|---|---|---|---|
| **CA-56** | GET /presbiterios anónimo devuelve solo ID/código/nombre activos; sin PII ni usuarios | I01 | H-04 |
| **CA-57** | Login productivo pide usuario tecleado y no permite enumerar cuentas | I01 | H-03 |
| **CA-58** | ADMIN/REPORTES tienen secretos distintos de contraseña común de digitadores | I01 | H-03 / E2-D03 |
| **CA-59** | 5 fallos por cuenta en 15 min provocan mitigación temporal sin revelar existencia | I01 | H-03 |
| **CA-60** | 20 fallos por IP en 15 min provocan mitigación y no bloquean permanentemente una IP compartida | I01 | H-03 |
| **CA-61** | Sesión con inactividad >45 min rechaza request y requiere login | I01 | H-32 |
| **CA-62** | Sesión con vida absoluta >12 h no se extiende por uso continuo | I01 | H-32 |
| **CA-63** | Refresh + GET /auth/me entrega nuevo contexto/CSRF; POST con CSRF inválido rechaza | I01 | H-32 |
| **CA-64** | ADMIN desactiva usuario → todas sus sesiones dejan de autorizar inmediatamente | I01/I05 | H-32 |
| **CA-65** | Cambio/reset credencial incrementa auth_version y revoca sesiones antiguas | I01/I05 | H-32 |
| **CA-66** | ADMIN puede cambiar explícitamente presbiterio; invalida sede del contexto previo | I01 | **E2-D02 pendiente** |
| **CA-67** | REPORTES consulta nacional sin sede y nunca crea ni edita registros | I01/I05 | **E2-D02 pendiente** |
| **CA-68** | DIGITADOR consulta fichas de su presbiterio y edita solo las propias | I04 | **E2-D01 pendiente** |
| **CA-69** | Persona en dos presbiterios → usuario A no ve la ficha ni PII de B | I02/I04 | H-12 / E1-D01 |
| **CA-70** | GET /catalogos lista dominios/códigos confirmados, sin valores inventados de ubigeo | I01/I02 | H-07 |
| **CA-71** | DNI con ceros no pierde ceros; DNI incompleto no se deduplica como válido | I02 | H-13 |
| **CA-72** | Dos documentos OTRO con igual número y distinto subtipo NO colisionan; OTRO sin subtipo no genera clave exacta | I02 | H-13 |
| **CA-73** | Cambiar campo crítico desactivable recalcula reporte sin reescribir fichas; relación estructural no desactivable | I05 | H-18 |
| **CA-74** | GC en ficha PERSONA → error y sin inserción; líder derivado de ficha.persona_id | I03 | H-35 |
| **CA-75** | Cónyuge/responsabilidad en ficha GC o de persona equivocada → FK/CHECK rechaza | I02/I03 | H-35 |
| **CA-76** | Revocación no causa segunda captura GPS al reabrir sesión existente; nuevo login inicia sesión GPS nueva opcional | I01 | H-32 |

**Estado E2:** 21 casos adicionales **diseñados, 0 ejecutados**; los umbrales de autenticación son parámetros iniciales propuestos, no garantías comprobadas.

## Etapa 3 — casos adicionales diseñados, NO ejecutados
| ID | Caso de prueba | Iteración |
|---|---|---|
| CA-77 | Acceso a Consulta: sin filtro devuelve página general (todos los presbiterios); DIGITADOR no puede editar ficha ajena | I04 | RN-29 |
| CA-78 | Filtros Presbiterio y/o Sede son opcionales, combinables y no modifican el permiso de consulta; validar relación sede/presbiterio | I04 | RN-29 |
| CA-79 | No existe ajuste ADMIN de ámbito de consulta por usuario; cambio de presbiterio operativo se audita sin ampliar permiso de edición | I05 | RN-29 |
| CA-80 | GET /grupos/{id} entrega grupo, líder derivado y version_ficha sin GPS | I03 |
| CA-81 | PATCH grupo con versión obsoleta de ficha GC → 409, sin modificación parcial | I03 |
| CA-82 | Idempotency-Key repetida con mismo payload → resultado original; distinta carga → 409 | I02 |
| CA-83 | GET /reportes/avance filtros presbiterio/sede/digitador/fecha y ediciones atribuidas al actor | I05 |
| CA-84 | Error 401/403/409/422 utiliza mismo DTO Error con request_id, sin revelar PII | I01–I05 |
| CA-85 | Administración permite presbiterio operativo del digitador, NO campo alcance_consulta ni tabla usuario_presbiterio | I05 | RN-29 |
| CA-86 | Prototipo muestra GPS/usuarios, pero producción omite GPS en consulta y no enumera cuentas en login | I01/I04 |
| CA-87 | Tras GPS denegado, refrescar/cambiar sede no vuelve a solicitar geolocalización | I01 |
| CA-88 | Reporte calidad recalcula faltantes al cambiar parámetros; sin aprobación de supervisor | I05 |
| CA-89 | Nuevo registro GC no crea miembros nominales aunque tenga cantidad aproximada | I03 |
| CA-90 | GET /personas y /reportes sin filtros se pagina globalmente; sede/presbiterio filtros de búsqueda, no restricciones de permiso | I04/I05 | RN-29 |

**Estado:** 14 pruebas diseñadas, **ninguna ejecutada**. H-23/H-24 requieren casos adicionales tras decisión funcional.

## Regresión adicional modelo PCD V4.0 — casos NO EJECUTADOS
| ID | Caso | Iteración |
|---|---|---|
| **CA-91** | SQL nuevo crea exactamente **11 tablas** y FKs válidas; ninguna de las tablas consolidadas permanece. | I00 |
| **CA-92** | ORGANIZACION permite presbiterio y sede hija; impide guardar ficha en sede de otro presbiterio operativo. | I01/I02 |
| **CA-93** | CATALOGO almacena sexos, tipo documento y campos críticos en misma tabla; cambios no desactivan relaciones técnicas. | I00/I05 |
| **CA-94** | SESION guarda un estado GPS (incluyendo denegado) y no vuelve a solicitar ubicación en el mismo login. | I01 |
| **CA-95** | Datos cónyuge/responsabilidades quedan dentro de FICHA.datos_fuente_json con estructura validada y auditoría; sin tablas independientes. | I02 |
| **CA-96** | Persona tipo OTRO y mismo número en varias fichas recibe aviso de coincidencia pero NO fusión exacta automática; sin campo subtipo. | I02 |
| **CA-97** | GET /personas sin filtros consulta alcance total de la funcionalidad; filtros separados y combinados por presbiterio/sede mantienen paginación, sin GPS. | I04 |
| **CA-98** | DIGITADOR con acceso a Consulta puede leer ficha de otro presbiterio pero PATCH de ficha creada por otro devuelve 403. | I04 |
| **CA-99** | Importación INEI carga código/tipo/padre válidos (2/4/6 caracteres) en una tabla UBIGEO y no códigos inventados. | I02 |
| **CA-100** | Restauración BD y rollback de API/build funcionan con un respaldo de prueba y controles de datos sensibles. | I06 |

**Los casos CA-91…CA-100 son propuestas de aceptación**, sin resultados. Ante contradicción con casos históricos, prevalece V4 y se debe corregir el caso antes de ejecutarlo.
