# CU-014 · Consultar préstamos e historial personal

Formato CU-01 (MD Anexo D) · Versión 1.0 · 25/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-014 Consultar préstamos e historial personal |
| Objetivo (2) | Que cada actor vea los préstamos de su ámbito: el usuario final, los suyos; el operador, los de su área o de toda la institución. |
| Actores (3) | Principales: usuario final, administrador del sistema (con o sin área) y asistente con área. Secundario: administrador institucional (solo comprobantes). |
| Disparador (4) | "Mis préstamos" (`/mis-prestamos`), "Préstamos" (`/prestamos`), detalle o comprobante (`/prestamos/{id}`), "Exportar CSV". |
| Precondiciones (5) | P1. Sesión iniciada. |
| Postcondiciones (6) | Q1. Lista paginada y filtrada según el rol; ninguna escritura. |
| Flujo principal (7) | **Usuario final:** 1. Ve "Activos y próximos" e "Historial" con folio, estado, recursos, periodo e indicaciones (dónde recoger, tolerancia, devolución atrasada), el estado de su cuenta y sus estadísticas de 6 meses. **Operador:** 2. Ve pestañas por estado con contadores, busca por folio o solicitante y filtra por área y fecha. 3. Al elegir una fila se abre el panel de detalle con datos, recursos, motivo y acciones según el estado. 4. "Exportar CSV" descarga los préstamos filtrados (UTF-8 con BOM). **Comprobante:** 5. Cualquier actor autorizado abre el detalle imprimible. |
| Flujos alternos (8) | A1. Sin resultados → estado vacío. A2. El parámetro `p` apunta a un préstamo fuera del ámbito → la lista se muestra sin panel de detalle. |
| Excepciones (9) | E1. Usuario final que abre `/prestamos` → 403. E2. Comprobante de otra institución → 404; de otra área → 403. |
| Reglas relacionadas (10) | RS-006; MD 13.1 (mínimo privilegio). |
| Requisitos relacionados (11) | RF-051, RF-058 (exportación), RF-018. |
| Criterios de aceptación (12) | CA1. El contador del menú, la lista, el panel y el CSV de un responsable o asistente de área solo incluyen préstamos cuyos recursos son todos de su área (CP-032). CA2. El CSV antepone `'` a los valores que empiezan con `=`, `+`, `-`, `@`, tabulador o salto de línea (CP-034). CA3. Ningún actor ve préstamos de otra institución (CP-004). CA4. La interfaz no muestra marcas ni sanciones mientras RF-041 a RF-044 no estén implementados (DT-028). |
