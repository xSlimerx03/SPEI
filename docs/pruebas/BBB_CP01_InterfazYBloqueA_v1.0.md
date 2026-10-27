# CASOS DE PRUEBA — CP-01 · Interfaz del prototipo hi-fi y bloque A

| Campo | Valor |
|---|---|
| Documento | BBB_CP01_InterfazYBloqueA_v1.0 |
| Formato | CP-01 (Manual de Desarrollo v1.2, Anexo G, Tabla 36) |
| Versión | 1.1 (25/10/2026: CP-032 a CP-035 y evidencia tras las correcciones de RED-01) |
| Fecha | 25/10/2026 |
| Ambiente y versión (4) | Desarrollo · Docker Compose (PHP 8.3, MySQL 8.4 base `spei_testing`, Redis 7) · rama de trabajo sin etiqueta, código del 25/10/2026 |
| Ejecutor y fecha (12) | Raúl Treviño Licona (ejecución automatizada con Pest 3) · 25/10/2026 |
| Defectos relacionados (13) | Ninguno abierto. Durante la ejecución se corrigieron: conversión de `p` a entero en `PrestamoController::index`, nombre aleatorio que provocaba una prueba intermitente y, en v1.1, la exportación de auditoría que dependía solo del Global Scope (CP-035; en producción el contexto sigue activo al enviar la descarga, pero la consulta ahora filtra `id_institucion` de forma explícita). |

**Evidencia general (11):** `docker compose exec -T app ./vendor/bin/pest --coverage --min=80` → 64 pruebas, 536 aserciones, 0 fallidas,
cobertura total 94.2 %; Pint `--test` PASS (142 archivos); PHPStan nivel 6 sin errores (25/10/2026). Archivos:
`tests/Feature/InterfazPrototipoTest.php`, `tests/Feature/AlcancePrestamosTest.php`, `tests/Feature/BloqueATest.php`,
`tests/Feature/PantallasPublicasTest.php`, `tests/Feature/EntornoTest.php`; escenarios compartidos en `tests/Pest.php`.

**Precondiciones comunes (5):** base `spei_testing` migrada y limpia por prueba (`RefreshDatabase`), reloj fijado en un día hábil a las 9:00
(`travelTo`), correo y colas falsos o síncronos (`MAIL_MAILER=array`, `QUEUE_CONNECTION=sync`).
**Datos de prueba (6):** generados con las factories (`Institucion`, `Area`, `Recurso`, `Usuario`, `Plan`); contraseña de prueba
`UsuarioFactory::CONTRASENA_PRUEBA`; ningún dato real.

| ID y nombre (1) | Requisito (2) | Tipo (3) | Pasos (7) | Resultado esperado (8) | Resultado obtenido (9) | Estado (10) |
|---|---|---|---|---|---|---|
| CP-001 Inicio por rol | RF-049, RF-015, CU-005 | Funcional | 1. Autenticar cada rol de tenant. 2. Abrir `/panel` siguiendo redirecciones. | Dashboard (admin. del sistema), suscripción (admin. institucional), mostrador (asistente) o catálogo (alumno y docente) con el layout del prototipo. | Igual al esperado en los 5 roles. | Aprobado |
| CP-002 Recorrido de préstamo | RF-022, RF-034, RF-036, RF-040 | Integración | 1. El alumno abre la nueva solicitud y envía recurso, día, horario, motivo y reglamento. 2. Genera el QR (JSON). 3. El operador valida el código en mostrador y confirma la entrega. 4. El alumno genera el QR de devolución y el operador la confirma. 5. Se abre el comprobante. | Préstamo pendiente → activo → devuelto; recurso bloqueado → prestado → disponible; QR con `Cache-Control: no-store`; comprobante con condición. | Igual al esperado. | Aprobado |
| CP-003 Rechazo de códigos | RF-034, RS-006 | Funcional | 1. Operador de otra institución valida y confirma un código ajeno. 2. Código con formato inválido. 3. Código ya consumido. | "Código rechazado"; confirmación ajena 404; código reutilizado rechazado. | Igual al esperado. | Aprobado |
| CP-004 Panel de préstamos y ámbito | RF-028, RS-006 | Funcional | 1. Operador abre `/prestamos?p=` con préstamo propio y ajeno. 2. Busca por folio. 3. Abre el comprobante ajeno. 4. Alumno abre `/prestamos`. | Pestañas con contadores y panel de detalle solo del ámbito; búsqueda por folio; ajeno 404; alumno 403. | Igual al esperado. | Aprobado |
| CP-005 Cancelación justificada | RF-028, RF-029 | Funcional | 1. Operador cancela con 14 caracteres. 2. Alumno cancela con 17 caracteres. | Operador exige ≥ 20 caracteres (sin cambios); alumno exige ≥ 5 y libera el recurso; aparece en el historial. | Igual al esperado. | Aprobado |
| CP-006 a CP-010 Validación de la solicitud | RF-022, RF-024, RN-004 | Funcional | Enviar sin reglamento, fin antes del inicio, fuera de horario (6:00), horario pasado (8:00) y sin recursos. | Error en el campo correspondiente y ningún préstamo creado. | Igual al esperado en los 5 casos. | Aprobado |
| CP-011 Recursos y catálogo | RF-013, RF-014, RF-015, RN-002 | Funcional | 1. Admin abre chips, lista filtrada y detalle. 2. Alumno abre catálogo y detalle de un recurso en mantenimiento. | Chips con conteo, disponibilidad y línea de tiempo; el alumno no ve el recurso en mantenimiento y su detalle responde 404. | Igual al esperado. | Aprobado |
| CP-012 Auditoría y reportes | RF-052, RF-053, RF-054 | Funcional | 1. Admin consulta y filtra auditoría, exporta CSV y abre reportes de 7 días. 2. Periodo inválido. 3. Responsable de área y alumno. | Eventos con resumen, CSV UTF-8, tarjetas de reportes; periodo inválido rechazado; responsable 403 en auditoría y alumno 403 en reportes. | Igual al esperado. | Aprobado |
| CP-013 Notificaciones | RF-046 | Funcional | Marcar todas como leídas y marcar una ajena. | Solo cambian las propias; la ajena responde 404. | Igual al esperado. | Aprobado |
| CP-014 Solo lectura | RF-006, RN-011 | Funcional | Institución en `solo_lectura`: abrir recursos y crear un área. | Banner visible; escritura 403. | Igual al esperado. | Aprobado |
| CP-015 Búsqueda de instituciones | RF-004 | Funcional | Superadmin abre el panel, busca por nombre y abre el detalle. | Próximas a vencer, resultados filtrados y detalle con renovación. | Igual al esperado. | Aprobado |
| CP-016 Áreas | RF-009, CU-006 | Funcional | Crear, duplicar nombre, exceder límite, editar, desactivar, eliminar con y sin recursos. | Reglas CA1–CA5 de CU-006. | Igual al esperado. | Aprobado |
| CP-017 Aislamiento de áreas | RS-006, CU-006 E4–E5 | Seguridad | Editar área ajena; responsable de área en gestión de áreas. | 404 en área ajena; 403 al responsable. | Igual al esperado. | Aprobado |
| CP-018 Recursos | RF-010, RF-011, RF-013, RF-014 | Funcional | Alta, duplicados, límite del plan, edición, mantenimiento, estado forzado, desactivación, recurso en uso y búsqueda por código. | Reglas de CU-007 y CU-009. | Igual al esperado. | Aprobado |
| CP-019 Responsable de área | RF-010, CU-007 CA4 | Seguridad | Responsable abre y registra recursos de otra área. | 403 y el inventario solo muestra su área. | Igual al esperado. | Aprobado |
| CP-020 Importación CSV | RF-012, CU-008 | Funcional | Archivo con fila inválida, archivo válido con BOM y archivo sin encabezado. | Todo o nada; 2 recursos y log `IMPORTAR`; encabezado inválido rechazado. | Igual al esperado. | Aprobado |
| CP-021 Suscripción | RF-003, RF-004, RF-006, CU-002 | Funcional | Cambio de plan con y sin cupo, renovación y cambios de estado del superadmin, renovación por el admin institucional. | CA1, CA2, CA7 y transiciones de MBD Tabla 28. | Igual al esperado. | Aprobado |
| CP-022 Suspensión con sesión abierta | RF-006, RN-013 | Funcional | Suspender la institución de un usuario con sesión iniciada. | Cierre de sesión con el mensaje de CU-005 E3. | Igual al esperado. | Aprobado |
| CP-023 Planes | RF-007, CU-003 | Funcional | Editar plan como superadmin (válido y con límite 0) y como admin institucional. | Propagación de límites y auditoría con `id_institucion` nulo; validación; 403. | Igual al esperado. | Aprobado |
| CP-024 Tareas programadas | RF-005, RF-006, RF-036 | Integración | Ejecutar `spei:aplicar-vencimientos`, `spei:alertar-vencimientos` (dos veces) y `spei:vencer-prestamos`. | Solo lectura, una notificación y un correo en cola, reserva vencida con recurso liberado. | Igual al esperado. | Aprobado |
| CP-025 API v1 | RNF-001, RF-011, RF-015 | Integración | Token Sanctum, inventario, búsqueda por código propio y ajeno, catálogo con rol incorrecto, cierre y credenciales inválidas. | 201, 200, 404, 403, 204 y 422 respectivamente. | Igual al esperado. | Aprobado |
| CP-026 Notificaciones individuales | RF-046 | Funcional | Listar, marcar propia y ajena. | Propia leída; ajena 404. | Igual al esperado. | Aprobado |
| CP-027 Auditoría inalterable | RNF-005, DT-010 | Base de datos | Actualizar y borrar un registro de `logs_auditoria`. | Los triggers rechazan ambas operaciones. | Igual al esperado. | Aprobado |
| CP-032 Ámbito por área | RF-034, RF-051, RF-058, RS-006, CU-012, CU-014 | Seguridad | 1. Préstamos en dos áreas. 2. Contador del menú del administrador sin área y del responsable de área. 3. Responsable abre `/prestamos?p=` con el préstamo ajeno y exporta CSV. 4. Asistente del área valida en mostrador el QR del préstamo de otra área, intenta confirmarlo y abre su comprobante. | Contadores 2 y 1; lista y CSV sin el folio ajeno; "El préstamo pertenece a un área que usted no atiende." con `Cache-Control: no-store, private`; confirmación y comprobante 403; préstamo sigue pendiente. | Igual al esperado. | Aprobado |
| CP-033 Permiso de cancelación | RF-028, RF-029, CU-013 | Seguridad | 1. Administrador institucional y asistente cancelan una reserva. 2. Operador abre el panel. 3. Alumno abre "Mis préstamos". | 403 en ambos; panel con "Cancelar reserva" sin "prioridad institucional" ni marcas; "Cuenta activa" sin marcas. | Igual al esperado. | Aprobado |
| CP-034 CSV sin fórmulas | RF-058, CU-014 | Seguridad | Exportar préstamos cuyo motivo empieza con `=`. | CSV UTF-8 con BOM; el valor aparece como `'=…`. | Igual al esperado. | Aprobado |
| CP-035 Aislamiento de auditoría y reportes | RF-052, RF-053, RF-054, RS-006, CU-015 | Seguridad | Dos instituciones con préstamos activos: reporte de 7 días por servicio y por pantalla, auditoría y su CSV. | Total 1 y solo "Proyector Epson" en más prestados; sin la cámara ni el usuario de la otra institución en pantalla ni en el CSV. | Igual al esperado tras agregar el filtro explícito por `id_institucion`. | Aprobado |
| CP-036 Reservas serializadas | RF-025, RF-026, CU-011 CA3, MD 5.6 | Concurrencia | 1. Otra operación retiene el lock de la institución y el alumno solicita. 2. Se libera; el alumno reserva. 3. Un docente solicita el mismo recurso y horario. | 1: "Hay otra operación en curso…" tras 5 s sin crear nada. 3: "Uno de los recursos ya no está disponible…"; un solo préstamo y el recurso `bloqueado`. | Igual al esperado. La prueba simula la contención en un proceso; la carga con procesos paralelos queda para las pruebas de rendimiento. | Aprobado |
| CP-028 a CP-031 Bloque A previo | RF-001, RF-002, RF-008, RNF-001 | Regresión | Pruebas existentes de `PantallasPublicasTest` y `EntornoTest` sobre la interfaz nueva. | Mismo comportamiento que antes del rediseño. | 28 pruebas en verde. | Aprobado |

## Revisión visual

Capturas de las pantallas renderizadas con datos de demostración (1440 px) comparadas contra el prototipo: login, dashboard,
préstamos, recursos (cuadrícula y lista), detalle de recurso, mostrador, nueva solicitud, mis préstamos, auditoría, reportes,
suscripción, áreas, importación, catálogo, panel de superadmin, instituciones y planes. Ajustes aplicados tras la revisión:
folios y horarios sin salto de línea, filtros en una sola fila, alineación de badges, día visible en devoluciones atrasadas,
etiqueta "Básico" con acento y versionado de CSS y JS para evitar cachés antiguas.
