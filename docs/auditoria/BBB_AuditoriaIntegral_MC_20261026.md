# Auditoría integral del SPEI contra el Manual de Calidad BBB-CA-001 v1.2

| Campo | Valor |
|---|---|
| Tipo | Auditoría interna de cumplimiento del SGC (MC 7.2), sin LV-01 formal de hito; sirve como insumo para A-01…A-04 |
| Fecha | 25/10/2026 |
| Auditor | Subagente `spei-auditor` (independiente, solo lectura; no es autor de ningún cambio) |
| Repositorio | `C:\Users\Lenovo\Documents\spei`, rama `feature/RF-011-gestion-prestamos` (HEAD `593db78`) + 49 archivos modificados sin commit + 1 archivo sin seguimiento |
| Norma de referencia | MC v1.2 (texto en `tmp/pdfs/BBB_ManualCalidad_v1.2.txt`), CLAUDE.md, registro DT-001…DT-031 |
| Dictamen propuesto | **No apto para liberar a producción.** La decisión final es de Raúl. |

---

## 1. Evidencia objetiva obtenida por el auditor (comandos re-ejecutados)

| Comando / consulta | Resultado |
|---|---|
| `docker compose exec -T app ./vendor/bin/pint --test` | PASS, 142 archivos |
| `docker compose exec -T app ./vendor/bin/phpstan analyse` | `[OK] No errors` (nivel 6) |
| `docker compose exec -T app composer audit` | `No security vulnerability advisories found.` |
| `docker compose exec -T app ./vendor/bin/pest --coverage --min=80` | Exit 0, cobertura total **94.2 %** (64 pruebas). Una primera ejecución dio 52 fallos "SQLSTATE[42S02] Base table or view not found: 1146" sobre `spei_testing.usuarios` porque otro proceso `pest` corría en paralelo sobre la misma base (ver MC-27) |
| `git branch -a`, `git tag`, `git ls-remote origin` | Ramas: `docs/BBB-I0-inicializacion-repositorio` y `feature/RF-011-gestion-prestamos`. **No existen `main` ni `develop`. No hay etiquetas.** Rama por defecto en GitHub: `docs/BBB-I0-inicializacion-repositorio` |
| `curl https://api.github.com/repos/xSlimerx03/SPEI` | `"private": false, "visibility": "public"` |
| `curl .../actions/runs` | `"total_count": 0` (el CI nunca se ha ejecutado) |
| `git log feature/RF-011-gestion-prestamos` | 2 commits: `82dae0f chore: inicializar repositorio SPEI` y `593db78 feat: implementar gestión integral de préstamos` (170 archivos, +18 675 / −2 073) |
| `git status --short` | 49 archivos `M` (vistas, CSS, compose, DT, CP-01, CU-011, RT-01) y `?? docs/auditoria/BBB_RED01_DocsI0Inicializacion_20261025.md` |
| `find docs -type f` | 30 `.md` + 1 PDF. `docs/diagramas/` y `docs/liberaciones/` vacías. No hay ningún NC-01, LV-01, SC-01 ni AR-01. `tests/Unit/` vacío |
| `git grep` de secretos en HEAD | Sin secretos (`.env` excluido por `.gitignore:9`; `.env.example` con `<definir>`) |

---

## 2. Recorrido del manual sección por sección

| Sección MC | Requisito | Cumple | Evidencia / comentario |
|---|---|---|---|
| 1.3 / 1.5 | Los demás manuales y documentos respetan formato y control del MC | No | Ver MC-07 y MC-08 |
| 1.8 (IEEE 730) | Revisor distinto del autor; auditorías con LV-01 | Parcial | RED-01 independiente (`docs/auditoria/BBB_RED01_…md:8`); no hay LV-01 (MC-05) |
| 1.8 (12207) | Gestión de configuración, verificación y validación | Parcial | Git y CI definidos, pero sin `main`/`develop`, sin etiquetas y sin ejecuciones de CI (MC-02) |
| 1.8 (25010) | RNF como criterio de aceptación | Parcial | REQ-01 con RNF-001…011; RT-01 sin filas RNF/RN/RS (`BBB_RT01_Matriz_v1.0.md:3-4`) |
| 2.3 | Exclusiones justificadas | Parcial | Solo se excluye 7.1.5; las omisiones de DT-018 y DT-022 no aparecen como exclusiones del SGC (AM-01) |
| 2.4 | Procesos con entradas, salidas y responsables | Sí (adaptado) | CLAUDE.md §9 y plan §3 asignan un agente a cada proceso |
| 3.1 / 3.3 / 9 | Roles: líder, revisor, responsable de documentación, autoridad de aprobación (docente) | Adaptado (DT-018 aprobada) | `BBB_DT_Registro_v1.0.md:135-139`. Ver AM-01 |
| 3.4 | Requisitos validados antes de cada etapa; satisfacción del cliente medida | No | REQ-01 "Propuesto" (`BBB_REQ01_LineaBase_v1.0.md:10`), DT-023 "Propuesta" (`:170`); satisfacción sin medir (MC-06, AM-07) |
| 4.1 | Matriz P×I 1–3 con nivel, acción y responsable; riesgo de satisfacción del cliente | Parcial | El plan §5 usa Alta/Media, sin nivel numérico ni responsable y sin el riesgo de cliente (MC-17) |
| 4.2 / Tabla 6 | Objetivos de calidad con seguimiento | No | Los 5 objetivos no se miden (MC-11) |
| 4.3 / 8.4 | Cambios planificados vía SC-01 con segregación de funciones | No | Ningún SC-01; documentos aprobados modificados sin control (MC-08, AM-01) |
| 5.1 / 5.1.1 | Recursos y ambiente | Sí | Docker Compose operativo (`docker compose ps`: 8 servicios) |
| 5.1.2 / 5.2 / 5.3 | Conocimiento, competencia e inducción vía AR-01 | N-A (DT-018) | AR-01 omitido por DT-018 (AM-07) |
| 5.4 | Matriz de comunicación | Parcial | Los resultados de auditoría se comunican en RED-01; la publicación de versiones no se comunica |
| 5.5.2 | Logotipo en encabezado y portada de todo documento oficial | No | 0/30 documentos (MC-07) |
| 5.5.3–5.5.6 | Calibri/Cambria, márgenes, portada, encabezado y pie, tablas | No | Todos los documentos son Markdown sin portada ni pie (MC-07, AM-03) |
| 5.5.7 | Español, términos extranjeros en cursiva, siglas definidas | Parcial | Español correcto; *lock*, *hash* y *badges* sin cursiva (MC-23) |
| 5.5.8 | Nomenclatura `BBB_Nombre_vX.Y`, versionado X.Y y vigencia | Parcial | 28/31 archivos conformes; excepciones en MC-13; versiones sin incrementar (MC-08) |
| 5.5.9 | Abreviaturas | Sí | Uso consistente de MC, MD, MP, MBD, RF y CU |
| 5.5.10 / 5.5.10.3 / 5.5.10.4 | Formatos REF en blanco y con instructivo | No | El repositorio no tiene plantillas AR, SC, NC ni LV (MC-14) |
| 5.5.11 | Actas AR-01 | N-A (DT-018) | Omitidas; su contenido útil se consolida en PI-01 y EI-01 |
| 5.5.12 | Bloques de código ≤ 30 líneas y ≤ 90 caracteres, sin secretos | No | `BBB_DDL03_TablasPrestamos_v1.0.md:47`: bloque de 71 líneas; 6 líneas > 90 en DDL-03 y 4 en DDL-06 (MC-15, AM-04). Sin secretos: Sí |
| 5.5.13 | Diagramas con prefijo DCU, DER, etc., editable + PNG/SVG | No | `docs/diagramas/` vacía; columna "Diagrama" de RT-01 en "—" en las 58 filas (MC-09) |
| 5.5.14 | Documentos creados desde `BBB_Plantilla_v1.1.dotx` | No | La plantilla no está en el repositorio y no hay ningún `.docx` entregable (MC-07) |
| 6.1 criterios documentales | Formato MC, versión y autor | No | MC-07, MC-08 |
| 6.1 criterios de requisitos | Pruebas superadas y ningún RF sin implementar | No | 23/58 RF pendientes, 2 parciales, 0 aprobados (MC-01) |
| 6.1 criterios técnicos | Código versionado en GitHub; pruebas unitarias de funciones críticas; integración antes de liberar | Parcial | 49 archivos sin commit; `tests/Unit/` vacío (MC-02, MC-19) |
| 6.1 criterios de revisión | Revisión por alguien distinto del autor, con resultado registrado | Parcial | RED-01 registrado, pero sobre otra rama y con correcciones sin verificar (MC-12) |
| 6.2 | Cambios de requisitos vía SC-01 | No | CU-011 modificado sin SC-01 ni cambio de versión (MC-08) |
| 6.3 | Salidas de diseño: código, diagramas, manuales técnicos, casos de prueba | Parcial | Hay código y CP-01; faltan diagramas y los DDL del bloque A (MC-09) |
| 6.4 / Tabla 17 | GitHub privado; hosting evaluado; librerías estables con licencia compatible | No | Repositorio **público**; hosting sin definir; `composer audit` limpio (MC-03, MC-20) |
| 6.5 | Salidas no conformes identificadas y tratadas, con NC-01 cuando amerite | Parcial | RED-01 lista hallazgos con estado; no hay NC-01 (MC-04) |
| 6.6 | Propiedad del cliente con acceso restringido | No | Repositorio público con el SRS del cliente; licencias contradictorias (MC-03) |
| 6.7 | Comunicar al cliente que no habrá soporte posterior | N-A todavía | No hay liberación |
| 7.1 / Tabla 18 | 10 indicadores con fórmula, frecuencia y responsable | No | No hay ningún registro de indicadores (MC-11) |
| 7.2 / Tabla 19 | Programa A-01…A-04 con LV-01 | No | El programa está en el plan §2, pero no se ha ejecutado ninguna LV-01 (MC-05) |
| 7.3 | Revisión por la dirección con entradas y salidas mínimas | No | Los EI-01 no recogen las entradas de 7.3 (MC-18) |
| 8.2 | NC-01 con causa raíz y verificación de eficacia | No | MC-04 |
| 8.3 | Los hallazgos alimentan la mejora del manual | Parcial | Las DT registran los conflictos, pero no se ha propuesto ningún SC-01 al MC (AM-*) |

---

## 3. Tabla de hallazgos

Severidades: CRÍTICO (impide producción), MAYOR (corregir antes de liberar), MENOR, OBSERVACIÓN, AJUSTE-MANUAL.

| ID | Sección MC | Requisito | Evidencia | Severidad | Acción recomendada |
|---|---|---|---|---|---|
| MC-01 | 6.1 (criterios de requisitos); 3.4; Tabla 18 "Requisitos implementados" (meta 100 %) | "No quedan requisitos funcionales sin implementar"; un entregable solo se libera si cumple todos los criterios | `docs/trazabilidad/BBB_RT01_Matriz_v1.0.md`: 58 RF, de los cuales 23 están "Pendiente" (RF-016…021, 027, 030, 033, 038, 040, 041…045, 047…050, 055…057), RF-039 está "Parcial" y RF-008 tiene el "aislamiento pendiente"; **0 filas "Aprobado"** (`:6-8`). Indicador: 33/58 = 56.9 % | CRÍTICO | Implementar los RF pendientes o reducir formalmente el alcance de la 1.0 con SC-01 o DT aprobada y una nueva línea base; aprobar filas de RT-01 solo con evidencia completa |
| MC-02 | 6.1 (código versionado en GitHub, integración antes de liberar); 1.8 (12207, gestión de configuración) | Línea base de configuración reproducible e integrada | No existen `main` ni `develop` (`git branch -a`, `git ls-remote`) y no hay etiquetas; el CI solo se dispara en `develop`/`main` (`.github/workflows/ci.yml:20-24`) y GitHub reporta 0 ejecuciones; 49 archivos modificados sin commit, entre ellos DT, CP-01 y RT-01; `docs/liberaciones/` vacía (sin LI-01) | CRÍTICO | Crear `develop` y `main`, hacer commit de todo, integrar por PR con CI verde en GitHub, etiquetar `v1.0.0` y registrar la liberación |
| MC-03 | 6.4 Tabla 17 ("GitHub: repositorio privado"); 6.6 (propiedad del cliente con acceso restringido) | El código y la documentación del cliente se resguardan con acceso restringido | La API de GitHub devuelve `"private": false, "visibility": "public"`. Quedan publicados `docs/requisitos/SRS_…v2.pdf` y el prototipo. Licencias contradictorias: `composer.json:7` dice `"license": "MIT"`, 182 cabeceras dicen `@license Propietario - BigBitByte / Institución Educativa` (p. ej. `app/Models/Prestamo.php:18`) y MC 6.6 asigna la propiedad al Mtro. Mauro Sánchez | MAYOR | Poner el repositorio en privado; unificar la licencia (`composer.json` = `proprietary`), alinear las cabeceras con MC 6.6 y registrarlo con DT o SC-01 |
| MC-04 | 8.2; 6.5; 7.2; Tabla 18 "NC cerradas ≥ 90 %" | Toda no conformidad se documenta con NC-01 (causa raíz, acción, eficacia y cierre) | No existe ningún NC-01. RED-01 clasifica H-01, H-02 y H-08 como "NC mayor" y otros 15 hallazgos como "NC menor" (`docs/auditoria/BBB_RED01_…md:27-48`), sin causa raíz ni verificación de eficacia; el indicador no se puede calcular | MAYOR | Abrir un NC-01 por cada NC mayor (como mínimo H-01, H-02, H-03, H-08 y N-01) con los 13 campos del Anexo C, y cerrarlos solo después de verificar la eficacia |
| MC-05 | 7.2 y Tabla 19; 1.8 (IEEE 730: LV-01 obligatoria); Tabla 18 "Cumplimiento del plan de auditorías" | Auditorías A-01…A-04 con LV-01 en sus hitos | No hay ninguna LV-01. El plan (`BBB_PlanDesarrollo_v1.0.md:28-30`) liga A-01 al cierre de los CU del bloque A, pero la construcción empezó el 18/10 con todos los CU en estado "propuesto" (`BBB_CU001…v1.0.md:3`): el hito de requisitos se rebasó sin auditarse. RED-01 no sustituye a LV-01 (Anexo D: folio y criterios Sí/No/N-A) | MAYOR | Ejecutar ya A-01 (requisitos y control documental) con LV-01; calendarizar A-02 (diseño, fin del bloque B), A-03 y A-04 (17–21/11) y registrar el indicador |
| MC-06 | 3.4 viñeta 1; 6.2; 6.3 (verificación y validación) | Requisitos capturados y validados antes de iniciar cada etapa | `BBB_REQ01_LineaBase_v1.0.md:10` "Propuesto — pendiente de aceptación"; DT-023 "Propuesta" (`BBB_DT_Registro_v1.0.md:170`); los 15 CU en "propuesto"; PI-01 del bloque A sin aprobar (`BBB_PI01_A_v1.0.md:73`); EI-01 I0 sin aprobar (`BBB_EI01_I0_v1.0.md:37`). Decisiones abiertas: domingos (`BBB_CU011…:20-21`) y modo sin conexión (DT-022, `:165`) | MAYOR | Que Raúl acepte o ajuste REQ-01, DT-023 y los CU-001…015 con fecha; resolver las dos decisiones abiertas; aprobar PI-01 A y EI-01 I0 |
| MC-07 | 5.5.2, 5.5.3 (Tabla 9), 5.5.4, 5.5.5, 5.5.6, 5.5.14; 4.2 objetivo 1; 6.1 criterio documental | Todo documento con logotipo, portada de 11 elementos, control de versiones, contenido, encabezado y pie "Página X de Y", creado desde `BBB_Plantilla_v1.1.dotx` | Los 30 documentos de `docs/` son Markdown sin portada, logo, índice ni pie. La plantilla no está en el repositorio y no hay ningún `.docx`. El agente `spei-documentador` prevé generar los `.docx` (`.claude/agents/spei-documentador.md:14-19`), pero nunca se generaron. Indicador "Documentos conformes": 0 % | MAYOR | Generar desde la plantilla los entregables `.docx` (al menos plan, REQ-01, CU, CP-01, RT-01 e informes de auditoría) o aprobar y registrar AM-03 |
| MC-08 | 5.5.8 (Tabla 10, vigencia); 5.5.14 (la versión coincide en portada, encabezado y tabla); 4.2 objetivo 2; 4.3; 8.4; 6.2 | Cada cambio incrementa la versión y queda en el control de versiones; los cambios a documentos aprobados van por SC-01 | El plan se aprobó el 18/10 (`BBB_PlanDesarrollo_v1.0.md:8`) y se modificó el 25/10 (`:63-64`, diff `82dae0f..593db78`) sin salir de la v1.0. El registro DT sigue en v1.0 con fecha 18/10 (`BBB_DT_Registro_v1.0.md:7-8`) aunque incluye DT-028…031 (23–25/10). CU-011 v1.0 se modificó sin commit (`git diff`). CP-01 se llama `_v1.0` y declara la versión 1.1 (`BBB_CP01…:7`). RT-01, EI-01 I0, PI-01 I0/A y MVP no tienen campo de versión o de autor. Ningún documento tiene tabla de historial y no hay SC-01 | MAYOR | Agregar una tabla "Control de versiones" a cada documento controlado; subir las versiones (p. ej. Plan v1.1, DT v1.4, CP-01 renombrado a `_v1.1`); registrar con SC-01 los cambios a documentos aprobados |
| MC-09 | 5.5.13 (Tabla 15); 6.3 (salidas de diseño); 7.2 A-02 | Diagramas DCU, DCL, DSE, DES, DER y DDE, con editable + PNG ≥ 300 ppp o SVG | `docs/diagramas/` vacía; columna "Diagrama (4)" de RT-01 en "—" en las 58 filas. Salidas del diseño de datos incompletas: hay 9 migraciones de negocio, pero solo existen los DDL de préstamos (`docs/bd/`); el riesgo sigue abierto en el plan (`:63`) | MAYOR | Elaborar como mínimo BBB_DCU_*, BBB_DER_SPEI, BBB_DSE (reserva, entrega con QR), BBB_DES (préstamo, recurso, institución) y BBB_DDE; completar DDL-03/06/07/08 del bloque A; enlazarlos en RT-01 |
| MC-10 | 8.4; 4.3; control de cambios (regla del encargo: una DT en "Propuesta" no cubre la desviación) | Las desviaciones se aprueban antes de liberarse | DT en "Propuesta / Pendiente": DT-023 (`:170`), DT-024 (`:177`), DT-025 (`:184`), DT-026 (`:191`), DT-027 (`:198`), enmienda de DT-028 (`:211`), DT-029 (`:217`), DT-030 (`:224`) y DT-031 (`:231`). Varias ya están implementadas (p. ej. DT-031 en `docker-compose.yml`, sin commit) | MAYOR (pendiente de aprobación) | Que Raúl apruebe o rechace las 9 DT con fecha; mientras tanto, las desviaciones que cubren cuentan como no conformidades abiertas |
| MC-11 | 7.1 Tabla 18; 4.2 Tabla 6 | 10 indicadores con fórmula, frecuencia y responsable, analizados en la revisión por la dirección | No existe ficha ni registro de indicadores; el plan solo enumera 5 (`BBB_PlanDesarrollo_v1.0.md:66-68`) y los EI-01 no los calculan. Valores reconstruidos por el auditor: requisitos implementados 56.9 %; pruebas superadas 100 % (64/64); NC cerradas: no calculable; SC cerradas: no calculable; documentos conformes 0 %; documentos con versión y autor en el encabezado 24/30 = 80 %; cumplimiento del plan de auditorías 0 %; satisfacción del cliente sin medir | MAYOR | Crear `docs/calidad/BBB_Indicadores_v1.0.md` con los 10 indicadores y actualizarlo en cada EI-01 |
| MC-12 | 6.1 (criterio de revisión); 1.2 (verificable); 7.2 | Revisión independiente registrada sobre el objeto que se libera | RED-01 revisó la rama `docs/BBB-I0-inicializacion-repositorio` con "cambios sin commit" (`BBB_RED01…:7`), sin un hash verificable, y declara que las correcciones posteriores no se han verificado (`:65`). No hay RED-01 de `feature/RF-011-gestion-prestamos` (`593db78`) ni de los 49 archivos modificados, y el propio RED-01 no está versionado en Git | MAYOR | Hacer el RED-01 de la rama feature sobre un commit concreto, verificar las correcciones H-11, H-12, H-20 y N-01…N-06 y versionar el RED-01 |
| MC-13 | 5.5.8 (nomenclatura con versión, sin espacios) | `BBB_<Nombre>_vX.Y.ext`, sin espacios | `docs/requisitos/SRS_Sistema_Prestamo_Equipo_Institucional_v2.pdf`: sin prefijo BBB, y el nombre dice "v2" mientras el contenido es la versión 1.0 (DT-023 `:171`). `docs/auditoria/BBB_RED01_DocsI0Inicializacion_20261025.md` no lleva versión. La carpeta `Prototipo interactivo SPEI/` y el archivo `SPEI Prototipo.dc.html` tienen espacios | MENOR | Renombrar (p. ej. `BBB_SRS_SPEI_v1.0.pdf`, `Prototipo_SPEI/`) o aprobar AM-06 para los registros fechados |
| MC-14 | 5.5.10.3, 5.5.10.4; Figura 6 | Cada formato REF en blanco y con instructivo | No hay plantillas de SC-01, NC-01 ni LV-01 en el repositorio (ni en `docs/` ni en `.claude/`) | MENOR | Crear `docs/formatos/` con `BBB_SC01_Vacio`, `BBB_NC01_Vacio` y `BBB_LV01_Vacio`, cada uno con su instructivo |
| MC-15 | 5.5.12 Tabla 14 | Bloques de código ≤ 30 líneas y ≤ 90 caracteres por línea | `docs/bd/BBB_DDL03_TablasPrestamos_v1.0.md:47`: bloque de 71 líneas; 6 líneas > 90 en DDL-03 y 4 en DDL-06 | MENOR | Partir los bloques o referenciar la migración en el repositorio; o aprobar AM-04 |
| MC-16 | 6.1; Tabla 5 ("Trazabilidad de casos de prueba contra la ERS") | Trazabilidad coherente entre requisitos, pruebas y código | CP-002 cita RF-040 y CP-001 cita RF-049 (`BBB_CP01…`), ambos "Pendiente" en RT-01; CP-024 cita RF-036, pero la fila RF-036 no lo menciona; RF-031 figura "Implementado" sin prueba; la columna "PR/commit" dice "Sin commit" aunque el código ya está en `593db78`; RT-01 no tiene filas RNF/RN/RS | MENOR | Conciliar CP-01 y RT-01, poner los hashes y agregar las filas RNF/RN/RS |
| MC-17 | 4.1 Tabla 5; 3.4 viñeta 2 | Matriz P×I (1–3) con nivel, acción, responsable y seguimiento; riesgo explícito de baja satisfacción del cliente; oportunidades | El plan (`:56-64`) califica P e I como Alta/Media, sin nivel numérico ni responsable; faltan el riesgo de satisfacción del cliente y las oportunidades | MENOR | Rehacer la matriz con la escala del MC, responsables y las oportunidades de la Tabla 5 |
| MC-18 | 7.3 | Revisión por la dirección al inicio y al cierre de etapa, con 8 entradas y 3 salidas | DT-018 consolida el contenido de las actas en PI-01 y EI-01, pero ningún EI-01 trata la retroalimentación del cliente, el desempeño de proveedores, la eficacia frente a riesgos ni las necesidades de recursos | MENOR | Agregar a cada EI-01 una sección "Revisión por la dirección (MC 7.3)" con las entradas y salidas mínimas |
| MC-19 | 6.1 (criterio técnico: "pruebas unitarias para las funciones críticas") | Pruebas unitarias de las funciones críticas | `tests/Unit/` vacío; todas las pruebas son Feature. Cobertura por archivo < 80 % en componentes críticos: `PerteneceAInstitucionTrait` 71.4 % (tenant), `RecursoPolicy` 71.4 %, `DisponibilidadRecursoService` 72.2 %, `ResumenAuditoria` 54.2 % | MENOR | Agregar pruebas unitarias del ámbito de tenant, las policies y el servicio de disponibilidad |
| MC-20 | 6.4 Tabla 17 | Hosting y despliegue seleccionados y evaluados; librerías estables con licencia compatible | No hay proveedor de hosting definido ni evaluado; CREDITS.md registra licencias de fuentes y de BaconQrCode, pero no existe inventario de licencias de las dependencias de Composer | MENOR | Registrar con DT el ambiente de producción y sus criterios; agregar `composer licenses` a la evidencia |
| MC-21 | 6.5 | Salidas no conformes con registro del tratamiento | DEF-001 (cobertura) sigue abierto en `BBB_EI01_I0_v1.0.md:31,37` aunque la cobertura ya es 94.2 %; no existe registro de defectos (DEF-XXX) | MENOR | Cerrar DEF-001 con evidencia y crear un registro de defectos |
| MC-22 | 6.1; 1.8 (trazabilidad de configuración) | Rama y commits trazables a un RF | La rama `feature/RF-011-gestion-prestamos` lleva el número de RF-011 ("registro con código de barras", RT-01), pero contiene RF-022…058; un único commit de 170 archivos mezcla los bloques A a D, el prototipo y documentación | MENOR | Integrar con un PR que liste los RF cubiertos; en adelante, una rama por RF o por bloque con su nombre real |
| MC-23 | 5.5.7 | Términos en otro idioma en cursiva; siglas definidas en su primera aparición | *lock*, *hash*, *badges*, *dashboard* y *toasts* sin cursiva en DT-028 y CP-01; siglas RN, RS y CA sin definir en los CU-01 | OBSERVACIÓN | Corregirlo al generar los `.docx` (MC-07) |
| MC-24 | 5.5.8 / 4.1 (SharePoint como fuente única) | Biblioteca documental controlada | CLAUDE.md §0 omite SharePoint, pero ninguna DT aprobada lo formaliza (DT-018 solo menciona AR-01, JD-01, SP-01 y EVS-01) | OBSERVACIÓN | Ampliar DT-018 o crear una DT que declare `docs/` + Git como biblioteca única (ver AM-02) |
| MC-25 | 1.2 / 6.1 | Evidencia de autoría y revisión no ambigua | El commit `593db78` lleva la marca de coautoría de Copilot, y en `refs/agents/*` y `refs/codex/*` hay commits de *checkpoint* ("Agent host session…") fuera de Conventional Commits. No están en las ramas ni en el remoto | OBSERVACIÓN | Mantener esas refs fuera del remoto y declarar en el EI-01 qué herramientas asistieron la autoría |
| MC-26 | 6.6 | Sin datos personales en un repositorio expuesto | Las cabeceras usan el correo genérico `raul.trevino@institucion.edu.mx` (p. ej. `.github/workflows/ci.yml`). No se detectaron secretos | OBSERVACIÓN | Revisarlo al corregir MC-03 |
| MC-27 | 1.2 (reproducible y verificable) | Evidencia de pruebas reproducible | Dos ejecuciones simultáneas de `pest` sobre la misma base `spei_testing` dieron 52 fallos falsos (tabla `usuarios` inexistente durante el `RefreshDatabase` del otro proceso); ejecutadas por separado, pasan | OBSERVACIÓN | Usar una base por proceso o un *lock* de ejecución; documentarlo en el README |

### Ajustes propuestos al manual (AJUSTE-MANUAL)

| ID | Sección MC | Problema | Redacción sugerida |
|---|---|---|---|
| AM-01 | 3.1, 3.3, 8.4 (segregación de funciones), 9, Anexo B campo (7) | Con un solo desarrollador (DT-018) nadie distinto del solicitante puede aprobar un SC-01, y una DT con formato del MD (precedencia menor) termina modificando requisitos del MC | Agregar en 3.3: "Cuando el proyecto lo ejecute un único integrante, la segregación de funciones se cumple con la revisión previa de un revisor independiente (persona o agente SQA que no sea autor del cambio), cuyo dictamen se adjunta al SC-01; aprueba el responsable del proyecto y la autoridad de aprobación ratifica al cierre de etapa." Declarar la excepción en 2.3 |
| AM-02 | 5.5.8, Figura 6, 4.1 Tabla 5, 5.1, 6.4 Tabla 17 | SharePoint no aplica, y Git cumple mejor la fuente única de verdad y el historial | "La biblioteca documental puede residir en el repositorio Git del proyecto (carpeta `docs/`). El historial de commits y las etiquetas sustituyen a la carpeta «Versiones»; `docs/formatos/` contiene «Vacíos» e «Instrucciones», y los formatos llenados residen en la carpeta de su proceso." |
| AM-03 | 5.5.3–5.5.6, 5.5.14 | El formato Word no aplica a los registros técnicos vivos (CU, CP, RT, DDL, DT, RED) que se versionan con el código; Markdown permite comparar versiones y revisar | "Los registros técnicos que se mantienen junto al código pueden elaborarse en Markdown con un encabezado mínimo (código REF, versión, fecha, autor, estado y tabla de control de versiones). Los entregables al cliente (manuales, informes de auditoría y de liberación) se generan con la plantilla institucional." |
| AM-04 | 5.5.12 Tabla 14 | El límite de 30 líneas choca con los formatos DDL del MBD, que exigen scripts completos con rollback | "Se exceptúan del límite los scripts que un formato DDL exija completos; se presentan en anexo o se cita el archivo de migración con su ruta y su commit." |
| AM-05 | 7.2 Tabla 19 | Los hitos por etapa (cascada) no corresponden a un ciclo RUP iterativo | "En proyectos iterativos, A-01 se realiza al aprobar la línea base de requisitos; A-02 y A-03 al cierre de la iteración que concluya el diseño o la construcción de cada bloque; A-04 antes de la liberación. Cada RED-01 alimenta la auditoría del hito." |
| AM-06 | 5.5.8 (versión en el nombre) | Los registros puntuales e inmutables (RED-01, LV-01, NC-01) se identifican mejor por folio y fecha que por versión | "Los registros de un evento se nombran `BBB_<REF>_<objeto>_<AAAAMMDD>` y no se versionan; una corrección genera un registro nuevo." |
| AM-07 | 3.4 (satisfacción), Tabla 18 (satisfacción, reuniones con acta), 5.2, 5.3, 6.3 (validación con el docente) | Con DT-018 el docente no participa en el proceso diario, así que estos indicadores no se pueden medir | "La satisfacción del cliente se mide al cierre de etapa con la aceptación del responsable en el EI-01 (escala 1–5) y, cuando sea posible, con la valoración del docente en la entrega." Marcar "Reuniones con acta" como N-A si no hay reuniones formales |
| AM-08 | 6.4 Tabla 17 (librerías) | El sistema ya aplica un control más fuerte que el manual (`composer audit` en CI, versiones y digest fijados, DT-002, DT-019) | "Las dependencias se fijan por archivo de bloqueo y digest, se auditan automáticamente en CI (`composer audit`) y su licencia se registra en `CREDITS.md`." |

---

## 4. Calificación de cumplimiento del MC

| Rubro | Peso | Puntaje | Justificación |
|---|---|---|---|
| Control documental: nomenclatura, versionado y vigencia (5.5.8, 8.4) | 10 | 4 | 28/31 nombres conformes; versiones sin incrementar, sin tablas de historial, 0 SC-01 (MC-08, MC-13) |
| Formato de documentos e identidad (5.5.2–5.5.7, 5.5.12–5.5.14) | 10 | 1 | Ningún documento tiene formato MC; solo se cumplen idioma y fechas (MC-07, MC-15, MC-23) |
| Formatos REF y registros (5.5.10, AR/SC/NC/LV) | 8 | 1 | Ningún formato REF, ni llenado ni en blanco; AR-01 omitido por DT-018 (MC-04, MC-05, MC-14) |
| Requisitos y validación (3.4, 6.2) | 10 | 5 | REQ-01 y 15 CU con criterios de aceptación, pero nada aprobado y 2 decisiones abiertas (MC-06) |
| Diseño y desarrollo: salidas y controles (6.3, 5.5.13) | 8 | 3 | Hay código, CU y CP; no hay diagramas y faltan los DDL del bloque A (MC-09) |
| Pruebas y evidencia técnica (6.1 criterios técnicos) | 8 | 6 | 64/64 pruebas, 94.2 % de cobertura; Pint, PHPStan 6 y `composer audit` en verde. Sin pruebas unitarias y con evidencia frágil ante ejecución concurrente (MC-19, MC-27) |
| Auditoría interna y revisión independiente (1.8, 7.2) | 12 | 4 | RED-01 independiente y archivado; sin LV-01, A-01 vencido y re-verificación pendiente (MC-05, MC-12) |
| No conformidades y acción correctiva (6.5, 8.2) | 8 | 2 | RED-01 lleva el estado de cada hallazgo; no hay NC-01, causa raíz ni verificación de eficacia (MC-04, MC-21) |
| Indicadores y revisión por la dirección (4.2, 7.1, 7.3) | 8 | 1 | Solo se enumeran en el plan; ninguno se mide (MC-11, MC-18) |
| Gestión de configuración y criterios de liberación (6.1, 1.8) | 10 | 3 | Git, CI definido y Conventional Commits; sin `main`/`develop`, CI sin ejecutar, 49 archivos sin commit, 56.9 % de RF (MC-01, MC-02, MC-22) |
| Riesgos y planificación (4.1, 4.3) | 4 | 2 | Plan con riesgos y tratamiento, pero escala y contenido distintos de la Tabla 5 (MC-17) |
| Proveedores y propiedad del cliente (6.4, 6.6) | 4 | 1 | A favor: `composer audit` y CREDITS. En contra: repositorio público y licencias contradictorias (MC-03, MC-20) |
| **Total** | **100** | **33** | |

**Puntaje: 33/100.** Las desviaciones cubiertas por DT aprobadas (DT-001…DT-022 y DT-028 original) no restaron puntos.

---

## 5. Conteo por severidad

| Severidad | Cantidad | IDs |
|---|---|---|
| CRÍTICO | 2 | MC-01, MC-02 |
| MAYOR | 10 | MC-03…MC-12 |
| MENOR | 10 | MC-13…MC-22 |
| OBSERVACIÓN | 5 | MC-23…MC-27 |
| AJUSTE-MANUAL | 8 | AM-01…AM-08 |

---

## 6. Propuestas de NC-01 (MC Anexo C) para los "No" graves

| Folio propuesto | Hallazgo | Requisito incumplido | Causa raíz probable |
|---|---|---|---|
| NC-001 | MC-01 | MC 6.1, criterios de requisitos | Calendario comprimido (58 RF en 4 semanas con una persona) y un MVP que adelantó RF de los bloques B a D sin cerrar ninguno |
| NC-002 | MC-02 | MC 6.1 criterio técnico; 1.8 (12207) | El flujo de ramas de DT-001 nunca se instanció (no existen `develop` ni `main`) y el CI depende de esas ramas |
| NC-003 | MC-03 | MC 6.4 Tabla 17; 6.6 | El repositorio se creó con visibilidad pública y no se verificó contra el MC |
| NC-004 | MC-04 / MC-05 | MC 8.2; 7.2 | El RED-01 (MD) se usó como único registro de revisión y nunca se instanciaron los formatos del MC |
| NC-005 | MC-07 / MC-08 | MC 5.5.3–5.5.14; 5.5.8 | Los documentos se quedan como borradores Markdown permanentes: no hay un paso de publicación que genere el `.docx` y suba la versión |
| NC-006 | MC-06 / MC-10 | MC 3.4; 6.2; 8.4 | La implementación se adelanta a la aprobación: las decisiones se marcan "Propuesta" y el trabajo sigue sin un punto de aceptación |

## 7. Qué falta para producción desde la óptica del MC

1. Cerrar el alcance: implementar los 23 RF pendientes y los 2 parciales, o reducir formalmente la línea base con SC-01 o DT aprobada; aprobar las filas de RT-01 con evidencia completa.
2. Aprobar o rechazar REQ-01, los CU-001…015, PI-01 A, EI-01 I0, las DT-023…027, la enmienda de DT-028 y las DT-029…031; decidir el tema de los domingos y el modo sin conexión.
3. Poner el repositorio en privado y unificar la licencia conforme a MC 6.6.
4. Crear `develop` y `main`, hacer commit de todo, integrar por PR con CI verde en GitHub, etiquetar `v1.0.0` y registrar la liberación (LI-01) en `docs/liberaciones/`.
5. Ejecutar A-01…A-04 con LV-01 y abrir y cerrar los NC-01 con verificación de eficacia.
6. Elaborar los diagramas de MC 5.5.13 y los DDL del bloque A.
7. Medir los 10 indicadores de la Tabla 18 y registrar la revisión por la dirección (7.3).
8. Generar los documentos con formato MC desde la plantilla, o aprobar AM-02, AM-03 y AM-06 mediante el control de cambios del manual.
9. Seleccionar y evaluar el proveedor de hosting y despliegue (6.4).
