# CU-016 · Dar de alta usuarios por invitación o CSV y definir reglas de dominio

Formato CU-01 (MD Anexo D) · Versión 1.0 · 27/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-016 Dar de alta usuarios por invitación o CSV y definir reglas de dominio |
| Objetivo (2) | Que la institución incorpore alumnos, docentes y asistentes cuyo rol se determine por el dominio del correo o por asignación registrada, nunca autodeclarado (RN-001, MD 5.4). |
| Actores (3) | Principal: administrador del sistema sin área (todas las altas y reglas) y responsable de área (solo asistentes de su área). Secundario: persona invitada. |
| Disparador (4) | "Usuarios" → "Invitar", "Importar CSV" o "Ver dominios" (`/usuarios`, `/dominios`). |
| Precondiciones (5) | P1. Sesión de administrador del sistema. P2. Institución `activa` (RN-011). P3. Para asistentes, área activa. |
| Postcondiciones (6) | Q1. Cuenta inactiva sin `fecha_registro` (invitación pendiente) con su rol y área. Q2. Correo de invitación encolado con enlace firmado de 72 h atado a la contraseña vigente (DT-035). Q3. Evento `CREAR` o `IMPORTAR` en auditoría. Q4. Al completar el registro, la cuenta queda activa con `fecha_registro`. |
| Flujo principal (7) | **Reglas:** 1. El administrador registra reglas `dominio → alumno/docente` (dominio contratado o subdominio). **Alta individual:** 2. Captura nombre, correo y, opcionalmente, rol y área. 3. El sistema valida que el correo sea del dominio o de un subdominio y no esté registrado (DT-012); sin rol, aplica la regla más específica. 4. Crea la cuenta y envía la invitación. **CSV:** 5. Sube un archivo con `correo_institucional,nombre_completo,rol,nombre_area`. 6. El sistema valida todas las filas (formato, dominio, rol o regla, área, duplicados en archivo y en BD) y crea todas o ninguna; envía una invitación por cuenta. |
| Flujos alternos (8) | A1. Responsable de área: el rol queda fijo en asistente y el área en la suya. A2. Invitación pendiente: "Reenviar" genera un enlace nuevo. A3. Archivo con BOM o líneas vacías: se aceptan. |
| Excepciones (9) | E1. Correo de otro dominio → "El correo no pertenece al dominio…". E2. Sin rol y sin regla aplicable → "indique el rol". E3. Asistente sin área activa → rechazo. E4. CSV con errores → lista "Fila N: motivo" y nada se importa. E5. Encabezado distinto → rechazo. E6. Regla con dominio ajeno o rol distinto de alumno/docente → rechazo. E7. Responsable de área en reglas de dominio → 403. |
| Reglas relacionadas (10) | RN-001, RN-011; DT-012, DT-025, DT-034, DT-035. |
| Requisitos relacionados (11) | RF-016, RF-017, RF-020, RS-006. |
| Criterios de aceptación (12) | CA1. El rol de un alumno de `@alumnos.<dominio>` sale de la regla más específica (CP-037). CA2. Un CSV con una fila inválida no crea ninguna cuenta (CP-039). CA3. Un correo de otro dominio nunca se registra (CP-038). CA4. Un responsable de área solo crea asistentes de su área (CP-041). CA5. Ninguna cuenta ni regla de otra institución es visible o editable (CP-042). |
