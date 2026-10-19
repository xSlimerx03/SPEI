# PLAN DE ITERACIÓN — PI-01

| Campo | Valor |
|---|---|
| Iteración (1) | Bloque A (I1 + I2 del MD) |
| Periodo (2) | 21/10/2026 – 27/10/2026 (arranque adelantado el 18/10/2026) |
| Fase RUP (3) | Construcción |
| Responsable (4) | Raúl Treviño Licona |

**Objetivo del incremento (5):** una institución contrata el servicio desde la web, su administrador
inicia sesión, crea administradores del sistema, registra áreas y recursos (manualmente, con lector de
código de barras o por CSV), y el usuario final consulta un catálogo que solo muestra lo disponible.
La suscripción vence y pasa a solo lectura de forma automática. Todo queda aislado por institución y
auditado en `logs_auditoria`.

**Criterio de entrada (MD Tabla 12):** requisitos identificados (DT-023, `BBB_REQ01_LineaBase_v1.0.md`);
criterios de aceptación en los CU-001…CU-010; dependencias revisadas (MD Tabla 13); ambiente I0 en verde;
responsable Raúl y revisor `spei-auditor`.

## Requisitos y casos de uso (6)

| ID | Descripción | Prioridad | Criterio de aceptación (resumen; detalle en el CU) |
|---|---|---|---|
| RF-001 | Landing de contratación y onboarding | Alta | CU-001: una institución nueva queda `activa` con plan, vigencia y dominio únicos |
| RF-002 | Alta del administrador institucional | Alta | CU-001: el contratante queda como `admin_institucional` con correo del dominio |
| RF-003 | Asignación y control del plan | Alta | CU-002: no se baja a un plan cuyos límites ya se superan; RN-010 se aplica al crear áreas y recursos |
| RF-004 | Monitoreo de vigencia y renovación | Alta | CU-002: días restantes visibles; la renovación del superadmin extiende la fecha de corte |
| RF-005 | Alertas de vencimiento | Media | CU-002: aviso a 7, 3 y 1 días, en el sistema y por correo, sin duplicados en el mismo día |
| RF-006 | Suspensión por falta de pago | Alta | CU-002: al vencer pasa a `solo_lectura`; las escrituras responden 403; `suspendida` impide iniciar sesión |
| RF-007 | Parámetros de planes por superadmin | Media | CU-003: solo el superadmin edita planes; el cambio se audita y se propaga a las instituciones del plan |
| RF-008 | Creación de administradores del sistema | Alta | CU-004: invitación con enlace firmado de 72 h; sin completar el registro no hay acceso |
| RF-009 | Gestión de áreas | Alta | CU-006: alta, edición y desactivación respetando el límite del plan y el nombre único |
| RF-010 | Alta manual de recursos | Alta | CU-007: número de inventario y de serie únicos por institución; límite del plan |
| RF-011 | Registro con código de barras | Media | CU-007: el campo acepta lector tipo teclado y la búsqueda por código devuelve el recurso del tenant |
| RF-012 | Carga masiva por CSV | Media | CU-008: validación previa de todas las filas; todo o nada; errores por fila |
| RF-013 | Estados en tiempo real | Alta | CU-009: cada cambio de estado se refleja en la siguiente consulta y se audita |
| RF-014 | Fuera de servicio / mantenimiento | Media | CU-009: no se envía a mantenimiento un recurso `prestado` o `bloqueado` |
| RF-015 | Catálogo diferenciado | Alta | CU-010: el usuario final solo ve recursos `disponible` y activos de áreas activas |
| RNF-001 | Autenticación segura | Alta | CU-005: rol por dominio; máximo 5 intentos por minuto; token de API de 24 h |
| RNF-005 | Auditoría inalterable | Alta | Triggers rechazan UPDATE y DELETE sobre `logs_auditoria` |
| RS-006 | Multi-tenant | Alta | Prueba de acceso cruzado rechazada en cada recurso del bloque |

## Tareas y asignación (7)

| Tarea | Responsable | Estimación (h) | Dependencia |
|---|---|---|---|
| Línea base de requisitos y DT-023…DT-025 | spei-analista / spei-documentador | 2 | — |
| CU-001…CU-010 | spei-analista | 4 | Línea base |
| Migraciones de 7 tablas, triggers y seeders | spei-datos | 4 | DT-024 |
| DDL-03, DDL-04, DDL-07, DDL-08 y diccionario DDL-06 | spei-datos | 3 | Migraciones |
| Contexto de tenant, Global Scope, middleware de rol y suscripción, auditoría | spei-backend | 3 | Migraciones |
| Autenticación web y API (Sanctum) | spei-backend | 2 | Contexto de tenant |
| Módulo SaaS y suscripción (RF-001…RF-008, comandos programados) | spei-backend | 6 | Autenticación |
| Módulo estructura y catálogo (RF-009…RF-015) | spei-backend | 6 | Módulo SaaS |
| Vistas Blade responsivas (paleta y componentes MP cap. 2) | spei-backend | 5 | Servicios |
| Pruebas Pest (feature, aislamiento, BD) y CP-01 | spei-qa | 5 | Código |
| Revisión RED-01 / LV-01 | spei-auditor | 1 | Pruebas |
| EI-01 y RT-01 | spei-orquestador | 1 | Revisión |

Capacidad: un desarrollador, ~42 h en la semana; estimado 42 h.

## Riesgos y respuesta (8)

| Riesgo | Probabilidad | Impacto | Tratamiento |
|---|---|---|---|
| Fuga de datos entre instituciones | Media | Alta | Global Scope, Policies que comparan `id_institucion` y pruebas de acceso cruzado por recurso |
| Línea base no aceptada | Baja | Alta | DT-023 deja marcados los supuestos; ajustes por SC-01 |
| Cobertura < 80 % por vistas y comandos | Media | Media | Pruebas feature de cada ruta y comando programado |
| Carga CSV grande que supere el tiempo de respuesta | Baja | Media | Límite de 1 000 filas por archivo en la 1.0; cola si se excede |

| Aprobó (9) | Fecha (10) |
|---|---|
| Pendiente — Raúl Treviño Licona | — |
