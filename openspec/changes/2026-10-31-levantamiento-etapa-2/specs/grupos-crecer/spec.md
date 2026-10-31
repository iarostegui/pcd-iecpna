# Delta E2 — integridad del líder GC
## GC-08 ADDED: una referencia de líder
GIVEN ficha tipo GC enlazada a Persona P WHEN se registra grupo THEN el líder se obtiene **únicamente** de ficha.persona_id, sin otro campo de líder duplicado.
## GC-09 ADDED: tipo de ficha consistente
GIVEN intento de asociar Grupo a ficha tipo PERSONA o responsabilidad/cónyuge a ficha GC WHEN guarda THEN servicio rechaza con 422 y se protege la integridad mediante FK/constraint disponible.
