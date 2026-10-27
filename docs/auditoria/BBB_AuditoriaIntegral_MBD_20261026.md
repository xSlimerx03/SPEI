# Auditoría integral del SPEI contra el Manual de Base de Datos BBB-BD-004 v1.2

| Campo | Valor |
|---|---|
| Tipo | Auditoría interna de calidad (MC 1.8, MD 3.3 / 10.2), base para LV-01 / NC-01 |
| Alcance | Todo el MBD v1.2 (cap. 1–8 y Anexo A) contra el repositorio y la BD real |
| Rama / commit | `feature/RF-011-gestion-prestamos` · HEAD `593db78` (árbol con cambios sin confirmar en vistas y docs; migraciones y `docs/bd` sin cambios) |
| Fecha | 25/10/2026 |
| Auditor | `spei-auditor` (independiente, solo lectura) |
| Decisión final | Raúl Treviño Licona |

## 1. Método y evidencia

- Texto del manual: `tmp/pdfs/BBB_ManualBaseDatos_v1.2.txt` (normalizado en el scratchpad para lectura).
- BD real (contenedor `spei-db-1`, MySQL **8.4.11**, esquema `spei`): consultas de solo lectura sobre
  `information_schema` (TABLES, COLUMNS, STATISTICS, REFERENTIAL_CONSTRAINTS, KEY_COLUMN_USAGE,
  CHECK_CONSTRAINTS, TRIGGERS, ROUTINES, EVENTS), `mysql.user`, `mysql.role_edges`, `SHOW GRANTS` y
  SELECT de consistencia entre tenants.
- `php artisan migrate:status` y `php artisan migrate:rollback --pretend --step=11` (no ejecuta SQL).
- Lectura de `database/migrations/*`, `app/Models/*`, `app/Models/Scopes`, `app/Models/Concerns`,
  `app/Services/PrestamoService.php`, `config/database.php`, `docker-compose.yml`, `docker/mysql-init.sql`,
  `.env.example`, `docs/bd/*`, `docs/decisiones/BBB_DT_Registro_v1.0.md`, `docs/plan/BBB_PlanDesarrollo_v1.0.md`, `tests/Feature/*`.
- Estado de DT relevantes: **Aprobadas** DT-008, DT-009, DT-010, DT-011, DT-012, DT-016, DT-017, DT-022.
  **Propuestas (pendientes)**: DT-024, DT-025, DT-026, DT-030, DT-031. Lo cubierto por DT aprobada no es NC;
  lo cubierto por DT propuesta se marca "pendiente".

## 2. Tablas del MBD vs. esquema real

| # | Tabla MBD (3.2.1 / 3.3.2) | Existe | Diferencias frente al diccionario MBD 4.x | Cobertura |
|---|---|---|---|---|
| 1 | instituciones | Sí | + `dominio_correo` VARCHAR(100) UK (DT-024 b, **pendiente**); + `chk_instituciones_vigencia`; resto idéntico a Tabla 37 | Conforme (pend. DT-024) |
| 2 | areas | Sí | + `uk_areas_institucion_nombre_area` (DT-024 f, pendiente); columnas idénticas a Tabla 38 | Conforme |
| 3 | usuarios | Sí | `id_institucion` NULL solo superadmin (DT-011 aprobada; triggers DT-026 pendiente); resto idéntico a Tabla 39 | Conforme |
| 4 | recursos | Sí | + `id_institucion` (DT-008 aprobada); UK compuestas por tenant (Tabla 7 RF-010) | Conforme |
| 5 | prestamos | Sí | + `motivo` TEXT NOT NULL, + `fecha_actualizacion` (DT-030, pendiente); **falta `justificacion_prioridad`** (RN-014, diferida a RF-030) | Parcial |
| 6 | detalle_prestamo | Sí | + `id_institucion` (DT-008), + `fecha_actualizacion` (DT-030); **falta `evidencia_url`** y el valor ENUM **`no_entregado`** (diferidos a RF-039/RF-042) | Parcial |
| 7 | tokens_qr | Sí | + `id_institucion` (DT-008); `token` CHAR(64) hash SHA-256 en lugar de VARCHAR(255) "cifrado" (DT-030, pendiente) | Conforme (pend.) |
| 8 | penalizaciones | **No** | Bloque D del plan (RF-042…RF-045) | Faltante |
| 9 | matriz_sanciones | **No** | Bloque D (RF-041, RF-044) | Faltante |
| 10 | logs_auditoria | Sí | `id_institucion` NULL para eventos de plataforma (DT-024 c, pendiente); `fecha_hora` como `fecha_creacion` (DT-024 e); FK usuarios RESTRICT (DT-010 aprobada) | Conforme (pend.) |
| 11 | notificaciones | Sí | + `id_institucion` (DT-008) | Conforme |
| 12 | conversaciones | **No** | Bloque C (RF-033) | Faltante |
| 13 | historial_comprimido | **No** | Bloque D (RN-012, RF-052) | Faltante |
| — | planes (extra) | Sí | Tabla maestra de planes admitida por MBD Tabla 7 RF-007 "si se normaliza" (DT-024 a, pendiente) | Extensión |
| — | migrations, personal_access_tokens, failed_jobs | Sí | Infraestructura (DT-016 aprobada); sin COMMENT en columnas | Exceptuadas |

Resultado: **9 de 13 tablas** (69 %) y 3 columnas/valores del diccionario ausentes en las tablas existentes.
Vistas, procedimientos almacenados y eventos: **0** (`information_schema.ROUTINES` y `EVENTS` vacíos; ninguna
tabla con `TABLE_TYPE` distinto de `BASE TABLE`). Triggers: **4** (`trg_usuarios_before_insert`,
`trg_usuarios_before_update`, `trg_logs_auditoria_before_update`, `trg_logs_auditoria_before_delete`).

## 3. Lo que sí cumple (evidencia resumida)

- **Motor y servidor (MBD 2.1):** MySQL 8.4.11 (mínimo 8.0.28); `innodb_file_per_table=1`,
  `innodb_flush_log_at_trx_commit=1`; `character_set_server=utf8mb4`, `collation_server=utf8mb4_unicode_ci`;
  esquema `spei` y las 13 tablas en InnoDB `utf8mb4_unicode_ci`; `sql_mode` con `STRICT_TRANS_TABLES`.
- **COMMENT (3.3.1 p10):** 10 tablas de negocio con COMMENT y **0 columnas sin COMMENT**.
- **PK (Tabla 18):** todas `id_<entidad>` BIGINT UNSIGNED AUTO_INCREMENT; FK con el mismo tipo.
- **FK (Tabla 19):** 16 restricciones, todas `fk_<origen>_<destino>`, todas ON UPDATE CASCADE.
- **Cascadas (Tabla 27):** las 11 relaciones de la Tabla 27 cuyas tablas existen coinciden exactamente
  (incluidas `areas→usuarios` SET NULL, `usuarios→notificaciones`, `prestamos→detalle_prestamo` y
  `prestamos→tokens_qr` CASCADE); `usuarios→logs_auditoria` RESTRICT por DT-010 (aprobada); las 4 FK extra hacia
  `instituciones` (DT-008) son RESTRICT / CASCADE.
- **UK / índices (Tablas 20–21):** `uk_*` / `idx_*` normalizados (migración `2026_10_25_000001`); índices de consulta
  empiezan por `id_institucion`; RF-053 `idx_logs_auditoria_institucion_fecha_hora`; RN-002 `idx_recursos_institucion_estado`.
- **CHECK:** `chk_prestamos_periodo` (RF-023), `chk_instituciones_vigencia` y `chk_planes_limites` (RF-003).
- **ENUM (3.4.3):** `estado_suscripcion`, `rol`, `recursos.estado`, `prestamos.estado`, `tipo_token`,
  `condicion_entrega` idénticos a Tablas 28–34 (salvo `no_entregado`, ver BD-01).
- **Inalterabilidad frente a UPDATE/DELETE:** triggers con `SIGNAL SQLSTATE '45000'`; prueba
  `tests/Feature/BloqueATest.php:335-340`.
- **Multi-tenant:** `id_institucion` en las 8 tablas del tenant; `PerteneceAInstitucionTrait` + `AmbitoInstitucionScope`
  en `Area`, `Usuario`, `Recurso`, `Prestamo`, `DetallePrestamo`, `TokenQr`, `Notificacion`, `LogAuditoria`;
  pruebas de acceso cruzado (`BloqueATest.php:103`, `AlcancePrestamosTest.php:165`, `InterfazPrototipoTest.php:104`).
  Consultas de consistencia hijo/padre entre tenants: **0 filas** en 7 cruces.
- **Modelos Eloquent:** los 10 modelos declaran `$table`, `$primaryKey`, `CREATED_AT` y `UPDATED_AT`.
- **Concurrencia (3.4.1 p6, 7.1):** `DB::transaction` + `lockForUpdate` + `Cache::lock` de Redis
  (`app/Services/PrestamoService.php:90-99` y `504-505`); prueba `AlcancePrestamosTest.php:136`.
- **Migraciones (MD 9.4 / MBD 8.1):** 11 migraciones con `down()`; `migrate:rollback --pretend --step=11` genera el
  SQL inverso completo (31 sentencias DROP/ALTER) sin errores.
- **Redis:** caché, sesiones y colas en Redis (`.env`: `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION=redis`),
  locks de reserva, rate limiting de inicio de sesión (`AppServiceProvider.php:80`), AOF `appendfsync everysec`,
  `requirepass`, servicios `cola` y `programador` (DT-031, pendiente).
- **Carga inicial (8.2):** `DemoSeeder` solo en `local` (`DatabaseSeeder.php`); contraseñas desde configuración;
  BD de pruebas separada `spei_testing`.
- **Diccionario de préstamos:** `docs/bd/BBB_DDL06_DiccionarioPrestamos_v1.0.md` coincide columna a columna con la BD.

## 4. Tabla de hallazgos

| ID | Sección MBD | Requisito | Evidencia | Severidad | Acción propuesta |
|---|---|---|---|---|---|
| BD-01 | 3.1.1, 3.2.1, 3.3.2, 4.6, 4.7, 4.9, 4.10, 4.13, 4.14, Tabla 35 | El modelo físico tiene 13 tablas; diccionario completo | Faltan `penalizaciones`, `matriz_sanciones`, `conversaciones`, `historial_comprimido` (`information_schema.TABLES`); faltan `prestamos.justificacion_prioridad`, `detalle_prestamo.evidencia_url` y el valor ENUM `no_entregado` (COLUMNS). Planificadas en bloques C/D (`BBB_PlanDesarrollo_v1.0.md:24-25`) y diferidas por DT-030 (propuesta). Sin ellas no existen RN-005, RN-012, RN-014 ni RF-033, RF-041…RF-045 | **CRÍTICO** (bloquea la liberación 1.0; no es NC de proceso mientras el plan lo cubra) | Crear las 4 tablas y columnas con migración aditiva + DDL-03/06/07/08 al implementar sus RF; dejarlo como criterio de salida de los bloques C y D |
| BD-02 | 8.4, 6.1.3 r2, 6.2.4 Tabla 80, 3.4.5 p4 | Mínimo privilegio; `rol_backend` sin TRIGGER/EVENT/DDL; auditoría inalterable | `SHOW GRANTS FOR spei_user@%` devuelve `GRANT ALL PRIVILEGES ON spei.*` (incluye DROP, ALTER, TRIGGER, CREATE ROUTINE) con host `%`. `docker-compose.yml:74-75` activa `log-bin-trust-function-creators=ON` para que ese usuario cree triggers. Con DROP la app puede ejecutar `TRUNCATE logs_auditoria` (no dispara triggers DELETE y ninguna FK referencia la tabla) o `DROP TRIGGER trg_logs_auditoria_*`: la inalterabilidad de DT-010 solo cubre UPDATE/DELETE fila a fila | **CRÍTICO** | Separar cuentas: `spei_migrador` (DDL, solo en despliegue) y `spei_backend` (SELECT/INSERT/UPDATE/DELETE); sobre `logs_auditoria` solo SELECT e INSERT para la cuenta de ejecución; retirar `log-bin-trust-function-creators` del entorno de ejecución; documentar en DDL-01/DDL-02 |
| BD-03 | 5.1, 5.2, 6.1.3, 6.2.3, 6.2.4 | DDL-01/DDL-02; REQUIRE SSL, PASSWORD EXPIRE INTERVAL 90 DAY, FAILED_LOGIN_ATTEMPTS 5 / PASSWORD_LOCK_TIME 1; roles `rol_dba`, `rol_backend`, `rol_reportes`, `rol_backup`, `rol_auditor` | `mysql.user`: `spei_user` con `password_lifetime` NULL, `ssl_type` vacío y `Password_locking` NULL; `mysql.role_edges` vacío; `@@require_secure_transport=0`; no hay DDL-01/DDL-02 en `docs/bd/` | **MAYOR** | Elaborar DDL-01 y DDL-02 (matriz Tabla 80) y aplicarlos en QA/producción con script versionado (credenciales desde `.env` o gestor de secretos) |
| BD-04 | 5.0.2, 5.3–5.8, 6.0.1 r7-r8, 8.1 | Todo cambio estructural con su formato DDL, rollback y validaciones | `docs/bd/` solo contiene DDL-03/06/07/08 de préstamos. Sin formato: `planes`, `instituciones`, `areas`, `usuarios`, `recursos`, `logs_auditoria`, `notificaciones` (DDL-03/06/07/08) y los 4 triggers (DDL-04). Reconocido en `BBB_DDL06_DiccionarioPrestamos_v1.0.md:200-201` y como riesgo abierto en `BBB_PlanDesarrollo_v1.0.md:63` | **MAYOR** | Elaborar con `spei-datos` DDL-03/06/07/08 del bloque A y DDL-04 de los 4 triggers antes del EI-01 del bloque A |
| BD-05 | 8.3 Tabla 104, 5.0.2 r7, 5.3.8 | Respaldo y recuperación con verificación y trazabilidad; respaldo previo a cada DDL | No existe servicio, script, tarea programada ni procedimiento de respaldo o restauración; solo el volumen `db_data` (`docker-compose.yml:83-84`). La única mención es un `mysqldump` sugerido en `BBB_DDL03_TablasPrestamos_v1.0.md:193-194`. Redis sin `save` RDB (solo AOF) | **MAYOR** | Definir procedimiento (p. ej. `mysqldump --single-transaction --routines --triggers` programado, retención, prueba de restauración documentada) y registrar su ejecución; incluirlo en LI-01 |
| BD-06 | 1.5.2, 3.4.1, 7.2 (cuándo se efectuó), Tabla 35 RN-008 | Fechas confiables y coherentes en auditoría y desempates | `.env.example:17` `APP_TIMEZONE=America/Mexico_City`; conexión MySQL sin clave `timezone` (`config/database.php:74-92`); servidor `@@system_time_zone=UTC`. Eloquent escribe hora local en columnas TIMESTAMP, mientras los DEFAULT y `ON UPDATE CURRENT_TIMESTAMP` (instituciones, planes) escriben UTC. Evidencia: `MAX(logs_auditoria.fecha_hora)=2026-10-25 18:02:12` frente a `NOW()=2026-10-26 00:11:42` (desfase de 6 h) | **MAYOR** | Fijar una sola zona: almacenar en UTC (`APP_TIMEZONE=UTC` y conversión al presentar) o declarar la zona de la sesión MySQL en la conexión (`timezone` = -06:00); DT-01 y prueba de regresión |
| BD-07 | 3.4.5 p8, 4.14, 7.2, DT-010 | Ciclo de vida RN-012: compresión y depuración con evidencia mínima | No hay SP (`ROUTINES` vacío) ni job de historial. DT-010 prevé un "SP de historial" como única vía de depuración, pero `trg_logs_auditoria_before_delete` lanza SIGNAL incondicional: hoy RN-012 es inejecutable sin desactivar el trigger (o usar el TRUNCATE de BD-02) | **MAYOR** | Diseñar en DT-01 el mecanismo (p. ej. cuenta `spei_mantenimiento` + `sp_eliminar_historial_antiguo` con verificación de `CURRENT_USER()` en el trigger, o particionamiento por fecha) con DDL-04/DDL-05 |
| BD-08 | 1.5 (precedencia), registro DT | Desviaciones del MBD con DT aprobada | DT-024 (planes, `dominio_correo`, logs con `id_institucion` NULL, `fecha_hora`, UK de áreas), DT-025 (`contrasena_hash`), DT-026 (triggers de usuarios, excepción de índices de soporte de FK), DT-030 (`motivo`, `fecha_actualizacion`, CHAR(64), columnas diferidas) y DT-031 en estado **Propuesta** | **MENOR** (pendiente) | Raúl acepta o rechaza estas DT; hasta entonces las desviaciones cuentan como abiertas |
| BD-09 | 6.3.3 r5, 6.3.4 Tabla 84, 4.2 | `fecha_actualizacion` con ON UPDATE CURRENT_TIMESTAMP | `prestamos.fecha_actualizacion` y `detalle_prestamo.fecha_actualizacion`: TIMESTAMP NULL sin DEFAULT ni ON UPDATE (`EXTRA` vacío), a diferencia de `instituciones` y `planes` | **MENOR** | Migración aditiva `useCurrent()->useCurrentOnUpdate()` o justificarlo en DT-030 |
| BD-10 | 6.4.3 r8, 7.4, 8.6 | Probar triggers y reversibilidad | Ninguna prueba verifica `trg_usuarios_before_insert/update` (DT-026 punto 9 la promete; sin coincidencias en `tests/`). La reversibilidad no se ejercita: `RefreshDatabase` (`tests/Pest.php:40-44`) usa `migrate:fresh`, no `down()` | **MENOR** | Prueba Pest que espere `QueryException` 45000 en ambos casos; paso de CI `migrate:rollback` + `migrate` sobre `spei_testing` |
| BD-11 | 3.3.8 Tabla 25 | `<timestamp>_create_<tabla>_table.php`, `add_<col>_to_<tabla>`, `drop_<col>_from_<tabla>` | `2026_10_18_200001_create_prestamos_mvp_tables.php` (3 tablas, término "mvp"); `2026_10_25_000001_normalizar_nombres_prestamos.php` | **MENOR** | No renombrar migraciones ya ejecutadas; registrar la excepción en DT y aplicar la convención en adelante |
| BD-12 | 5.0.2 r8, 6.0.1 r10, 6.1.3 r8, 6.4.3 r10, 6.5.3 r10, 6.6.3 r10 | Registrar cada cambio estructural en `logs_auditoria` con `DDL_CHANGE` | Agrupación de `logs_auditoria.accion` sin `DDL_CHANGE` (solo CAMBIAR_PLAN, CARGAR_DEMO, CERRAR_SESION, CREAR, INICIAR_SESION, SOLICITAR_PRESTAMO, VENCER_PRESTAMO) | **MENOR** | Registrar `DDL_CHANGE` al migrar (listener de `MigrationEnded`) o ajustar el manual (BD-38) |
| BD-13 | 6.6.5 Tabla 97, 6.6.3 r4, firmas 5.x | Consultas a COLUMNS, KEY_COLUMN_USAGE y STATISTICS; formatos firmados | `BBB_DDL06_DiccionarioPrestamos_v1.0.md:33-44` usa COLUMNS y TABLE_CONSTRAINTS, sin KEY_COLUMN_USAGE ni STATISTICS; firmas "Pendiente" en los 4 DDL | **MENOR** | Completar las consultas obligatorias; firmar (Raúl como DBA y líder técnico, DT-018) |
| BD-14 | 3.1.3, 3.2.4 (Figuras 1 y 2) | DER conceptual y lógico/físico vigentes | `docs/diagramas/` vacío; el modelo real difiere del DER del manual (`planes`, `id_institucion` en 6 tablas, CHECK) | **MENOR** | Elaborar el DER del modelo físico real (nomenclatura DT-013) |
| BD-15 | 2.2 (configuración recomendada) | `maxmemory`, `maxmemory-policy`, `save 900/300/60` | `docker-compose.yml:94`: solo `--appendonly yes --appendfsync everysec --requirepass` | **MENOR** | Fijar `maxmemory`, política (ver BD-37) y `save` |
| BD-16 | Tabla 35 RN-008 | Índices en `prestamos.fecha_creacion` y `usuarios.rol` | Existe `idx_usuarios_institucion_rol`; ningún índice incluye `prestamos.fecha_creacion` (STATISTICS) | OBSERVACIÓN | Agregarlo al implementar RF-027/RF-030 si el plan de ejecución lo requiere |
| BD-17 | 3.4.3 | Referencias correctas al manual | `2026_10_18_000004_create_usuarios_table.php:42` dice "MBD 4.4 y Tabla 31"; los roles están en la Tabla 29 (la 31 es `prestamos.estado`) | OBSERVACIÓN | Corregir el comentario en la próxima modificación |
| BD-18 | 1.3, 3.4.4 RN-002, RS-006 | Aislamiento consistente | `app/Models/Scopes/AmbitoInstitucionScope.php` (método `apply`): sin contexto de tenant no filtra (falla abierto); el aislamiento depende de `TenantMiddleware` y Policies | OBSERVACIÓN | Preferir fallo cerrado salvo superadmin o consola declarados, o `withoutGlobalScope` explícito en tareas |
| BD-19 | 3.4.1 p4, 5.7.6 | Coherencia de `id_institucion` hijo/padre | Solo validada en servicios (DT-008); sin FK compuestas `(id_institucion, id_area)` ni `(id_institucion, id_prestamo)`. Hoy 0 inconsistencias en 7 cruces | OBSERVACIÓN | Evaluar FK compuestas contra UK `(id_institucion, id_<padre>)` |
| BD-20 | Tabla 7 RF-003/RF-007, DT-024 punto 9 | Límites coherentes con el plan | Instituciones con plan `basico` y límites 10/500 y con `enterprise` y 10/500, frente a `planes` (3/100 y 50/5000) | OBSERVACIÓN | Confirmar si son límites personalizados o datos de demostración; documentar la regla |
| BD-21 | 3.3.1 p10 | COMMENT en toda tabla y columna | `migrations` y `personal_access_tokens` sin COMMENT de tabla; 20 columnas de infraestructura sin COMMENT. DT-016 las saca del diccionario pero no exime el COMMENT | OBSERVACIÓN | Ampliar DT-016 para declarar la excepción |
| BD-22 | 2.2, 3.3.7, Tabla 92 | Caché de catálogo, vistas `vw_`, limpieza de tokens | Sin vistas; el catálogo no usa caché Redis (`Cache::` solo en `PrestamoService.php:504`); ninguna tarea depura `tokens_qr` usados o expirados | OBSERVACIÓN | Opcionales; considerar job de limpieza de `tokens_qr` y caché de catálogo si RNF-009 lo exige |
| BD-23 | 8.4 | Acceso restringido a la BD | Puertos publicados al anfitrión: MySQL `3307:3306`, Redis `6379:6379` (`docker-compose.yml`) | OBSERVACIÓN | Aceptable en desarrollo; el compose de QA/producción no debe publicarlos |
| BD-24 | 6.3.3 r7, 1.5.2 | Índices compuestos empiezan por `id_institucion` | `idx_instituciones_estado_fecha_corte (estado_suscripcion, fecha_corte)`; no figura entre las excepciones de DT-026 | OBSERVACIÓN | Incluirlo en DT-026 como tabla raíz (ver BD-31) |
| BD-25 | 3.4.5 p2, 5.7.6, 6.7.3 r3 frente a Tabla 27 y Tabla 102 | Coherencia interna del manual | El manual dice "solo RESTRICT y SET NULL" y a la vez define 5 ON DELETE CASCADE (resuelto por DT-009 aprobada) | AJUSTE-MANUAL | Redacción: "Las acciones ON DELETE siguen la Tabla 27; CASCADE solo en relaciones de dependencia (detalle, tokens, conversaciones, notificaciones)" |
| BD-26 | Tabla 27 (`usuarios→logs_auditoria` SET NULL) frente a 3.4.5 p4 | Coherencia interna | SET NULL modifica filas de la bitácora inalterable (DT-010 aprobada) | AJUSTE-MANUAL | "RESTRICT / CASCADE: los usuarios se desactivan, no se eliminan" |
| BD-27 | 4.8 Tabla 43, 3.2.2 Tabla 11, 1.5.3 "Token QR" | Mejor decisión del sistema | `token VARCHAR(255)` "token cifrado"; el sistema guarda el hash SHA-256 CHAR(64) del código aleatorio (irreversible: una fuga de la tabla no expone códigos vigentes) | AJUSTE-MANUAL | "token CHAR(64) UK NOT NULL: hash SHA-256 hexadecimal del código de un solo uso; el código en claro no se persiste" |
| BD-28 | 2.2, Tabla 35 RN-004 | Mejor decisión del sistema | "Redis expira automáticamente los tokens"; el sistema usa `fecha_expiracion` + `usado` + `FOR UPDATE` en MySQL como fuente única (más simple y auditable) | AJUSTE-MANUAL | "La vigencia se controla con `fecha_expiracion` y `usado`; Redis puede servir como caché o candado, no como única fuente" |
| BD-29 | 3.3.5 Tabla 22, 5.4.7, 6.4.3 r6 | Inaplicable | Exige COMMENT en triggers; CREATE TRIGGER de MySQL 8.4 no admite COMMENT | AJUSTE-MANUAL | "El propósito del trigger se documenta en su DDL-04 y con un comentario SQL dentro del cuerpo" |
| BD-30 | Tabla 7 (RF-004, RF-006, RF-014, RF-026, RF-030, RF-049), Tabla 35 RN-003/RN-005, 3.4.1 p7, 3.4.5 p3 frente a 7.2/7.3 | Contradicción / inaplicable | Prescribe triggers para notificar o sincronizar estados; un trigger MySQL no envía notificaciones y 7.2/7.3 permiten lógica de aplicación | AJUSTE-MANUAL | "Mediante trigger, procedimiento o lógica transaccional de la aplicación, con registro en logs_auditoria" |
| BD-31 | 6.3.3 r7, 5.3.8, 1.5.2 | Inaplicable tal cual | InnoDB exige un índice con prefijo de la columna FK; las tablas globales (instituciones, planes) no tienen tenant (DT-026, propuesta) | AJUSTE-MANUAL | "Los índices de consulta de tablas del tenant empiezan por id_institucion; se exceptúan los de soporte de FK y las tablas globales" |
| BD-32 | 4.5, 4.7, 4.8, 4.9, 4.12, 4.13 frente a 3.4.1 p4, 6.3.3 r3, Tabla 7 RF-010 | Contradicción | El diccionario omite id_institucion en 6 tablas del tenant y define UNIQUE(numero_serie, id_institucion) sobre una columna inexistente (DT-008 aprobada) | AJUSTE-MANUAL | Agregar id_institucion BIGINT UNSIGNED FK NOT NULL a esas fichas |
| BD-33 | 4.4 Tabla 39, 4.11 Tabla 46 | Contradicción con RF-007 | usuarios.id_institucion y logs_auditoria.id_institucion NOT NULL son incompatibles con el superadmin sin tenant (DT-011 aprobada; DT-024 c propuesta) | AJUSTE-MANUAL | "NULL solo para el rol superadmin y eventos de plataforma, garantizado por trigger" |
| BD-34 | 3.3.3 Tabla 16 frente a 4.3 Tabla 38 y 6.3.4 Tabla 84 | Contradicción | La Tabla 16 marca correcto "nombre" e incorrecto "nombre_area" en areas, pero el diccionario usa nombre_area y la Tabla 84 nombre_<entidad> | AJUSTE-MANUAL | Unificar a nombre_<entidad> y corregir el ejemplo de la Tabla 16 |
| BD-35 | 5.0.2 r1, 6.0.1 r2 y r5 frente al MC | Precedencia MC | Archivo DDL-XX_AAAAMMDD_descripcion.sql y fechas ISO frente a la nomenclatura MC BBB_<código>_<nombre>_vX.Y y fechas DD/MM/AAAA de sus formatos (los DDL del repo siguen al MC). Sin DT que lo registre | AJUSTE-MANUAL | Registrar DT-01 (precedencia MC) y alinear el MBD con el MC |
| BD-36 | 5.0.1 Tabla 50, 5.0.2 r2, 6.0.1 r4, 5.7 frente a Tabla 52 | Errores editoriales | La Tabla 50 lista 7 formatos (falta DDL-08); "v1.1, v1.1"; "v1.1 para la primera versión"; DDL-07 (5.7) no trae validaciones previas/posteriores aunque la Tabla 52 fija 9 secciones y 6.7.3 r7-r8 exige verificar huérfanos y tipos | AJUSTE-MANUAL | Agregar DDL-08 a la Tabla 50; "v1.0 para la primera versión"; añadir secciones 6 y 7 a DDL-07 |
| BD-37 | 2.2 (configuración recomendada) | Riesgo técnico del manual | maxmemory-policy allkeys-lru en la instancia que también aloja colas, locks y sesiones puede expulsar trabajos y candados de reserva | AJUSTE-MANUAL | "noeviction (o volatile-lru) en la instancia de colas, locks y sesiones; allkeys-lru solo en una instancia exclusiva de caché" |
| BD-38 | 5.0.2 r8 y reglas DDL_CHANGE | Mejor alternativa | logs_auditoria es una bitácora de negocio por tenant; los cambios de esquema ya quedan en la tabla migrations, Git y el formato DDL | AJUSTE-MANUAL | "El cambio estructural se evidencia con el formato DDL, la migración versionada y la tabla migrations; opcionalmente un evento DDL_CHANGE con id_institucion NULL" |
| BD-39 | 3.4.5 p1, 1.5.5, Tabla 7 RF-038/RF-040/RF-047 | Inaplicable / fuera de alcance | "No se permite un INSERT sin BEGIN...COMMIT" es inaplicable a inserciones atómicas simples (autocommit); Flutter, SQLite/Hive y FCM quedan fuera de alcance (DT-022 aprobada) | AJUSTE-MANUAL | "Las operaciones críticas de varias sentencias son transaccionales"; marcar Flutter como cliente futuro |

## 5. Conteo por severidad

| Severidad | Cantidad | IDs |
|---|---|---|
| CRÍTICO | 2 | BD-01, BD-02 |
| MAYOR | 5 | BD-03, BD-04, BD-05, BD-06, BD-07 |
| MENOR | 8 | BD-08 a BD-15 |
| OBSERVACIÓN | 9 | BD-16 a BD-24 |
| AJUSTE-MANUAL | 15 | BD-25 a BD-39 |

## 6. Calificación de cumplimiento del MBD

| # | Rubro | Peso | Puntaje | Justificación |
|---|---|---|---|---|
| 1 | Modelo físico: tablas y columnas requeridas | 15 | 9.0 | 9/13 tablas (69 %); faltan 3 columnas/valores en préstamos; planes admitida por Tabla 7 |
| 2 | Columnas: tipos, NULL, defaults, COMMENT | 10 | 8.0 | Tipos y defaults iguales al diccionario; 0 columnas sin COMMENT; fecha_actualizacion sin ON UPDATE (BD-09); desviaciones en DT propuestas |
| 3 | Nomenclatura (tablas, columnas, PK, FK, UK, idx, trg, migraciones) | 8 | 7.0 | Todo normalizado tras la migración 2026_10_25_000001; migraciones fuera de la Tabla 25 (BD-11); índice de instituciones (BD-24) |
| 4 | Integridad referencial, cascadas (Tabla 27), CHECK, ENUM | 10 | 9.0 | 100 % de las relaciones existentes coinciden con Tabla 27 / DT-009 / DT-010; 3 CHECK; ENUM conformes; coherencia de tenant solo en aplicación (BD-19) |
| 5 | Multi-tenant (id_institucion, Global Scope, índices, pruebas) | 10 | 8.5 | Columna, FK, scope y pruebas de acceso cruzado presentes; scope falla abierto (BD-18); excepciones de DT-026 pendientes |
| 6 | Triggers, procedimientos almacenados y vistas | 7 | 2.5 | 4 triggers correctos; sin DDL-04 ni pruebas de los de usuarios; 0 SP; RN-005 y RN-012 sin mecanismo; depuración bloqueada (BD-07) |
| 7 | Seguridad y cuentas de BD (DDL-01/02, mínimo privilegio) | 12 | 3.0 | La app no usa root y los secretos están en .env; pero ALL PRIVILEGES, host %, sin SSL, sin política de contraseñas ni roles; bitácora truncable (BD-02, BD-03) |
| 8 | Respaldos y recuperación | 6 | 0.5 | Solo volumen persistente; sin procedimiento, verificación ni trazabilidad (BD-05) |
| 9 | Migraciones y control de cambios | 7 | 5.5 | Solo migraciones, todas reversibles en modo pretend; rollback no probado, sin DDL_CHANGE, nombres fuera de convención |
| 10 | Formatos DDL y diccionario de datos | 10 | 3.5 | DDL-03/06/07/08 de préstamos completos y fieles a la BD; bloque A y DDL-01/02/04/05 inexistentes; firmas pendientes; sin DER (BD-04, BD-13, BD-14) |
| 11 | Redis (locks, caché, colas, sesiones, tokens) | 3 | 2.5 | Locks, colas, sesiones, caché y rate limiting en Redis con AOF; faltan maxmemory y save; tokens en MySQL (mejor decisión, BD-28) |
| 12 | Motor, charset, collation y configuración del servidor | 2 | 1.5 | MySQL 8.4.11, InnoDB, utf8mb4_unicode_ci, parámetros de MBD 2.1; zona horaria incoherente (BD-06) |
| | **Total** | **100** | **60.5** | **Cumplimiento parcial: 61 / 100** |

Lectura: lo construido (bloque A + préstamos) tiene buena calidad estructural (rubros 2 a 5 entre 85 y 90 %);
el puntaje baja por el modelo incompleto, la seguridad de cuentas, la falta de respaldos y la documentación DDL del bloque A.

## 7. Propuestas de NC-01 (MC Anexo C) para hallazgos graves

| NC propuesta | Hallazgo | Requisito incumplido | Causa raíz probable |
|---|---|---|---|
| NC-BD-A | BD-02 | MBD 8.4, 6.1.3 r2, Tabla 80; RNF-005 (inalterabilidad) | Se reutilizó el usuario que crea la imagen oficial (MYSQL_USER recibe ALL sobre la BD) para ejecutar la app y las migraciones; no se diseñó la separación entre cuenta de despliegue y cuenta de ejecución |
| NC-BD-B | BD-03 | MBD 5.1, 5.2, 6.1.3, 6.2.3 | DDL-01/02 no figuran en el plan de iteraciones; el entorno solo contempla desarrollo |
| NC-BD-C | BD-04 | MBD 5.0.2 r4, 8.1; MD 9.4; CLAUDE.md sección 4 | Las migraciones del bloque A se escribieron antes de adoptar el flujo con formato DDL; el riesgo quedó abierto sin fecha de cierre |
| NC-BD-D | BD-05 | MBD 8.3 Tabla 104, 5.0.2 r7 | No existe aún LI-01 ni plan de operación; el compose cubre solo desarrollo |
| NC-BD-E | BD-06 | MBD 7.2 (cuándo), RNF-005 | APP_TIMEZONE local sin declarar la zona de la sesión MySQL; mezcla de valores asignados por Eloquent y por DEFAULT de la BD |
| NC-BD-F | BD-07 | MBD 3.4.5 p8, 7.2 (eliminación con evidencia), RN-012, DT-010 | DT-010 asume un SP de depuración que el trigger incondicional impide; falta diseño del ciclo de vida |
| (seguimiento) | BD-01 | MBD 3.2.1, 4.6 a 4.14 | Alcance por bloques; no es NC mientras el plan (bloques C y D) lo cubra, pero es criterio de salida de la liberación |

## 8. Qué falta para producción (criterios de salida propuestos)

1. Tablas penalizaciones, matriz_sanciones, conversaciones e historial_comprimido, y las columnas
   justificacion_prioridad, evidencia_url y el valor no_entregado, con sus DDL (BD-01).
2. Cuentas de BD separadas y con mínimo privilegio; logs_auditoria solo SELECT/INSERT para la app; SSL y política de
   contraseñas; DDL-01/DDL-02 (BD-02, BD-03).
3. Procedimiento de respaldo y restauración probado y registrado (BD-05).
4. Zona horaria única entre aplicación y BD, con corrección de datos si hace falta (BD-06).
5. Mecanismo de ciclo de vida RN-012 compatible con la inalterabilidad (BD-07).
6. DDL-03/04/06/07/08 del bloque A, DER actualizado y firmas (BD-04, BD-13, BD-14).
7. Aceptación o rechazo por Raúl de DT-024, DT-025, DT-026, DT-030 y DT-031 (BD-08).

## 9. Decisión propuesta

**Aceptar con correcciones** el diseño de datos implementado (bloque A y préstamos) como base de los bloques
siguientes; **no apto para producción** hasta cerrar BD-01 a BD-07. La decisión final corresponde a Raúl.
