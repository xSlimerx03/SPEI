# Línea base de requisitos — SPEI liberación 1.0

| Campo | Valor |
|---|---|
| Documento | BBB_REQ01_LineaBase_v1.0 |
| Formato | REQ-01 (MD Anexo E) consolidado |
| Versión | 1.0 |
| Fecha | 18/10/2026 |
| Autor | Raúl Treviño Licona |
| Estado | Propuesto — pendiente de aceptación de Raúl (DT-023) |
| Fuentes | MBD v1.2 Tabla 7 (§1.6) y §3.4.4; MD v1.2 Tablas 10, 13, 19 y 25; SRS v1.0 (23/08/2026) |

## 1. Criterio de la línea base (DT-023)

Los cuatro manuales v1.2 trabajan con **58 RF** (RF-001…RF-058), **RN-001…RN-014**, **RNF-001…RNF-011**
y **RS-001…RS-006**. El SRS que está en `docs/requisitos/` es la versión 1.0 del 23/08/2026, con 18 RF y
otra numeración. Por precedencia (MD 1.5) y por ser el catálogo más reciente y más amplio, la línea base
son los 58 RF de MBD Tabla 7, agrupados por módulos según MD Tabla 13. Cada RF del SRS se conserva como
**fuente** del RF equivalente (sección 5), así que el alcance no se reduce. Para evitar confusiones, los
identificadores del SRS se citan con el prefijo `SRS-` (por ejemplo, `SRS-RF-015`).

## 2. Requisitos funcionales (58)

| ID | Módulo (MD Tabla 13) | Requisito | Prioridad | Bloque |
|---|---|---|---|---|
| RF-001 | SaaS y suscripción | Landing de contratación y onboarding SaaS | Alta | A |
| RF-002 | SaaS y suscripción | Registro y alta del Administrador Institucional | Alta | A |
| RF-003 | SaaS y suscripción | Asignación y control del plan de suscripción | Alta | A |
| RF-004 | SaaS y suscripción | Monitoreo de vigencia y renovación del plan | Alta | A |
| RF-005 | SaaS y suscripción | Emisión de alertas de vencimiento de suscripción | Media | A |
| RF-006 | SaaS y suscripción | Suspensión y bloqueo automático por falta de pago | Alta | A |
| RF-007 | SaaS y suscripción | Modificación de parámetros de planes por Superadmin | Media | A |
| RF-008 | SaaS y suscripción | Creación de Administradores del Sistema | Alta | A |
| RF-009 | Estructura y catálogo | Gestión de áreas y departamentos | Alta | A |
| RF-010 | Estructura y catálogo | Alta manual e inventariado de recursos prestables | Alta | A |
| RF-011 | Estructura y catálogo | Registro de recursos mediante código de barras | Media | A |
| RF-012 | Estructura y catálogo | Carga masiva de catálogo vía CSV | Media | A |
| RF-013 | Estructura y catálogo | Catalogación y etiquetado por estados en tiempo real | Alta | A |
| RF-014 | Estructura y catálogo | Control de estado "Fuera de servicio / Mantenimiento" | Media | A |
| RF-015 | Estructura y catálogo | Visualización diferenciada del catálogo e inventario | Alta | A |
| RF-016 | Usuarios y roles | Carga masiva de usuarios y dominios vía CSV | Alta | B |
| RF-017 | Usuarios y roles | Mapeo automatizado de roles por dominio | Alta | B |
| RF-018 | Usuarios y roles | Asignación y control de permisos por rol | Alta | B |
| RF-019 | Usuarios y roles | Alta y gestión de responsables de área | Alta | B |
| RF-020 | Usuarios y roles | Alta y registro de asistentes de mostrador | Alta | B |
| RF-021 | Usuarios y roles | Delegación operativa a asistentes | Media | B |
| RF-022 | Solicitudes y reservas | Creación de solicitudes de préstamo | Alta | B |
| RF-023 | Solicitudes y reservas | Programación de fecha y hora | Alta | B |
| RF-024 | Solicitudes y reservas | Solicitudes multirecurso (préstamo múltiple) | Alta | B |
| RF-025 | Solicitudes y reservas | Verificación automatizada de disponibilidad real | Alta | B |
| RF-026 | Solicitudes y reservas | Bloqueo (reserva) temporal del activo | Alta | B |
| RF-027 | Solicitudes y reservas | Resolución de conflictos por concurrencia | Alta | B |
| RF-028 | Solicitudes y reservas | Cancelación voluntaria por usuario final | Media | C |
| RF-029 | Solicitudes y reservas | Cancelación administrativa de préstamos | Media | C |
| RF-030 | Solicitudes y reservas | Cancelación por prioridad institucional | Media | C |
| RF-031 | Solicitudes y reservas | Prohibición de prórrogas directas | Media | C |
| RF-032 | Solicitudes y reservas | Cancelación automática por margen de tolerancia | Alta | C |
| RF-033 | Solicitudes y reservas | Canal de diálogo y aclaraciones previo a la aprobación | Baja | C |
| RF-034 | Entrega y devolución | Generación de código QR dinámico para entrega | Alta | C |
| RF-035 | Entrega y devolución | Confirmación de recepción por escaneo del QR | Alta | C |
| RF-036 | Entrega y devolución | Generación de código QR dinámico para devolución | Alta | C |
| RF-037 | Entrega y devolución | Escaneo y liberación de responsabilidad en devolución | Alta | C |
| RF-038 | Entrega y devolución | Modo offline y sincronización diferida de escaneos | Media | C |
| RF-039 | Entrega y devolución | Registro y reporte de incidencias en recepción | Alta | C |
| RF-040 | Entrega y devolución | Generación e integración de comprobante PDF | Media | C |
| RF-041 | Sanciones | Configuración de reglas y matriz de sanciones | Alta | D |
| RF-042 | Sanciones | Registro automatizado de marcas por incumplimiento | Alta | D |
| RF-043 | Sanciones | Asignación y bloqueo automatizado de usuarios | Alta | D |
| RF-044 | Sanciones | Exención excepcional de sanciones | Media | D |
| RF-045 | Sanciones | Notificación de sanción al usuario | Media | D |
| RF-046 | Notificaciones | Envío de recordatorios previos de entrega y devolución | Media | D |
| RF-047 | Notificaciones | Notificaciones push (en el sistema web, DT-022) | Media | D |
| RF-048 | Notificaciones | Notificaciones vía correo electrónico | Media | D |
| RF-049 | Notificaciones | Alertas de solicitudes pendientes para administración | Media | D |
| RF-050 | Notificaciones | Notificaciones de cancelación por prioridad | Media | D |
| RF-051 | Historial y reportes | Historial personal para el usuario final | Media | D |
| RF-052 | Historial y reportes | Registro histórico centralizado de operaciones | Alta | D |
| RF-053 | Historial y reportes | Filtros de búsqueda y auditoría histórica | Media | D |
| RF-054 | Historial y reportes | Reportes de equipos más prestados | Media | D |
| RF-055 | Historial y reportes | Reportes de horas de uso por recurso | Media | D |
| RF-056 | Historial y reportes | Reporte de incidentes, sanciones y devoluciones | Media | D |
| RF-057 | Historial y reportes | Reporte de cancelaciones y justificaciones | Media | D |
| RF-058 | Historial y reportes | Exportación de reportes operativos | Media | D |

La prioridad proviene del RF equivalente del SRS cuando existe; si no, del módulo en MD Tabla 13. Las
tablas, columnas y mecanismos de cada RF son los de MBD Tabla 7, con las modificaciones de DT-008 a DT-012,
DT-016 y DT-024.

## 3. Reglas de negocio

| ID | Regla | Fuente |
|---|---|---|
| RN-001 | El rol se determina por el dominio del correo institucional y la asignación registrada; no se autodeclara. | MBD 3.1.4; MD 5.4; SRS-RN-009 |
| RN-002 | Solo los recursos en estado `disponible` son visibles al usuario final. | MBD 3.1.4, Tabla 35; SRS-RN-001 |
| RN-003 | La custodia se transfiere solo con el escaneo del QR; un bloqueo sin escaneo no genera responsabilidad ni penalización. | MBD 3.1.4; SRS-RN-006 |
| RN-004 | Los tokens QR son de un solo uso y tienen TTL de 30 a 60 s (por defecto 45 s). | MD 5.5; MBD Tabla 35 |
| RN-005 | Las penalizaciones se aplican automáticamente al registrarse una incidencia; la tercera marca bloquea (las dos primeras son de tolerancia). | MBD Tabla 35; SRS-RN-004 |
| RN-006 | *Supuesto:* un usuario no puede extender unilateralmente el plazo de devolución si existe otra solicitud confirmada para el periodo siguiente. | SRS-RN-005; MBD RF-031 |
| RN-007 | Si no se escanea el QR de entrega dentro de la tolerancia (15–20 min), el préstamo pasa a `vencido` y libera el recurso. | MBD Tabla 35; MD Tabla 25 |
| RN-008 | Prioridad ante conflicto: docente > alumno → mejor historial (menos marcas) → `fecha_creacion` más antigua. | MD 5.6; MBD Tabla 7 RF-027 |
| RN-009 | *Supuesto:* la fecha y hora de devolución las fija el solicitante al crear la solicitud. | SRS-RN-002 |
| RN-010 | El plan de suscripción limita las áreas y los recursos registrables. | MBD Tabla 35; SRS-RN-007 |
| RN-011 | Con la suscripción en `solo_lectura` se bloquean las escrituras del tenant. | MBD Tabla 35 |
| RN-012 | El historial se comprime al año y se elimina a los cuatro años, conservando el log mínimo de auditoría. | MBD Tabla 35; MD 13.2 |
| RN-013 | Al vencer la suscripción sin renovación, la institución pasa a `solo_lectura`. | MBD Tabla 28 y 35 |
| RN-014 | La cancelación por prioridad institucional exige justificación y notificación al afectado. | MBD Tabla 35; SRS-RN-003 |

RN-006 y RN-009 no están definidas en los manuales; se toman del SRS por equivalencia funcional y quedan
marcadas como **supuesto** para que Raúl las confirme.

## 4. Requisitos no funcionales y restricciones

| ID | Atributo | Condición | Umbral | Método / evidencia |
|---|---|---|---|---|
| RNF-001 | Autenticación segura | Inicio de sesión web y API | Rol por dominio; bcrypt; token Sanctum de 24 h; máximo 5 intentos por minuto | Prueba feature de login y de límite |
| RNF-002 | Estados en tiempo real | Cambio de estado de un recurso | El catálogo refleja el estado en la siguiente consulta (sin caché obsoleta) | Prueba feature |
| RNF-003 | Operación sin conexión | Escaneo QR sin red | Pendiente de decisión (DT-022) | — |
| RNF-004 | Notificación por mensaje | Diálogo previo y cambios | Notificación en el sistema por cada mensaje | Prueba feature |
| RNF-005 | Auditoría inalterable | Acciones críticas | 100 % registradas; UPDATE y DELETE rechazados en `logs_auditoria` | Prueba de BD con triggers |
| RNF-006 | Plazo de notificaciones | Notificación por correo | Promedio 60 s, máximo 300 s (MD Tabla 25) | Medición en QA |
| RNF-007 | Trazabilidad de acciones | Bloqueo, entrega, devolución, cancelación y penalización | Fecha, hora, usuario e institución en cada registro | Prueba feature |
| RNF-008 | Comprobante PDF | Entrega y devolución | Generación en cola; promedio ≤ 5 s | Prueba feature |
| RNF-009 | Rendimiento | Catálogo, red institucional | Promedio ≤ 2 s; máximo 5 s (MD Tablas 19 y 25) | Prueba cronometrada de 10 ejecuciones |
| RNF-010 | Concurrencia | Operaciones mixtas | 200 usuarios simultáneos sin errores críticos ni doble reserva | Prueba básica de carga |
| RNF-011 | Disponibilidad | Cómputo mensual | ≥ 90 % obligatorio; 99 % objetivo L–V 7:00–22:00 | Registro de *uptime* |

| ID | Restricción | Estado en 1.0 |
|---|---|---|
| RS-001 | Backend en Laravel | Laravel 12.70 (DT-019) |
| RS-002 | App móvil Flutter | Fuera de alcance (DT-022) |
| RS-003 | Gestor MySQL | MySQL 8.4 LTS (DT-017) |
| RS-004 | Sin otros frameworks principales | Se cumple |
| RS-005 | Docker como entorno de ejecución | Se cumple |
| RS-006 | SaaS multi-tenant con esquema compartido | DT-008 |

RS-005 no está definida de forma explícita en los manuales; se registra como **supuesto** a partir de MP 3.5.

## 5. Correspondencia con el SRS v1.0

| SRS | Título en el SRS | RF de la línea base |
|---|---|---|
| SRS-RF-001 | Registro de recursos prestables por área | RF-010, RF-013 |
| SRS-RF-002 | Autenticación basada en dominio institucional | RF-017, RF-018 (inicio de sesión desde el bloque A) |
| SRS-RF-003 | Solicitud de préstamo (evento) | RF-022, RF-023 |
| SRS-RF-004 | Verificación de disponibilidad real | RF-025 |
| SRS-RF-005 | Bloqueo (reserva) de recursos | RF-026, RF-027 |
| SRS-RF-006 | Confirmación de entrega mediante QR | RF-034, RF-035 |
| SRS-RF-007 | Confirmación de devolución mediante QR | RF-036, RF-037 |
| SRS-RF-008 | Cancelación total o parcial | RF-028, RF-029 |
| SRS-RF-009 | Recordatorios previos al evento | RF-046 |
| SRS-RF-010 | Penalización por retraso en devolución | RF-041, RF-042, RF-043 |
| SRS-RF-011 | Historial de operaciones | RF-051, RF-052, RF-053 |
| SRS-RF-012 | Generación de reportes | RF-054 a RF-058 |
| SRS-RF-013 | Gestión de roles y alta de responsables de área | RF-008, RF-019 |
| SRS-RF-014 | Cancelación por prioridad institucional | RF-030, RF-050 |
| SRS-RF-015 | Exportación a calendario externo (.ics) | Criterio de aceptación de RF-026 |
| SRS-RF-016 | Préstamos múltiples el mismo día | Criterio de aceptación de RF-024 |
| SRS-RF-017 | Gestión de estado del recurso | RF-014 |
| SRS-RF-018 | Diálogo previo a la aceptación | RF-033 |

Los 18 RF del SRS quedan cubiertos. SRS-RF-015 (archivo `.ics`) y SRS-RF-016 no tienen un RF propio en los
manuales y se incorporan como criterios de aceptación de RF-026 y RF-024 para no perder alcance.
