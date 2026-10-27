# CU-017 · Gestionar rol, área, baja y delegación a asistentes

Formato CU-01 (MD Anexo D) · Versión 1.0 · 27/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-017 Gestionar rol, área, baja y delegación a asistentes |
| Objetivo (2) | Mantener el control de permisos por rol (RF-018), registrar a los asistentes de mostrador (RF-020) y delegarles la operación de un área (RF-021). |
| Actores (3) | Administrador del sistema sin área (alumnos, docentes y asistentes) y responsable de área (solo asistentes de su área). |
| Disparador (4) | "Usuarios" → "Editar", "Dar baja" o "Reactivar". |
| Precondiciones (5) | P1. La cuenta pertenece a la institución y es gestionable por el actor. |
| Postcondiciones (6) | Q1. Rol y área actualizados; evento `ACTUALIZAR`, o `DELEGAR` cuando se asigna o mueve un asistente a un área (RF-021). Q2. Baja: `activo = 0` y la sesión abierta se cierra en la siguiente petición; evento `DAR_BAJA`. Q3. Reactivación: `activo = 1`; evento `REACTIVAR`. |
| Flujo principal (7) | 1. El actor abre la cuenta. 2. Elige rol (alumno, docente o asistente) y, si es asistente, el área de su mostrador. 3. El sistema valida y guarda. 4. Para dar de baja, confirma; el sistema verifica que no haya préstamos pendientes o activos. |
| Flujos alternos (8) | A1. Invitación pendiente: no se da de baja ni se reactiva; se reenvía la invitación. A2. Cambio de asistente a usuario final: el área se borra. |
| Excepciones (9) | E1. Rol administrativo → no se asigna aquí (CU-004, CU-018). E2. Cuenta con préstamos abiertos → "ciérrelos antes". E3. Cuenta de otra área para un responsable, o de otra institución → 404. E4. Enlace de invitación viejo de una cuenta dada de baja → "El registro ya fue completado" (DT-035). |
| Reglas relacionadas (10) | RN-001, MD 13.1 (mínimo privilegio); DT-035. |
| Requisitos relacionados (11) | RF-018, RF-020, RF-021, RS-006. |
| Criterios de aceptación (12) | CA1. La delegación queda en auditoría como `DELEGAR` (CP-040). CA2. Una cuenta dada de baja no inicia sesión (CP-040). CA3. No se da de baja una cuenta con préstamos abiertos (CP-040). CA4. Una invitación vieja no reactiva una baja (CP-044). |
