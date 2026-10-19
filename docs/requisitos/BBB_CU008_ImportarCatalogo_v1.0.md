# CU-008 · Importar catálogo por CSV

Formato CU-01 (MD Anexo D) · Versión 1.0 · 18/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-008 Importar catálogo por CSV |
| Objetivo (2) | Registrar muchos recursos en una sola operación verificable. |
| Actores (3) | Principal: administrador del sistema. |
| Disparador (4) | "Importar" en "Inventario". |
| Precondiciones (5) | P1. Rol `admin_sistema`. P2. Suscripción `activa`. P3. Áreas creadas. |
| Postcondiciones (6) | Q1. Si todas las filas son válidas, todos los recursos quedan creados en una transacción; si alguna falla, ninguno. Q2. Un registro `IMPORTAR` en `logs_auditoria` con el número de filas. |
| Flujo principal (7) | 1. El sistema muestra el formato: archivo `.csv` UTF-8 separado por comas, máximo 2 MB y 1 000 filas, con el encabezado `nombre_recurso,numero_inventario,numero_serie,tipo_recurso,marca,anio_adquisicion,nombre_area`. 2. El administrador sube el archivo. 3. El sistema valida cada fila: campos obligatorios, tipo permitido, año, área existente y activa de la institución, inventario y serie únicos en el archivo y en la BD. 4. Valida que el total no supere `limite_recursos`. 5. Inserta todo en una transacción, audita y muestra "Se importaron N recursos correctamente." |
| Flujos alternos (8) | A1. El responsable de un área solo puede importar filas de su área. |
| Excepciones (9) | E1. Encabezado distinto → "El encabezado del archivo no es válido." E2. Filas inválidas → lista "Fila N: motivo" y no se inserta nada. E3. Se excede el límite → "El plan contratado no permite más recursos." E4. Archivo vacío, mayor a 2 MB o de otro tipo → mensaje de validación. |
| Reglas relacionadas (10) | RN-010, RN-011. |
| Requisitos relacionados (11) | RF-012, RNF-005, RS-006. |
| Criterios de aceptación (12) | CA1. Un archivo válido de N filas crea exactamente N recursos. CA2. Un archivo con una sola fila inválida no crea ningún recurso y reporta el número de fila. CA3. Los inventarios repetidos dentro del archivo se detectan. CA4. Un área de otra institución se reporta como inexistente. CA5. Queda un registro `IMPORTAR`. |
