# CU-015 · Consultar auditoría y reportes

Formato CU-01 (MD Anexo D) · Versión 1.0 · 25/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-015 Consultar auditoría y reportes |
| Objetivo (2) | Dar a la administración trazabilidad de las operaciones y métricas de uso del inventario. |
| Actores (3) | Auditoría: administrador del sistema sin área y administrador institucional. Reportes: administrador del sistema (con o sin área). |
| Disparador (4) | "Auditoría" (`/auditoria`), "Exportar CSV" de auditoría, "Reportes" (`/reportes`). |
| Precondiciones (5) | P1. Sesión iniciada con un rol autorizado. |
| Postcondiciones (6) | Q1. Consulta sin modificar datos; `logs_auditoria` es de solo inserción (DT-010). |
| Flujo principal (7) | **Auditoría:** 1. El sistema lista los eventos de la institución (fecha, usuario, acción, tabla, registro y resumen), 25 por página, del más reciente al más antiguo. 2. Filtra por texto (acción, tabla, usuario o folio), acción, tabla y fecha. 3. "Exportar CSV" descarga los eventos filtrados. **Reportes:** 4. El usuario elige el periodo (7, 30 o 90 días) y, si no tiene área asignada, el área. 5. El sistema muestra total de préstamos, recursos más prestados, uso promedio por área, retraso promedio de devoluciones, condición al devolver y cierres sin entrega (cancelados por solicitante, por operador y vencidos). |
| Flujos alternos (8) | A1. Sin eventos o sin préstamos en el periodo → estado vacío. |
| Excepciones (9) | E1. Responsable de un área en auditoría → 403 (la bitácora completa no se expone a una sola área). E2. Usuario final o asistente → 403. E3. Periodo distinto de 7, 30 o 90 → error de validación. |
| Reglas relacionadas (10) | RS-006, DT-010; MD 13.1. |
| Requisitos relacionados (11) | RF-052, RF-053, RF-054, RF-058. Pendientes: RF-055 a RF-057 (otros reportes). |
| Criterios de aceptación (12) | CA1. Ningún evento ni métrica incluye datos de otra institución, aunque la consulta se ejecute al enviar la descarga (filtro explícito por `id_institucion`, CP-035). CA2. El CSV de auditoría neutraliza fórmulas igual que el de préstamos. CA3. Los reportes de un responsable de área solo cuentan préstamos de su área. CA4. La bitácora no puede actualizarse ni borrarse (CP-027). |
