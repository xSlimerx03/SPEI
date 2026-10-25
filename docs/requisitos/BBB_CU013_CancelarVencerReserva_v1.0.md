# CU-013 · Cancelar o vencer una reserva

Formato CU-01 (MD Anexo D) · Versión 1.0 · 25/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-013 Cancelar o vencer una reserva |
| Objetivo (2) | Liberar los recursos de una reserva que no se va a recoger, por decisión del solicitante, de un administrador del sistema o por vencimiento de la tolerancia. |
| Actores (3) | Principales: usuario final propietario y administrador del sistema que opera el área. Secundario: tarea programada `spei:vencer-prestamos`. |
| Disparador (4) | "Cancelar solicitud" en "Mis préstamos"; "Cancelar reserva" en el panel de detalle de "Préstamos"; ejecución programada cada minuto. |
| Precondiciones (5) | P1. Préstamo `pendiente` (aún sin entrega). P2. Para el administrador del sistema: el préstamo está en su ámbito (todos sus recursos en su área, o sin área asignada). |
| Postcondiciones (6) | Q1. Préstamo `cancelado` (o `vencido`) con la justificación en `motivo_cancelacion`. Q2. Recursos `bloqueado` → `disponible`. Q3. Códigos QR del préstamo revocados. Q4. Evento `CANCELAR_PRESTAMO` o `VENCER_PRESTAMO` en auditoría y notificación al solicitante. |
| Flujo principal (7) | 1. El actor abre la ventana de cancelación. 2. Escribe la justificación (mínimo 5 caracteres el propietario, 20 el administrador); el botón "Confirmar" se habilita al llegar al mínimo. 3. El sistema valida, toma el lock y la transacción, revalida que siga `pendiente` y aplica Q1–Q4. 4. Regresa a la lista con el aviso de éxito. |
| Flujos alternos (8) | A1. **Vencimiento:** la tarea programada busca préstamos `pendiente` cuyo inicio + tolerancia (15 a 20 min) ya pasó y los vence uno por uno; es idempotente. A2. "Cerrar" o Esc cierran la ventana sin cambios. |
| Excepciones (9) | E1. Justificación corta → error en la ventana, que se reabre con lo capturado. E2. El préstamo ya se entregó, canceló o venció → "Solo puede cancelar un préstamo pendiente de entrega." E3. Administrador institucional, asistente o administrador de otra área → 403. E4. Préstamo de otra institución → 404. |
| Reglas relacionadas (10) | RN-007 (tolerancia), RN-011; RF-031: no se permiten prórrogas (el panel lo indica y no ofrece la acción). |
| Requisitos relacionados (11) | RF-028, RF-029, RF-031, RF-032. Pendientes: RF-030 y RF-050 (cancelación por prioridad institucional con aviso al afectado). |
| Criterios de aceptación (12) | CA1. El propietario cancela con ≥ 5 caracteres y el administrador con ≥ 20 (CP-005). CA2. Solo el propietario y el administrador del sistema del ámbito pueden cancelar (CP-033). CA3. Cancelar o vencer libera todos los recursos en la misma transacción. CA4. La tarea de vencimiento no afecta préstamos dentro de la tolerancia (CP-024). |
