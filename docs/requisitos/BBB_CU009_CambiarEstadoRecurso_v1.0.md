# CU-009 · Cambiar estado de un recurso

Formato CU-01 (MD Anexo D) · Versión 1.0 · 18/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-009 Cambiar estado de un recurso |
| Objetivo (2) | Reflejar en todo momento la situación real del recurso y retirarlo temporalmente por mantenimiento sin perder su historial. |
| Actores (3) | Principal: administrador del sistema. Secundarios: módulos de reservas y entregas (bloques B y C), que cambian a `bloqueado` y `prestado`. |
| Disparador (4) | "Mantenimiento" o "Reactivar" en el inventario. |
| Precondiciones (5) | P1. El recurso pertenece a la institución del actor. P2. Suscripción `activa`. |
| Postcondiciones (6) | Q1. Nuevo estado guardado. Q2. `CAMBIAR_ESTADO` en `logs_auditoria` con el estado anterior y el nuevo. Q3. El catálogo y el inventario muestran el nuevo estado en la siguiente consulta. |
| Flujo principal (7) | 1. El administrador selecciona el recurso. 2. Elige "Mantenimiento" (desde `disponible`) o "Reactivar" (desde `mantenimiento`). 3. El sistema valida la transición, guarda y audita. 4. El inventario muestra la etiqueta de color del estado: disponible (éxito #10B981), bloqueado (advertencia #F59E0B), prestado (información #3B82F6), mantenimiento (error #EF4444). |
| Flujos alternos (8) | Ninguno. |
| Excepciones (9) | E1. Recurso `prestado` o `bloqueado` → "No se puede enviar a mantenimiento un recurso en uso." E2. Transición manual a `prestado` o `bloqueado` → no se ofrece; si se fuerza, 422. E3. Recurso de otra institución → 404. |
| Reglas relacionadas (10) | RN-002, RN-011. |
| Requisitos relacionados (11) | RF-013, RF-014, RNF-002, RNF-005, RS-006. |
| Criterios de aceptación (12) | CA1. `disponible` ↔ `mantenimiento` funciona y se audita. CA2. No se envía a mantenimiento un recurso `prestado` ni `bloqueado`. CA3. Un recurso en mantenimiento desaparece del catálogo en la siguiente consulta. CA4. No se puede fijar manualmente `prestado` ni `bloqueado`. |
