# CU-024 · Consultar y exportar reportes de uso, incidentes y cancelaciones

Formato CU-01 (MD Anexo D) · Versión 1.0 · 27/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-024 Consultar y exportar reportes de uso, incidentes y cancelaciones |
| Objetivo (2) | Completar los reportes operativos: horas de uso por recurso (RF-055), incidentes y sanciones (RF-056), cancelaciones y justificaciones (RF-057) y su exportación (RF-058). |
| Actores (3) | Administrador del sistema (el responsable de un área ve solo su área). |
| Disparador (4) | "Reportes" con periodo (7, 30 o 90 días) y área; "Exportar" en cada sección; "Imprimir". |
| Precondiciones (5) | P1. Sesión de administrador del sistema. |
| Postcondiciones (6) | Q1. Consulta sin cambios; la exportación deja evento `EXPORTAR_REPORTE` (MBD Tabla 7). |
| Flujo principal (7) | 1. El sistema muestra, además de los reportes de CU-015: minutos reales de uso por recurso (entrega → devolución), sanciones por tipo con marcas y exentadas, últimas incidencias, y cancelaciones/vencimientos con su origen (solicitante, operador, prioridad RN-008 o no recogido) y justificación. 2. "Exportar" descarga el CSV UTF-8 de la sección (más prestados, horas, sanciones o cancelaciones) con fórmulas neutralizadas. 3. "Imprimir" genera el PDF desde el navegador (DT-036). |
| Flujos alternos (8) | A1. Sin datos en el periodo → estado vacío. |
| Excepciones (9) | E1. Sección o periodo no válidos → error de validación. E2. Usuario final o asistente → 403. |
| Reglas relacionadas (10) | RS-006; DT-036. |
| Requisitos relacionados (11) | RF-055, RF-056, RF-057, RF-058. |
| Criterios de aceptación (12) | CA1. Las horas, sanciones y cancelaciones corresponden al ámbito y periodo (CP-054). CA2. La exportación queda en auditoría y rechaza secciones desconocidas (CP-054). |
