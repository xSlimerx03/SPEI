# CU-020 · Exentar una sanción

Formato CU-01 (MD Anexo D) · Versión 1.0 · 27/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-020 Exentar una sanción |
| Objetivo (2) | Anular de forma excepcional y justificada una penalización (RF-044). |
| Actores (3) | Administrador del sistema (el responsable de un área, solo sanciones de préstamos de su área). |
| Disparador (4) | "Sanciones" → "Exentar". |
| Precondiciones (5) | P1. Penalización vigente de la institución. |
| Postcondiciones (6) | Q1. `exentada = 1` y `motivo_exencion`; el trigger resta sus marcas. Q2. Si el usuario queda por debajo del umbral, se levanta su bloqueo y el contador se reinicia. Q3. Evento `EXENTAR` y aviso al usuario. |
| Flujo principal (7) | 1. El administrador abre la ventana de exención. 2. Escribe la justificación (mínimo 20 caracteres). 3. El sistema guarda, recalcula el bloqueo y avisa. |
| Flujos alternos (8) | A1. Justificación corta → la ventana se reabre con el error. |
| Excepciones (9) | E1. Penalización ya exentada → rechazo. E2. Penalización de otra área (responsable) o de otra institución → 404. |
| Reglas relacionadas (10) | RN-005; DT-036. |
| Requisitos relacionados (11) | RF-044, RS-006. |
| Criterios de aceptación (12) | CA1. La exención resta las marcas, levanta el bloqueo y queda en auditoría (CP-047). CA2. Un responsable de otra área no puede exentar (CP-047). |
