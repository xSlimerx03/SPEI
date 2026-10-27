# CU-022 · Conversar antes de la entrega

Formato CU-01 (MD Anexo D) · Versión 1.0 · 27/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-022 Conversar antes de la entrega |
| Objetivo (2) | Aclarar dudas de una solicitud entre el solicitante y el mostrador del área antes de la entrega (RF-033). |
| Actores (3) | Solicitante y operadores del área (administradores del sistema del área o sin área y asistentes del área). |
| Disparador (4) | Detalle del préstamo → "Conversación" → "Enviar". |
| Precondiciones (5) | P1. Préstamo `pendiente`. P2. El actor es el solicitante u opera el área de todos los recursos. |
| Postcondiciones (6) | Q1. Mensaje en `conversaciones` con emisor y hora. Q2. Aviso en el sistema a la otra parte (RNF-004). Q3. Al abrir el detalle, los mensajes recibidos quedan leídos. |
| Flujo principal (7) | 1. El actor escribe (2 a 1000 caracteres) y envía. 2. El sistema guarda y avisa: si escribe el solicitante, a los operadores activos del área; si escribe un operador, al solicitante. |
| Flujos alternos (8) | A1. Préstamo ya entregado, cancelado o vencido → la conversación queda de solo lectura. |
| Excepciones (9) | E1. Usuario ajeno al préstamo o al área → 403. E2. Más de 30 mensajes por minuto → 429. |
| Reglas relacionadas (10) | MD 13.1; RNF-004; DT-036. |
| Requisitos relacionados (11) | RF-033, RS-006. |
| Criterios de aceptación (12) | CA1. Solo participan el solicitante y el mostrador del área mientras está pendiente (CP-052). CA2. Abrir el detalle marca como leídos los mensajes recibidos (CP-052). |
