# CU-012 · Entregar y devolver recursos con código QR

Formato CU-01 (MD Anexo D) · Versión 1.0 · 25/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-012 Entregar y devolver recursos con código QR |
| Objetivo (2) | Transferir la custodia de los recursos al solicitante y de regreso a la institución con un código de un solo uso que solo el propietario puede generar. |
| Actores (3) | Principales: usuario final propietario del préstamo y operador de mostrador (administrador del sistema o asistente con área asignada). |
| Disparador (4) | El usuario pulsa "Mostrar QR" en "Mis préstamos"; el operador escanea o captura el código en "Mostrador" (`/mostrador`). |
| Precondiciones (5) | P1. Préstamo `pendiente` (entrega) o `activo` (devolución). P2. Para la entrega: hora actual entre el inicio y el inicio + tolerancia (15 a 20 min) y antes del fin. P3. El operador atiende el área de todos los recursos del préstamo. |
| Postcondiciones (6) | **Entrega:** préstamo `activo`, `fecha_hora_entrega_real`, recursos `prestado`, condición de entrega registrada. **Devolución:** préstamo `devuelto`, `fecha_hora_devolucion_real`, recursos `disponible` (o `mantenimiento` si la condición es `malo` o `danado`), condición e incidencia registradas. En ambos: código marcado como usado, evento `ENTREGA` o `DEVOLUCION` en auditoría y notificación al solicitante. |
| Flujo principal (7) | 1. El usuario pulsa "Mostrar QR". 2. El sistema revoca los códigos anteriores, genera uno nuevo de 128 bits, guarda solo su hash SHA-256 con vigencia de 30 a 60 s y devuelve el SVG con `Cache-Control: no-store`. 3. La ventana muestra el QR, el código y la cuenta regresiva. 4. El operador escanea con la cámara o captura el código; se envía por POST. 5. El sistema valida el código sin consumirlo y muestra solicitante, recursos y periodo (respuesta `no-store`). 6. El operador elige la condición (y la incidencia en devolución) y confirma. 7. El sistema revalida bajo lock y transacción, aplica las postcondiciones y regresa al mostrador con "Entrega confirmada correctamente." o "Devolución confirmada correctamente." 8. El comprobante queda disponible en el detalle del préstamo. |
| Flujos alternos (8) | A1. El código expira → la ventana ofrece "Generar nuevo". A2. Sin JavaScript, "Mostrar QR" abre una página con el código. A3. El navegador no permite la cámara → captura manual. |
| Excepciones (9) | E1. Código inexistente, expirado, usado o de otro tipo → "El código no es válido, ya se utilizó o ha caducado." E2. Préstamo de un área que el operador no atiende → "El préstamo pertenece a un área que usted no atiende." y la confirmación responde 403. E3. Préstamo de otra institución → el código no se encuentra. E4. Entrega antes del inicio o fuera de tolerancia → rechazo. E5. El estado del inventario cambió fuera del préstamo → rechazo sin cambios. E6. Un usuario distinto del propietario pide el QR → 403. |
| Reglas relacionadas (10) | RN-004 (custodia), RN-007 (tolerancia), RS-006; DT-029 (BaconQrCode). |
| Requisitos relacionados (11) | RF-034, RF-035, RF-036, RF-037, RF-039. Pendiente: RF-038 (modo sin conexión). |
| Criterios de aceptación (12) | CA1. Un código solo sirve una vez y solo para su tipo de movimiento (CP-002, CP-003). CA2. Generar un código nuevo invalida el anterior. CA3. El código en claro nunca se guarda ni se registra en auditoría. CA4. Un asistente o responsable de área no valida ni confirma préstamos de otra área (CP-032). CA5. Un recurso devuelto `malo` o `danado` pasa a `mantenimiento`. |
