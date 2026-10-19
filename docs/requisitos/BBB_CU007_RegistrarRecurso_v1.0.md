# CU-007 · Registrar recurso

Formato CU-01 (MD Anexo D) · Versión 1.0 · 18/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-007 Registrar recurso |
| Objetivo (2) | Dar de alta y mantener equipos y espacios prestables con identificador único, capturado a mano o con lector de código de barras. |
| Actores (3) | Principal: administrador del sistema (global o responsable de un área). |
| Disparador (4) | "Crear" en "Inventario" o lectura de un código en "Buscar código". |
| Precondiciones (5) | P1. Rol `admin_sistema`. P2. Existe al menos un área activa. P3. Suscripción `activa` para escribir. |
| Postcondiciones (6) | Q1. Recurso guardado con el `id_institucion` del actor, estado `disponible` o `mantenimiento` y `activo = TRUE`. Q2. Auditoría `CREAR` o `ACTUALIZAR`. |
| Flujo principal (7) | 1. Captura área, nombre, tipo (`equipo_fisico` o `espacio`), número de inventario, número de serie (opcional), marca (opcional), año de adquisición (opcional) y estado inicial. 2. El sistema valida el límite del plan, la unicidad del inventario y de la serie en la institución, y que el área sea activa y de la misma institución. 3. Guarda y audita. **Código de barras (RF-011):** 4. En "Buscar código" el lector escribe el código y envía Enter. 5. Si el código ya existe en la institución (como inventario o serie), el sistema abre ese recurso; si no, abre el alta con el número de inventario precargado. API: `GET /api/v1/recursos/codigo/{codigo}`. |
| Flujos alternos (8) | A1. Editar un recurso con las mismas validaciones (el estado se cambia en CU-009). A2. Desactivar: `activo = FALSE` y deja de mostrarse en el catálogo. |
| Excepciones (9) | E1. Límite alcanzado → "El plan contratado no permite más recursos." E2. Inventario o serie repetidos → "El número de inventario ya está registrado." o "El número de serie ya está registrado." E3. Área inactiva o de otra institución → "Seleccione un área válida." E4. Responsable que elige otra área → 403. E5. Código inexistente en la API → 404. E6. Desactivar un recurso `prestado` o `bloqueado` → "No se puede desactivar un recurso en uso." E7. Año fuera del intervalo 1990 – año actual → mensaje de validación. |
| Reglas relacionadas (10) | RN-010, RN-011. |
| Requisitos relacionados (11) | RF-010, RF-011, RNF-005, RS-006; DT-008. |
| Criterios de aceptación (12) | CA1. Inventario y serie únicos por institución y repetibles entre instituciones. CA2. No se supera `limite_recursos`. CA3. La búsqueda por código solo encuentra recursos de la propia institución. CA4. Un responsable de área solo registra en su área. CA5. Cada alta y edición se audita con el detalle anterior y el nuevo. |
