# Auditoría integral del SPEI contra el Manual de Desarrollo BBB-MD-002 v1.2

| Campo | Valor |
|---|---|
| Tipo | Auditoría de cumplimiento (insumo para LV-01 / RED-01; MC 1.8, MD 3.3, MD 10.2) |
| Repositorio | C:\Users\Lenovo\Documents\spei |
| Rama / commit | `feature/RF-011-gestion-prestamos` @ `593db78` + 49 archivos sin commit (822+/285-) |
| Revisor | Subagente `spei-auditor` (independiente, solo lectura; no es autor) |
| Fecha | 25/10/2026 |
| Fuentes | MD v1.2 (tmp/pdfs/BBB_ManualDesarrollo_v1.2.txt), REQ-01 línea base (58 RF, DT-023), DT-001…DT-031, CLAUDE.md, código, pruebas y configuración |
| Decisión | La decisión final corresponde a Raúl Treviño Licona. El revisor no aprueba ni declara terminado nada. |

## 1. Verificaciones ejecutadas por el revisor

| Comando | Resultado |
|---|---|
| `docker compose exec -T app ./vendor/bin/pest --compact` | 64 pruebas aprobadas, 536 aserciones (26.8 s) |
| `docker compose exec -T app ./vendor/bin/pest --coverage --min=80` | Total 94.2 % (umbral 80 % cumplido). Archivos bajos: `ResumenAuditoria` 54.2 %, `PerteneceAInstitucionTrait` 71.4 %, `RecursoPolicy` 71.4 %, `DisponibilidadRecursoService` 72.2 % |
| Primera ejecución de Pest (con otro proceso `pest` activo en el contenedor) | 64 fallos `QueryException` (tabla ya existe / no existe) sobre `spei_testing`: la suite no tolera ejecuciones simultáneas (ver MD-31) |
| `./vendor/bin/pint --test` | PASS, 142 archivos |
| `./vendor/bin/phpstan analyse` (nivel 6, Larastan 3.13) | `[OK] No errors` |
| `composer audit` | "No security vulnerability advisories found." |
| `php artisan --version` | Laravel Framework 12.70.0 |
| `php artisan route:list --except-vendor` | 65 rutas (60 web, 5 API v1) |
| `git ls-remote --heads --tags origin` | Solo `docs/BBB-I0-inicializacion-repositorio` y `feature/RF-011-gestion-prestamos`; **sin `main`, sin `develop`, sin etiquetas** |
| `git log feature/RF-011-gestion-prestamos` | 2 commits: `82dae0f chore: inicializar repositorio SPEI`, `593db78 feat: implementar gestión integral de préstamos` (170 archivos, 18 675 inserciones) |

## 2. Calificación de cumplimiento del MD

| # | Rubro (sección MD) | Peso | Puntaje | Justificación (evidencia) |
|---|---|---|---|---|
| 1 | RUP, iteraciones, criterios de entrada y salida (2–4, Anexos B y C) | 8 | 3.0 | PI-01 I0 aprobado; PI-01 A sin aprobar (`docs/iteraciones/A/BBB_PI01_A_v1.0.md:73`); EI-01 I0 en "Corrección" sin aprobar (`BBB_EI01_I0_v1.0.md:37`); no hay PI-01 de B/C/D aunque se implementaron RF de esos bloques; ninguna iteración cerrada |
| 2 | Arquitectura y multi-tenant (5.1–5.3) | 8 | 6.0 | Monolito por capas (Controller → Service → Model), Policies, `TenantMiddleware`, Global Scope en 8 modelos, pruebas de acceso cruzado. Scope fail-open sin contexto (`AmbitoInstitucionScope.php:61`); comandos y colas sin contexto; módulos no separados físicamente |
| 3 | Seguridad (5.4, 13.1) | 12 | 7.0 | Bien: FormRequests, Policies, bcrypt, enlaces firmados, QR con hash SHA-256 y `no-store`, CSV sin fórmulas, triggers de inalterabilidad, throttle de login. Falta: HTTPS, cabeceras CSP/HSTS, registro de intentos fallidos, `.dockerignore`, límite de peticiones fuera del login |
| 4 | Concurrencia y reservas (5.6, RN-008) | 8 | 3.5 | Transacción + `lockForUpdate` + lock Redis (`PrestamoService.php:501-509`). Sin prioridad RN-008, sin restricción de periodos incompatibles, sin prueba con procesos paralelos |
| 5 | Stack y dependencias (6, 6.1, Tablas 14–17) | 6 | 5.5 | PHP 8.3, Laravel 12.70 (DT-019 aprobada), Sanctum 4.3.3, Pest 3.8.7, PHPStan 2 / nivel 6, MySQL 8.4, Redis 7.4, Nginx 1.27, todas las imágenes con digest. Extensiones `pecl` sin versión fija |
| 6 | **Completitud funcional (11.3; 58 RF)** | 22 | 13.0 | 29 implementados y probados, 17 parciales, 12 sin implementar → (29 + 0.5·17)/58 = 64.7 % → 14.2; −1.2 por el bloqueo CRÍTICO MD-02 (no hay forma de dar de alta usuarios finales) |
| 7 | Pruebas y calidad (10.3 local, 11.1–11.2) | 10 | 7.5 | 64/64 en verde, 94.2 % de cobertura, Pint, PHPStan y audit limpios. Sin pruebas unitarias, sin RNF (rendimiento, carga, disponibilidad), sin prueba de rollback, sin registro de defectos |
| 8 | Flujo de trabajo y CI (10.1–10.3, 14.1) | 8 | 3.0 | `ci.yml` cubre 6 pasos, pero sin `main`/`develop` nunca se ha ejecutado; sin PR, sin etiquetas; un commit de 18 675 líneas; rama mal nombrada; 49 archivos sin commit |
| 9 | Definición de terminado (10.4) | 5 | 1.0 | 0 de 58 RF cumplen la DoD (sin aceptación en EI-01, sin diagramas, API sin documentar, RT-01 desactualizada) |
| 10 | Ambientes, despliegue, respaldos (7, 12.1, 13.3, LI-01) | 7 | 2.0 | Solo ambiente de desarrollo; sin QA ni producción, sin TLS, sin LI-01, sin respaldos. A favor: `.env` fuera de Git, `.env.example` documentado, perfil `dev` para phpMyAdmin y MailHog |
| 11 | Requisitos, trazabilidad y diseño (8, 9) | 6 | 2.5 | REQ-01 con 58 RF y RNF medibles (pendiente de aprobación); 15 CU-01 con los 12 campos. RT-01 solo con filas RF y varias inexactas; `docs/diagramas/` vacío; sin ER ni DG-01; sin DDL del bloque A |
| | **Total** | **100** | **54.0** | **Cumplimiento parcial. El sistema no está listo para producción.** |

## 3. Completitud funcional: estado de los 58 RF (verificado en código y pruebas, no solo en RT-01)

Leyenda: **IP** = implementado y probado (ruta, servicio y prueba Pest que lo ejerce); **P** = parcial; **NI** = no implementado.
Ningún RF está "terminado" según MD 10.4 (falta aceptación en EI-01).

| RF | Requisito | Estado | Evidencia / faltante |
|---|---|---|---|
| RF-001 | Landing de contratación | IP | `ContratacionController`, `web.php:58-59`; `PantallasPublicasTest` |
| RF-002 | Alta del administrador institucional | IP | `OnboardingService`; `PantallasPublicasTest` |
| RF-003 | Control del plan | IP | `SuscripcionService`, `web.php:96`; CP-021 |
| RF-004 | Vigencia y renovación | IP | `web.php:85`; CP-015, CP-021 |
| RF-005 | Alertas de vencimiento | IP | `AlertaVencimientoService`, `console.php:251`; CP-024 |
| RF-006 | Suspensión automática | IP | `AplicarVencimientosCommand`, `SuscripcionMiddleware`; CP-014, CP-022 |
| RF-007 | Planes por superadmin | IP | `PlanController`, `web.php:89-91`; CP-023 |
| RF-008 | Administradores del sistema | IP | `AdministradorService::invitar`; `PantallasPublicasTest` (falta la prueba de reenvío entre instituciones) |
| RF-009 | Áreas | IP | `AreaController`; CP-016, CP-017 |
| RF-010 | Alta manual de recursos | IP | `RecursoService`; CP-018, CP-019 |
| RF-011 | Código de barras | IP | `web.php:119`, `api.php:212`; CP-018, CP-025 |
| RF-012 | CSV de catálogo | IP | `ImportacionCatalogoService`; CP-020 |
| RF-013 | Estados en tiempo real | IP | `web.php:128`; CP-011, CP-018 |
| RF-014 | Mantenimiento | IP | CP-018 |
| RF-015 | Catálogo diferenciado | IP | `CatalogoController`; CP-011, CP-025 |
| RF-016 | CSV de usuarios y dominios | NI | Sin ruta ni servicio |
| RF-017 | Mapeo de roles por dominio | P | Solo valida el dominio al iniciar sesión (`AutenticacionService::perteneceAlDominio`); no hay mapeo dominio → rol |
| RF-018 | Permisos por rol | P | Control con `RolMiddleware` y Policies; no hay asignación de roles por la institución |
| RF-019 | Responsables de área | P | Invitación de `admin_sistema` con `id_area_asignada` (`AdministradorService.php:79-80`); sin gestión ni baja |
| RF-020 | Asistentes de mostrador | P | El rol opera el mostrador, pero no existe forma de darlo de alta (solo `DemoSeeder`) |
| RF-021 | Delegación a asistentes | NI | — |
| RF-022 | Crear solicitud | IP | `PrestamoService::solicitar`; CP-002, CP-006…010 |
| RF-023 | Fecha y hora | IP | `SolicitarPrestamoRequest`; CP-006…010 |
| RF-024 | Multirecurso | IP | Hasta 10 recursos de una misma área (`PrestamoService.php:282-295`); CP-002 |
| RF-025 | Disponibilidad real | P | Revisa `estado = disponible`, no la superposición de periodos; el recurso queda bloqueado desde la solicitud (`PrestamoService.php:115`) |
| RF-026 | Bloqueo temporal | P | El bloqueo existe, pero no es por periodo; falta el criterio `.ics` (REQ-01 §5, SRS-RF-015) |
| RF-027 | Conflictos por concurrencia | P | Serialización con lock y transacción (CP-036); sin prioridad RN-008 |
| RF-028 | Cancelación por el usuario | IP | `PrestamoService::cancelar`; CP-005, CP-033 |
| RF-029 | Cancelación administrativa | IP | CP-005, CP-033 |
| RF-030 | Cancelación por prioridad | NI | Sin `justificacion_prioridad` (DT-030) |
| RF-031 | Sin prórrogas directas | P | Se cumple porque no existe la función; sin prueba (RT-01:43) |
| RF-032 | Cancelación por tolerancia | IP | `VencerPrestamosCommand`, `console.php:252`; CP-024 |
| RF-033 | Diálogo previo | NI | Sin tabla `conversaciones` |
| RF-034 | QR de entrega | IP | `PrestamoService::generarToken`, `CodigoQrService`; CP-002, CP-003, CP-032 |
| RF-035 | Escaneo de entrega | IP | `MostradorController::validar`, `PrestamoService::confirmar`; CP-002, CP-003 |
| RF-036 | QR de devolución | IP | CP-002 |
| RF-037 | Escaneo de devolución | IP | CP-002 |
| RF-038 | Modo sin conexión | NI | Decisión pendiente (DT-022 punto 7) |
| RF-039 | Incidencias en recepción | P | Condición e incidencia sí; evidencia fotográfica no (DT-030) |
| RF-040 | Comprobante PDF | P | Vista HTML imprimible (`prestamos.show`); no hay PDF generado en el servidor ni en cola (RNF-008) |
| RF-041 | Matriz de sanciones | NI | — |
| RF-042 | Marcas por incumplimiento | NI | — |
| RF-043 | Bloqueo automático | NI | Solo se consulta `usuarios.bloqueado` (`PrestamoService.php:261`); nada lo asigna |
| RF-044 | Exención | NI | — |
| RF-045 | Aviso de sanción | NI | — |
| RF-046 | Recordatorios previos | **NI** | No hay comando, servicio ni tarea; RT-01:58 lo marca "Implementado" por error |
| RF-047 | Notificaciones en el sistema (DT-022) | P | Se crean al cambiar el préstamo (`PrestamoService.php:439-450`) y se listan (CP-013, CP-026); sin tiempo real ni todos los eventos |
| RF-048 | Correo | P | Solo invitación y alerta de suscripción; ningún correo de préstamo |
| RF-049 | Alertas de pendientes al administrador | P | `avisarOperadores` (`PrestamoService.php:459-478`); sin prueba que lo verifique |
| RF-050 | Aviso de cancelación por prioridad | NI | Depende de RF-030 |
| RF-051 | Historial personal | IP | `/mis-prestamos`; CP-004, CP-032 |
| RF-052 | Histórico centralizado | IP | `logs_auditoria` y `/auditoria`; CP-012, CP-027, CP-035 |
| RF-053 | Filtros de auditoría | IP | CP-012, CP-035 |
| RF-054 | Equipos más prestados | IP | `ReporteService::obtenerMasPrestados`; CP-012, CP-035 |
| RF-055 | Horas de uso por recurso | P | Se calcula por área, no por recurso (`ReporteService` `obtenerUsoPorArea`); RT-01 dice "Pendiente" |
| RF-056 | Incidentes, sanciones y devoluciones | P | Condición al devolver y retraso; sin sanciones |
| RF-057 | Cancelaciones y justificaciones | P | Solo conteo por origen (`desglosarCierres`); no muestra justificaciones |
| RF-058 | Exportación de reportes | P | CSV de préstamos y de auditoría (CP-034); los indicadores del reporte no se exportan |

**Resumen por bloque**

| Bloque | RF | IP | P | NI |
|---|---|---|---|---|
| A (RF-001…015) | 15 | 15 | 0 | 0 |
| B (RF-016…027) | 12 | 3 | 7 | 2 |
| C (RF-028…040) | 13 | 7 | 3 | 3 |
| D (RF-041…058) | 18 | 4 | 7 | 7 |
| **Total** | **58** | **29 (50 %)** | **17 (29 %)** | **12 (21 %)** |

## 4. Hallazgos

| ID | Sección MD | Requisito | Evidencia | Severidad | Acción propuesta |
|---|---|---|---|---|---|
| MD-01 | 11.3, 4.1 | "Todos los RF-001 a RF-058 … implementados o con decisión de cambio aprobada" | Sección 3: 29 IP, 17 P, 12 NI; el módulo de sanciones (RF-041…045) no existe | CRÍTICO | Completar los bloques B–D según el plan o aprobar SC-01 por cada RF diferido |
| MD-02 | 5.2 (Usuarios y roles), 5.4 | Carga CSV de usuarios y roles; operación por alumnos, docentes y asistentes | `AdministradorService.php:79` fija `Rol::AdminSistema`; los únicos alumnos, docentes y asistentes salen de `DemoSeeder.php:97-100`, que solo corre con `APP_ENV=local` (`DatabaseSeeder.php:57`). Sin RF-016…021, en producción ningún usuario final puede solicitar un préstamo | CRÍTICO | Implementar RF-016…RF-021 (CSV + invitación por rol) antes de cualquier despliegue. NC-01 propuesta |
| MD-03 | 7, 7.2, 13.1, 13.3, Anexo J | Ambientes de QA y producción, HTTPS obligatorio, respaldos, LI-01 | `docker-compose.yml:30` solo `target: dev`; `docker/nginx.conf:18` solo `listen 80`; no hay compose de QA ni de producción; `docs/liberaciones/` vacío; ningún script ni política de respaldo en el repositorio | CRÍTICO | Crear compose de QA y producción (`target: prod`, TLS, `SESSION_SECURE_COOKIE=true`), respaldo diario con prueba de restauración y plantilla LI-01. NC-01 propuesta |
| MD-04 | 5.6, RN-008 | Restricción contra periodos incompatibles; prioridad: rol → historial → antigüedad | `PrestamoService.php:85-123` asigna por orden de llegada; `:115` bloquea el recurso completo; `:291` exige `estado = disponible`. Un recurso pedido para la semana siguiente queda indisponible desde hoy | MAYOR | Modelar la reserva por intervalo (verificar superposición de inicio y fin bajo lock) e implementar RN-008 con su prueba |
| MD-05 | 5.3 punto 2 | "Toda consulta, comando, trabajo de cola … debe recibir y validar el contexto institucional" | `AmbitoInstitucionScope.php:61`: sin contexto no filtra (fail-open); `VencerPrestamosCommand.php:69` usa `withoutGlobalScopes()` y no fija contexto; los correos `ShouldQueue` (`AlertaVencimientoMail.php:48`, `InvitacionAdministradorMail.php:48`) se procesan sin tenant | MAYOR | Fijar el contexto por institución en comandos y trabajos; que el scope falle cerrado salvo exclusión explícita (superadmin) |
| MD-06 | 7.1, 13.1 | Secretos fuera de los artefactos | No existe `.dockerignore`; `docker/Dockerfile:50` (`COPY . /var/www/html`) mete `.env`, `.git`, `tmp/` y `vendor/` del anfitrión en la imagen `prod` que construye `ci.yml:96` | MAYOR | Agregar `.dockerignore` (`.env*`, `.git`, `tmp/`, `vendor/`, `storage/*`, prototipo) |
| MD-07 | 13.1 puntos 4 y 5 | "Registro del intento de validación" del QR; logs con resultado | `MostradorService.php:67,75` lanza la excepción sin auditar; `AutenticacionService::verificarCredenciales` no registra los fallos; `logs_auditoria` no tiene columna de resultado (migración `000006`, líneas 51-63) | MAYOR | Auditar los intentos rechazados de QR e inicio de sesión (sin el token completo) con su resultado |
| MD-08 | 10.1, 10.2, 10.3, 14.1 | `main` y `develop` protegidas; PR revisado; CI en verde antes de integrar | `git ls-remote`: sin `main`, `develop` ni etiquetas; `ci.yml:20-24` solo se dispara con PR o push a `develop`/`main`, así que nunca se ha ejecutado; sin PR | MAYOR | Crear `main` y `develop` protegidas, abrir PR desde la rama, adjuntar la ejecución de Actions como evidencia |
| MD-09 | 4.3, Anexos B y C | Criterios de entrada (PI-01) y salida (EI-01 aprobado) | No hay PI-01 de los bloques B/C/D aunque se implementaron RF-022…058 (el MVP `docs/plan/BBB_MVP_v1.0.md` no es un PI-01); `BBB_PI01_A_v1.0.md:73` "Pendiente"; `BBB_EI01_I0_v1.0.md:37` "Corrección", sin aprobación; no hay EI-01 del bloque A | MAYOR | Elaborar el PI-01 que cubra el MVP, cerrar el EI-01 de I0 y del bloque A con la decisión de Raúl |
| MD-10 | 10.4 | DoD: diseño, revisión, API, configuración, trazabilidad, aprobación | Ningún RF tiene aceptación en EI-01; `routes/api.php` (cabecera, `@see`) cita `docs/api/BBB_API_v1.0.md`, que no existe; RT-01 dice "Sin commit" | MAYOR | No marcar ningún RF como terminado hasta reunir todas las evidencias; crear la documentación de la API |
| MD-11 | 9.1–9.4, Anexo F | Diagramas (contexto, CU, actividad, secuencia, clases, estados, componentes, despliegue, ER) y DG-01; DDL de cada migración | `docs/diagramas/` vacío; no hay ningún DG-01; `docs/bd/` solo tiene DDL de préstamos; las 7 migraciones del bloque A no tienen DDL (riesgo abierto en `BBB_PlanDesarrollo_v1.0.md`, §5) | MAYOR | Como mínimo: ER, despliegue, estados del préstamo y secuencia de la entrega con QR, cada uno con DG-01; DDL-03/04/06/07/08 del bloque A |
| MD-12 | 8.3, Anexo H | RT-01 relaciona RF, RNF, RN y RS con evidencia exacta | RT-01:58 marca RF-046 "Implementado" y no hay recordatorios; RF-047, 049 y 055-057 aparecen "Pendiente" pese a tener código parcial (la cabecera de `ReporteService.php` declara RF-055…057); columna (6) "Sin commit" aunque existe `593db78`; columna (4) vacía; sin filas RNF, RN ni RS | MAYOR | Rehacer RT-01 con los estados de la sección 3, commit completo y filas RNF/RN/RS |
| MD-13 | 14.4, Anexo I | Decisión técnica con aprobación | DT-023 (línea base), 024, 025, 026, 027, 029, 030 y 031 en estado "Propuesta", ya implementadas (por ejemplo, el compose de DT-031 y la dependencia de DT-029) | MAYOR (pendiente de aprobación) | Raúl aprueba o rechaza cada DT; las que se rechacen se revierten |
| MD-14 | 8.2, 11.1 (No funcional), 12, Tablas 19 y 25 | RNF medidos en QA: rendimiento (10 ejecuciones), 200 usuarios, disponibilidad | No hay pruebas de RNF-009/010/011; CP-036 simula la contención en un solo proceso; la medición de DT-031 es informal y en desarrollo | MAYOR | Prueba cronometrada y prueba básica de carga en QA con reporte adjunto |
| MD-15 | 10.4, DT-022 | API documentada para el cliente móvil futuro | La API v1 solo expone sesiones, catálogo e inventario (`routes/api.php`, 5 rutas); préstamos y QR no están en la API; no hay documento de la API | MENOR | Definir el alcance de la API v1 y documentarla (OpenAPI o Markdown) |
| MD-16 | 5.5 | Tokens QR "almacenados en Redis" con TTL | Se guardan en MySQL `tokens_qr` como hash SHA-256 (`PrestamoService.php:149-155`); DT-030 justifica el hash pero no el uso de MySQL en lugar de Redis | MENOR | Ampliar DT-030 (ver MD-37) |
| MD-17 | 13.1 | Controles mínimos de seguridad (cabeceras y abuso) | `nginx.conf:26-27` solo envía X-Frame-Options y X-Content-Type-Options; sin CSP, HSTS ni Referrer-Policy global; throttle solo en el inicio de sesión web y API; `/mostrador/validacion`, `/prestamos/{id}/token` y la API no tienen límite | MENOR | Agregar las cabeceras en nginx y limitadores por usuario en la API y en las operaciones de QR |
| MD-18 | 10.1 Tabla 21 | La rama identifica su requisito; commits atómicos | `feature/RF-011-gestion-prestamos` (RF-011 es código de barras) contiene el bloque A y RF-022…058; un solo commit de 170 archivos; 49 archivos sin commit, entre ellos DT y RT-01 | MENOR | Hacer commits por RF o tema; dividir en ramas en el futuro |
| MD-19 | 11.1 Tabla 23 | Pruebas unitarias de servicios, reglas y validadores | No existe `tests/Unit`; las 64 pruebas son Feature | MENOR | Pruebas unitarias de `PrestamoService`, `ReporteService`, `ResumenAuditoria` y `FormatoFecha` |
| MD-20 | 10.4 (Datos), 9.4 | Migraciones reversibles y probadas | Hay 11 `down()` en 11 migraciones, pero ni CI ni las pruebas ejecutan `migrate:rollback` | MENOR | Paso de CI: `migrate:fresh` → `migrate:rollback` → `migrate` |
| MD-21 | 11.2 | Gestión de defectos con severidad | No hay registro DEF-XXX; DEF-001 sigue abierto en el EI-01 de I0; CP-01 menciona defectos corregidos sin ID | MENOR | Registro de defectos en `docs/pruebas/` |
| MD-22 | 13.2, RN-012 | Retención de 1 + 3 años y log mínimo | Sin tarea de compresión ni depuración; RN-012 no está asignada a ningún RF ni CP | MENOR | Planificarla en el bloque D con su prueba |
| MD-23 | Anexos C e I | Campos completos en EI-01 y DT-01 | DT-013…018 y DT-020 no tienen los campos 5, 6, 8, 9 y 10 ("8–12. —"); `BBB_EI01_Publicas_v1.0.md` no sigue el EI-01 (le faltan los campos 4–8 y la decisión) | MENOR | Completar los formatos |
| MD-24 | 8.4 | Cambio de alcance mediante SC-01 y análisis de impacto | La retirada de Flutter (DT-022) y la línea base (DT-023) se registraron solo como DT | MENOR | Registrar SC-01 retroactivos |
| MD-25 | 16, Anexo K | DA-01 documento de análisis | No existe; DT-018 no lo excluye | MENOR | Elaborar DA-01 o excluirlo con un DT |
| MD-26 | 14.1 | La evidencia apunta al commit completo | `BBB_CP01_InterfazYBloqueA_v1.0.md`, campo (4): "rama de trabajo sin etiqueta" | MENOR | Volver a ejecutar sobre un commit y citar su hash |
| MD-27 | 5.5, RF-038, RNF-003 | Operación sin conexión | DT-022 punto 7: "pendiente de decisión"; RNF-003 sin umbral (REQ-01 §4) | MENOR | Decidirlo con DT/SC-01 (ver MD-36) |
| MD-28 | 5.1 | Límites de módulo de dominio | `app/Services` sin agrupar por módulo; controladores con consultas Eloquent (`AdministradorController.php:70`, `PanelController.php:112`, `AuditoriaController.php:65`) | OBSERVACIÓN | Llevar las consultas a servicios y agrupar por módulo de MD Tabla 13 |
| MD-29 | 6.1 | Builds reproducibles | `Dockerfile:25` (pecl install redis), `:37` (pecl install pcov) y paquetes apk sin versión | OBSERVACIÓN | Fijar las versiones de las extensiones |
| MD-30 | 7.1 | Exposición mínima | MySQL y Redis publicados en 0.0.0.0 (`docker-compose.yml:82,96`); contraseña de root en el healthcheck (`:87`) | OBSERVACIÓN | Publicar en 127.0.0.1 y no pasar la contraseña por la línea de comando |
| MD-31 | 11.1 | Pruebas reproducibles | Dos ejecuciones simultáneas sobre `spei_testing` dieron 64 fallos (sección 1) | OBSERVACIÓN | Usar una base por proceso (ejecución paralela de Pest o una base distinta por proceso) |
| MD-32 | 5.6, RNF-010 | Escalabilidad de la reserva | El lock por institución (`PrestamoService.php:504`) serializa todas las operaciones de la institución, con espera de 5 s | OBSERVACIÓN | Lock por recurso o por intervalo; medir con 200 usuarios |
| MD-33 | 4.1 | Calendario | Al 25/10 quedan 29 RF pendientes o parciales, con los bloques B–D programados del 28/10 al 16/11 | OBSERVACIÓN | Revisar la capacidad en el PI-01 del bloque B |
| MD-34 | 10.3 | Integridad del CI | Acciones por etiqueta, no por SHA (`ci.yml:56,58`) | OBSERVACIÓN | Fijar por SHA |
| MD-35 | 3.1–3.3, 7.2, Tablas 8, 9, 27 y 29 | Roles de cinco personas; "ningún autor puede aprobar su propio cambio" | Contradicción con el modo de desarrollador único (DT-018) | AJUSTE-MANUAL | Redacción sugerida para MD 3.3: "En modo de desarrollador único, el responsable aprueba y la revisión independiente la realiza un revisor distinto del autor (persona o agente automatizado de revisión), registrada en RED-01." |
| MD-36 | 1.3, 5.1, 5.5, Tabla 15, 6.1, 10.3 pasos 2–3 | Flutter, Dart, pubspec.lock, dart format, flutter_lints | Inaplicables por DT-022 | AJUSTE-MANUAL | Condicionar: "Cuando la liberación incluya cliente móvil..." y los pasos de CI correspondientes; para la web: "el modo sin conexión puede sustituirse por captura manual diferida con registro firmado". |
| MD-37 | 5.5 frente a MBD tokens_qr | Almacenamiento del QR | El MD exige Redis y el MBD define una tabla; la solución con hash en MySQL es más auditable | AJUSTE-MANUAL | "Los tokens QR se guardan como hash irreversible con TTL verificado en servidor, en Redis o en la tabla tokens_qr (MBD), sin persistir el valor en claro." |
| MD-38 | 2.4, 9.2, 14.2, 14.3 | SharePoint como fuente de documentos | Sin SharePoint en el modo de desarrollador único | AJUSTE-MANUAL | "SharePoint o la carpeta docs/ del repositorio controlado." |
| MD-39 | Tabla 14 | Laravel 11.x obligatorio | La rama 11.x ya no recibe parches de seguridad; DT-019 aprobada fue la mejor decisión | AJUSTE-MANUAL | "Laravel 11.x o una versión posterior con soporte de seguridad, registrada en DT-01." |
| MD-40 | Anexo K | Formato DA-01 | Titulado "Tabla 39. Formato de Registro de Liberación DA-01" con encabezado "REGISTRO DE LIBERACIÓN" (duplica la Tabla 39 de LI-01) | AJUSTE-MANUAL | Corregir: "Tabla 40. Formato de Documento de Análisis DA-01". |
| MD-41 | 4.1, Tabla 10 | Calendario fijo de 8 iteraciones | No prevé replanificación | AJUSTE-MANUAL | "El calendario puede replanificarse mediante DT-01/SC-01 sin reducir el alcance." |
| MD-42 | 10.3 | Pasos del CI | composer audit (exigido por 13.1, "dependencias revisadas") no figura entre los pasos | AJUSTE-MANUAL | Agregar el paso "auditoría de dependencias (composer audit) sin avisos". |

**Conteo por severidad:** CRÍTICO 3 · MAYOR 11 · MENOR 13 · OBSERVACIÓN 7 · AJUSTE-MANUAL 8 (42 en total).

## 5. Propuestas de NC-01 (MC Anexo C) para los "No" graves

| NC | Hallazgo | Requisito incumplido | Causa raíz probable |
|---|---|---|---|
| NC-P1 | MD-02 | MD 5.2 / 5.4; RF-016 a RF-021 | Se adelantó el MVP de préstamos (bloques C y D) sin el bloque B, del que depende (MD Tabla 13: Solicitudes depende de Usuarios) |
| NC-P2 | MD-03 | MD 7, 13.1 (HTTPS), 13.3 | El ambiente se diseñó solo para desarrollo en Windows (DT-021, DT-031) y la etapa prod quedó sin orquestación |
| NC-P3 | MD-04 | MD 5.6, RN-008 | Límite deliberado del MVP (BBB_MVP_v1.0.md, "Límites explícitos") que no se registró como desviación aprobada |
| NC-P4 | MD-05 | MD 5.3 | Se priorizó el filtrado web; los procesos de consola y de cola no se diseñaron con contexto institucional |
| NC-P5 | MD-08 / MD-09 | MD 10.1 a 10.3, 4.3 | Flujo de desarrollador único sin crear develop ni main; se trabajó por sesiones sin el ciclo PI-01 a EI-01 |
| NC-P6 | MD-12 | MD 8.3 | RT-01 se actualiza a mano, sin cotejarla con el código y las pruebas |

## 6. Qué falta para producción (en orden)

1. RF-016 a RF-021: alta de alumnos, docentes y asistentes (CSV e invitación por rol). Sin esto el sistema no opera.
2. Ambientes de QA y producción con TLS, .dockerignore, respaldo diario con restauración probada, prueba de humo y LI-01.
3. Ramas main y develop protegidas, PR y CI ejecutado en GitHub Actions como evidencia; commit de los 49 archivos pendientes.
4. Reserva por intervalo y prioridad RN-008 (RF-025 a RF-027), con prueba concurrente real.
5. Bloque D: sanciones (RF-041 a RF-045), recordatorios y correos (RF-046, RF-048, RF-050) y reportes restantes; además RF-030, RF-033, RF-040 (PDF) y la decisión sobre RF-038.
6. Contexto de tenant en comandos y colas; auditoría de intentos fallidos; cabeceras de seguridad y límites de peticiones.
7. Diagramas con DG-01, DDL del bloque A, API documentada, RT-01 corregida y RNF medidos en QA.
8. Aprobación de DT-023 a DT-031, PI-01 de los bloques B a D y EI-01 aceptados por Raúl.
