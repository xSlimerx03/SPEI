# CU-021 · Recibir recordatorios y avisos del préstamo

Formato CU-01 (MD Anexo D) · Versión 1.0 · 27/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-021 Recibir recordatorios y avisos del préstamo |
| Objetivo (2) | Mantener informados al solicitante y a los operadores en el sistema y por correo (RF-046 a RF-049). |
| Actores (3) | Sistema (tareas programadas y eventos), usuario final, administradores del sistema del área. |
| Disparador (4) | Cada evento del préstamo (solicitud, entrega, devolución, cancelación, vencimiento, desplazamiento por prioridad, sanción) y `spei:enviar-recordatorios` cada minuto. |
| Precondiciones (5) | P1. Servicios `cola` y `programador` en ejecución (DT-031). |
| Postcondiciones (6) | Q1. Notificación en el sistema (`push`, web por DT-022) y, para el solicitante, correo encolado (`ambas`) que solo sale si la transacción se confirma. |
| Flujo principal (7) | 1. Al ocurrir un evento, el sistema avisa al solicitante en el sistema y por correo con el folio y la acción. 2. Al registrar una solicitud avisa en el sistema a los administradores del sistema del área (RF-049). 3. 10 minutos antes del inicio (préstamo pendiente) o del fin (préstamo activo) envía un recordatorio una sola vez (clave atómica en Redis). |
| Flujos alternos (8) | A1. `SPEI_NOTIFICAR_CORREO=false` → solo avisos en el sistema. A2. Cuenta inactiva → sin correo. |
| Excepciones (9) | E1. Falla del servidor de correo → el trabajo se reintenta (3 intentos) en la cola. |
| Reglas relacionadas (10) | RNF-004; DT-022, DT-036. |
| Requisitos relacionados (11) | RF-045, RF-046, RF-047, RF-048, RF-049. Pendiente: RF-050 (depende de RF-030). |
| Criterios de aceptación (12) | CA1. Cada recordatorio se envía una sola vez aunque la tarea corra varias veces (CP-050). CA2. Los avisos del solicitante salen también por correo (CP-045, CP-050). |
