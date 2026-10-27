# Auditoría integral del SPEI contra el Manual de Programación BBB-MP-003 v1.2

| Campo | Valor |
|---|---|
| Tipo | Auditoría de cumplimiento (MC 1.8, MD 3.3 / 10.2) — revisión independiente `spei-auditor` |
| Alcance | Código completo del repositorio `C:\Users\Lenovo\Documents\spei` (Laravel 12): `app/`, `routes/`, `database/`, `resources/views`, `public/css/spei.css`, `public/js/spei.js`, `tests/`, `config/`, `bootstrap/`, `docker/`, CI, git |
| Rama / commit | `feature/RF-011-gestion-prestamos` @ `593db78` + 50 archivos modificados sin commit (árbol de trabajo) |
| Fecha | 2026-10-25 / 2026-10-26 |
| Criterio | MP v1.2 caps. 1, 2, 3 y Anexos A–B; excepciones aprobadas en `docs/decisiones/BBB_DT_Registro_v1.0.md` (DT-001…DT-031) |
| Decisión final | Corresponde a Raúl Treviño (el auditor propone, no aprueba) |

## 1. Método y evidencia objetiva

Todo se ejecutó en solo lectura sobre el repositorio.

| Verificación | Comando / herramienta | Resultado |
|---|---|---|
| Estilo PSR-12 | `docker compose exec -T app ./vendor/bin/pint --test` | **PASS, 142 archivos** |
| Análisis estático | `./vendor/bin/phpstan analyse --no-progress` (nivel 6, Larastan) | **[OK] No errors** |
| Pruebas + cobertura | `./vendor/bin/pest --coverage --min=80` (ejecución aislada) | **64 passed (536 aserciones), cobertura total 94.2 %** |
| Dependencias | `composer audit` / `composer validate` | Sin avisos de seguridad; `composer.json` válido |
| Medición de complejidad, firmas, PHPDoc, orden de clase, cabeceras | Analizador propio basado en `token_get_all` (PHP 8.3 en el contenedor) sobre 140 archivos PHP y 415 métodos/funciones | Ver § 2 |
| Formato de archivos (BOM, CRLF, tabs, columnas) | Script Python sobre PHP, Blade (55), CSS, JS, Docker, rutas | Ver § 2 |
| Paleta | Inventario de 50 colores HEX distintos en CSS/JS/Blade/PHP contra Tablas 48–49/52/69 y DT-028 | Ver MP-04, MP-21 |
| Contraste WCAG | Cálculo de luminancia relativa (WCAG 2.x) de los pares texto/fondo críticos | Ver MP-04 |
| Iconos y textos de acción | Inventario de 79 iconos y 45 etiquetas de botón en Blade | Ver § 2 |
| Control de versiones | `git branch -a`, `git for-each-ref`, `git log`, `git status` | Ver MP-01, MP-07 |

**Nota sobre Pest:** dos ejecuciones previas dieron 6 y >60 fallos (`SQLSTATE 1412 Table definition has changed`, `1050 Table already exists`) porque otro proceso ejecutaba `pest` en paralelo sobre la misma BD `spei_testing` (verificado con `ps aux` en el contenedor). En ejecución aislada: 64/64 en verde. Ver MP-09.

## 2. Métricas automáticas (cap. 1)

| Regla MP | Medición | Cumple |
|---|---|---|
| 1.2.4.1 `declare(strict_types=1)` | 140 de 140 archivos PHP | Sí |
| 1.2.3.3/1.2.3.4 UTF-8 sin BOM, LF, sin tabulaciones | 0 BOM, 0 CRLF, 0 tabs en PHP, Blade, CSS, JS, Docker, YAML | Sí |
| 1.2.3.2 ≤ 120 columnas (PHP/Blade) | 6 líneas en 2 archivos (MP-12); Blade 0 | Parcial |
| 1.3.3.3 anidamiento ≤ 3 | 0 de 415 métodos > 3 (distribución: 0→299, 1→105, 2→10, 3→1) | Sí |
| 1.4.7 ≤ 50 líneas por método | 0 de 415 (máximo 49: `OnboardingService::contratar`, l. 69); closures de prueba: máximo 41 líneas (`BloqueATest.php:116`) | Sí |
| 1.4.7 ≤ 5 parámetros | 0 de 415 (máx. 5 en 3 métodos) | Sí |
| 1.4.3.4/1.4.3.5 tipos explícitos | 0 métodos sin tipo de retorno; 0 parámetros sin tipo | Sí |
| 1.6.2 PHPDoc en públicos/protegidos | 338 de 338 con PHPDoc; 6 sin `@return` (MP-18) | Sí (casi total) |
| 1.6.4 PHPDoc de clase | 104 de 105 clases con `@package/@author/@since` (falta `Controller.php`, generado) | Sí |
| 1.3.7.2 sin `catch (Exception)` genérico | 0 en PHP | Sí |
| 1.3.4.4 sin ternarios anidados | 0 anidados reales; 3 ternarios multilínea (MP-13) | Parcial |
| 1.7.5 un archivo = una clase; orden const→prop→ctor→public→protected→private | 0 archivos multiclase; 0 violaciones de orden en 105 clases | Sí |
| 1.6.3 r5 sin código comentado | 0 detecciones | Sí |
| 1.6.7 etiquetas especiales | Solo `NOTE:` (9 archivos); sin TODO/FIXME pendientes | Sí |
| 1.5.3 r5 identificadores sin acentos ni ñ | 0 identificadores no ASCII | Sí |
| 1.5.4 variables camelCase | 0 variables snake_case en código | Sí |
| 1.5.5 métodos con verbo en infinitivo | 57 nombres no inician con infinitivo; ~30 son relaciones Eloquent/estados de factory (convención del framework) y ~27 son atribuibles (MP-10) | Parcial |
| 1.5.7.3 rutas kebab-case | 69 rutas, 0 fuera de kebab-case (excepto `sanctum/…`, `storage/…`, `up` del framework) | Sí |
| 1.1 cabecera de prólogo | 187 archivos propios con cabecera; sin cabecera solo archivos generados sin cambios sustanciales (config nativos, `Controller.php`, `TestCase.php`, `providers.php`, migración Sanctum) | Sí (MP-14…17) |
| 2.9 un solo set de iconos | 79 iconos, todos Material Symbols Outlined vía clase `.icono`; 0 SVG inline, 0 emojis, 0 otros sets | Sí (DT-028 d) |
| 2.8.2 textos de acción | 45 etiquetas de botón: todas ≤ 2 palabras, sin punto final, inicial mayúscula | Sí (MP-22) |
| 2.5.4 r1 máx. 7 elementos por menú | Máximo por rol ≤ 7 (`app/Support/MenuNavegacion.php`) | Sí |
| 2.2.2 modales | `.modal` max-width 600 px, padding 24, radio 16, overlay `rgba(31,41,55,0.5)` | Sí |
| 2.5.1/2.5.2 sidebar 240/64 px, topbar 64 px | `--ancho-lateral: 240px`, `--ancho-lateral-contraido: 64px`, `--alto-topbar: 64px` | Sí |

## 3. Tabla de hallazgos

Severidades: CRÍTICO (impide producción), MAYOR, MENOR, OBSERVACIÓN, AJUSTE-MANUAL (en § 4).

| ID | Sección MP | Requisito | Evidencia | Severidad | Acción propuesta |
|---|---|---|---|---|---|
| MP-01 | 3.2.2 Tabla 81; 3.2.5 Tabla 84 r1, r4, r6; 3.1.5 r4; 3.4.5 r5 | Ramas `main` y `develop` protegidas; todo cambio por PR con pruebas y análisis estático | `git branch -a` y `git for-each-ref`: solo existen `docs/BBB-I0-inicializacion-repositorio` y `feature/RF-011-gestion-prestamos` (local y `origin`); no hay `main`, `develop` ni etiquetas. `.github/workflows/ci.yml:20-24` solo dispara en `pull_request`/`push` a `develop`/`main`, por lo que **el CI nunca se ha ejecutado** sobre el código. Sin PR registrado | MAYOR | Crear `main` y `develop`, protegerlas en GitHub (CI obligatorio), integrar las ramas por PR con el RED-01 adjunto; agregar disparador en `feature/**`, `fix/**`, `docs/**` o ejecutar CI en todo PR |
| MP-02 | 3.3.3 Tabla 86 | CORS restringido al dominio institucional | No existe `config/cors.php`; Laravel 12 aplica `HandleCors` con su configuración por defecto (orígenes permitidos = comodín) a `api/*` | MAYOR | Publicar `config/cors.php` con orígenes desde `.env` (dominio institucional) y probarlo con una petición de origen ajeno |
| MP-03 | 3.3.3 Tabla 86 | Rate limiting 60 req/min por usuario | Solo existe el limitador `inicio-sesion` (5/min) en `app/Providers/AppServiceProvider.php:80-86`, aplicado en `routes/api.php:39` y `routes/web.php:62`. Las rutas autenticadas de API y web no tienen `throttle` (`bootstrap/app.php:49-63` no agrega `throttleApi`) | MAYOR | Definir un limitador `api` de 60/min por id de usuario y aplicarlo a la API (y a la web autenticada); prueba que verifique el 429 |
| MP-04 | 2.1.5 r4; 2.2.3 Tabla 54 | Contraste ≥ 4.5:1 en texto normal | Cálculo WCAG: (a) enlace activo del sidebar, blanco sobre `#3B82F6` = **3.68:1**, 14 px negrita (`public/css/spei.css:598-602`, visible en todas las pantallas); (b) contador de notificaciones, blanco 10 px sobre `#EF4444` = **3.76:1** (`.boton-campana__contador`, `spei.css` ~l. 803-812); (c) `.boton--oscuro-primario`, blanco 15 px sobre `#3B82F6` = 3.68:1 (`spei.css:1359-1363`, usado en `resources/views/mostrador/index.blade.php:74`). DT-028 (a) reconoce que blanco sobre `#3B82F6` no cumple y lo corrigió solo en `.boton--primario` | MAYOR | Usar `--primario-fuerte` (`#2563EB`, 5.17:1) en el enlace activo y en ese botón, y `--error-texto` (`#B91C1C`) en el contador; mantener una lista de pares verificados |
| MP-05 | 3.3.3 Tabla 86 | HTTPS obligatorio en producción | Sin `URL::forceScheme` ni HSTS en `app/`; `docker/nginx.conf` solo HTTP (su cabecera indica que el bloque HTTPS se agrega en QA y producción); `.env.example:69` `SESSION_SECURE_COOKIE=false` | MENOR (bloqueante para producción) | Bloque TLS en nginx de QA/producción, forzar esquema https en producción, HSTS y cookie segura en la plantilla de producción |
| MP-06 | 3.3.3 Tabla 86 (Validación con Form Requests) | Validación con Form Requests | 15 Form Requests en `app/Http/Requests`, pero 5 validaciones en línea: `AuditoriaController.php:129`, `InstitucionController.php:76`, `MostradorController.php:91`, `RecursoController.php:223`, `ReporteController.php:72` | MENOR | Extraer a Form Requests |
| MP-07 | 3.2.3 Tabla 82; DT-001; 3.2.4 | Rama `feature/RF-XXX-descripcion` coherente con el RF | `feature/RF-011-gestion-prestamos` contiene RF-022…RF-037 (préstamos); RF-011 es «Registro de recursos mediante código de barras» (`docs/requisitos/BBB_REQ01_LineaBase_v1.0.md:36`). Todo el bloque A (227 archivos, 26 590 líneas) entró con un `chore:` en la rama `docs/BBB-I0-…`; `593db78` mezcla 170 archivos / 18 675 líneas. 50 archivos modificados sin commit (`git status`) | MENOR | Rama con el RF real (p. ej. `feature/RF-022-gestion-prestamos`), commits atómicos por RF y tipo, versionar el trabajo pendiente antes de cerrar la iteración |
| MP-08 | 3.4.2 Tabla 89 | Pruebas unitarias ≥ 80 % además de Feature | `tests/Unit/` vacío; las 64 pruebas son Feature (5 archivos). Cobertura global 94.2 %, con clases bajo 80 %: `Support/ResumenAuditoria` 54.2 %, `Concerns/PerteneceAInstitucionTrait` 71.4 %, `Policies/RecursoPolicy` 71.4 %, `Services/DisponibilidadRecursoService` 72.2 %, `Models/Institucion` 76.9 %, `Support/FormatoFecha` 78.6 % | MENOR | Pruebas unitarias de Support, Enums, Policies y servicios puros |
| MP-09 | 3.4.5 Tabla 90 r3 | Pruebas independientes y reproducibles | Dos ejecuciones concurrentes sobre `spei_testing` fallaron (6 y más de 60 fallos por `RefreshDatabase` simultáneo); aislada: 64/64 | OBSERVACIÓN | No ejecutar Pest en paralelo sobre la misma BD o usar una BD por proceso |
| MP-10 | 1.5.5 | Métodos con verbo en infinitivo | Nombres atribuibles (≈27 de 415): `SolicitudController::mis`, `::token`; `FormatoFecha::corta`, `::dia`, `::periodo`, `::conAnio`; `Iniciales::de`; `MenuNavegacion::enlace`; `ResumenAuditoria::detalle`, `::registro`; `ConsultaPrestamoService::paraUsuario`; `SolicitarPrestamoRequest::datosServicio`; `GuardarRecursoRequest::unicoEnInstitucion`; predicados `Usuario::esSuperadmin`, `::tieneRol`, `::esResponsableDeArea`, `Prestamo::tieneIncidencia`, `EstadoPrestamo::estaAbierto`, `EstadoRecurso::estaEnUso`, `EstadoSuscripcion::permiteAcceso`, `::permiteEscritura`, `::puedeCambiarA`, `AutenticacionService::perteneceAlDominio`, `::puedeContinuar`, `ContextoInstitucional::existe`, `AlertaVencimientoService::existeAlertaDelDia`. Relaciones Eloquent y estados de factory: ver AM-06 | MENOR | Renombrar los que no son predicados (`mostrarMisPrestamos`, `formatearCorta`, `obtenerIniciales`, `armarEnlace`…); para predicados, resolver con AM-06 |
| MP-11 | 1.5.3 r2; 1.3.9 Tabla 26 r2 | Sin abreviaturas ambiguas | `$q` 58 usos y además `$u`, `$r`, `$e`, `$d`, `$a`, `$b`, `$v` en closures: `CatalogoService.php:77-128`, `ConsultaPrestamoService.php:95,145`, `TableroService.php:178-191`, `AuditoriaController.php:184`, `ImportacionCatalogoService.php:299` | MENOR | `$consulta`, `$subconsulta`, `$valor`, `$primero` / `$segundo` |
| MP-12 | 1.2.3.2 Tabla 10 | PHP ≤ 120 columnas | `app/Services/CatalogoService.php:77` (125), `:78` (121), `:96` (126), `:127` (128), `:128` (129); `app/Services/ConsultaPrestamoService.php:92` (121). Pint no verifica longitud | MENOR | Partir las closures; agregar comprobación de longitud (PHP_CodeSniffer, que MP Tabla 7 ya menciona) al hook y al CI |
| MP-13 | 1.3.4.4 | Ternario solo si es simple y cabe en una línea | Ternarios multilínea dentro de `match`: `app/Models/Prestamo.php:125-127`, `app/Services/DisponibilidadRecursoService.php:168-173`; Blade `resources/views/partials/campo.blade.php:45-46` | MENOR | Extraer a métodos privados o `if` con *early return* |
| MP-14 | 1.1.7 Tabla 5 r7 | `@description` máximo 3 líneas | 35 de 187 cabeceras superan 3 líneas: `public/js/spei.js` (6); `RecursoController.php`, `PrestamoService.php`, `ReporteService.php`, `routes/web.php`, `2026_10_18_200001_create_prestamos_mvp_tables.php`, `2026_10_25_000001_normalizar_nombres_prestamos.php`, `InterfazPrototipoTest.php` (5 cada uno), etc. | MENOR | Reducir a ≤ 3 líneas; el detalle va al PHPDoc de clase |
| MP-15 | 1.1.7 Tabla 5 r8 | Actualizar `@lastModified` en cada cambio | Modificados en `593db78` sin actualizar la fecha (siguen en 2026-10-18): `app/Exceptions/ReglaNegocioException.php` (se agregó el campo codigo en l. 62) y `routes/console.php` (nueva tarea `spei:vencer-prestamos`, l. 36) | MENOR | Actualizar `@lastModified` / `@version`; validar la cabecera en el hook |
| MP-16 | 1.1.2 Tabla 3; 1.1.3 | Cabecera completa en configuración propia | `docker/nginx.conf` y `docker/php.ini` sin `@project` ni `@see`; `.env.example` sin cabecera MP (solo comentario, l. 1-5) | MENOR | Completar las etiquetas |
| MP-17 | 1.1.7 Tabla 5 r3 | Correo institucional real del autor | Las 187 cabeceras usan `raul.trevino@institucion.edu.mx`, dominio de ejemplo del manual (MP 1.1.8) | OBSERVACIÓN | Confirmar el correo real o registrar la convención |
| MP-18 | 1.4.4.2 Tabla 31; 1.6.5 | `@return` y `@param` obligatorios | `database/migrations/0001_01_01_000002_create_failed_jobs_table.php:42` (`up`) y `:59` (`down`) sin `@return`; `tests/Pest.php:72` `solicitarComoAlumno()` sin `@return` y con 3 `@param` para 4 parámetros | MENOR | Completar PHPDoc |
| MP-19 | 2.6.1 Tabla 66; 2.6.2 Tabla 67; B.3.2; B.4.1; B.4.2; 2.7.1 Tabla 70 | Dimensiones y estilos de componentes | `.boton` base 40 px / 20 px y `.boton--primario` sin altura propia (primario = 40 px; MP 48 / 24 px; solo `.boton--grande` llega a 48); `.casilla input` 20×20 (MP 24); `.tarjeta--relleno` 20 px (MP 16); `.tabla th` fondo `#F0F9FF` (MP B.4.2: `#1F2937`); `.toast` fondo `#1F2937` y radio 12 (Tabla 70: toast verde; B.4.1: radio 8); botones peso 700 (MP SemiBold). DT-028 (aprobada) adopta el prototipo, pero sus justificaciones (a)–(f) no enumeran estas desviaciones | MENOR (documental) | Enumerarlas en la enmienda de DT-028 o ajustar el CSS (ver AM-04, AM-05) |
| MP-20 | 2.5.3 Tabla 64; 2.5.4 Tabla 65 r5 | En móvil, Bottom Navigation (≤ 5 elementos) | `spei.css:3984` (`max-width: 639px`) solo contrae el sidebar a 64 px; no hay barra inferior en la web responsiva | MENOR | Implementar barra inferior en < 640 px o decidir por DT (AM-14) |
| MP-21 | 2.1.2–2.1.5; DT-028 enmienda 25/10 | Paleta institucional | Los 50 colores HEX distintos pertenecen a Tablas 48/49/69, a DT-028 punto 3 (aprobado) o a la enmienda del 25/10 (**Propuesta, pendiente**); 37 de 50 dependen de la enmienda pendiente; 0 colores fuera de esos conjuntos | OBSERVACIÓN (pendiente de DT) | Raúl acepta o rechaza la enmienda de DT-028 |
| MP-22 | 2.8.2 Tabla 74 r5; 1.5.3 r1 | Consistencia de textos y en español | Menú del admin del sistema: «Dashboard»; superadmin: «Panel» (`app/Support/MenuNavegacion.php`, `armarAdminSistema` frente a `armarSuperadmin`); coexisten «Regresar» y «Volver» para la misma acción | MENOR | Unificar en «Panel» y un solo verbo de retorno |
| MP-23 | 1.3.7.2 Tabla 25 r2 | No ocultar errores en `catch` | `resources/views/layouts/app.blade.php:44` catch vacío; `public/js/spei.js:41, 386, 710, 728` capturan sin registrar (muestran mensaje al usuario) | OBSERVACIÓN | Comentar el *por qué* o mover el script inline a `spei.js` |
| MP-24 | 1.6.2 (por analogía) | Documentación de funciones | 25 de 43 funciones con nombre de `public/js/spei.js` sin comentario (p. ej. `iniciarCalendario` l. 634, `solicitarQr` l. 360); el MP no define estándar JS (AM-11) | OBSERVACIÓN | Documentar las funciones de más de 15 líneas |
| MP-25 | B.6.1; DT-014, DT-028 | Recursos sin uso que contradicen la tipografía | `tailwind.config.js` (fuente Figtree), `package.json` (tailwindcss, axios, vite), `resources/js/app.js`, `resources/js/bootstrap.js`, `resources/css/app.css`: andamiaje de Laravel que la interfaz no usa | OBSERVACIÓN | Eliminarlos o registrar por qué se conservan |
| MP-26 | 2.3.1 Tabla 55; 2.3.3 Tabla 57 r5; 2.3.2 Tabla 56; B.6.3 r3–r4 | Logotipo vectorial, ≥ 48×48 en escritorio, favicon multi-tamaño, imágenes optimizadas | Existe `public/img/logo-bigbitbyte.svg`, pero el layout usa el PNG de 100 KB a 32×32 (`layouts/app.blade.php:56`) y como favicon (`:36`) | OBSERVACIÓN | Usar el SVG, 48 px en escritorio y favicon 16–512 |
| MP-27 | 3.3.2 Tabla 85 | Token 24 h y refresh 7 días | `config/sanctum.php:82` expira a 1440 min (cumple); no hay refresh token (Sanctum no lo ofrece) y DT-007 no lo menciona | OBSERVACIÓN | Registrar en DT-007 (AM-12) |

## 4. AJUSTE-MANUAL (el manual es ambiguo, contradictorio o inaplicable, o el sistema tomó mejor decisión)

| ID | Sección MP | Problema del manual | Qué hace el sistema | Redacción sugerida |
|---|---|---|---|---|
| AM-01 | 1.1.4 vs 1.2.4.1 | 1.1.4 ubica la cabecera inmediatamente después de `<?php`; 1.2.4.1 exige `declare(strict_types=1);` inmediatamente después de `<?php` | Cabecera, luego `declare`, luego `namespace` (sigue 1.1.4 en 140 archivos) | 1.2.4.1: «Después de `<?php` va la cabecera de prólogo (1.1.4) y a continuación `declare(strict_types=1);`». |
| AM-02 | 1.1.3 Tabla 4 | Dice «seis (6) bloques obligatorios», la tabla lista 7 y la plantilla usa 12 etiquetas | Se usan las 12 etiquetas | «La cabecera contiene las 12 etiquetas de la plantilla 1.1.4; `@requisitos` es obligatoria en código de negocio». |
| AM-03 | 2.5.1 Tabla 62 vs 2.2.3 Tabla 54 | Prescribe texto blanco sobre `#3B82F6` en el elemento activo (3.68:1), lo que viola el contraste mínimo del propio manual | Hereda el fallo (MP-04) | «Color activo: `#2563EB` (tono derivado del primario) con texto blanco; `#3B82F6` solo para acentos, bordes e iconos». |
| AM-04 | 2.6.3 Tabla 68; 2.6.1 Tabla 66 | Tamaños en pt para pantalla y pesos SemiBold/Medium que Calibri/Carlito no ofrecen (solo 400/700) | Escala en px y peso 700 (DT-028 c, DT-014) | «En web los tamaños se expresan en px (14 px texto normal, 24 px título…); con Carlito, SemiBold y Medium se sustituyen por 700». |
| AM-05 | 2.6.1 Tabla 66; 2.6.2 | Botón primario de 48 px es impráctico en tablas, barras de filtros y tarjetas densas | 40 px por defecto, 48 px en acciones principales (`.boton--grande`) | Añadir «Botón primario compacto: 40 px / 20 px, para tablas y barras de herramientas». |
| AM-06 | 1.5.5 | «Todos los métodos… con verbo en infinitivo» choca con el propio ejemplo del MP (Tabla 32, 1.4.4.4: `usuario()`), con estados de factory y con predicados booleanos | Relaciones con sustantivo; predicados `es…`, `tiene…`, `puede…` | «Excepciones: relaciones Eloquent (sustantivo), scopes, estados de factory y predicados booleanos (`es…`, `tiene…`, `puede…`, `esta…`)». |
| AM-07 | 1.7.4 vs 1.7.5 | 1.7.4 pone los casts en la posición 3; desde Laravel 11 `casts()` es un método protegido, que 1.7.5 obliga a colocar después de los públicos | `casts()` al final, después de los métodos públicos | «Si los casts se declaran con el método `casts()`, se ubica con los métodos protegidos». |
| AM-08 | 2.9.1 Tabla 75; B.5.1 | Solo menciona Material Icons; Material Symbols Outlined es su sucesor (misma familia, Apache 2.0) | Material Symbols Outlined, un solo estilo (DT-028 d) | Agregar «Material Symbols (Apache 2.0)» a la Tabla 75 y B.5.1 como set preferente. |
| AM-09 | 3.2.2–3.2.5 Tablas 81–82, 84 r5 | Convención `bugfix/` y `release/` contradice MD (DT-001); «1 aprobación» es inaplicable con desarrollador único (DT-018) | Convención MD; revisión por `spei-auditor` | Remitir al MD 10.1 y admitir «aprobación del responsable tras RED-01 del revisor independiente». |
| AM-10 | 3.1.3; 3.4.2 Tabla 89; 3.5.2–3.5.3 | Opciones de PHPStan eliminadas en 2.x (DT-005), 70 % en integración frente al 80 % del MD (DT-015), imágenes latest y contraseñas en el compose (DT-002, DT-003, DT-017) | Siguen los DT | Actualizar los fragmentos a la configuración vigente. |
| AM-11 | Cap. 1 completo | No hay estándar para JavaScript ni CSS, aunque `public/js/spei.js` (772 líneas) y `public/css/spei.css` (4 107 líneas) son código propio | Cabecera, 4 espacios y nombres en español aplicados por analogía | Nueva sección «1.8 JavaScript y CSS»: sangría 4, 120 columnas, comentario de documentación por función, sin catch vacío sin justificación, clases CSS en español con BEM. |
| AM-12 | 3.3.2 Tabla 85 | Refresh token de 7 días y LDAP/SSO no existen en Sanctum (DT-007) | Token de 24 h sin refresh | «Sanctum: token de acceso de 24 h; la renovación se hace con un nuevo inicio de sesión». |
| AM-13 | 1.2.7.1 | El `pint.json` del manual no incluye las reglas que DT-020 y DT-027 desactivan para respetar la cabecera y `@package` | `phpdoc_separation`, `phpdoc_no_package` y `no_superfluous_phpdoc_tags` en false | Incluir esas tres reglas en el `pint.json` del manual. |
| AM-14 | 2.5.3 Tabla 64; 2.5.4 r5 | Bottom Navigation pensado para la app Flutter, fuera de alcance (DT-022); no define la web responsiva | Sidebar contraído a 64 px en < 640 px | Definir si la web móvil usa barra inferior o sidebar contraído. |

## 5. Calificación de cumplimiento del MP

| # | Rubro (sección MP) | Peso | Puntaje | Justificación |
|---|---|---|---|---|
| 1 | Cabeceras de prólogo (1.1) | 10 | 8.5 | 187 archivos con cabecera completa; solo faltan archivos generados (exentos). Descuentos: 35 descripciones de más de 3 líneas (MP-14), 2 lastModified sin actualizar (MP-15), 3 archivos de configuración incompletos (MP-16) |
| 2 | Formato y espaciado (1.2) | 10 | 9.0 | Pint PASS en 142 archivos; 0 BOM, CRLF o tabs; editorconfig, gitattributes y hook pre-commit presentes; 6 líneas de más de 120 columnas (MP-12) |
| 3 | Bloques de control (1.3) | 10 | 9.5 | 0 métodos con anidamiento mayor a 3; 0 catch genéricos en PHP; match en uso; 3 ternarios multilínea (MP-13) |
| 4 | Métodos y firmas (1.4) | 10 | 9.5 | 0 de 415 métodos con más de 50 líneas o más de 5 parámetros; 100 % tipados; promoción de propiedades; 6 PHPDoc sin @return (MP-18) |
| 5 | Nomenclatura (1.5) | 8 | 6.5 | Español sin acentos, camelCase y rutas kebab-case al 100 %; unos 27 métodos sin infinitivo (MP-10), abreviaturas como $q en 58 usos (MP-11), Dashboard (MP-22) |
| 6 | Documentación y comentarios (1.6) | 6 | 5.5 | PHPDoc en 338 de 338 métodos públicos/protegidos y 104 de 105 clases; comentarios en español; 0 código comentado; JS documentado en parte (MP-24) |
| 7 | Orden y estructura de clase (1.7) | 4 | 4.0 | 0 violaciones en 105 clases; un archivo = una clase |
| 8 | Paleta, fondos, contraste y logotipo (2.1–2.3) | 10 | 7.0 | 0 colores fuera de MP/DT-028; 3 pares sin contraste AA, uno en todas las pantallas (MP-04); 37 de 50 tintes dependen de una enmienda pendiente (MP-21); logo PNG (MP-26) |
| 9 | Layouts, menús y componentes (2.4–2.6) | 8 | 5.5 | Sidebar, topbar, modales, badges, avatares e interruptor conformes; botón primario de 40 px, checkbox de 20 px, encabezado de tabla y toast distintos (MP-19); sin bottom navigation móvil (MP-20) |
| 10 | Mensajes, textos de acción e iconos (2.7–2.9) | 6 | 5.5 | 45 etiquetas conformes; un solo set (79 iconos), aria-hidden correcto, CREDITS.md con licencias; inconsistencias menores (MP-22) |
| 11 | Análisis estático y seguridad (3.1, 3.3) | 8 | 5.0 | PHPStan 6 sin errores, Sanctum a 24 h, CSRF, bcrypt, composer audit limpio; faltan CORS (MP-02), rate limit general (MP-03) y HTTPS (MP-05); 5 validaciones en línea (MP-06) |
| 12 | Control de versiones, CI y entorno (3.2, 3.5) | 6 | 2.5 | Conventional Commits, plantilla de PR, CI bien definido, Docker con digest; pero sin main/develop, CI nunca ejecutado y sin PR (MP-01), rama con RF incorrecto, commits masivos y 50 archivos sin versionar (MP-07) |
| 13 | Pruebas (3.4) | 4 | 3.0 | 64 de 64 en verde, cobertura 94.2 % con factories; sin pruebas unitarias y 6 clases bajo 80 % (MP-08); sensibles a ejecución concurrente (MP-09) |
| | **Total** | **100** | **81.0** | **Cumplimiento alto en el código; débil en el proceso de versiones y en la seguridad de la API** |

## 6. Conteo por severidad

| Severidad | Cantidad | IDs |
|---|---|---|
| CRÍTICO | 0 | — |
| MAYOR | 4 | MP-01, MP-02, MP-03, MP-04 |
| MENOR | 15 | MP-05, MP-06, MP-07, MP-08, MP-10, MP-11, MP-12, MP-13, MP-14, MP-15, MP-16, MP-18, MP-19, MP-20, MP-22 |
| OBSERVACIÓN | 8 | MP-09, MP-17, MP-21, MP-23, MP-24, MP-25, MP-26, MP-27 |
| AJUSTE-MANUAL | 14 | AM-01 a AM-14 |

DT pendientes que condicionan esta evaluación (no son NC, pero no tienen respaldo aprobado): DT-027 (reglas de Pint), la enmienda de DT-028 (tintes, JS, dependencias) y DT-029 (BaconQrCode); también DT-023 a DT-026, DT-030 y DT-031, del ámbito MD/MBD.

## 7. Propuestas de NC-01 (MC Anexo C) para los No graves

| NC propuesta | Requisito incumplido | Causa raíz probable |
|---|---|---|
| NC-MP-01 (MP-01) | MP 3.2.2 y 3.2.5 r1, r4, r6; MD 10.3 (CI en cada integración) | El repositorio se inicializó directamente en ramas de trabajo y nunca se crearon main y develop; el disparador del CI depende de ellas, por lo que la verificación automática quedó inactiva sin detectarse |
| NC-MP-02 (MP-02, MP-03, MP-05) | MP 3.3.3 Tabla 86 (CORS, rate limiting, HTTPS) | Solo se implementó el limitador de inicio de sesión y se confió en los valores por defecto de Laravel 12, que no publica config/cors.php; no existe lista de verificación de endurecimiento para producción |
| NC-MP-03 (MP-04) | MP 2.1.5 r4 y 2.2.3 Tabla 54 (WCAG AA) | La corrección de contraste de DT-028 (a) se aplicó al componente botón y no a los demás usos del primario con texto blanco; no hay verificación automatizada de pares de color |

## 8. Decisión propuesta y qué falta para producción

**Decisión propuesta: aceptar con correcciones.** El código PHP cumple de forma sobresaliente los límites de complejidad, el tipado, la documentación y el orden del cap. 1. Los incumplimientos están en el proceso de versiones y CI, en la seguridad de la API y en la accesibilidad.

Para producción falta, como mínimo:

1. Crear main y develop protegidas, integrar por PR y obtener el CI en verde (MP-01); versionar los 50 archivos pendientes (MP-07).
2. Restringir CORS al dominio institucional y aplicar rate limiting de 60 req/min por usuario (MP-02, MP-03).
3. HTTPS con HSTS, esquema forzado y cookie segura en QA y producción (MP-05).
4. Corregir los 3 pares sin contraste AA (MP-04).
5. Que Raúl resuelva DT-027, la enmienda de DT-028 y DT-029 (MP-21).
6. Recomendado: pruebas unitarias para las clases bajo 80 % (MP-08) y las correcciones MENOR de nomenclatura, longitud y cabeceras.

La decisión final corresponde a Raúl Treviño.
