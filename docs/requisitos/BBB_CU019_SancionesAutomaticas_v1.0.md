# CU-019 · Aplicar sanciones automáticas y configurar la matriz

Formato CU-01 (MD Anexo D) · Versión 1.0 · 27/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-019 Aplicar sanciones automáticas y configurar la matriz |
| Objetivo (2) | Registrar marcas por incumplimiento al cerrar un préstamo, bloquear al usuario al alcanzar el umbral y avisarle (RF-041 a RF-043, RF-045). |
| Actores (3) | Sistema (al confirmar devolución o cierre), administrador del sistema (matriz y cierre "no devuelto"), usuario final (recibe el aviso). |
| Disparador (4) | Devolución confirmada en mostrador; "Reportar pérdida" sobre un préstamo atrasado; "Sanciones" → "Ver matriz". |
| Precondiciones (5) | P1. Hubo entrega (RN-003: una reserva vencida sin escaneo no genera marca). P2. Regla activa del tipo de incidente en la matriz de la institución. |
| Postcondiciones (6) | Q1. Una `penalizacion` por tipo de incidente y préstamo; el trigger suma las marcas a `usuarios.marcas_acumuladas`. Q2. Al alcanzar el umbral: `bloqueado = 1` hasta `fecha_fin_bloqueo`; eventos `SANCIONAR` y `BLOQUEAR`. Q3. Aviso en el sistema y por correo (RF-045). Q4. Al vencer el plazo, `spei:levantar-sanciones` desbloquea y reinicia el contador. |
| Flujo principal (7) | 1. Al confirmar la devolución, el sistema detecta incidentes: retraso (devolución real posterior al fin), mal estado, dañado o no devuelto. 2. Por cada tipo con regla activa crea la penalización con las marcas de la regla. 3. Si las marcas alcanzan el umbral, bloquea por los días de la regla. 4. Avisa al usuario con el motivo, las marcas y, si aplica, la fecha de desbloqueo. |
| Flujos alternos (8) | A1. **No devuelto:** el administrador del sistema del área cierra un préstamo atrasado con justificación ≥ 20; el préstamo queda `devuelto` con condición `no_entregado`, sus recursos en mantenimiento y se aplica la sanción. A2. **Matriz:** el administrador sin área ajusta marcas (0–10), umbral (1–20), días (1–90) y estado por tipo; la matriz se crea con los valores de `config/spei.php` la primera vez. |
| Excepciones (9) | E1. Regla inactiva o con 0 marcas → sin penalización. E2. Cierre "no devuelto" de un préstamo no atrasado → rechazo. E3. Asistente → 403 en el cierre. E4. Responsable de área en la matriz → 403. E5. Usuario bloqueado que solicita → "Su cuenta no puede solicitar préstamos". |
| Reglas relacionadas (10) | RN-003, RN-005; DT-036. |
| Requisitos relacionados (11) | RF-041, RF-042, RF-043, RF-045, RF-051 (las sanciones aparecen en "Mis préstamos"). |
| Criterios de aceptación (12) | CA1. Devolver tarde y dañado genera dos penalizaciones y bloquea al llegar a 3 marcas (CP-045). CA2. Una reserva vencida no genera marca y el bloqueo se levanta al cumplirse (CP-046). CA3. "No devuelto" solo para atrasados y solo por el administrador del sistema (CP-048). CA4. La matriz es propia de cada institución y una regla inactiva no sanciona (CP-049). |
