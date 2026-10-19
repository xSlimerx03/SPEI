# CU-005 · Iniciar sesión

Formato CU-01 (MD Anexo D) · Versión 1.0 · 18/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-005 Iniciar sesión |
| Objetivo (2) | Un usuario accede con su correo institucional y obtiene los permisos de su rol, sin poder declararlo. |
| Actores (3) | Principal: cualquier usuario registrado. Secundario: cliente de la API REST. |
| Disparador (4) | Envío del formulario `/iniciar-sesion` o `POST /api/v1/sesiones`. |
| Precondiciones (5) | P1. El usuario existe y está activo. P2. Su institución no está `suspendida` ni `cancelada` (el superadmin no tiene institución). |
| Postcondiciones (6) | Q1. Web: sesión regenerada en Redis. API: token Sanctum con expiración de 24 h. Q2. `INICIAR_SESION` en `logs_auditoria`. |
| Flujo principal (7) | 1. El usuario captura correo y contraseña. 2. El sistema limita a 5 intentos por minuto por correo e IP. 3. Valida credenciales (bcrypt), que la cuenta esté activa, que el dominio del correo coincida con el de su institución (RN-001) y el estado de la suscripción. 4. Inicia la sesión con el rol registrado y redirige al panel. 5. Audita el acceso. |
| Flujos alternos (8) | A1. Cerrar sesión: invalida la sesión o revoca el token y audita `CERRAR_SESION`. |
| Excepciones (9) | E1. Credenciales incorrectas o cuenta inactiva → "Las credenciales no son válidas." (mensaje único para no revelar qué cuentas existen). E2. Más de 5 intentos → 429 "Demasiados intentos. Intente de nuevo en N segundos." E3. Suscripción suspendida o cancelada → "La suscripción de la institución no está disponible." |
| Reglas relacionadas (10) | RN-001, RN-011, RN-013. |
| Requisitos relacionados (11) | RNF-001, RNF-005, RS-006; base de RF-017 y RF-018 (bloque B); DT-007, DT-025. |
| Criterios de aceptación (12) | CA1. Con credenciales válidas se accede al panel según el rol. CA2. Cuenta inactiva o contraseña errónea → el mismo mensaje genérico. CA3. El sexto intento en un minuto responde 429. CA4. El token de API expira a las 24 h. CA5. Institución suspendida → acceso denegado. CA6. Cada inicio de sesión queda auditado. |
