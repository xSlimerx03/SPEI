# CU-004 · Invitar administrador del sistema

Formato CU-01 (MD Anexo D) · Versión 1.0 · 18/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-004 Invitar administrador del sistema |
| Objetivo (2) | El administrador institucional da de alta a quienes operarán áreas y recursos, sin conocer ni fijar su contraseña. |
| Actores (3) | Principal: administrador institucional. Secundarios: administrador del sistema invitado; sistema de correo. |
| Disparador (4) | El administrador institucional selecciona "Invitar" en "Administradores". |
| Precondiciones (5) | P1. Sesión de administrador institucional. P2. Suscripción `activa`. |
| Postcondiciones (6) | Q1. Existe un usuario `admin_sistema` inactivo de la misma institución. Q2. Se envió un correo con enlace firmado de 72 h. Q3. Al completar el registro la cuenta queda activa con la contraseña definida por el invitado. Q4. Se auditan `CREAR` y `COMPLETAR_REGISTRO`. |
| Flujo principal (7) | 1. Captura nombre completo, correo institucional y, de forma opcional, el área que tendrá a su cargo. 2. El sistema valida que el correo pertenezca al dominio de la institución y que no exista. 3. Crea el usuario inactivo con contraseña aleatoria y encola el correo de invitación. 4. El invitado abre el enlace, define su contraseña y la confirma. 5. El sistema activa la cuenta y lo dirige al inicio de sesión con "Registro completado. Ya puede iniciar sesión." |
| Flujos alternos (8) | A1. Reenviar la invitación a un usuario aún inactivo genera un enlace nuevo. |
| Excepciones (9) | E1. Correo fuera del dominio → "El correo debe pertenecer al dominio de la institución." E2. Correo existente → "El correo ya está registrado." E3. Enlace vencido o alterado → 403 "El enlace no es válido o ha caducado." E4. Cuenta ya activa → "El registro ya fue completado." E5. Área de otra institución → "Seleccione un área válida." |
| Reglas relacionadas (10) | RN-001. |
| Requisitos relacionados (11) | RF-008 (antecedente de RF-019), RNF-001, RNF-005, RS-006; DT-025. |
| Criterios de aceptación (12) | CA1. Un usuario invitado no puede iniciar sesión antes de completar el registro. CA2. El enlace caduca a las 72 h y no funciona con la firma alterada. CA3. Tras completar el registro, el usuario inicia sesión con rol `admin_sistema`. CA4. Solo el administrador institucional invita; otros roles reciben 403. CA5. No se puede asignar un área de otra institución. |
