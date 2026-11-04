# I02 — Captura de Personas y fichas (continuidad sin DeepSeek)

**Producto:** PCD IECPNA temporal, independiente SGSP. **Base:** SDD V4.1, I01 integrado en 41ecc8b. **Datos:** únicamente sintéticos.

## Objetivo
Construir captura Persona/Ficha con persistencia transaccional, reconocimiento documental confiable, nuevas fichas por fuente, prevención de reintentos y preservación de autoría. La visualización y edición general siguen planificadas para I04; en I02 se habilitan las lecturas mínimas necesarias para enlazar fichas.

## Puertas no autorizadas
- **H-24**: umbral mínimo de contenido sin aprobación. No establecer regla propia ni declarar aceptación de ficha totalmente vacía.
- **CE**: formato de identificación confiable pendiente de fuente oficial. Se admite el dato si estructuralmente válido, sin construir clave de identidad confiable hasta validar la regla.
- **INEI**: usar únicamente datos de fuente oficial y versión/corte comprobables; prohibido inventar ubigeos.
- **H-14** XLSX original, **H-40** privacidad, cPanel y especialista: pendientes; prohibida producción/datos reales.

## Alcance técnico permitido
Backend PHP/PDO/MySQL existente; validación fechas y estructura JSON; identidad DNI 8 dígitos (ceros intactos), OTRO/CE sin fusión automática mientras CE no esté ratificado; sede/contexto servidor; transacción por ficha; idempotencia por usuario/clave/hash; impedir edición de ficha ajena; pruebas automáticas con datos sintéticos.

## Fuera de alcance
GC, conciliación, SGSP, nuevas tablas, cambiar modelo SQL, consentimiento obligatorio de GPS, despliegue productivo.