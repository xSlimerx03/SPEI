# CASOS DE PRUEBA — CP-01 · Usuarios, reservas por periodo, sanciones, avisos y reportes

| Campo | Valor |
|---|---|
| Documento | BBB_CP01_UsuariosSancionesReportes_v1.0 |
| Formato | CP-01 (Manual de Desarrollo v1.2, Anexo G, Tabla 36) |
| Versión | 1.0 |
| Fecha | 27/10/2026 |
| Ambiente y versión (4) | Desarrollo · Docker Compose (PHP 8.3, MySQL 8.4 base `spei_testing`, Redis 7) · rama `feature/RF-011-gestion-prestamos` sin etiqueta, código del 27/10/2026 |
| Ejecutor y fecha (12) | Raúl Treviño Licona (ejecución automatizada con Pest 3) · 27/10/2026 |
| Defectos relacionados (13) | Ninguno abierto. Corregidos durante la ejecución: cierres `fn (): void` que devolvían valor (error fatal) en `SancionService::levantarVencidas` y `HistorialService::comprimirMes`; una baja con invitación vieja respondía 403 en lugar de "registro completado" (CP-044). |

**Evidencia general (11):** `docker compose exec -T app ./vendor/bin/pest --coverage --min=80` → 85 pruebas, 774 aserciones,
0 fallidas, cobertura total 94.5 %; Pint `--test` PASS (182 archivos); PHPStan nivel 6 sin errores (27/10/2026). Archivos:
`tests/Feature/UsuariosTest.php`, `tests/Feature/SancionesNotificacionesTest.php`, `tests/Feature/AlcancePrestamosTest.php`.

**Precondiciones comunes (5):** las de `BBB_CP01_InterfazYBloqueA_v1.0` (base migrada y limpia por prueba, reloj fijado en un día
hábil a las 9:00, `MAIL_MAILER=array`, `QUEUE_CONNECTION=sync`), más `Mail::fake()` donde se verifica el correo y el disco
`spei.historial.disco` falso (`Storage::fake`) en CP-053.
**Datos de prueba (6):** factories (`Institucion`, `Area`, `Recurso`, `Usuario`) y CSV generados en la prueba; ningún dato real.

| ID y nombre (1) | Requisito (2) | Tipo (3) | Pasos (7) | Resultado esperado (8) | Resultado obtenido (9) | Estado (10) |
|---|---|---|---|---|---|---|
| CP-037 Rol por dominio | RF-016, RF-017, CU-016 CA1 | Funcional | 1. Registrar `escuela.edu.mx` → docente y `alumnos.escuela.edu.mx` → alumno. 2. Invitar un correo de cada uno sin rol explícito. | Cada cuenta recibe el rol de la regla más específica; invitación enviada por correo. | Igual al esperado. | Aprobado |
| CP-038 Dominio ajeno y rol explícito | RF-016, RF-017, CU-016 CA3 | Validación | 1. Invitar sin rol ni regla. 2. Invitar un correo de otro dominio. 3. Iniciar sesión con correo de subdominio y con dominio externo. | 1: error en `rol`. 2: error en `correo_institucional`, ninguna cuenta. 3: el subdominio entra; el externo se rechaza. | Igual al esperado. | Aprobado |
| CP-039 Importación todo o nada | RF-016, CU-016 CA2 | Funcional | 1. Importar CSV válido de 2 filas. 2. Importar CSV con una fila con rol inválido. | 1: 2 cuentas pendientes con invitación. 2: error con número de fila y ninguna cuenta creada. | Igual al esperado. | Aprobado |
| CP-040 Rol, delegación y baja | RF-018, RF-020, RF-021, CU-017 CA1–CA3 | Funcional | 1. Cambiar alumno → docente. 2. Delegar un asistente a otra área. 3. Dar de baja con préstamo abierto y sin él. 4. Iniciar sesión dado de baja. | Rol cambiado; evento `DELEGAR` en auditoría; baja rechazada con préstamo abierto y aceptada sin él; login rechazado. | Igual al esperado. | Aprobado |
| CP-041 Responsable limitado a su área | RF-019, RF-020, CU-016 CA4 | Seguridad | El responsable de área invita un asistente de su área, uno de otra área y un alumno; abre la edición de un usuario ajeno. | Solo crea el asistente de su área; los demás se rechazan y la edición ajena responde 404. | Igual al esperado. | Aprobado |
| CP-042 Aislamiento de usuarios | RS-006, CU-016 CA5 | Seguridad | Administrador de la institución A lista, edita y elimina usuarios y reglas de dominio de la institución B. | Listado sin datos de B; edición y eliminación 404. | Igual al esperado. | Aprobado |
| CP-043 Un responsable por área | RF-008, RF-019, CU-018 | Funcional | 1. Asignar un segundo responsable al área ocupada. 2. Cambiar el área del responsable. 3. Dar de baja al último administrador general. | 1: rechazado. 2: aceptado. 3: rechazado (se conserva uno sin área). | Igual al esperado. | Aprobado |
| CP-044 Invitación vieja tras la baja | RF-021, CU-017 CA4 | Seguridad | 1. Completar el registro. 2. Dar de baja. 3. Abrir el enlace de invitación original. | Pantalla "registro completado"; la cuenta sigue inactiva y la contraseña no cambia. | Igual al esperado tras la corrección de `RegistroController::validarHuella`. | Aprobado |
| CP-045 Marcas por trigger y bloqueo | RF-041, RF-042, RF-043, RF-047, CU-019 CA1 | Base de datos | 1. Devolver 30 min tarde (1 marca). 2. Devolver con condición `malo` y daño (2 marcas). | `usuarios.marcas_acumuladas` = 3 por los triggers; bloqueo de 7 días al llegar al umbral; aviso en el sistema y por correo. | Igual al esperado. | Aprobado |
| CP-046 Reserva vencida y levantamiento | RN-003, RF-043, CU-019 CA2 | Funcional | 1. Dejar vencer una reserva sin entrega. 2. Avanzar el reloj tras un bloqueo y ejecutar `spei:levantar-sanciones`. | 1: sin penalización. 2: cuenta desbloqueada y marcas en 0. | Igual al esperado. | Aprobado |
| CP-047 Exención | RF-044, CU-020 | Funcional | 1. Exentar con 10 caracteres. 2. Exentar con justificación válida. 3. Responsable de otra área intenta exentar. | 1: error. 2: marcas restadas por el trigger, bloqueo levantado y evento en auditoría. 3: 403. | Igual al esperado. | Aprobado |
| CP-048 Cierre como no devuelto | RF-039, RF-042, CU-019 CA3 | Funcional | 1. Cerrar un préstamo activo en tiempo. 2. Responsable de área cierra uno atrasado. 3. El administrador del sistema cierra uno atrasado. | 1: rechazado. 2: 403. 3: préstamo devuelto, condición `no_entregado`, recurso en mantenimiento y 3 marcas. | Igual al esperado. | Aprobado |
| CP-049 Matriz por institución | RF-041, CU-019 CA4 | Funcional | 1. Cambiar marcas de `retraso` en A. 2. Desactivar la regla y devolver tarde. 3. Consultar la matriz de B. | 1: se guarda solo en A. 2: sin penalización. 3: valores por defecto. | Igual al esperado. | Aprobado |
| CP-050 Recordatorios únicos | RF-045, RF-046, RF-048, CU-021 | Funcional | Ejecutar `spei:enviar-recordatorios` dos veces 10 min antes del inicio y del fin. | Un solo recordatorio de entrega y uno de devolución, en el sistema y por correo. | Igual al esperado. | Aprobado |
| CP-051 Ventana de recogida | RF-025, RF-026, CU-011 | Funcional | 1. Reservar dos periodos consecutivos del mismo recurso. 2. Devolver el primero cuando ya empezó el segundo. | El recurso queda `bloqueado` para la reserva siguiente en lugar de `disponible`. | Igual al esperado. | Aprobado |
| CP-052 Conversación previa | RF-033, CU-022 | Funcional | 1. El solicitante escribe en su préstamo pendiente. 2. El operador del área lo abre y responde. 3. Operador de otra área intenta escribir. | Mensajes visibles a ambos; abrir el detalle marca como leído; 403 al operador ajeno. | Igual al esperado. | Aprobado |
| CP-053 Historial comprimido | RF-052, RN-012, CU-023 | Base de datos | 1. Ejecutar `spei:comprimir-historial` con préstamos de hace 13 meses. 2. Ejecutarlo otra vez. 3. Avanzar cuatro años y ejecutarlo. | 1: archivo `.json.gz` y fila en `historial_comprimido`; préstamos eliminados, mensajes en cascada y penalizaciones con `id_prestamo` nulo. 2: sin duplicados. 3: archivo eliminado y `eliminado = 1`. | Igual al esperado. | Aprobado |
| CP-054 Reportes detallados | RF-055, RF-056, RF-057, RF-058, CU-024 | Funcional | 1. Abrir reportes con préstamos devueltos, sanciones y cancelaciones. 2. Exportar cada sección. 3. Exportar una sección desconocida. | Horas de uso, incidentes y sanciones y cancelaciones con su origen; CSV con los mismos datos y evento `EXPORTAR_REPORTE`; sección desconocida 404. | Igual al esperado. | Aprobado |
| CP-055 Prioridad RN-008 y periodos | RF-027, RN-008, CU-011 CA6 | Concurrencia | 1. Dos alumnos (2 marcas y 0 marcas) reservan periodos traslapados dentro de la ventana. 2. Pasada la ventana, un docente pide un periodo traslapado. 3. El docente pide un periodo contiguo. | 1: gana el alumno sin marcas y la otra reserva se cancela con aviso. 2: rechazo "ya está reservado". 3: se reserva. | Igual al esperado. | Aprobado |
| CP-056 Aviso a operadores del área | RF-049, CU-021 | Funcional | Un alumno registra una solicitud de préstamo con recursos de un área. | Reciben aviso push en el sistema el administrador general y los administradores con acceso a esa área; los administradores de otras áreas no reciben el aviso ni correo. | Igual al esperado en `SancionesNotificacionesTest`. | Aprobado |

## Regresión actualizada

Las reservas por periodo (DT-033) cambiaron dos resultados esperados de `BBB_CP01_InterfazYBloqueA_v1.0`: en CP-002 el recurso
sigue `disponible` hasta su ventana de recogida, y en CP-036 la segunda solicitud se rechaza con "Uno de los recursos ya está
reservado en ese horario." y un docente dentro de la ventana desplaza al alumno. Ambos casos se ejecutaron con el resultado nuevo.
