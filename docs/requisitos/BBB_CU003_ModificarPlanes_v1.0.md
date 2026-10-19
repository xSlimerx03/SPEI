# CU-003 · Modificar parámetros de planes

Formato CU-01 (MD Anexo D) · Versión 1.0 · 18/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-003 Modificar parámetros de planes |
| Objetivo (2) | El dueño del SaaS ajusta nombre, precio, límites, duración y disponibilidad de cada plan. |
| Actores (3) | Principal: superadmin. |
| Disparador (4) | El superadmin abre "Planes" y edita uno. |
| Precondiciones (5) | P1. Sesión de superadmin. P2. El plan existe. |
| Postcondiciones (6) | Q1. El plan queda actualizado. Q2. Las instituciones con ese plan reciben los nuevos límites. Q3. Se registra `ACTUALIZAR` en `logs_auditoria` con el estado anterior y el nuevo, e `id_institucion` nulo (DT-024). |
| Flujo principal (7) | 1. El sistema lista los planes. 2. El superadmin selecciona "Editar". 3. Captura nombre, monto mensual, límite de áreas, límite de recursos, duración en días y si está activo. 4. El sistema valida, guarda en una transacción, propaga los límites y audita. 5. Muestra "Plan actualizado correctamente." |
| Flujos alternos (8) | A1. Desactivar un plan: deja de ofrecerse en la contratación; las instituciones que ya lo tienen lo conservan. |
| Excepciones (9) | E1. Valores negativos o límites en cero → mensaje de validación. E2. Usuario sin rol superadmin → 403. |
| Reglas relacionadas (10) | RN-010. |
| Requisitos relacionados (11) | RF-007, RNF-005; DT-011, DT-024. |
| Criterios de aceptación (12) | CA1. Solo el superadmin accede a la edición (otros roles: 403). CA2. El cambio de límites se refleja en las instituciones del plan. CA3. Queda un registro de auditoría con el detalle anterior y el nuevo. CA4. Un plan inactivo no aparece en `/contratar`. |
