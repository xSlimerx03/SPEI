# CU-023 · Comprimir y depurar el historial

Formato CU-01 (MD Anexo D) · Versión 1.0 · 27/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-023 Comprimir y depurar el historial |
| Objetivo (2) | Cumplir el ciclo de vida del historial: comprimir al año y eliminar a los cuatro, conservando la bitácora mínima (RN-012, RF-052). |
| Actores (3) | Sistema (`spei:comprimir-historial`, diario a las 02:30). |
| Disparador (4) | Tarea programada. |
| Precondiciones (5) | P1. Disco de almacenamiento configurado (`SPEI_HISTORIAL_DISCO`, local en desarrollo; S3/MinIO en producción). |
| Postcondiciones (6) | Q1. Un archivo `historial/{institución}/{AAAA-MM}.json.gz` por institución y mes con más de 12 meses. Q2. Registro en `historial_comprimido` con eliminación programada a periodo_fin + 3 años. Q3. Préstamos del mes eliminados (cascada de detalles, tokens y mensajes; las penalizaciones conservan la sanción con `id_prestamo` nulo). Q4. Evento `COMPRIMIR_HISTORIAL`; `logs_auditoria` se conserva. |
| Flujo principal (7) | 1. La tarea agrupa por institución y mes los préstamos cerrados con fin anterior al corte. 2. Escribe el archivo comprimido. 3. En una transacción registra el archivo y elimina las filas. 4. Elimina los archivos cuya fecha programada llegó y marca `eliminado = 1` (evento `ELIMINAR_HISTORIAL`). |
| Flujos alternos (8) | A1. Mes ya comprimido → se omite (idempotente). |
| Excepciones (9) | E1. Falla de la transacción → se borra el archivo escrito y se reintenta en la siguiente ejecución. |
| Reglas relacionadas (10) | RN-012; DT-010 (bitácora inalterable). |
| Requisitos relacionados (11) | RF-052, RS-006. |
| Criterios de aceptación (12) | CA1. A los 14 meses el préstamo se comprime, el archivo contiene sus datos y la sanción se conserva (CP-053). CA2. Volver a ejecutar no duplica (CP-053). CA3. A los cuatro años el archivo se elimina (CP-053). |
