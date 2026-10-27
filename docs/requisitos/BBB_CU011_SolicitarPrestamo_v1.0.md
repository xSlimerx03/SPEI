# CU-011 · Solicitar préstamo

Formato CU-01 (MD Anexo D) · Versión 1.1 · 27/10/2026 (reservas por periodo y RN-008, DT-033) · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-011 Solicitar préstamo |
| Objetivo (2) | El usuario final reserva de uno a diez recursos de una misma área para un día y horario sin que nadie más pueda reservarlos en un periodo que se traslape. |
| Actores (3) | Principal: usuario final (alumno o docente). Secundarios: administradores del sistema del área (reciben aviso). |
| Disparador (4) | "Solicitar préstamo" desde el catálogo, el detalle de un recurso o el menú "Nueva solicitud" (`/prestamos/nueva`). |
| Precondiciones (5) | P1. Sesión iniciada como alumno o docente, cuenta activa y no bloqueada. P2. Institución en estado `activa` (en `solo_lectura` la escritura se rechaza, RN-011). P3. Existen recursos `disponible`, activos y de áreas activas (RN-002). |
| Postcondiciones (6) | Q1. Préstamo en estado `pendiente` con folio `PR-AAAA-NNNN`. Q2. Los recursos quedan reservados para el periodo; pasan a `bloqueado` cuando empieza la ventana de recogida (de inmediato si el inicio ya llegó). Q3. Evento `SOLICITAR_PRESTAMO` en `logs_auditoria`. Q4. Notificación al solicitante y a los administradores del sistema del área. |
| Flujo principal (7) | **Paso 1 · Recursos:** 1. El sistema agrupa por área los recursos elegibles y permite filtrarlos por nombre o inventario. 2. El usuario marca de uno a diez. **Paso 2 · Fecha y horario:** 3. Elige el día (hoy a 60 días) en el calendario y el horario de inicio y fin (7:00 a 22:00) y escribe el motivo (5 a 1000 caracteres). **Paso 3 · Confirmación:** 4. Revisa el resumen, acepta el reglamento y envía. 5. El sistema valida en el servidor, toma el lock Redis de la institución, bloquea las filas con `lockForUpdate`, busca reservas que se traslapen, aplica RN-008 si son concurrentes (DT-033) y registra el préstamo (Q1–Q4). 6. Redirige a "Mis préstamos" con el aviso "Solicitud PR-… registrada correctamente". |
| Flujos alternos (8) | A1. Sin JavaScript: el formulario muestra los tres pasos a la vez y se envía igual. A2. El usuario regresa a un paso anterior con "Regresar"; la selección se conserva. A3. Errores de validación: el formulario vuelve al paso con el error y conserva lo capturado. |
| Excepciones (9) | E1. Sin recursos, más de diez o de áreas distintas → "Seleccione entre uno y diez recursos de una misma área." E2. Fin no posterior al inicio, fuera de 7:00–22:00 o en el pasado → error en el campo. E3. Sin aceptar el reglamento → "Debe aceptar el reglamento de préstamo." E4. El periodo choca con una reserva existente → "Uno de los recursos ya está reservado en ese horario." Si la reserva en conflicto es pendiente, se creó dentro de la ventana de concurrencia y su titular tiene menor prioridad (docente > alumno; mismo rol, más marcas), se cancela con aviso y se registra la nueva (RN-008). E5. Lock ocupado más de 5 s → "Hay otra operación en curso. Intente de nuevo en unos segundos." E6. Cuenta bloqueada o inactiva → rechazo sin crear el préstamo. E7. Rol distinto de alumno o docente → 403. |
| Reglas relacionadas (10) | RN-002, RN-004, RN-008, RN-011; MD 5.6 (transacción + `lockForUpdate` + lock Redis + periodos incompatibles). |
| Requisitos relacionados (11) | RF-022, RF-023, RF-024, RF-025, RF-026, RF-027, RS-006. El diálogo previo (RF-033) está en CU-022. |
| Criterios de aceptación (12) | CA1. Ningún préstamo se crea si falla cualquier validación (CP-006 a CP-010). CA2. Al registrarse, todos los recursos quedan `bloqueado` en la misma transacción (CP-002). CA3. Dos solicitudes simultáneas sobre el mismo recurso no generan dos reservas (CP-036). CA6. Periodos contiguos del mismo recurso se reservan; RN-008 solo desplaza reservas concurrentes de menor prioridad (CP-055). CA4. Un usuario no puede solicitar recursos de otra institución. CA5. El aviso a operadores solo llega a los administradores del sistema del área o sin área asignada. |

**Observación para aclaración (REQ-01):** el calendario del prototipo deshabilita los domingos, pero el
servidor acepta cualquier día dentro de los 60 días. Raúl debe decidir si los domingos son inhábiles
para todas las instituciones (y entonces se valida en el servidor) o si el calendario debe habilitarlos.
