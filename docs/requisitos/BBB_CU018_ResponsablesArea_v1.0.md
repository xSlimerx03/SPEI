# CU-018 · Gestionar responsables de área

Formato CU-01 (MD Anexo D) · Versión 1.0 · 27/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-018 Gestionar responsables de área |
| Objetivo (2) | Asignar a cada área un administrador del sistema responsable, cambiarlo o darlo de baja (RF-019). |
| Actores (3) | Administrador institucional. |
| Disparador (4) | "Administradores" → "Cambiar área", "Dar baja" o "Reactivar". |
| Precondiciones (5) | P1. El administrador del sistema pertenece a la institución. |
| Postcondiciones (6) | Q1. `id_area_asignada` actualizado (nulo = toda la institución); evento `ACTUALIZAR`. Q2. Baja o reactivación con evento `DAR_BAJA` / `REACTIVAR`. |
| Flujo principal (7) | 1. El administrador institucional elige un administrador del sistema. 2. Asigna un área activa o "Todas las áreas". 3. El sistema valida que el área no tenga otro responsable activo o con invitación pendiente y guarda. |
| Flujos alternos (8) | A1. Invitación pendiente: se reenvía, no se da de baja. A2. Al invitar un administrador con área (CU-004) se aplica la misma validación de unicidad. |
| Excepciones (9) | E1. El área ya tiene responsable → "El área ya tiene un responsable asignado." E2. Dar de baja al último administrador sin área → rechazo. E3. Reactivar a un responsable cuya área ya tiene otro → rechazo. E4. Cuenta que no es administrador del sistema → 404. |
| Reglas relacionadas (10) | MBD Tabla 7 (RF-019: "validación de unicidad"); MD 13.1. |
| Requisitos relacionados (11) | RF-008, RF-019, RS-006. |
| Criterios de aceptación (12) | CA1. Un área nunca tiene dos responsables vigentes (CP-043). CA2. La institución conserva al menos un administrador del sistema sin área (CP-043). |
