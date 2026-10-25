# CU-011 · Solicitar préstamo

Formato CU-01 (MD Anexo D) · Versión 1.0 · 25/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-011 Solicitar préstamo |
| Objetivo (2) | El usuario final reserva de uno a diez recursos de una misma área para un día y horario, y el sistema los bloquea para que nadie más los tome. |
| Actores (3) | Principal: usuario final (alumno o docente). Secundarios: administradores del sistema del área (reciben aviso). |
| Disparador (4) | "Solicitar préstamo" desde el catálogo, el detalle de un recurso o el menú "Nueva solicitud" (`/prestamos/nueva`). |
| Precondiciones (5) | P1. Sesión iniciada como alumno o docente, cuenta activa y no bloqueada. P2. Institución en estado `activa` (en `solo_lectura` la escritura se rechaza, RN-011). P3. Existen recursos `disponible`, activos y de áreas activas (RN-002). |
| Postcondiciones (6) | Q1. Préstamo en estado `pendiente` con folio `PR-AAAA-NNNN`. Q2. Cada recurso pasa a `bloqueado`. Q3. Evento `SOLICITAR_PRESTAMO` en `logs_auditoria`. Q4. Notificación al solicitante y a los administradores del sistema del área. |
| Flujo principal (7) | **Paso 1 · Recursos:** 1. El sistema agrupa por área los recursos elegibles y permite filtrarlos por nombre o inventario. 2. El usuario marca de uno a diez. **Paso 2 · Fecha y horario:** 3. Elige el día (hoy a 60 días) en el calendario y el horario de inicio y fin (7:00 a 22:00) y escribe el motivo (5 a 1000 caracteres). **Paso 3 · Confirmación:** 4. Revisa el resumen, acepta el reglamento y envía. 5. El sistema valida en el servidor, toma el lock Redis de la institución, bloquea las filas con `lockForUpdate`, revalida disponibilidad y registra el préstamo (Q1–Q4). 6. Redirige a "Mis préstamos" con el aviso "Solicitud PR-… registrada correctamente". |
| Flujos alternos (8) | A1. Sin JavaScript: el formulario muestra los tres pasos a la vez y se envía igual. A2. El usuario regresa a un paso anterior con "Regresar"; la selección se conserva. A3. Errores de validación: el formulario vuelve al paso con el error y conserva lo capturado. |
| Excepciones (9) | E1. Sin recursos, más de diez o de áreas distintas → "Seleccione entre uno y diez recursos de una misma área." E2. Fin no posterior al inicio, fuera de 7:00–22:00 o en el pasado → error en el campo. E3. Sin aceptar el reglamento → "Debe aceptar el reglamento de préstamo." E4. Otro usuario reservó el recurso entre la consulta y el envío → "Uno de los recursos ya no está disponible. Actualice el catálogo." E5. Lock ocupado más de 5 s → "Hay otra operación en curso. Intente de nuevo en unos segundos." E6. Cuenta bloqueada o inactiva → rechazo sin crear el préstamo. E7. Rol distinto de alumno o docente → 403. |
| Reglas relacionadas (10) | RN-002, RN-004, RN-011; MD 5.6 (transacción + `lockForUpdate` + lock Redis). |
| Requisitos relacionados (11) | RF-022, RF-023, RF-024, RF-025, RF-026, RS-006. Pendientes de bloque posterior: RF-027 (prioridad RN-008 ante empate), RF-033 (diálogo previo). |
| Criterios de aceptación (12) | CA1. Ningún préstamo se crea si falla cualquier validación (CP-006 a CP-010). CA2. Al registrarse, todos los recursos quedan `bloqueado` en la misma transacción (CP-002). CA3. Dos solicitudes simultáneas sobre el mismo recurso no generan dos reservas. CA4. Un usuario no puede solicitar recursos de otra institución. CA5. El aviso a operadores solo llega a los administradores del sistema del área o sin área asignada. |

**Observación para aclaración (REQ-01):** el calendario del prototipo deshabilita los domingos, pero el
servidor acepta cualquier día dentro de los 60 días. Raúl debe decidir si los domingos son inhábiles
para todas las instituciones (y entonces se valida en el servidor) o si el calendario debe habilitarlos.
