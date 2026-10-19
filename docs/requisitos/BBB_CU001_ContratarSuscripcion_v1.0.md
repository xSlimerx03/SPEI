# CU-001 · Contratar suscripción institucional

Formato CU-01 (MD Anexo D) · Versión 1.0 · 18/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-001 Contratar suscripción institucional |
| Objetivo (2) | Una institución se da de alta en el SaaS, elige un plan y obtiene su cuenta de administrador institucional lista para operar. |
| Actores (3) | Principal: contratante (futuro administrador institucional). Secundario: sistema. |
| Disparador (4) | El contratante abre la página pública `/contratar` y envía el formulario. |
| Precondiciones (5) | P1. Existe al menos un plan activo en `planes`. P2. El dominio de correo no está registrado por otra institución. P3. El correo no existe en `usuarios`. |
| Postcondiciones (6) | Q1. Existe una fila en `instituciones` con estado `activa`, el plan elegido, `fecha_inicio` = hoy, `fecha_corte` = hoy + `duracion_dias` del plan, y los límites del plan copiados. Q2. Existe un usuario `admin_institucional` activo ligado a la institución. Q3. Se registraron dos `CREAR` en `logs_auditoria` (institución y usuario). Q4. El contratante queda con sesión iniciada. |
| Flujo principal (7) | 1. El sistema muestra los planes activos con su precio y límites. 2. El contratante captura nombre de la institución, dominio de correo, plan, nombre completo, correo institucional, contraseña y confirmación. 3. El sistema valida los datos. 4. En una sola transacción crea la institución y el administrador institucional y registra la auditoría. 5. Inicia la sesión y muestra el panel de la suscripción con el mensaje "Institución registrada correctamente." |
| Flujos alternos (8) | A1. El contratante cambia de plan antes de enviar; el formulario conserva los demás datos. |
| Excepciones (9) | E1. Dominio ya registrado → "El dominio ya pertenece a otra institución." E2. Correo fuera del dominio → "El correo debe pertenecer al dominio de la institución." E3. Correo existente → "El correo ya está registrado." E4. Plan inexistente o inactivo → "Seleccione un plan válido." E5. Contraseña de menos de 10 caracteres o sin letras y números → mensaje de validación. E6. Falla de BD → se revierte la transacción y se muestra "No se pudo registrar la institución. Intente de nuevo." |
| Reglas relacionadas (10) | RN-001, RN-010. |
| Requisitos relacionados (11) | RF-001, RF-002, RNF-001, RNF-005, RS-006; DT-011, DT-024. |
| Criterios de aceptación (12) | CA1. Con datos válidos se crean institución `activa` y administrador `admin_institucional` activo con contraseña bcrypt. CA2. `fecha_corte` = `fecha_inicio` + `duracion_dias`. CA3. `limite_areas` y `limite_recursos` coinciden con el plan. CA4. Un dominio repetido, un correo fuera del dominio o un correo repetido se rechazan sin crear registros. CA5. Se generan dos registros `CREAR` en `logs_auditoria`. |
