# Registro de Decisiones Técnicas (DT-01) — SPEI

| Campo | Valor |
|---|---|
| Documento | BBB_DT_Registro_v1.0 |
| Formato | DT-01 (Manual de Desarrollo v1.2, Anexo I) |
| Versión | 1.0 |
| Fecha | 18/10/2026 |
| Autor | Raúl Treviño Licona |

Cada decisión conserva los 12 campos del formato DT-01: (1) ID y título, (2) fecha y estado,
(3) contexto y problema, (4) requisitos y restricciones, (5) alternativas, (6) criterios,
(7) decisión, (8) consecuencias, (9) riesgos y mitigaciones, (10) responsables, (11) aprobación,
(12) decisión reemplazada. Regla general: ante contradicción gana el manual de mayor precedencia
(MC > MD > MP / MBD, MD 1.5); si la precedencia no resuelve, se aplica el criterio más específico y
más seguro.

---

## DT-001 · Convención de ramas
1. **DT-001** Convención de nombres de ramas. 2. 18/10/2026 · Aprobada.
3. MD Tabla 21 define `feature/`, `fix/DEF-XXX`, `hotfix/vX.Y.Z`, `docs/`; MP Tabla 81–82 define `feature/`, `bugfix/`, `hotfix/`, `release/`.
4. MD 1.5 (precedencia), MD 10.1. 5. a) MD; b) MP; c) unión. 6. Precedencia y trazabilidad a DEF-XXX.
7. Se usa la convención del MD: `main`, `develop`, `feature/RF-XXX-descripcion`, `fix/DEF-XXX-descripcion`, `hotfix/vX.Y.Z-descripcion`, `docs/BBB-codigo-descripcion`. No se usan `bugfix/` ni `release/`; la preparación de versión se hace en `develop` y se etiqueta en `main`.
8. Cada corrección queda ligada a un defecto. 9. Ninguno relevante. 10. Raúl Treviño. 11. Raúl Treviño, 18/10/2026. 12. —

## DT-002 · Versionado de imágenes Docker
1. **DT-002** Imágenes fijadas por etiqueta y digest. 2. 18/10/2026 · Aprobada.
3. MP 3.5.2 usa `phpmyadmin:latest`, `nginx:alpine`, `mysql:8.0`; MD 6.1 exige etiqueta de versión y, cuando sea posible, digest.
4. MD 6.1, MD Tabla 16. 5. a) etiquetas flotantes; b) etiqueta + digest. 6. Reproducibilidad.
7. Todas las imágenes se fijan por etiqueta y digest (nginx 1.27, mysql 8.4, redis 7.4, phpmyadmin 5.2, mailhog v1.0.1, php 8.3-fpm, composer 2.8). Una actualización mayor requiere nuevo DT y regresión.
8. Builds reproducibles; actualizar implica editar el digest. 9. Digest retirado del registro → se re-fija con DT. 10. Raúl. 11. Raúl, 18/10/2026. 12. —

## DT-003 · Secretos y servicios de desarrollo
1. **DT-003** Credenciales fuera del compose y servicios solo de desarrollo. 2. 18/10/2026 · Aprobada.
3. MP 3.5.3 escribe contraseñas en `docker-compose.yml` y MP 3.5.5 pide entorno idéntico en producción; MD 7.1 exige secretos fuera del repositorio y prohíbe exponer phpMyAdmin y MailHog en producción.
4. MD 7.1, MD 13.1. 5. a) literal MP; b) variables `${}` desde `.env` + perfil `dev`. 6. Seguridad, precedencia.
7. El compose toma credenciales de `.env` (excluido de Git); `.env.example` documenta cada variable sin valores secretos; phpMyAdmin y MailHog viven en el perfil `dev` (`COMPOSE_PROFILES=dev`). Los ambientes son idénticos en servicios de aplicación, no en herramientas de desarrollo.
8. Hay que crear `.env` antes de levantar. 9. Olvido de `.env` → el compose falla en vez de usar claves débiles. 10. Raúl. 11. Raúl, 18/10/2026. 12. —

## DT-004 · Estructura del repositorio
1. **DT-004** Monorepo según MP Figura 10. 2. 18/10/2026 · Aprobada; **parcialmente reemplazada por DT-022** (sin Flutter).
3. MP Figura 10 ubica en la raíz `docker/`, `docker-compose.yml`, `.env`, `app/` (Laravel), `lib/` (Flutter), `database/`, `tests/`.
4. MP 3.5.6. 5. a) monorepo en raíz; b) carpetas `backend/` y `mobile/`. 6. Cumplimiento literal.
7. Laravel ocupa la raíz (`app/`, `database/`, `tests/`, `composer.json`). Por DT-022 no se crean `lib/`, `pubspec.yaml` ni `android/`.
8. Un solo repo y un solo CI. 9. Herramientas que escanean la raíz → exclusiones en `phpstan.neon`. 10. Raúl. 11. Raúl, 18/10/2026. 12. —

## DT-005 · PHPStan 2.x y Larastan
1. **DT-005** Configuración compatible con PHPStan 2.x. 2. 18/10/2026 · Aprobada.
3. MP 3.1.3 incluye `checkMissingIterableValueType` y `checkGenericClassInNonGenericObjectType`, eliminadas en PHPStan 2.x; con ellas el analizador no arranca. MD Tabla 17 exige PHPStan 2.x nivel 6.
4. MD Tabla 17, MP 3.1. 5. a) PHPStan 1.x; b) 2.x sin esas opciones. 6. Precedencia del MD.
7. PHPStan 2.x nivel 6 con Larastan 3.x; se retiran las dos opciones. Los tipos iterables se documentan con PHPDoc genérico (`array<int, string>`), lo que además mejora la calidad.
8. Más exigencia de PHPDoc en arreglos. 9. — 10. Raúl. 11. Raúl, 18/10/2026. 12. —

## DT-006 · Puertos del equipo anfitrión
1. **DT-006** Puertos del host configurables. 2. 18/10/2026 · Aprobada.
3. MP 3.5.3 publica 80 y 3306; en el equipo de desarrollo ya están ocupados por otros servicios.
4. MP 3.5. 5. a) liberar puertos; b) variables con valor por defecto. 6. Portabilidad.
7. Los puertos internos de los contenedores no cambian (80, 3306, 6379); los del anfitrión se leen de `.env`: `APP_PORT=8000`, `FORWARD_DB_PORT=3307`, `FORWARD_REDIS_PORT=6379`, `FORWARD_PMA_PORT=8080`, `FORWARD_MAILHOG_PORT=8025`.
8. URL local `http://localhost:8000`. 9. — 10. Raúl. 11. Raúl, 18/10/2026. 12. —

## DT-007 · Autenticación
1. **DT-007** Sanctum + carga CSV; SSO opcional. 2. 18/10/2026 · Aprobada.
3. MP 3.3.2 menciona LDAP/SSO institucional y JWT; MD 5.4 fija Laravel Sanctum con base de usuarios, dominios y roles cargada por CSV, y deja SSO (OIDC/OAuth 2.0) como extensión opcional.
4. MD 5.4, MD Tabla 28 (riesgo de identidad). 5. a) LDAP; b) JWT; c) Sanctum. 6. Precedencia, riesgo.
7. Sanctum 4.x con tokens de API (clientes de la API REST) y sesión con cookie HttpOnly (Blade). Expiración: 24 h acceso (MP Tabla 85). Rol derivado del dominio del correo y de la asignación registrada. Sin JWT ni LDAP en la liberación 1.0.
8. — 9. — 10. Raúl. 11. Raúl, 18/10/2026. 12. —

## DT-008 · `id_institucion` en todas las tablas del tenant
1. **DT-008** Discriminador de tenant completo. 2. 18/10/2026 · Aprobada.
3. MD 5.3 y MBD 6.3.3 regla 3 exigen `id_institucion` en toda entidad del tenant e índices que comiencen por ella; el diccionario MBD 4 no la incluye en `recursos`, `detalle_prestamo`, `tokens_qr`, `penalizaciones`, `notificaciones` ni `conversaciones`, y MBD 1.6 define `UNIQUE(numero_serie, id_institucion)` sobre una columna inexistente.
4. MD 5.3 (mayor precedencia), RS-006. 5. a) derivar el tenant por JOIN; b) agregar la columna. 6. Precedencia, seguridad, rendimiento.
7. Se agrega `id_institucion BIGINT UNSIGNED NOT NULL` con FK `fk_<tabla>_instituciones` (RESTRICT / CASCADE) a esas seis tablas. Cada cambio se documenta con DDL-03 y DDL-08 y se refleja en el diccionario (DDL-06). Se valida por aplicación que el `id_institucion` de un hijo coincida con el de su padre.
8. Global Scope uniforme en todos los modelos. 9. Inconsistencia hijo/padre → validación en servicio + prueba. 10. Raúl. 11. Raúl, 18/10/2026. 12. —

## DT-009 · Política de cascadas
1. **DT-009** Cascadas según MBD Tabla 27. 2. 18/10/2026 · Aprobada.
3. MBD 3.4.5 punto 2 y MBD 5.7.6 dicen "solo RESTRICT y SET NULL", pero MBD Tabla 27 define ON DELETE CASCADE en 5 relaciones (usuarios→notificaciones, prestamos→detalle_prestamo, prestamos→tokens_qr, prestamos→conversaciones) y MBD Tabla 102 lo confirma para "Dependencia".
4. MBD 3.4.2. 5. a) solo RESTRICT/SET NULL; b) Tabla 27. 6. Especificidad (la tabla define relación por relación).
7. Se aplica MBD Tabla 27 tal cual; ON UPDATE CASCADE en todas las FK. En la práctica los préstamos no se eliminan (cambian de estado), por lo que el CASCADE solo actúa ante depuraciones controladas.
8. — 9. Borrado accidental → los préstamos no exponen DELETE en la API. 10. Raúl. 11. Raúl, 18/10/2026. 12. —

## DT-010 · Inalterabilidad de `logs_auditoria`
1. **DT-010** FK usuarios → logs con RESTRICT. 2. 18/10/2026 · Aprobada.
3. MBD 3.4.5 punto 4 prohíbe UPDATE y DELETE sobre `logs_auditoria` (RNF-005), pero MBD Tabla 27 define `usuarios → logs_auditoria ON DELETE SET NULL`, que actualiza el log.
4. RNF-005, MD 13.1. 5. a) SET NULL; b) RESTRICT + baja lógica. 6. Inalterabilidad (requisito no funcional).
7. `fk_logs_auditoria_usuarios` ON DELETE RESTRICT. Los usuarios nunca se eliminan físicamente: se desactivan (`activo = FALSE`). Triggers `trg_logs_auditoria_before_update` y `..._before_delete` lanzan `SIGNAL SQLSTATE '45000'`. Solo la eliminación por ciclo de vida RN-012 (SP de historial) puede depurar, dejando log mínimo.
8. — 9. — 10. Raúl. 11. Raúl, 18/10/2026. 12. —

## DT-011 · Superadmin sin institución
1. **DT-011** `usuarios.id_institucion` nulo solo para superadmin. 2. 18/10/2026 · Aprobada.
3. MBD 4.4 declara `id_institucion NOT NULL`, pero el rol `superadmin` (dueño del SaaS, RF-007) no pertenece a ningún tenant.
4. RF-007, MD 5.3. 5. a) tenant ficticio "plataforma"; b) columna nula condicionada. 6. Claridad del modelo.
7. `id_institucion` NULL permitido y `CHECK ((rol = 'superadmin' AND id_institucion IS NULL) OR (rol <> 'superadmin' AND id_institucion IS NOT NULL))`.
8. El superadmin no pasa por el Global Scope de tenant. 9. Escalamiento de privilegios → Policy específica y prueba. 10. Raúl. 11. Raúl, 18/10/2026. 12. —

## DT-012 · Unicidad del correo institucional
1. **DT-012** `uk_usuarios_correo_institucional` global. 2. 18/10/2026 · Aprobada.
3. MBD 4.4 define UNIQUE global; MD 5.3 pide que la unicidad local al tenant incluya `id_institucion`.
4. MD 5.3, RN-001. 5. a) global; b) `(id_institucion, correo)`. 6. El correo identifica al tenant en el login.
7. Se mantiene global: el inicio de sesión resuelve institución y rol a partir del correo; un correo pertenece a un solo dominio institucional. No contradice MD 5.3 porque esta unicidad no es local al tenant.
8. — 9. — 10. Raúl. 11. Raúl, 18/10/2026. 12. —

## DT-013 · Nomenclatura de diagramas
1. **DT-013** Prefijos del Manual de Calidad. 2. 18/10/2026 · Aprobada.
3. MD 9.3 usa `BBB_DG_[Tipo]_[Modulo]_vX.Y`; MC 5.5.13 usa `BBB_<prefijo>_<nombre>_vX.Y` con DCU, DCL, DSE, DAC, DES, DCO, DDE, DER, DFL.
4. MC 5.5.13 (mayor precedencia). 7. Se usa la nomenclatura del MC; el registro de diagrama conserva el código DG-XXX (MD Anexo F).
8–12. — · Raúl, 18/10/2026.

## DT-014 · Tipografía de interfaz
1. **DT-014** Calibri con Carlito como equivalente libre. 2. 18/10/2026 · Aprobada.
3. MP 2.6.3 fija Calibri en pantalla; Calibri es propietaria y no puede distribuirse con el sistema; MP 2.9.3 y B.6.3 exigen recursos con licencia libre o adquirida.
4. MP 2.6.3, MP B.6.3. 5. a) Roboto; b) Carlito (OFL, métricamente idéntica a Calibri). 6. Fidelidad visual + licencia.
7. Web: `font-family: Calibri, Carlito, sans-serif` (Carlito servida localmente). Se registra en `CREDITS.md`.
8–12. — · Raúl, 18/10/2026.

## DT-015 · Umbral de cobertura
1. **DT-015** Cobertura mínima única de 80 % en backend. 2. 18/10/2026 · Aprobada.
3. MP Tabla 89 pide 70 % en integración; MD Tabla 17 y 10.3 exigen ≥ 80 % en backend crítico.
7. CI falla si la cobertura de `app/` es < 80 % (`pest --coverage --min=80`). Sin código Dart (DT-022).
8–12. — · Raúl, 18/10/2026.

## DT-016 · Tablas internas del framework
1. **DT-016** Excepción de nomenclatura para tablas de Laravel. 2. 18/10/2026 · Aprobada.
3. Laravel y Sanctum crean `migrations`, `personal_access_tokens`, `jobs`, `job_batches`, `failed_jobs`, `cache`, `sessions`, en inglés y con PK `id`, contra MBD 3.3.
7. Se conservan solo las indispensables (`migrations`, `personal_access_tokens`, `failed_jobs`) como tablas de infraestructura, fuera del diccionario de negocio. Caché, sesiones y colas usan Redis (MD Tabla 16), por lo que se eliminan las migraciones de `cache`, `sessions`, `jobs` y `job_batches`. La tabla `users` se sustituye por `usuarios` (MBD 4.4).
8–12. — · Raúl, 18/10/2026.

## DT-017 · Versión de MySQL y PHP
1. **DT-017** MySQL 8.4 LTS y PHP 8.3. 2. 18/10/2026 · Aprobada.
3. MP 3.5.2 usa `mysql:8.0` y `php:8.2-fpm`; MD Tabla 14 fija PHP 8.3 (mínimo 8.2) y MD Tabla 16 admite MySQL 8.0 u 8.4 LTS; MBD 2.1 exige ≥ 8.0.28.
7. PHP 8.3-FPM y MySQL 8.4 LTS, con `innodb_file_per_table=ON` e `innodb_flush_log_at_trx_commit=1` (MBD 2.1).
8–12. — · Raúl, 18/10/2026.

## DT-018 · Desarrollador único
1. **DT-018** Adaptación de roles y aprobaciones. 2. 18/10/2026 · Aprobada.
3. Los manuales asignan aprobación a David, revisión a otro integrante y aceptación final al docente; el desarrollo lo realiza una sola persona.
7. Raúl ejecuta todos los roles RUP y aprueba. La revisión independiente (MD 3.3, MD 10.2, MC 1.8) la realiza el subagente `spei-auditor`, nunca el agente autor. Se omiten AR-01, JD-01, SP-01 y EVS-01; su contenido útil se consolida en PI-01 y EI-01. Las iteraciones se replanifican en `docs/plan/BBB_PlanDesarrollo_v1.0.md`.
8–12. — · Raúl, 18/10/2026.

## DT-019 · Actualización de Laravel 11.x a 12.x por seguridad
1. **DT-019** Laravel 12.70 en lugar de Laravel 11.x. 2. 18/10/2026 · **Aprobada** (desviación controlada de MD Tabla 14).
3. `composer audit` reportó 4 avisos sobre `laravel/framework` 11.57.0 sin parche en la rama 11.x, que ya no recibe correcciones de seguridad: inyección CRLF en la regla `email` (alta, GHSA-5vg9-5847-vvmq / CVE-2026-48019), confusión de ruta en URLs firmadas temporales (media, GHSA-crmm-hgp2-wgrp) y XSS en la página de depuración (baja, CVE-2026-102279). Las correcciones existen solo a partir de 12.69.0 y 13.30.0. MD Tabla 14 declara Laravel 11.x obligatoria, pero MD 13.1 exige dependencias revisadas y la seguridad es requisito del producto (SRS 3.5).
4. MD Tabla 14, MD Tabla 17 (Pest 3.x obligatorio), MD 13.1, MD 6.1. 5. a) Mantener 11.x con mitigaciones (riesgo residual permanente); b) Laravel 12.x; c) Laravel 13.x (exige Pest 4, segunda desviación del MD). 6. Seguridad, menor desviación posible de los manuales, compatibilidad con Pest 3, Sanctum 4 y Larastan 3, soporte de seguridad que cubra la liberación (12.x lo tiene hasta febrero de 2027).
7. **Laravel 12.70.0** (`laravel/framework:^12.70`) con `laravel/tinker:^2.10.1` y `nunomaduro/collision:^8.6`. Se conservan PHP 8.3, Sanctum 4.3, Pest 3.8, Pint y Larastan 3 / PHPStan 2. Toda mención a "Laravel 11" en documentos y agentes del proyecto se entiende como Laravel 12.x. Una nueva versión mayor requiere otro DT.
8. `composer audit` sin avisos; la estructura de Laravel 11 (`bootstrap/app.php`, sin Kernel) es la misma en 12, por lo que no hubo cambios de código. Verificado: migraciones, Pest (3/3), Pint y PHPStan 6 en verde, HTTP 200. 9. Paquetes de terceros que solo soporten 11.x → verificar compatibilidad antes de instalarlos. 10. Raúl Treviño. 11. Raúl Treviño, 18/10/2026. 12. —

## DT-020 · Formato de cabecera frente a Pint
1. **DT-020** Desactivar `phpdoc_separation` del preset `laravel`. 2. 18/10/2026 · Aprobada.
3. El preset `laravel` de Pint incluye la regla `phpdoc_separation`, que inserta una línea en blanco entre cada etiqueta y altera la plantilla de cabecera definida en MP 1.1.4; el `pint.json` del MP 1.2.7.1 no menciona la regla.
4. MP 1.1.4, MP 1.2.7.1. 5. a) cabecera reformateada; b) desactivar la regla. 6. La plantilla del MP es explícita.
7. `"phpdoc_separation": false` en `pint.json`; las demás reglas del MP se conservan. 8–12. — · Raúl, 18/10/2026.

## DT-021 · Usuario de PHP-FPM en desarrollo
1. **DT-021** FPM como root solo en la etapa `dev`. 2. 18/10/2026 · Aprobada.
3. El montaje de carpetas de Windows en Docker Desktop no conserva los permisos de `www-data`; los comandos `artisan` y `pest` ejecutados como root crean archivos en `storage/` que FPM no puede escribir (HTTP 500 "Permission denied").
4. MD 7 (ambiente reproducible), MD 13.1 (seguridad en QA y producción). 5. a) `chown` manual repetido; b) FPM con el mismo usuario en desarrollo. 6. Reproducibilidad sin afectar producción.
7. La etapa `dev` del Dockerfile ejecuta FPM como root (`--allow-to-run-as-root`); la etapa `prod` conserva `www-data` y asigna `storage/` y `bootstrap/cache` a ese usuario.
8. Solo afecta al equipo de desarrollo. 9. Uso accidental de la imagen `dev` en QA → el compose de QA debe usar `target: prod`. 10. Raúl. 11. Raúl, 18/10/2026. 12. —

## DT-022 · Alcance solo backend Laravel (sin app móvil Flutter)
1. **DT-022** El proyecto se limita a Laravel: API REST + vistas web Blade responsivas. 2. 18/10/2026 · **Aprobada** (desviación de alcance decidida por Raúl).
3. MD 6 (Tablas 14–16), MD 5.5 y el SRS (RI-002, RNF-005) prevén una app móvil Flutter que lee el QR con la cámara y opera sin conexión. Raúl decidió desarrollar solo la parte Laravel.
4. MD 6, MD 5.5, SRS RI-002 y RNF-005, MP cap. 2 (UI). 5. a) Flutter como estaba previsto; b) solo Laravel con vistas web responsivas. 6. Decisión del responsable del proyecto.
7. No se crea código Dart, proyecto Flutter, job de CI ni agente móvil. La API REST con Sanctum se mantiene versionada y documentada (`/api/v1`) para un cliente móvil futuro. El escaneo QR se resuelve en la web, con propuesta de lector por cámara del navegador (requiere HTTPS) y captura manual del código; la forma de cubrir el modo sin conexión queda **pendiente de decisión** de Raúl.
8. RI-002, RNF-005 y el modo sin conexión de MD 5.5 se cumplen solo de forma parcial o adaptada; se marcan así en RT-01. Desaparecen DT-004 (parte Flutter), la parte Dart de DT-015 y la parte móvil de DT-007 y DT-014.
9. Auditoría A-01…A-04 → la desviación se evidencia con este DT y con las filas adaptadas en RT-01. 10. Raúl. 11. Raúl, 18/10/2026. 12. DT-004 (parcial).

## DT-023 · Línea base de requisitos (58 RF de los manuales)
1. **DT-023** Catálogo de 58 RF como línea base y SRS v1.0 como fuente trazada. 2. 18/10/2026 · Propuesta (pendiente de aceptación de Raúl).
3. El SRS disponible (`docs/requisitos/SRS_Sistema_Prestamo_Equipo_Institucional_v2.pdf`, versión 1.0 del 23/08/2026) define 18 RF, RN-001…RN-010 y RNF-001…RNF-010. Los manuales v1.2 (MD Tablas 10, 13 y 11.3; MBD Tabla 7) trabajan con 58 RF, RN-001…RN-014, RNF-001…RNF-011 y RS-001…RS-006, con otra numeración (por ejemplo, RN-008 es la prioridad de reservas en el MD y el rol del superadmin en el SRS).
4. MD 1.5 (precedencia), MD 8.1, MD 11.3 ("todos los RF-001 a RF-058"), CLAUDE.md (el alcance no se reduce). 5. a) 18 RF del SRS; b) 58 RF de los manuales; c) unión sin correspondencia. 6. Precedencia, cobertura y trazabilidad para la auditoría.
7. La línea base es `docs/requisitos/BBB_REQ01_LineaBase_v1.0.md`: 58 RF de MBD Tabla 7, RN-001…RN-014 de MBD Tabla 35, RNF-001…RNF-011 medibles (MD Tablas 19 y 25) y RS-001…RS-006. Cada RF del SRS se mapea al RF equivalente; SRS-RF-015 (`.ics`) y SRS-RF-016 pasan a criterios de aceptación de RF-026 y RF-024. Los identificadores del SRS se citan con el prefijo `SRS-`. RN-006, RN-009 y RS-005 no existen en los manuales y se toman del SRS y de MP 3.5 como **supuestos** a confirmar.
8. El alcance no se reduce; RT-01 conserva las 58 filas. 9. Interpretación distinta del docente → los supuestos quedan marcados y cualquier ajuste se hace con SC-01. 10. Raúl Treviño. 11. Pendiente — Raúl Treviño. 12. —

## DT-024 · Extensiones del modelo de datos para el bloque A
1. **DT-024** Ajustes al diccionario MBD 4 que exige el bloque A. 2. 18/10/2026 · Propuesta (pendiente de aceptación de Raúl).
3. Al diseñar RF-001…RF-015 aparecen huecos en el diccionario MBD 4: (a) RF-007 pide que el superadmin modifique "planes, precios y límites", pero el modelo solo tiene un ENUM en `instituciones` (MBD Tabla 7 admite "tabla maestra de planes si se normaliza"); (b) RN-001 determina el rol por el dominio del correo, pero `instituciones` no guarda su dominio; (c) MBD 4.11 declara `logs_auditoria.id_institucion NOT NULL`, pero las acciones del superadmin (RF-007) no pertenecen a ningún tenant (DT-011); (d) RF-005 requiere `notificaciones`, que el plan situaba en el bloque D; (e) `logs_auditoria` no tiene `fecha_creacion` (MBD 6.3.3 regla 4), pero el diccionario usa `fecha_hora` para el instante del evento; (f) dos áreas de la misma institución podrían llamarse igual.
4. MBD Tabla 7 (RF-007, RF-005), MBD 4, MBD 6.3.3, RN-001, RN-010, DT-008, DT-011. 5. a) Resolver solo en la aplicación; b) extender el modelo con migraciones y formatos DDL. 6. Integridad en BD, trazabilidad, mínima desviación del MBD.
7. (a) Tabla global `planes` (`id_plan`, `clave` ENUM igual a `instituciones.plan_suscripcion` con `uk_planes_clave`, `nombre`, `monto_mensual`, `limite_areas`, `limite_recursos`, `duracion_dias`, `activo`, fechas); `instituciones` conserva su ENUM y copia los límites al contratar o cambiar de plan. (b) `instituciones.dominio_correo VARCHAR(100)` con `uk_instituciones_dominio_correo`. (c) `logs_auditoria.id_institucion` admite NULL solo para eventos de plataforma del superadmin; el servicio de auditoría lo asigna siempre que existe contexto de tenant. (d) `notificaciones` se crea en el bloque A con `id_institucion` (DT-008); el canal `push` se interpreta como notificación dentro del sistema web (DT-022). (e) En `logs_auditoria` la columna `fecha_hora` cumple la función de `fecha_creacion`. (f) `uk_areas_institucion_nombre_area (id_institucion, nombre_area)`.
8. Siete tablas en el bloque A en lugar de cinco; cada cambio lleva DDL-03/07/08 y se refleja en el diccionario DDL-06. 9. Divergencia entre `planes.clave` y el ENUM de `instituciones` → validación en servicio y prueba. 10. Raúl Treviño. 11. Pendiente — Raúl Treviño. 12. —

## DT-025 · Contraseña y registro por enlace firmado
1. **DT-025** Columna `contrasena_hash` como contraseña de Laravel y alta por enlace firmado. 2. 18/10/2026 · Propuesta (pendiente de aceptación de Raúl).
3. MBD 4.4 nombra la contraseña `contrasena_hash` y no tiene `remember_token`; Laravel espera `password`. RF-008 (y SRS-RF-013) exigen que el administrador invitado complete su registro desde un enlace, pero DT-016 eliminó `password_reset_tokens`.
4. MBD 4.4, DT-016, MP Tabla 88. 5. a) Renombrar la columna; b) mapearla en el modelo; c) tabla de invitaciones. 6. Fidelidad al MBD sin tablas nuevas.
7. El modelo `Usuario` declara `$authPasswordName = 'contrasena_hash'` y desactiva *remember me*. La invitación crea el usuario con `activo = FALSE` y contraseña aleatoria inutilizable, y envía un enlace firmado temporal de Laravel (72 h); al completarlo se define la contraseña y se activa la cuenta. Un usuario inactivo no puede iniciar sesión.
8. Sin tablas nuevas. 9. Enlace interceptado → caduca a las 72 h, solo sirve mientras la cuenta está inactiva y se usa por HTTPS en QA y producción. 10. Raúl Treviño. 11. Pendiente — Raúl Treviño. 12. —

## DT-026 · Triggers en `usuarios` e índices de soporte de llaves foráneas
1. **DT-026** La regla del superadmin se impone con triggers y los índices de soporte de FK se exceptúan del prefijo `id_institucion`. 2. 18/10/2026 · Propuesta (pendiente de aceptación de Raúl).
3. (a) DT-011 prevé `CHECK ((rol = 'superadmin') = (id_institucion IS NULL))`, pero MySQL 8.4 rechaza un CHECK sobre una columna usada en una FK con acción referencial (`id_institucion` tiene ON UPDATE CASCADE, DT-009; error 3823). (b) MBD 6.3.3 regla 3 pide que los índices empiecen por `id_institucion`, pero InnoDB exige que la columna de cada FK sea prefijo izquierdo de un índice; sin él crea uno implícito con el nombre de la restricción.
4. DT-009, DT-011, MBD 6.3.3, MBD 6.2 (triggers `trg_<tabla>_<momento>_<evento>`). 5. a) Quitar ON UPDATE CASCADE; b) validar solo en la aplicación; c) triggers BEFORE INSERT/UPDATE con SIGNAL. Para los índices: a) dejar que InnoDB cree índices implícitos sin nombre normalizado; b) declararlos con nombre `idx_<tabla>_<columna>`. 6. Integridad en BD y nomenclatura explícita.
7. (a) `trg_usuarios_before_insert` y `trg_usuarios_before_update` lanzan `SQLSTATE '45000'` si un superadmin tiene institución o un usuario de tenant no la tiene. (b) Se declaran como excepción a la regla 3 solo los índices de soporte de FK: `idx_usuarios_area_asignada`, `idx_recursos_area`, `idx_logs_auditoria_usuario` e `idx_notificaciones_usuario_leida`; desde el 25/10/2026 también `idx_prestamos_usuario`, `idx_detalle_prestamo_prestamo`, `idx_detalle_prestamo_recurso` e `idx_tokens_qr_prestamo` (migración `2026_10_25_000001_normalizar_nombres_prestamos`, DDL-08). Todos los índices de consulta empiezan por `id_institucion`.
8. Dos triggers más en el inventario DDL-06. 9. Cambio de rol a superadmin desde la aplicación → el trigger lo rechaza y una prueba lo verifica. 10. Raúl Treviño. 11. Pendiente — Raúl Treviño. 12. —

## DT-027 · Preservar documentación explícita al aplicar Pint
1. **DT-027** Conservar `@package` y etiquetas de tipos explícitos. 2. 18/10/2026 · Propuesta técnica implementada, pendiente de revisión.
3. El preset Laravel elimina `@package` y etiquetas `@return` que considera redundantes; el código y las reglas documentales del proyecto las incluyen expresamente.
4. MP 1.1 y 1.7, CLAUDE.md sección 3, DT-020. 5. a) Eliminar las etiquetas; b) conservarlas y aplicar las demás reglas. 6. Trazabilidad y documentación explícita.
7. Desactivar exclusivamente `phpdoc_no_package` y `no_superfluous_phpdoc_tags`. Se conserva Pint como comprobación de formato; no se desactivan controles de seguridad ni de análisis estático.
8. El formateo del código previo ajusta espacios PHPDoc y constructores vacíos. 9. No altera reglas de negocio. 10. Raúl Treviño. 11. Pendiente. 12. Complementa DT-020.

## DT-028 · Sistema de diseño del prototipo hi-fi para las vistas Blade
1. **DT-028** Adoptar los tokens y pantallas del prototipo `Prototipo interactivo SPEI/design_handoff_spei_web` como implementación de MP cap. 2. 2. 23/10/2026 · **Aprobada** por instrucción de Raúl del 23/10/2026 ("adaptar la interfaz al prototipo, lo más idéntica posible, respetando los manuales").
3. El prototipo hi-fi precisa la paleta y los componentes de MP Tablas 48–78 con valores que el manual no fija: (a) tonos derivados del azul primario `#2563EB` (fondo de botón y enlaces) y `#1D4ED8` (hover y folios), porque blanco sobre `#3B82F6` da 3.68:1 y no cumple WCAG AA para texto normal (MP Tabla 54); (b) badges con fondo tenue del color de estado y texto oscuro del mismo tono (por ejemplo `#D1FAE5`/`#065F46`), que cumplen AA; (c) escala tipográfica en px con la misma jerarquía que la Tabla 68 en pt; (d) Material Symbols Outlined en lugar de Material Icons (misma familia de Google, licencia Apache 2.0, un solo estilo por pantalla); (e) Fira Code (OFL) para folios, inventarios, horas y tokens; (f) el logotipo real (`public/img/logo-bigbitbyte.png`) sustituye el tile "B" de prueba.
4. MP 2.1–2.9 (Tablas 48–78), MP 2.9.3 regla 5 (CREDITS.md), MD 13.1, plan MVP (`docs/plan/BBB_MVP_v1.0.md`: "las interfaces no ofrecerán acciones todavía inexistentes"), DT-014, DT-016, DT-022, DT-025. 5. a) Mantener la interfaz anterior; b) reproducir el prototipo con sus tokens; c) usar Alpine.js o Livewire para la interactividad. 6. Fidelidad al diseño aprobado, contraste AA, cero dependencias nuevas de ejecución y honestidad funcional.
7. (1) `public/css/spei.css` define los tokens del README del prototipo como variables CSS y los componentes (botones, inputs, tabs, chips, tablas en grid, badges, tarjetas, modales, toasts, dropdowns). (2) Las fuentes se sirven localmente: Carlito, Fira Code y una instancia estática de Material Symbols Outlined (FILL 0, wght 400, GRAD 0, opsz 24) generada con fontTools para reducir 4 MB a 383 KB; todas registradas en `CREDITS.md`. (3) La interactividad ligera (menús desplegables, sidebar contraíble, modales, toasts, mostrar contraseña, vista cuadrícula/lista, stepper de solicitud, contador del QR, lector de cámara opcional) se implementa en `public/js/spei.js` con JavaScript nativo, sin Alpine ni Livewire, y la aplicación sigue funcionando sin JavaScript. (4) Del prototipo **no** se muestran los controles sin respaldo en el backend: SSO institucional, "¿Olvidaste tu contraseña?" (DT-016), "Mantener sesión iniciada" (DT-025), selector "Ver como" de demostración, conversación previa, aprobación manual y cancelación por prioridad, carga CSV y mapeo de usuarios, matriz de sanciones y exenciones, modo sin conexión y comprobante PDF generado en servidor. Cada uno se incorpora cuando exista su RF en el bloque correspondiente. (5) Los datos de todas las pantallas vienen de la BD filtrados por `id_institucion`.
8. Las pantallas se ven como el prototipo; algunas tarjetas del prototipo (sanciones, conversación) se ocultan hasta que exista su funcionalidad. 9. Diferencias de tono respecto a la Tabla 48 → solo se agregan tonos derivados del azul primario y de los colores de estado; `#3B82F6` sigue siendo el primario institucional. 10. Raúl Treviño. 11. Raúl Treviño, 23/10/2026. 12. Complementa DT-014 (tipografía).

**Enmienda 25/10/2026 (RED-01 H-04, H-15, H-18)** · Propuesta, pendiente de aceptación de Raúl.
(a) *Tonos.* Además de los tonos del punto 3, `spei.css` usa escalas claras y oscuras de los mismos colores de la Tabla 48 para fondos, bordes y textos sobre fondo tenue: azul (`#EFF6FF`, `#DBEAFE`, `#BFDBFE`, `#93C5FD`, `#1E40AF`, `#1E3A8A`), verde (`#ECFDF5`, `#D1FAE5`, `#A7F3D0`, `#047857`, `#065F46`), ámbar (`#FFFBEB`, `#FEF3C7`, `#FDE68A`, `#D97706`, `#92400E`, `#78350F`), rojo (`#FEF2F2`, `#FEE2E2`, `#FECACA`, `#FCA5A5`, `#B91C1C`, `#991B1B`) y neutros (`#F3F4F6`, `#E5E7EB`, `#D1D5DB`, `#4B5563`, `#374151`, superficies `#F8FBFF`, `#F5F9FE`, `#F1F5F9`, `#EEF2F7`, `#EAF2FB`, `#E6EEF8`, `#E5EDF5`, `#CBD5E1` y hover del menú lateral `#273244`, `#2C3849`). Regla: solo se admiten tintes y sombras de los colores de la Tabla 48; todo par texto/fondo cumple WCAG AA y los iconos con significado alcanzan 3:1 (por eso `.icono--verde` usa `#047857`).
(b) *JavaScript.* La frase "la aplicación sigue funcionando sin JavaScript" se precisa: la navegación, los filtros, los formularios y el comprobante funcionan sin JavaScript; las acciones que se confirman en un `<dialog>` (cancelar solicitud o reserva, código QR en ventana) requieren JavaScript, disponible en los navegadores soportados. El QR conserva su página propia (`prestamos.token`) como alternativa.
(c) *Dependencias.* "Cero dependencias nuevas de ejecución" se refiere al front-end (sin Alpine, Livewire ni bibliotecas JS). La única dependencia nueva de PHP es `bacon/bacon-qr-code`, registrada en DT-029.

## DT-029 · Generación del código QR en el servidor con BaconQrCode
1. **DT-029** Generar el SVG del QR de custodia en el servidor con `bacon/bacon-qr-code` 3.x. 2. 25/10/2026 · Propuesta, pendiente de aceptación de Raúl (RED-01 H-04).
3. RF-034 y RF-036 exigen un QR dinámico de un solo uso. El MVP agregó `bacon/bacon-qr-code ^3.0` (v3.1.1, con su dependencia `dasprid/enum` 1.0.7) sin registrarlo en una DT ni en `CREDITS.md`, y DT-028 afirmaba "cero dependencias nuevas".
4. MD 6 (Tablas 14–17: el stack no fija biblioteca de QR), MD 13 (seguridad: el código en claro solo viaja en la respuesta `no-store`), MP 2.9.3 regla 5, DT-022 (sin app móvil), DT-028. 5. a) Generar el QR en el navegador con una biblioteca JS; b) servicio externo de imágenes QR; c) BaconQrCode en el servidor. 6. Sin terceros que vean el código, sin JS adicional, licencia libre, mantenimiento activo y compatibilidad con PHP 8.3.
7. Se adopta (c): `App\Services\CodigoQrService` produce un SVG con BaconQrCode; el código en claro solo existe en la respuesta JSON con `Cache-Control: no-store, private` y en BD se guarda su hash SHA-256 (`tokens_qr.token`). Licencias BSD-2-Clause registradas en `CREDITS.md`; `composer audit` la revisa en CI.
8. Una dependencia de producción más en `composer.lock`. 9. Vulnerabilidad en la biblioteca → `composer audit` en CI y actualización por rama `fix/`; servicio externo descartado porque expondría el código. 10. Raúl Treviño. 11. Pendiente — Raúl Treviño. 12. Precisa DT-028 (enmienda del 25/10/2026).

## DT-030 · Diferencias del esquema de préstamos frente a MBD 4.6–4.8
1. **DT-030** Documentar y mantener las diferencias de `prestamos`, `detalle_prestamo` y `tokens_qr` respecto a MBD Tablas 41–43. 2. 25/10/2026 · Propuesta, pendiente de aceptación de Raúl (RED-01 re-verificación, N-01).
3. La migración del MVP (`2026_10_18_200001`) se escribió antes de esta DT y se aparta de MBD sin registro: (a) agrega `prestamos.motivo` (TEXT NOT NULL); (b) agrega `fecha_actualizacion` a `prestamos` y `detalle_prestamo`; (c) agrega `id_institucion` a `detalle_prestamo` y `tokens_qr` (ya decidido en DT-008); (d) `tokens_qr.token` es CHAR(64) y MBD dice VARCHAR(255) "token cifrado"; (e) no crea `prestamos.justificacion_prioridad`, el valor `no_entregado` de `detalle_prestamo.condicion_devolucion` ni `detalle_prestamo.evidencia_url`.
4. MD 9.4, MBD 4.6–4.8 y 5.3.8 ("fecha_actualizacion si aplica"), RF-022, RF-030, RF-039, RF-042, RS-006, DT-008. 5. a) Ajustar el esquema a MBD de inmediato; b) mantener lo implementado, justificar (a)–(d) y diferir (e) a su RF; c) revertir columnas agregadas. 6. Seguridad del código QR, necesidad funcional comprobada por CU-011, cambios solo por migración aditiva y no ofrecer en la interfaz acciones sin RF.
7. Opción b. (a) `motivo` se conserva: CU-011 exige la finalidad del préstamo y el comprobante y la auditoría la muestran. (b) `fecha_actualizacion` se conserva porque ambas tablas cambian después del alta (MBD 5.3.8). (d) Se guarda el hash SHA-256 en hexadecimal (64 caracteres exactos) en lugar de un token cifrado: un hash no es reversible y una fuga de la tabla no permite reconstruir códigos vigentes (RS-006); CHAR(64) es el tipo exacto. (e) Se agregarán con migración aditiva y su DDL-03/DDL-06 cuando se implementen RF-030 (`justificacion_prioridad`), RF-039 en su parte de evidencia fotográfica (`evidencia_url`) y RF-042 (`no_entregado`, que genera marca). Mientras tanto RT-01 marca RF-039 como parcial.
8. El diccionario `BBB_DDL06_DiccionarioPrestamos_v1.0.md` refleja el esquema real y cita esta DT. 9. Olvidar las columnas diferidas → quedan como pendientes en RT-01 y en el plan del bloque correspondiente. 10. Raúl Treviño. 11. Pendiente — Raúl Treviño. 12. —

## DT-031 · `vendor/` en volumen Docker y servicios de cola y planificador
1. **DT-031** Montar `vendor/` en un volumen nombrado y ejecutar `queue:work` y `schedule:work` en servicios propios. 2. 25/10/2026 · Aceptada por Raúl Treviño Licona el 27/10/2026.
3. Raúl reportó tiempos de carga excesivos. Medición del 25/10/2026: PHP vacío vía nginx 7 ms; arranque de Laravel 150 ms con la caché caliente y 1.3–1.8 s cuando opcache revalida; página `/iniciar-sesion` de 1.6 a 4.4 s. La causa: el proyecto completo está montado desde NTFS (Docker Desktop en Windows) y cada petición incluye 581 archivos PHP, 549 de ellos de `vendor/`, cuya revalidación de fechas (`opcache.revalidate_freq=0`) cruza el montaje lento. Además, `queue:work` y `schedule:work` no corrían en ningún contenedor, por lo que los correos encolados en Redis y las tareas (vencimiento de reservas RF-032, alertas RF-005) solo funcionaban si alguien los lanzaba a mano.
4. RNF-009 (tiempo de respuesta), MD 6.1 (Docker Compose, imágenes fijadas), RF-005, RF-032, DT-002, DT-006. 5. a) Subir `opcache.revalidate_freq` (los cambios de código tardan en verse); b) mover el proyecto al sistema de archivos de WSL2 (cambia el flujo de trabajo de Raúl); c) volumen nombrado solo para `vendor/`. 6. Mejora medible sin perder la recarga inmediata del código propio y sin cambiar de herramienta.
7. Opción c. `docker-compose.yml` monta `vendor:/var/www/html/vendor` en `app`, `cola` y `programador`. Las dependencias se instalan dentro del contenedor (`docker compose exec app composer install`); el `vendor/` del equipo anfitrión solo lo usa el IDE. Servicios nuevos con la misma imagen `spei-app:dev`: `cola` (`queue:work --tries=3 --max-time=3600`) y `programador` (`schedule:work`), ambos con `restart: unless-stopped`. Resultado medido con peticiones espaciadas 3 s (peor caso): 0.15–0.44 s por página.
8. Tras cambiar dependencias hay que ejecutar `composer install` dentro del contenedor; tras recrear `app` puede ser necesario reiniciar `web` para que nginx resuelva la nueva IP. 9. `vendor/` del anfitrión desactualizado respecto al del contenedor → el CI instala desde `composer.lock`, que sigue siendo la fuente de verdad. 10. Raúl Treviño. 11. Aceptada por Raúl Treviño Licona el 27/10/2026. 12. —


## DT-032 · Proxies de confianza y exposición temporal para revisión
1. **DT-032** Confiar en las cabeceras `X-Forwarded-*` solo desde redes privadas y publicar el entorno de desarrollo por un túnel HTTPS temporal para la primera revisión. 2. 27/10/2026 · Aceptada por Raúl Treviño Licona.
3. Raúl pidió publicar el sistema en la web para una primera revisión. No existe aún ambiente de QA ni de producción (auditoría MD-03). Detrás de un proxy HTTPS, Laravel generaba enlaces `http://` (contenido mixto bloqueado) y registraba la IP del proxy en lugar de la del cliente, lo que afecta el límite de intentos de inicio de sesión y la auditoría.
4. MD 13 (seguridad), MP 3.3, RS-003, RS-006, DT-031. 5. a) Hosting administrado (requiere cuenta y costo); b) túnel temporal hacia el entorno local; c) no publicar. 6. Rapidez para la revisión, sin costo, reversible y sin exponer secretos.
7. `bootstrap/app.php` confía en proxies de `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16` y `127.0.0.1` (red interna de Docker: nginx y túnel). Para la revisión se usa un túnel HTTPS temporal (`cloudflared`) hacia el servicio `web`, con `APP_DEBUG=false`. Al terminar se detiene el túnel. No sustituye al ambiente de producción, que sigue pendiente con TLS, respaldos y LI-01.
8. Mientras el túnel esté activo, cualquiera con la URL ve la pantalla de inicio de sesión; las cuentas de demostración deben tener una contraseña que no se publique. 9. Cliente en la red local que falsifique `X-Forwarded-For` → solo afecta el registro de IP en desarrollo; producción usará la IP fija del balanceador. 10. Raúl Treviño. 11. Aceptada por Raúl Treviño Licona el 27/10/2026. 12. —

## DT-033 · Reservas por periodo y prioridad RN-008 ante solicitudes concurrentes
1. **DT-033** Un recurso admite varias reservas en periodos que no se traslapan; RN-008 se aplica a las solicitudes en conflicto creadas dentro de una ventana de concurrencia configurable. 2. 27/10/2026 · Aceptada por Raúl Treviño Licona.
3. El MVP bloqueaba el recurso completo desde la solicitud hasta la devolución y asignaba por orden de llegada (auditoría MD-04). MD 5.6 pide "una restricción que impida periodos incompatibles" y, "ante solicitudes concurrentes", el algoritmo RN-008 (rol → historial → antigüedad), pero no define cuándo dos solicitudes son concurrentes ni el estado del recurso entre reservas.
4. MD 5.6, MBD Tabla 7 (RF-025 a RF-027), RN-002, RN-007, RN-008, DT-008. 5. a) Mantener el bloqueo total; b) arbitrar cualquier conflicto con RN-008, aunque la reserva previa lleve días confirmada; c) arbitrar solo dentro de una ventana corta y respetar después la reserva confirmada. 6. Previsibilidad para el usuario, fidelidad a "concurrentes" y ausencia de sobreasignación.
7. Opción c. (1) `ReservaService::obtenerConflictos` busca préstamos pendientes o activos con traslape de periodo (y activos atrasados) bajo `lockForUpdate`, dentro del lock Redis de la institución. (2) Si todos los conflictos son pendientes creados en los últimos `SPEI_VENTANA_PRIORIDAD` minutos (5 por omisión, 1–60) y el solicitante tiene prioridad estricta (docente > alumno; mismo rol, menos marcas), esas reservas se cancelan con `DESPLAZAR_PRESTAMO` y aviso al titular; en otro caso se rechaza con "ya está reservado en ese horario". En empate gana la más antigua. (3) El recurso queda `disponible` mientras la reserva es futura y pasa a `bloqueado` al iniciar su ventana de recogida (`spei:activar-reservas`, cada minuto); al devolverse, si otra reserva ya está en ventana, se bloquea de inmediato. (4) RN-002 se conserva: el catálogo solo muestra recursos `disponible`.
8. Una solicitud puede cancelarse automáticamente minutos después de crearse; el titular recibe aviso en el sistema y por correo. 9. Ventana demasiado larga → acotada a 60 min. Recurso en mantenimiento con reservas futuras → la entrega se rechaza por estado del inventario. 10. Raúl Treviño. 11. Aceptada por Raúl Treviño Licona el 27/10/2026. 12. Sustituye el "Límite explícito" de reserva total del MVP.

## DT-034 · Tabla `dominios_rol` para el mapeo de roles por dominio y subdominios
1. **DT-034** Guardar en una tabla las reglas dominio → rol de cada institución y aceptar subdominios del dominio contratado. 2. 27/10/2026 · Aceptada por Raúl Treviño Licona.
3. MD 5.4 pide "una base de usuarios, dominios y roles cargada mediante CSV" y que el rol se determine por el dominio (RN-001), pero el MBD solo tiene `instituciones.dominio_correo` y `usuarios.rol`; no hay dónde guardar qué dominio corresponde a alumnos o docentes. El inicio de sesión solo aceptaba el dominio exacto.
4. MD 5.4, MBD Tabla 7 (RF-016, RF-017), RN-001, RS-006, DT-008. 5. a) Indicar el rol a mano en cada alta; b) columna JSON en `instituciones`; c) tabla `dominios_rol`. 6. Integridad referencial, unicidad por institución y consulta sencilla.
7. Opción c: `dominios_rol (id_dominio_rol, id_institucion, dominio, rol ENUM('alumno','docente'), fecha_creacion, fecha_actualizacion)`, UK `(id_institucion, dominio)`, FK RESTRICT/CASCADE. El dominio debe ser el contratado o un subdominio de él; gana la regla más específica. Por dominio solo se asignan roles de usuario final; asistentes y administradores siempre se asignan de forma explícita. El inicio de sesión acepta correos de subdominios (`DominioCorreo::perteneceA`).
8. Tabla 14 del modelo, documentada en DDL-03/06/07/08. 9. Regla mal definida → el rol explícito del CSV o del formulario tiene precedencia; cambiar una regla no altera cuentas existentes. 10. Raúl Treviño. 11. Aceptada por Raúl Treviño Licona el 27/10/2026. 12. —

## DT-035 · Columna `usuarios.fecha_registro` y enlaces de invitación atados a la contraseña
1. **DT-035** Distinguir invitación pendiente (sin `fecha_registro`) de cuenta dada de baja (`activo = 0` con fecha) y atar cada enlace de invitación a una huella HMAC de la contraseña vigente. 2. 27/10/2026 · Aceptada por Raúl Treviño Licona.
3. RF-018 a RF-020 requieren dar de baja y reactivar cuentas; con solo `activo` no se distinguía una invitación pendiente de una baja, y un enlace de invitación vigente permitía a una cuenta dada de baja reactivarse sola.
4. RF-016 a RF-020, MD 13 (seguridad), DT-025. 5. a) Reutilizar `fecha_bloqueo`; b) nueva columna; c) solo la huella en el enlace. 6. Semántica clara y defensa en profundidad.
7. Opción b + c: `fecha_registro DATETIME NULL` (las cuentas activas existentes toman `fecha_creacion`); completar el registro la fija; el enlace firmado lleva `huella = HMAC-SHA256(APP_KEY, contrasena_hash)` y deja de servir en cuanto la contraseña cambia.
8. Migración aditiva `2026_10_27_000002` con DDL-03/06. 9. Rotación de `APP_KEY` → invalida invitaciones pendientes; se reenvían. 10. Raúl Treviño. 11. Aceptada por Raúl Treviño Licona el 27/10/2026. 12. Complementa DT-025.

## DT-036 · Reglas operativas de sanciones, reportes y avisos no fijadas por los manuales
1. **DT-036** Precisar cómo se aplican las sanciones del bloque D y cómo se exportan los reportes. 2. 27/10/2026 · Aceptada por Raúl Treviño Licona.
3. MBD Tablas 7, 39 y 40 y RN-005 definen la matriz, las marcas y el bloqueo, pero no: cuántas penalizaciones genera un préstamo con varios recursos dañados, qué pasa con las marcas al cumplir la sanción, cómo se cierra un préstamo cuyo recurso nunca regresa, ni el formato "PDF y CSV/Excel" de RF-058 sin app móvil.
4. RF-041 a RF-046, RF-055 a RF-058, RN-003, RN-005, DT-022, DT-028. 5./6. Equidad para el usuario, trazabilidad y no agregar dependencias.
7. (1) Una penalización por tipo de incidente y préstamo (no por recurso); "no devuelto" absorbe el retraso. (2) Las marcas las suma y resta un trigger (`trg_penalizaciones_after_insert/update`); al alcanzar el umbral de la regla se bloquea por `dias_sancion`; al terminar, `spei:levantar-sanciones` desbloquea y reinicia el contador. (3) La exención resta marcas y, si quedan por debajo del umbral, levanta el bloqueo. (4) El administrador del sistema del área cierra un préstamo atrasado como "no devuelto" con justificación ≥ 20: estado `devuelto`, condición `no_entregado`, recursos a mantenimiento y sanción. (5) Matriz por omisión: retraso 1, mal estado 1, dañado 2, no devuelto 3 marcas; umbral 3; 7 días. (6) RF-058: CSV por sección (con neutralización de fórmulas y registro `EXPORTAR_REPORTE`) y PDF mediante la impresión del navegador; no se agrega generador PDF en servidor. (7) Las alertas a operadores (RF-049) y los mensajes de conversación (RF-033) solo se notifican en el sistema; los eventos del solicitante también por correo.
8. Reglas explícitas en CU-019 a CU-024. 9. Usuario inconforme con la sanción → exención justificada y auditada. 10. Raúl Treviño. 11. Aceptada por Raúl Treviño Licona el 27/10/2026. 12. Precisa DT-030 (`no_entregado` ya implementado).
