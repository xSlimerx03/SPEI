# Propuesta de actualización de los manuales del SPEI (v1.2 a v1.3)

| Campo | Valor |
|---|---|
| Documento | BBB_PropuestaActualizacionManuales_v1.0 |
| Código | PAM-2026-01 (insumo para los SC-01 de MC 8.3 y 8.4) |
| Nombre | Propuesta de actualización de los manuales BBB-CA-001, BBB-MD-002, BBB-MP-003 y BBB-BD-004 |
| Versión | 1.0 |
| Fecha | 27/10/2026 |
| Autor | Raúl Treviño Licona (redacción asistida por el subagente `spei-documentador`) |
| Estado | **Propuesta — pendiente de aprobación de Raúl Treviño** |
| Revisión independiente | Pendiente: RED-01 del subagente `spei-auditor` antes de abrir los SC-01 |
| Insumos | `docs/auditoria/BBB_AuditoriaIntegral_MC_20261026.md` (33/100), `..._MD_20261026.md` (54/100), `..._MP_20261026.md` (81/100), `..._MBD_20261026.md` (61/100); textos de los manuales v1.2 en `tmp/pdfs/`; `docs/decisiones/BBB_DT_Registro_v1.0.md` (DT-001…DT-031) |

Este documento propone cambios al texto de los cuatro manuales; no los aplica, no aprueba nada y no
modifica ningún otro archivo del repositorio. Cada propuesta se convierte en un cambio del manual solo
cuando Raúl la acepta y se tramita con su SC-01 (MC 8.4).

## Control de versiones

| Versión | Fecha | Descripción del cambio | Autor |
|---|---|---|---|
| 1.0 | 27/10/2026 | Emisión inicial de la propuesta a partir de las auditorías integrales del 25 y 26/10/2026 | Raúl Treviño Licona (asistido) |

---

## 1. Objeto y alcance

### 1.1 Qué incluye

1. Todos los hallazgos **AJUSTE-MANUAL** de los cuatro informes: MC AM-01…AM-08, MD-35…MD-42,
   MP AM-01…AM-14 y BD-25…BD-39 (45 hallazgos).
2. Los hallazgos **MENOR** y **OBSERVACIÓN** cuya atención exige aclarar o precisar el texto de un
   manual (por ejemplo, MC-18, MC-25, MD-24, MP-12, MP-16, MP-21, BD-11, BD-21).
3. Un anexo con los MENOR y OBSERVACIÓN que solo piden corregir el sistema (Anexo A), para que no se
   pierdan.
4. Las dependencias cruzadas entre manuales, las decisiones que Raúl debe tomar y una tabla de control
   de cambios propuesta para la v1.3 de cada manual.

Como los informes de MC y MP usan ambos el prefijo `AM-`, en este documento se citan como
**MC/AM-xx** y **MP/AM-xx**.

### 1.2 Nota de alcance: hallazgos CRÍTICO y MAYOR (excluidos)

Los hallazgos CRÍTICO y MAYOR son incumplimientos del sistema que se corrigen en el sistema, no en los
manuales, y no se desarrollan aquí:

| Manual | CRÍTICO | MAYOR |
|---|---|---|
| MC | MC-01, MC-02 | MC-03, MC-04, MC-05, MC-06, MC-07, MC-08, MC-09, MC-10, MC-11, MC-12 |
| MD | MD-01, MD-02, MD-03 | MD-04, MD-05, MD-06, MD-07, MD-08, MD-09, MD-10, MD-11, MD-12, MD-13, MD-14 |
| MP | — | MP-01, MP-02, MP-03, MP-04 |
| MBD | BD-01, BD-02 | BD-03, BD-04, BD-05, BD-06, BD-07 |

Tres de ellos se relacionan con propuestas de este documento. La propuesta **no los cierra**, pero
cambia la forma de atenderlos:

- **MC-07** (formato MC de los documentos): PM-MC-03 limita la obligación de la plantilla a los
  entregables. Si Raúl la rechaza, MC-07 se corrige generando los `.docx`.
- **MC-03** (repositorio público): PM-MC-02 y PM-MD-04 solo son válidas si el repositorio es privado
  (MC 6.4 Tabla 17 y 6.6).
- **MP-04** (contraste): PM-MP-10 corrige la regla del manual que origina el fallo. El CSS debe
  corregirse de todos modos.

## 2. Criterios de la propuesta

| Prioridad | Criterio |
|---|---|
| **Alta** | El texto vigente impide cumplir un proceso que ya se ejecuta (aprobación, control documental, auditoría), contradice un requisito de seguridad o integridad, o bloquea propuestas de otros manuales |
| **Media** | El texto es ambiguo, interno-contradictorio o está desactualizado frente a una DT aprobada; el sistema ya opera con la interpretación propuesta |
| **Baja** | Corrección editorial o precisión que no cambia la conducta exigida |

- **Texto actual** cita el manual v1.2 tal como aparece en `tmp/pdfs/*.txt`. Las citas pueden estar
  abreviadas (…).
- **Redacción propuesta** está lista para pegarse en el manual. Los textos entre corchetes `[ ]` son
  opciones que Raúl debe elegir (sección 9).
- Toda propuesta que contradiga el texto vigente requiere SC-01. Si además resuelve un conflicto
  nuevo entre manuales, requiere un DT-01 (CLAUDE.md §1); en la sección 10 se listan las DT sugeridas.
- Los cambios se aplican en orden de precedencia: primero MC, luego MD y al final MP y MBD (MD 1.5).

## 3. Índice de propuestas

| Manual | Propuestas | Alta | Media | Baja |
|---|---|---|---|---|
| MC (BBB-CA-001) | 11 | 4 | 5 | 2 |
| MD (BBB-MD-002) | 11 | 4 | 5 | 2 |
| MP (BBB-MP-003) | 17 | 2 | 12 | 3 |
| MBD (BBB-BD-004) | 19 | 7 | 8 | 4 |
| **Total** | **58** | **17** | **30** | **11** |

| ID | Origen | Manual y sección | Tema | Prioridad | DT |
|---|---|---|---|---|---|
| PM-MC-01 | MC/AM-01 | MC 1.8, 2.3, 3.3, 7.2, 8.4, 9, Anexo B (7) | Segregación de funciones con integrante único | Alta | DT-018 |
| PM-MC-02 | MC/AM-02, MC-24 | MC 5.1, 5.1.2, 5.2, 5.4, 5.5.8, Fig. 6, 5.5.13, 5.5.14, 6.4, 6.6 | Biblioteca documental en Git | Alta | DT-018 (ampliar) |
| PM-MC-03 | MC/AM-03 | MC 5.5.1, 5.5.2–5.5.6, 5.5.14 | Registros técnicos en Markdown | Alta | Nueva |
| PM-MC-04 | MC/AM-04, MC-15 | MC 5.5.12 Tabla 14 | Scripts completos en formatos DDL | Media | — |
| PM-MC-05 | MC/AM-05 | MC 7.2 Tabla 19 | Hitos de auditoría en ciclo iterativo | Alta | DT-018 |
| PM-MC-06 | MC/AM-06, MC-13 | MC 5.5.8 | Registros de evento por folio y fecha | Media | Nueva |
| PM-MC-07 | MC/AM-07 | MC 3.4, 5.2, 5.3, Tabla 18 | Satisfacción y reuniones con integrante único | Media | DT-018 |
| PM-MC-08 | MC-18 | MC 7.3 | Revisión por la dirección en el EI-01 | Media | DT-018 |
| PM-MC-09 | MC/AM-08, MC-20 | MC 6.4 Tabla 17 | Control de dependencias de terceros | Media | DT-002, DT-019 |
| PM-MC-10 | MC-13 | MC 5.5.8 | Documentos externos del cliente | Baja | DT-023 |
| PM-MC-11 | MC-25 | MC 6.1 | Herramientas de asistencia en la autoría | Baja | DT-018 |
| PM-MD-01 | MD-35 | MD 3.1–3.3, 8.4, 10.2, 10.4, Tablas 9 y 27 | Aprobaciones con desarrollador único | Alta | DT-018 |
| PM-MD-02 | MD-36, MD-27 | MD 1.3, 5.1, 5.5, Tabla 15, 6.1, 10.3, 13.1 | Cliente móvil condicional | Alta | DT-022 |
| PM-MD-03 | MD-37, MD-16 | MD 5.5, Tabla 16 | Almacenamiento de tokens QR | Alta | DT-029, DT-030 |
| PM-MD-04 | MD-38 | MD 2.2, Tabla 8, 9.2, 13.3, 14.2, 14.3 | Biblioteca documental en Git | Alta | DT-018 (ampliar) |
| PM-MD-05 | MD-39 | MD 6, Tablas 14 y 17 | Versión de Laravel | Media | DT-019 |
| PM-MD-06 | MD-40 | MD Anexo K | Errata del formato DA-01 | Baja | — |
| PM-MD-07 | MD-41, MD-33 | MD 4.1 | Replanificación del calendario | Media | DT-018 |
| PM-MD-08 | MD-42, MD-20, MD-34, BD-10 | MD 10.3 | Pasos del CI | Media | DT-015 |
| PM-MD-09 | MP/AM-08 (dependencia) | MD Tabla 17 | Material Symbols | Media | DT-028 |
| PM-MD-10 | MD-24 | MD 8.4, 14.4 | Frontera entre DT-01 y SC-01 | Media | — |
| PM-MD-11 | MD-18, MP-07, MC-22 | MD 10.1 Tabla 21 | Ramas que cubren varios RF | Baja | DT-001 |
| PM-MP-01 | MP/AM-01 | MP 1.2.4.1, 1.1.4 | Orden de cabecera y `declare` | Media | — |
| PM-MP-02 | MP/AM-02 | MP 1.1.3 Tabla 4 | Bloques y etiquetas de la cabecera | Media | — |
| PM-MP-03 | MP-16, MP/AM-11 | MP 1.1.2 Tabla 3 | Archivos que llevan cabecera | Baja | — |
| PM-MP-04 | MP-12 | MP 1.2.3.2 Tabla 10, 1.1.10 Tabla 7 | Verificación de la longitud de línea | Baja | — |
| PM-MP-05 | MP/AM-13 | MP 1.2.7.1 | `pint.json` vigente | Media | DT-020, DT-027 |
| PM-MP-06 | MP/AM-06, MP-10 | MP 1.5.5 | Excepciones al verbo en infinitivo | Media | — |
| PM-MP-07 | MP/AM-07 | MP 1.7.4 | Método `casts()` | Baja | — |
| PM-MP-08 | MP/AM-11, MP-23, MP-24 | MP cap. 1 (nueva 1.8) | Estándar de JavaScript y CSS | Media | DT-028 |
| PM-MP-09 | MP-21 | MP 2.1.5 Tabla 51 | Tintes y sombras de la paleta | Media | DT-028 (enmienda) |
| PM-MP-10 | MP/AM-03 | MP 2.1.5, 2.5.1 Tabla 62, 2.5.3 Tabla 64 | Color activo accesible | Alta | DT-028 |
| PM-MP-11 | MP/AM-04 | MP 2.6.1 Tabla 66, 2.6.3 Tabla 68 | Tipografía en px y pesos | Media | DT-014, DT-028 |
| PM-MP-12 | MP/AM-05, MP-19 | MP 2.6.1 Tabla 66, 2.6.2 | Botón primario compacto | Media | DT-028 |
| PM-MP-13 | MP/AM-08 | MP 2.5.1 Tabla 62, 2.9.1 Tabla 75, B.5.1 | Material Symbols | Media | DT-028 |
| PM-MP-14 | MP/AM-14, MP-20 | MP 2.5.3 Tabla 64, 2.5.4 Tabla 65 | Navegación en web móvil | Media | DT-022 |
| PM-MP-15 | MP/AM-09 | MP 3.2.2, 3.2.3, 3.2.5 | Ramas y aprobación de PR | Alta | DT-001, DT-018 |
| PM-MP-16 | MP/AM-10 | MP 3.1.3, 3.4.2, 3.4.5, 3.5.2, 3.5.3 | Fragmentos de configuración vigentes | Media | DT-002, 003, 005, 015, 017 |
| PM-MP-17 | MP/AM-12, MP-27 | MP 3.3.2 Tabla 85 | Autenticación con Sanctum | Media | DT-007 |
| PM-BD-01 | BD-25 | MBD 3.4.5 p2, 5.7.6, 6.7.3 r3 | Política de cascadas | Media | DT-009 |
| PM-BD-02 | BD-26 | MBD Tabla 27 | `usuarios → logs_auditoria` RESTRICT | Alta | DT-010 |
| PM-BD-03 | BD-27 | MBD 1.5.3, 3.2.2 Tabla 11, 4.8 Tabla 43 | Hash SHA-256 del token QR | Alta | DT-030 |
| PM-BD-04 | BD-28 | MBD 2.2, Tabla 7, Tabla 35 | Vigencia del token en MySQL | Alta | DT-030 |
| PM-BD-05 | BD-29 | MBD 3.3.5 Tabla 22, 3.3.7 Tabla 24, 5.4.7, 6.4.3 | COMMENT en triggers y vistas | Media | Nueva |
| PM-BD-06 | BD-30 | MBD Tabla 7, Tabla 26 p7, 3.4.5 p3 | Triggers o lógica de aplicación | Media | Nueva |
| PM-BD-07 | BD-31, BD-24 | MBD 1.5.2, 5.3.8, 6.3.3 Tabla 83 r7 | Excepciones al prefijo `id_institucion` | Media | DT-026 |
| PM-BD-08 | BD-32, BD-14 | MBD 4.5, 4.7–4.9, 4.12, 4.13, Tablas 7 y 27, Figs. 1–2 | `id_institucion` en el diccionario | Alta | DT-008 |
| PM-BD-09 | BD-33 | MBD 4.4 Tabla 39, 4.11 Tabla 46 | NULL para superadmin y eventos de plataforma | Alta | DT-011, DT-024, DT-026 |
| PM-BD-10 | BD-34 | MBD 3.3.3 Tabla 16 | Ejemplo `nombre_area` | Baja | — |
| PM-BD-11 | BD-35 | MBD 5.0.2 Tabla 51 r1, 6.0.1 Tabla 72 r2 y r5 | Nomenclatura y fechas de los DDL | Alta | Nueva |
| PM-BD-12 | BD-36 | MBD 5.0.1 Tabla 50, 5.0.2, 5.2.1, 5.7, 6.0.1 | Errores editoriales de los formatos DDL | Media | — |
| PM-BD-13 | BD-13 (firmas) | MBD 5.0.2 Tabla 51 r3 | Firmas DDL con desarrollador único | Alta | DT-018 |
| PM-BD-14 | BD-37, BD-15 | MBD 2.2 | Política de memoria de Redis | Media | DT-031 |
| PM-BD-15 | BD-38, BD-12 | MBD 5.0.2 r8 y reglas DDL_CHANGE | Evidencia del cambio estructural | Media | Nueva |
| PM-BD-16 | BD-39 | MBD 1.5.5, 3.4.5 p1, Tabla 26 p5, Tabla 7 | Transacciones y cliente móvil | Media | DT-022 |
| PM-BD-17 | BD-21 | MBD 3.3.1 p10 | COMMENT en tablas de infraestructura | Baja | DT-016 |
| PM-BD-18 | BD-11 | MBD 3.3.8 Tabla 25 | Patrones de nombre de migraciones | Baja | — |
| PM-BD-19 | BD-19 | MBD 3.4.1 Tabla 26 p4 | Coherencia de tenant hijo/padre | Baja | DT-008 |

Cobertura de los AJUSTE-MANUAL: MC/AM-01…08 → PM-MC-01…07 y PM-MC-09; MD-35…42 → PM-MD-01…08;
MP/AM-01…14 → PM-MP-01, 02, 05…08, 10…17 (AM-08 también en PM-MD-09); BD-25…39 → PM-BD-01…12 y
PM-BD-14…16. No queda ningún AJUSTE-MANUAL sin propuesta.

---

## 4. Manual de Calidad BBB-CA-001

### PM-MC-01 · Segregación de funciones con integrante único

| Campo | Contenido |
|---|---|
| Origen | MC/AM-01 (relacionados: MD-35, MP/AM-09, BD-13) |
| Sección | MC 1.8 (IEEE 730), 2.3, 3.3, 7.2, 8.4, 9 y Anexo B campo (7) |
| Texto actual | 8.4: «Segregación de funciones: quien solicita un cambio no puede ser quien lo aprueba.» Anexo B (7): «Nombre y firma de la autoridad que autoriza el cambio (distinta del solicitante).» 7.2: «la revisión de un documento la efectúa un integrante distinto de su autor.» |
| Problema | Con un solo desarrollador (DT-018), nadie distinto del solicitante puede aprobar un SC-01. Además, DT-018 usa el formato DT-01 del MD (precedencia menor) para adaptar requisitos del MC: la adaptación debe quedar en el propio MC |
| DT relacionada | DT-018 (aprobada) |
| Prioridad | Alta: condiciona todos los SC-01, incluidos los de esta propuesta, y las propuestas PM-MD-01, PM-MP-15 y PM-BD-13 |

**Redacción propuesta**

- 2.3, nuevo párrafo después de «Exclusión justificada»: «*Adaptación por integrante único.* Cuando
  el proyecto lo ejecute un solo integrante, este asume los roles de Líder de proyecto, autor,
  responsable de documentación y aprobador. La independencia que exigen los apartados 1.8, 7.2 y 8.4
  se asegura con un revisor independiente, persona o agente automatizado de aseguramiento de calidad,
  que no haya participado en la elaboración del cambio. Las actas AR-01 se sustituyen por los registros
  de planeación y cierre de iteración del Manual de Desarrollo (PI-01 y EI-01). Esta adaptación no
  excluye ningún requisito de la norma: modifica quién lo ejecuta.»
- 3.3, nueva viñeta: «Revisor independiente (modo de integrante único): revisa cada entregable y cada
  solicitud de cambio antes de su aprobación y registra un dictamen (RED-01 o LV-01). No aprueba ni
  declara terminado ningún entregable.»
- 8.4, viñeta de segregación: «Segregación de funciones: quien solicita un cambio no puede ser quien
  lo aprueba. En modo de integrante único (2.3), la segregación se cumple cuando el revisor
  independiente emite su dictamen antes de la aprobación y este se adjunta al SC-01; aprueba el
  responsable del proyecto [y la autoridad de aprobación ratifica los cambios al cierre de la etapa].»
- 7.2, segunda oración: «Para asegurar objetividad, la revisión de un documento la efectúa un
  integrante distinto de su autor o, en modo de integrante único, el revisor independiente definido en
  3.3.»
- 1.8, viñeta IEEE 730, última oración: «De este estándar proviene el principio, aplicado en todo el
  manual, de que el revisor de un entregable debe ser distinto de su autor (otro integrante o el revisor
  independiente del apartado 3.3).»
- 9, viñeta «Revisor»: «Revisor (o revisor independiente en modo de integrante único): confirmar que
  el documento cumple el manual antes de su aprobación.»
- Anexo B, instructivo (7): «Nombre y firma de la autoridad que autoriza el cambio, distinta del
  solicitante; en modo de integrante único, se anota además la referencia al dictamen del revisor
  independiente.»

### PM-MC-02 · Biblioteca documental en el repositorio Git

| Campo | Contenido |
|---|---|
| Origen | MC/AM-02, MC-24 (relacionado: MD-38) |
| Sección | MC 5.1, 5.1.2, 5.2, 5.4 Tabla 7, 5.5.8 (estructura, organización, vigencia y Figura 6), 5.5.13, 5.5.14, 6.4 Tabla 17, 6.6 |
| Texto actual | 5.5.8: «La documentación se almacena en un sitio de SharePoint compartido del equipo. Cada documento reside únicamente en su carpeta correspondiente para conservar una sola fuente de verdad.» Tabla 17: «SharePoint / Microsoft 365 — Resguardo y control documental» |
| Problema | SharePoint no se usa (desarrollador único). Git cumple mejor la fuente única de verdad, el historial y la comparación de versiones. Hoy ninguna DT aprobada lo formaliza: DT-018 solo menciona AR-01, JD-01, SP-01 y EVS-01 |
| DT relacionada | DT-018: ampliar o crear una DT nueva |
| Prioridad | Alta: sin ella, todo el control documental actual incumple 5.5.8. Requiere corregir antes MC-03 (repositorio privado) |

**Redacción propuesta**

- 5.5.8, «Estructura de carpetas y ubicación»: «La documentación se almacena en una biblioteca
  documental única y controlada: el sitio de SharePoint del equipo o la carpeta `docs/` del
  repositorio Git **privado** del proyecto. La biblioteca elegida se declara en el plan del proyecto.
  Cada documento reside únicamente en su carpeta para conservar una sola fuente de verdad.»
- 5.5.8, «Organización de la biblioteca», párrafo final nuevo: «Cuando la biblioteca es el
  repositorio Git, el historial de commits y las etiquetas sustituyen a la carpeta «Versiones»; la
  carpeta `docs/formatos/<REF>/` contiene «Instrucciones» y «Vacíos»; los formatos llenados residen en
  la carpeta de su proceso (por ejemplo, `docs/decisiones/`, `docs/auditoria/`, `docs/iteraciones/`), y
  la plantilla de trabajo reside en `docs/formatos/plantilla/`. Los permisos de la biblioteca son los de
  los colaboradores del repositorio y del docente.»
- 5.5.8, «Vigencia», última oración: «…y se conserva solo como referencia histórica en la carpeta de
  versiones o en el historial del repositorio.»
- Figura 6: agregar una segunda variante con la estructura de `docs/` del repositorio.
- 5.1, 5.1.2, 5.2, 5.4 (Tabla 7), 5.5.13 («Elaboración y resguardo»), 5.5.14 («Control de la
  plantilla») y 6.6: sustituir «SharePoint» o «biblioteca de SharePoint» por «la biblioteca documental
  (5.5.8)».
- 6.4, Tabla 17, primera fila: «Biblioteca documental (SharePoint / Microsoft 365 o repositorio Git
  privado) | Resguardo y control documental | Disponibilidad y respaldo; permisos por integrante».

### PM-MC-03 · Registros técnicos en Markdown

| Campo | Contenido |
|---|---|
| Origen | MC/AM-03 (relacionados: MC-07 MAYOR, MC-23) |
| Sección | MC 5.5.1, 5.5.2–5.5.6 y 5.5.14 («Obligatoriedad y uso») |
| Texto actual | 5.5.14: «Todo documento nuevo del proyecto —manuales, reportes y entregables— se crea a partir de la plantilla…» 5.5.2: «Ningún documento oficial debe distribuirse sin el logotipo en el encabezado.» |
| Problema | El formato Word no sirve para los registros técnicos que se versionan con el código (CU, CP, RT, DDL, DT, DG, RED): no se pueden comparar versiones ni revisar en un PR. El manual no distingue entre registros técnicos de trabajo y entregables |
| DT relacionada | Nueva DT sugerida (sección 10) |
| Prioridad | Alta: define cómo se cierra MC-07 (MAYOR) |

**Redacción propuesta** (nuevo párrafo en 5.5.14, después de «Obligatoriedad y uso»)

«*Registros técnicos en texto plano.* Los registros técnicos que evolucionan junto con el código
(casos de uso CU-01, casos de prueba CP-01, matriz de trazabilidad RT-01, formatos DDL, decisiones
técnicas DT-01, registros de diagrama DG-01 y revisiones RED-01) pueden elaborarse en Markdown dentro de
la biblioteca documental (5.5.8). Cada registro lleva un encabezado mínimo con código REF, nombre,
versión, fecha (DD/MM/AAAA), autor y estado, y una tabla de control de versiones, y cumple los apartados
5.5.7, 5.5.8 y 5.5.12. Mientras no se entreguen al cliente, quedan exceptuados de los apartados 5.5.2 a
5.5.6 y del uso de la plantilla. Los manuales, los reportes y los entregables al cliente (informes de
auditoría, cierre de etapa y liberación) se generan siempre con la plantilla institucional; un
registro técnico que forme parte de una entrega se exporta a la plantilla antes de entregarse. [La
relación de entregables de cada etapa se fija en el plan del proyecto.]»

Consecuencia en Tabla 18: el indicador «Documentos conformes» se calcula con el criterio que
corresponda a cada tipo (plantilla para entregables, encabezado mínimo para registros técnicos).

### PM-MC-04 · Scripts completos en los formatos DDL

| Campo | Contenido |
|---|---|
| Origen | MC/AM-04, MC-15 |
| Sección | MC 5.5.12, Tabla 14 y «Reglas de contenido» |
| Texto actual | Tabla 14: «Extensión máxima: 30 líneas por bloque y 90 caracteres por línea». Reglas de contenido: «Cuando el código exceda las 30 líneas se incluye solo la parte relevante y el resto se referencia al repositorio de GitHub…» |
| Problema | El MC ya permite citar el repositorio, pero los formatos DDL del MBD (5.0.2 Tabla 51 r4, secciones de script y rollback) exigen el script completo dentro del formato. El autor no sabe cuál de las dos reglas cumplir |
| DT relacionada | — (cambio coordinado con PM-BD-12) |
| Prioridad | Media |

**Redacción propuesta** (al final de «Reglas de contenido»)

«Los formatos que por norma exigen el script completo (formatos DDL del Manual de Base de Datos)
cumplen esa exigencia de una de dos maneras: (a) dividen el script en bloques consecutivos de hasta
30 líneas, cada uno precedido de su párrafo explicativo, o (b) incluyen el fragmento esencial y citan
el archivo de migración versionado con su ruta y el identificador del commit, que se considera parte
del formato. En ambos casos se mantiene el límite de 90 caracteres por línea.»

### PM-MC-05 · Hitos de auditoría en un ciclo iterativo

| Campo | Contenido |
|---|---|
| Origen | MC/AM-05 (relacionados: MC-05 MAYOR, MD-41) |
| Sección | MC 7.2, Tabla 19 |
| Texto actual | «A-01 Al cerrar la etapa de requisitos… A-02 Al cerrar la etapa de diseño… A-03 Al cerrar la etapa de desarrollo… A-04 Previo a la entrega final» |
| Problema | Los hitos suponen etapas en cascada. Con RUP (MD cap. 2), requisitos, diseño y construcción se traslapan por bloque, así que no hay un «cierre de la etapa de diseño» único. Eso permitió rebasar A-01 sin auditarlo |
| DT relacionada | DT-018 (replanificación por bloques) |
| Prioridad | Alta |

**Redacción propuesta** (párrafo nuevo después de la Tabla 19)

«En un ciclo de vida iterativo (Manual de Desarrollo, cap. 2), los hitos se interpretan así: A-01, al
aprobarse la línea base de requisitos y antes de la primera iteración de construcción; A-02, al cerrar
la iteración en la que concluye el diseño de cada bloque funcional (arquitectura, datos e interfaz);
A-03, al cerrar la última iteración de construcción de cada bloque; A-04, antes de la liberación. Las
revisiones independientes de cada integración (RED-01) son insumo de la auditoría del hito y no la
sustituyen: la LV-01 sigue siendo obligatoria. El calendario de auditorías se fija en el plan del
proyecto y se actualiza con cada replanificación.»

### PM-MC-06 · Registros de evento identificados por folio y fecha

| Campo | Contenido |
|---|---|
| Origen | MC/AM-06, MC-13 (parte de RED-01) |
| Sección | MC 5.5.8, «Nomenclatura de archivos» |
| Texto actual | «Los archivos se nombran de forma descriptiva… incluyendo el nombre de la empresa, el tipo de documento y la versión, por ejemplo: BBB_ManualCalidad_v1.0.docx.» |
| Problema | Los registros de un evento puntual (RED-01, LV-01, NC-01, informes de auditoría) no evolucionan: corregirlos crea un registro nuevo. Versionarlos confunde. El repositorio ya los nombra con fecha (`BBB_RED01_DocsI0Inicializacion_20261025.md`, `BBB_AuditoriaIntegral_MC_20261026.md`) |
| DT relacionada | Nueva DT sugerida (compartida con PM-BD-11) |
| Prioridad | Media |

**Redacción propuesta** (después del ejemplo de nomenclatura)

«Los registros de un evento puntual (revisiones RED-01, listas de verificación LV-01, informes de
auditoría y reportes de no conformidad NC-01) se nombran `BBB_<REF>_<Objeto>_<AAAAMMDD>.<ext>` y no
se versionan: una corrección o re-verificación genera un registro nuevo que cita al anterior. La fecha
del nombre usa AAAAMMDD para ordenar los archivos; dentro del documento rige el formato DD/MM/AAAA. Los
demás documentos conservan el sufijo `_vX.Y`.»

### PM-MC-07 · Satisfacción del cliente, reuniones, competencia e inducción con integrante único

| Campo | Contenido |
|---|---|
| Origen | MC/AM-07 |
| Sección | MC 3.4 (viñetas 3 y 4), 5.2, 5.3, 7.1 Tabla 18 («Satisfacción del cliente», «Reuniones con acta») |
| Texto actual | 3.4: «la satisfacción del cliente-docente se mide al cierre de cada etapa del proyecto mediante una valoración en escala 1–5». 5.3: «La realización de esta inducción se deja asentada en un acta de reunión (AR-01)» |
| Problema | Con DT-018 el docente no participa en el proceso diario y no hay reuniones formales ni integrantes que inducir, así que los indicadores no se pueden medir y quedan como incumplimiento permanente |
| DT relacionada | DT-018 |
| Prioridad | Media |

**Redacción propuesta**

- 3.4, viñeta «Medición de la satisfacción»: «…con meta ≥ 4, conforme al indicador de la Tabla 18. En
  modo de integrante único (2.3), la valoración la registra el responsable del proyecto en el EI-01 de
  cierre de etapa y, cuando sea posible, el docente en la entrega; junto al resultado se anota quién
  valoró.»
- 3.4, viñeta «Comunicación con el cliente»: «…registrándose en el acta AR-01 o, en modo de integrante
  único, en el EI-01 de la etapa…»
- 5.2 y 5.3, oración final: «En modo de integrante único, la inducción y la evaluación de competencia
  entre pares no aplican y se declaran como N-A en el plan del proyecto.»
- Tabla 18, «Satisfacción del cliente», fórmula: «Valoración del cliente al cierre de etapa (escala
  1–5): docente o, en modo de integrante único, responsable del proyecto en el EI-01».
- Tabla 18, «Reuniones con acta», meta: «100 %; N-A cuando no se celebran reuniones formales (modo de
  integrante único)».

### PM-MC-08 · Revisión por la dirección registrada en el EI-01

| Campo | Contenido |
|---|---|
| Origen | MC-18 (MENOR) |
| Sección | MC 7.3 |
| Texto actual | «Salidas: decisiones sobre oportunidades de mejora, sobre las necesidades de cambio en el SGC y sobre las necesidades de recursos, registradas en el acta de reunión correspondiente (AR-01).» |
| Problema | DT-018 omite AR-01, pero el manual no dice dónde se registra entonces la revisión por la dirección, y los EI-01 no recogen sus ocho entradas |
| DT relacionada | DT-018 |
| Prioridad | Media |

**Redacción propuesta**

«…registradas en el acta de reunión correspondiente (AR-01) o, en modo de integrante único (2.3), en
un apartado «Revisión por la dirección» del EI-01 que cierra la etapa, el cual contiene las ocho
entradas y las tres salidas de este apartado y los valores de los indicadores de la Tabla 18.»

### PM-MC-09 · Control de dependencias de terceros

| Campo | Contenido |
|---|---|
| Origen | MC/AM-08, MC-20 (parte de licencias) |
| Sección | MC 6.4, Tabla 17, filas «GitHub» y «Librerías y paquetes de terceros» |
| Texto actual | «Librerías y paquetes de terceros — Componentes del software — Versiones estables y con licencia compatible»; «GitHub — Repositorio privado; revisión de cambios por pares» |
| Problema | El sistema aplica un control más fuerte y verificable que el manual: archivo de bloqueo, imágenes con etiqueta y *digest*, `composer audit` en CI y licencias en `CREDITS.md` (DT-002, DT-019, DT-029). La «revisión por pares» no existe con integrante único |
| DT relacionada | DT-002, DT-019, DT-029 |
| Prioridad | Media. Depende de PM-MD-08, que agrega el paso de auditoría al CI |

**Redacción propuesta**

- Fila «Librerías y paquetes de terceros», criterio: «Versiones estables fijadas por archivo de
  bloqueo (y por etiqueta y *digest* en imágenes de contenedor); auditoría automática de
  vulnerabilidades en cada integración continua; licencia compatible registrada en el archivo de
  créditos del repositorio antes de incorporarla.»
- Fila «GitHub», criterio: «Repositorio privado; revisión de cambios por pares o por el revisor
  independiente (3.3).»

### PM-MC-10 · Documentos externos del cliente

| Campo | Contenido |
|---|---|
| Origen | MC-13 (MENOR, parte del SRS) |
| Sección | MC 5.5.8 |
| Texto actual | «…incluyendo el nombre de la empresa, el tipo de documento y la versión…» |
| Problema | El manual no prevé documentos recibidos del cliente. `SRS_Sistema_Prestamo_Equipo_Institucional_v2.pdf` no lleva el prefijo BBB y su nombre dice «v2» aunque el contenido es la versión 1.0 (DT-023) |
| DT relacionada | DT-023 |
| Prioridad | Baja |

**Redacción propuesta** (párrafo nuevo en 5.5.8)

«*Documentos externos.* Los documentos recibidos del cliente o de terceros conservan su contenido sin
modificar, se guardan como `BBB_<Nombre>_vX.Y.<ext>` con la versión que declara el propio documento y
se identifican como externos, con su origen y fecha de recepción, en el documento que los cita. Se
tratan como propiedad del cliente (6.6).»

### PM-MC-11 · Herramientas de asistencia en la autoría

| Campo | Contenido |
|---|---|
| Origen | MC-25 (OBSERVACIÓN) |
| Sección | MC 6.1 (criterios de revisión) |
| Texto actual | Criterio de revisión: revisión por alguien distinto del autor, con resultado registrado |
| Problema | El proyecto se desarrolla con agentes de IA (CLAUDE.md §9) y hay commits con marcas de coautoría automática, pero el manual no dice cómo se declara la asistencia ni quién responde del entregable |
| DT relacionada | DT-018 |
| Prioridad | Baja |

**Redacción propuesta** (nueva viñeta en los criterios de revisión de 6.1)

«Cuando en la elaboración de un entregable intervengan herramientas automatizadas de generación
(incluidos los asistentes de inteligencia artificial), el registro de cierre de la etapa declara cuáles
se usaron y en qué artefactos. La autoría y la responsabilidad corresponden al integrante que integra
el cambio, y la revisión independiente no puede hacerla la misma instancia que generó el artefacto.»

---

## 5. Manual de Desarrollo BBB-MD-002

### PM-MD-01 · Aprobaciones con desarrollador único

| Campo | Contenido |
|---|---|
| Origen | MD-35 |
| Sección | MD 3.1–3.3, Tabla 9, 8.4 (paso 4), 10.2 (viñeta 2), 10.4 Tabla 22 («Aprobación»), 14.3 Tabla 27 |
| Texto actual | 3.3: «Ningún autor puede aprobar por sí solo su propio cambio.»; 10.2: «Se requiere al menos una aprobación de un integrante distinto del autor; los cambios críticos requieren además la aprobación de David.»; Tabla 22: «Incremento aceptado por David y registrado en EI-01.» |
| Problema | Los roles y aprobaciones están asignados a cinco personas. Con DT-018 Raúl aprueba, y la revisión independiente la hace un agente que no es autor; el manual no lo prevé |
| DT relacionada | DT-018 |
| Prioridad | Alta. Depende de PM-MC-01 |

**Redacción propuesta**

- 3.3, párrafo nuevo al final: «*Modo de desarrollador único.* Cuando el proyecto lo ejecute un solo
  integrante (Manual de Calidad 2.3), este asume los roles de la Tabla 8 y las aprobaciones asignadas a
  David. La revisión que exige este apartado la realiza un revisor distinto del autor, persona o agente
  automatizado de revisión que no haya elaborado el cambio, y se registra en RED-01 antes de la
  aprobación. El responsable aprueba con el dictamen a la vista; un dictamen de rechazo impide integrar
  el cambio mientras no se atienda.»
- 8.4 paso 4, 10.2 viñeta 2, Tabla 22 fila «Aprobación» y Tabla 27 columna «Responsable de verificar»:
  sustituir «David» por «el líder del proyecto (en modo de desarrollador único, el responsable tras el
  RED-01)».
- 10.2, viñeta 2: «Se requiere al menos una aprobación de un revisor distinto del autor (otro
  integrante o, en modo de desarrollador único, el revisor independiente); los cambios críticos
  requieren además la aprobación del líder del proyecto.»
- Tabla 9, nota al pie: «En modo de desarrollador único todas las columnas R y A recaen en el
  responsable, salvo la revisión independiente (3.3).»

### PM-MD-02 · Cliente móvil Flutter como componente condicional

| Campo | Contenido |
|---|---|
| Origen | MD-36, MD-27 (relacionados: BD-39, MP/AM-14, MP/AM-10) |
| Sección | MD 1.3, 2.2, 5.1, 5.5 (segundo párrafo), Tabla 15, 6.1, 10.3 (pasos 2 y 3), 13.1 (viñeta 2) |
| Texto actual | 1.3: «El manual aplica al backend Laravel, la aplicación móvil Flutter, las vistas web Blade…»; 5.5: «Cuando no exista conexión, la aplicación guardará localmente el evento de escaneo…»; 10.3: «2. Ejecutar Laravel Pint y dart format… 3. Ejecutar PHPStan nivel 6 y Dart Analyzer con flutter_lints.» |
| Problema | DT-022 (aprobada) deja Flutter fuera del alcance. El manual lo trata como obligatorio, así que cada auditoría reabre la desviación. La contingencia sin conexión de la web sigue pendiente (DT-022 punto 7) |
| DT relacionada | DT-022 |
| Prioridad | Alta |

**Redacción propuesta**

- 1.3: «El manual aplica al backend Laravel, las vistas web Blade, la base de datos MySQL, los servicios
  Redis, la infraestructura Docker y los artefactos técnicos asociados. Aplica además a la aplicación
  móvil Flutter cuando la liberación la incluya en su alcance, según la decisión registrada en DT-01.»
- 5.1, segunda oración: «Laravel concentrará la API REST y la aplicación web; un cliente móvil,
  cuando forme parte del alcance, consumirá la API para las funciones móviles.»
- Tabla 15, título: «Tecnologías de la aplicación móvil (obligatorias solo cuando la liberación
  incluya cliente móvil)».
- 6.1: «…y Flutter conservará pubspec.lock cuando exista cliente móvil.»
- 10.3, pasos 2 y 3: «2. Ejecutar Laravel Pint en modo de verificación (y dart format si existe
  cliente móvil). 3. Ejecutar PHPStan nivel 6 (y Dart Analyzer con flutter_lints si existe cliente
  móvil).»
- 13.1, viñeta 2: «…cookies seguras cuando correspondan; en un cliente móvil, tokens almacenados con
  los mecanismos del sistema operativo.»
- 5.5, segundo párrafo: «*Operación sin conexión.* Cuando exista cliente móvil, la aplicación
  guardará localmente el evento de escaneo… [texto vigente]. En una liberación solo web, la
  contingencia sin conexión se resuelve con el mecanismo que apruebe un DT-01 [por ejemplo, captura
  manual diferida del folio con registro firmado y conciliación posterior], y RF-038 y RNF-003 se
  trazan a esa decisión.»
- 2.2, viñeta 1: «…operación sin conexión (en el cliente móvil o en la contingencia web que se defina)…»

### PM-MD-03 · Almacenamiento de los tokens QR

| Campo | Contenido |
|---|---|
| Origen | MD-37, MD-16 (relacionados: BD-27, BD-28) |
| Sección | MD 5.5 (primer párrafo), Tabla 16 (fila Redis), 13.1 (viñeta 4) |
| Texto actual | 5.5: «Los códigos QR usarán tokens aleatorios de un solo uso almacenados en Redis de forma segura y con TTL predeterminado de 45 segundos…»; Tabla 16: «Redis 7.x — Caché, colas, bloqueo breve y tokens QR.» |
| Problema | MD exige Redis y el MBD define la tabla `tokens_qr`: dos fuentes. El sistema guarda el *hash* SHA-256 en MySQL y valida con bloqueo de fila. Es más auditable y una fuga de la tabla no expone códigos vigentes (DT-030) |
| DT relacionada | DT-029, DT-030 (propuestas) |
| Prioridad | Alta. Se aplica junto con PM-BD-03 y PM-BD-04 |

**Redacción propuesta**

- 5.5, primer párrafo: «Los códigos QR usarán tokens aleatorios de un solo uso con TTL
  predeterminado de 45 segundos, configurable entre 30 y 60 segundos. El servidor persiste solo el
  *hash* irreversible del token (SHA-256), junto con su vigencia, su uso y la operación asociada, en la
  tabla `tokens_qr` (Manual de Base de Datos 4.8), que es la fuente de verdad; la vigencia se verifica
  en el servidor bajo bloqueo de fila al validar. Redis puede emplearse como caché o candado
  complementario, nunca como único registro. El valor en claro solo viaja en la respuesta que lo
  entrega, marcada para no almacenarse en caché.»
- Tabla 16, fila Redis, uso: «Caché, colas, sesiones y bloqueos breves (reservas y validación de QR).»
- 13.1, viñeta 4: «Tokens QR de un solo uso, almacenados como *hash*, con expiración, asociación a la
  operación y registro del intento de validación.»

### PM-MD-04 · Biblioteca documental en el repositorio Git

| Campo | Contenido |
|---|---|
| Origen | MD-38 |
| Sección | MD «Control de versiones» (párrafo inicial), 2.2 (viñeta 4), Tabla 8 (fila «Configuración y cambios»), 9.2 (viñeta 4), 13.3 Tabla 26 (fila «Documentos»), 14.2, 14.3 Tabla 27 |
| Texto actual | 14.2: «SharePoint será la fuente de verdad para documentos. Cada manual tendrá las carpetas Manual, Versiones y Formatos…»; 9.2: «El archivo editable y la exportación PDF o PNG se conservarán juntos en SharePoint.» |
| Problema | Igual que PM-MC-02: SharePoint no se usa |
| DT relacionada | DT-018 (ampliar) o la DT nueva de PM-MC-02 |
| Prioridad | Alta. Depende de PM-MC-02 |

**Redacción propuesta**

- 14.2, título «Biblioteca documental»: «La biblioteca documental definida en el Manual de Calidad 5.5.8
  (SharePoint o la carpeta `docs/` del repositorio Git privado) será la fuente de verdad para
  documentos. El nombre seguirá la convención `BBB_NombreDocumento_vX.Y.ext` y la versión vigente se
  identificará en el control de versiones. Cuando la biblioteca sea el repositorio, el historial de Git
  y las etiquetas sustituyen a la carpeta Versiones.»
- 9.2 viñeta 4, 13.3 Tabla 26, 2.2, Tabla 8 y párrafo de «Control de versiones»: sustituir
  «SharePoint» por «la biblioteca documental (14.2)».
- Tabla 27, ubicaciones con biblioteca en Git: PI-01 y EI-01 → `docs/iteraciones/<iteración>/`;
  capturas y registros funcionales → `docs/iteraciones/<iteración>/evidencias/`; RT-01 →
  `docs/trazabilidad/`; defectos → `docs/pruebas/`.

### PM-MD-05 · Versión de Laravel

| Campo | Contenido |
|---|---|
| Origen | MD-39 |
| Sección | MD 6 (párrafo inicial), Tabla 14 (Laravel, Sanctum, Blade), Tabla 17 (Laravel Pint) |
| Texto actual | Tabla 14: «Laravel 11.x — Framework… — Obligatoria»; «Laravel Sanctum 4.x compatible con Laravel 11»; Tabla 17: «Laravel Pint — Compatible con Laravel 11» |
| Problema | La rama 11.x dejó de recibir parches de seguridad. DT-019 adoptó 12.x, y el manual obliga a una versión vulnerable |
| DT relacionada | DT-019 (aprobada) |
| Prioridad | Media |

**Redacción propuesta**

- 6, párrafo inicial, oración nueva: «Una versión obligatoria puede sustituirse por una versión mayor
  posterior cuando la obligatoria pierda el soporte de seguridad durante el proyecto, con DT-01 y
  pruebas de regresión.»
- Tabla 14: «Laravel | 11.x o versión mayor posterior con soporte de seguridad vigente, registrada en
  DT-01 | … | Obligatoria»; «Laravel Sanctum | 4.x compatible con la versión de Laravel adoptada»;
  «Blade | Incluido con Laravel».
- Tabla 17: «Laravel Pint | Compatible con la versión de Laravel adoptada».

### PM-MD-06 · Errata del formato DA-01

| Campo | Contenido |
|---|---|
| Origen | MD-40 |
| Sección | MD Anexo K |
| Texto actual | «Tabla 39. Formato de Registro de Liberación DA-01.» con el encabezado «REGISTRO DE LIBERACIÓN» |
| Problema | El título y el encabezado copian los del LI-01 (Anexo J, también «Tabla 39») |
| DT relacionada | — |
| Prioridad | Baja |

**Redacción propuesta:** título «Tabla 40. Formato de Documento de Análisis DA-01»; encabezado del
formato «DOCUMENTO DE ANÁLISIS»; renumerar las tablas siguientes y el índice de tablas.

### PM-MD-07 · Replanificación del calendario

| Campo | Contenido |
|---|---|
| Origen | MD-41, MD-33 |
| Sección | MD 4.1 y Tabla 10 |
| Texto actual | «Las iteraciones de construcción duran siete días y comienzan en martes… su distribución por iteración organiza el trabajo, pero no reduce el alcance final.» |
| Problema | El calendario es fijo y no prevé replanificar. El plan vigente ya agrupa dos iteraciones del MD por semana (DT-018), sin que el manual lo admita |
| DT relacionada | DT-018 |
| Prioridad | Media |

**Redacción propuesta** (párrafo nuevo después de la Tabla 10)

«El calendario de la Tabla 10 puede replanificarse (agrupar, dividir o desplazar iteraciones) mediante
DT-01 o SC-01 aprobado, sin reducir el alcance ni mover la fecha de Transición; el plan vigente se
identifica en el PI-01 de cada iteración. La reducción de alcance solo procede mediante SC-01 (8.4),
con análisis de impacto y una nueva línea base.»

### PM-MD-08 · Pasos de la integración continua

| Campo | Contenido |
|---|---|
| Origen | MD-42; también MD-20 (rollback), MD-34 (acciones por SHA) y BD-10 (reversibilidad) |
| Sección | MD 10.3 |
| Texto actual | «1. Instalar dependencias con los archivos de bloqueo. … 5. Generar reporte de cobertura y fallar si el componente crítico queda por debajo del 80 %. 6. Construir las imágenes…» |
| Problema | 13.1 exige «dependencias revisadas» y 10.4 «migraciones reversibles y probadas», pero ningún paso del CI lo verifica. El CI del proyecto ya ejecuta `composer audit` (CLAUDE.md §6) |
| DT relacionada | DT-015 |
| Prioridad | Media. Se coordina con PM-MC-09, PM-MP-16 (Tabla 90) y MBD 8.6 |

**Redacción propuesta**

«1. Instalar dependencias con los archivos de bloqueo. 2. Ejecutar Laravel Pint en modo de
verificación (y dart format si existe cliente móvil). 3. Ejecutar PHPStan nivel 6 (y Dart Analyzer si
existe cliente móvil). 4. Ejecutar las pruebas unitarias, feature e integración disponibles.
5. Generar el reporte de cobertura y fallar si el componente crítico queda por debajo del 80 %.
6. Auditar las dependencias (`composer audit`) y fallar ante cualquier aviso de seguridad sin
mitigación registrada en DT-01. 7. Verificar la reversibilidad de las migraciones (aplicar, revertir y
volver a aplicar) sobre una base de pruebas efímera. 8. Construir las imágenes o artefactos del
incremento cuando se integre a develop. Las acciones de terceros del flujo de CI se fijan por el
identificador completo de su commit.»

### PM-MD-09 · Iconografía obligatoria: Material Symbols

| Campo | Contenido |
|---|---|
| Origen | Dependencia de MP/AM-08 (DT-028 punto d) |
| Sección | MD Tabla 17, fila «Material Icons» |
| Texto actual | «Material Icons — Apache 2.0 — Iconografía principal. — Obligatoria» |
| Problema | El sistema usa Material Symbols Outlined, sucesor de Material Icons (misma familia, misma licencia; DT-028 aprobada). Como el MD tiene mayor precedencia, cambiar solo el MP no basta |
| DT relacionada | DT-028 |
| Prioridad | Media. Se aplica antes que PM-MP-13 |

**Redacción propuesta:** «Material Icons o Material Symbols | Apache 2.0 | Iconografía principal;
un solo set y un solo estilo por pantalla | Obligatoria».

### PM-MD-10 · Frontera entre DT-01 y SC-01

| Campo | Contenido |
|---|---|
| Origen | MD-24 (MENOR); problema de fondo de MC/AM-01 |
| Sección | MD 8.4 y 14.4 |
| Texto actual | 14.4: «Las decisiones que afecten arquitectura, datos, seguridad, dependencias, despliegue o requisitos no funcionales se documentarán con DT-01.»; 8.4: «1. Registrar la solicitud en SC-01 y describir el requisito afectado.» |
| Problema | El manual no dice cuándo basta un DT-01 y cuándo hace falta además un SC-01. Por eso la retirada de Flutter (DT-022) y la línea base de 58 RF (DT-023) cambiaron el alcance solo con DT |
| DT relacionada | — |
| Prioridad | Media |

**Redacción propuesta** (párrafo nuevo al final de 14.4)

«Cuando una decisión técnica modifique la línea base aprobada (alcance, requisitos o calendario
comprometido) o un documento controlado ya aprobado, el DT-01 registra el análisis técnico y se
acompaña de un SC-01 que lo cita; el SC-01 es el que autoriza el cambio de línea base (Manual de
Calidad 8.4). Un conflicto entre manuales se resuelve por precedencia (1.5) en un DT-01 y, si obliga a
modificar un manual, se propone la corrección del manual mediante SC-01.»

### PM-MD-11 · Ramas que cubren varios requisitos

| Campo | Contenido |
|---|---|
| Origen | MD-18, MP-07, MC-22 (MENOR) |
| Sección | MD 10.1 y Tabla 21 |
| Texto actual | «…cada cambio se desarrollará en una rama feature, fix, hotfix o docs identificada con el requisito o incidencia.» Tabla 21: «feature/RF-XXX-descripcion» |
| Problema | El manual no dice cómo nombrar una rama que implementa varios RF de un bloque. La rama actual lleva el número de RF-011 y contiene RF-022…RF-058 |
| DT relacionada | DT-001 |
| Prioridad | Baja |

**Redacción propuesta** (oración nueva en 10.1)

«Cuando una rama implemente varios RF del mismo bloque, se nombra con el primer RF y una descripción
del conjunto (por ejemplo, `feature/RF-022-gestion-prestamos`) y su pull request enumera todos los RF
cubiertos. No se combinan en una misma rama RF de bloques distintos.»

---

## 6. Manual de Programación BBB-MP-003

### PM-MP-01 · Orden de la cabecera y de `declare(strict_types=1)`

| Campo | Contenido |
|---|---|
| Origen | MP/AM-01 |
| Sección | MP 1.2.4.1 frente a 1.1.4 |
| Texto actual | 1.1.4: cabecera «ubicada inmediatamente después de la etiqueta <?php y antes de las declaraciones namespace y use». 1.2.4.1: «Inmediatamente después, se debe incluir la declaración declare(strict_types=1);» |
| Problema | Las dos reglas exigen el mismo lugar para dos elementos distintos. El sistema sigue 1.1.4 en sus 140 archivos PHP (cabecera, `declare`, `namespace`) |
| DT relacionada | — |
| Prioridad | Media |

**Redacción propuesta**

- 1.2.4.1: «La etiqueta `<?php` se ubica en la primera línea del archivo, sin espacios ni líneas en
  blanco antes de ella. A continuación va la cabecera de prólogo (1.1.4) y, después de ella, la
  declaración `declare(strict_types=1);`, que obliga a PHP a verificar los tipos de forma estricta. Tras
  la declaración de tipos se incluye el namespace…»
- 1.1.4, plantilla oficial: mostrar la línea `declare(strict_types=1);` después del cierre de la
  cabecera.

### PM-MP-02 · Bloques y etiquetas de la cabecera

| Campo | Contenido |
|---|---|
| Origen | MP/AM-02 |
| Sección | MP 1.1.3 y Tabla 4 |
| Texto actual | «Toda cabecera de archivo debe contener los siguientes seis (6) bloques obligatorios…» La Tabla 4 lista 7 bloques (el 7.º, «Requisitos Asociados», como opcional) y la plantilla de 1.1.4 usa 12 etiquetas |
| Problema | Las cifras se contradicen (6, 7 y 12), y CLAUDE.md §3 trata `@requisitos` como obligatoria |
| DT relacionada | — |
| Prioridad | Media |

**Redacción propuesta**

«Toda cabecera contiene las doce etiquetas de la plantilla de 1.1.4 (`@project`, `@module`, `@file`,
`@path`, `@author`, `@date`, `@lastModified`, `@version`, `@license`, `@description`, `@requisitos`,
`@see`), agrupadas en los siete bloques de la Tabla 4. El bloque 7 (`@requisitos`) es obligatorio en el
código de negocio (controladores, servicios, modelos, políticas, solicitudes, migraciones y vistas) y
opcional en archivos de infraestructura.» En la Tabla 4, fila 7, columna «Obligatorio»: «Sí en código
de negocio».

### PM-MP-03 · Archivos que llevan cabecera

| Campo | Contenido |
|---|---|
| Origen | MP-16 (MENOR), MP/AM-11 (parte de cabeceras) |
| Sección | MP 1.1.2, Tabla 3 |
| Texto actual | Tabla 3: «Archivos de configuración de entorno — .env, .yaml — Infraestructura» |
| Problema | La tabla incluye `.env` (que no se versiona) y omite `Dockerfile`, `nginx.conf`, `php.ini`, `.env.example` y el JavaScript y CSS propios, todos con cabecera en el proyecto |
| DT relacionada | — |
| Prioridad | Baja |

**Redacción propuesta** (filas de la Tabla 3 y nota)

- «Plantilla de variables de entorno | `.env.example` | Infraestructura (comentarios con `#`)»
- «Contenedores y servidor | `Dockerfile`, `*.conf`, `*.ini`, `docker-compose.yml` | Infraestructura
  (comentario del formato: `#` o `;`)»
- «JavaScript y CSS propios | `.js`, `.css` | Web (comentario de bloque `/** … */`)»
- Nota: «El archivo `.env` real no lleva cabecera y nunca se versiona.»

### PM-MP-04 · Verificación de la longitud de línea

| Campo | Contenido |
|---|---|
| Origen | MP-12 (MENOR) |
| Sección | MP 1.2.3.2 Tabla 10 y 1.1.10 Tabla 7 |
| Texto actual | Tabla 10: «PHP — 120 caracteres — PSR-12 recomienda 120; Laravel Pint lo aplica.» |
| Problema | Pint no verifica la longitud de línea. Por eso hay 6 líneas de más de 120 columnas aunque Pint pasa |
| DT relacionada | — |
| Prioridad | Baja |

**Redacción propuesta:** Tabla 10, justificación: «PSR-12 recomienda 120. Laravel Pint no verifica
la longitud, por lo que el límite se comprueba con PHP_CodeSniffer (regla de longitud de línea con
límite 120) en el *hook* previo al commit y en el CI.» Agregar el propósito «Validar la longitud de
línea» a la fila de PHP_CodeSniffer de la Tabla 7.

### PM-MP-05 · Configuración vigente de `pint.json`

| Campo | Contenido |
|---|---|
| Origen | MP/AM-13 |
| Sección | MP 1.2.7.1 |
| Texto actual | El `pint.json` del manual no menciona `phpdoc_separation`, `phpdoc_no_package` ni `no_superfluous_phpdoc_tags` |
| Problema | Con el preset `laravel`, esas tres reglas reformatean la cabecera de 1.1.4 y borran `@package` y etiquetas `@return` que el MP exige. DT-020 (aprobada) y DT-027 (propuesta) las desactivan |
| DT relacionada | DT-020, DT-027 |
| Prioridad | Media. La parte de DT-027 queda condicionada a su aprobación |

**Redacción propuesta:** agregar al texto de 1.2.7.1: «El preset `laravel` incluye reglas que alteran
la cabecera de prólogo y eliminan etiquetas PHPDoc obligatorias; por eso se desactivan
`phpdoc_separation`, `phpdoc_no_package` y `no_superfluous_phpdoc_tags`.» y sustituir la configuración
por la siguiente. El fragmento, en JSON, corresponde al archivo `pint.json` de la raíz del proyecto:

```json
{
    "preset": "laravel",
    "rules": {
        "declare_strict_types": true,
        "blank_line_after_opening_tag": true,
        "blank_line_after_namespace": true,
        "ordered_imports": { "sort_algorithm": "alpha" },
        "single_quote": true,
        "trailing_comma_in_multiline": true,
        "no_unused_imports": true,
        "not_operator_with_successor_space": true,
        "concat_space": { "spacing": "one" },
        "phpdoc_separation": false,
        "phpdoc_no_package": false,
        "no_superfluous_phpdoc_tags": false
    }
}
```

### PM-MP-06 · Excepciones a la regla del verbo en infinitivo

| Campo | Contenido |
|---|---|
| Origen | MP/AM-06, MP-10 (MENOR) |
| Sección | MP 1.5.5 |
| Texto actual | «Regla: Todos los métodos y funciones usan camelCase con un verbo en infinitivo al inicio.» |
| Problema | La regla choca con los ejemplos del propio MP (1.7.4: `usuario()`, `detalle()`), con las convenciones de Laravel (relaciones, scopes, estados de *factory*) y con los predicados booleanos |
| DT relacionada | — |
| Prioridad | Media |

**Redacción propuesta** (después de la regla)

«Excepciones: (a) relaciones Eloquent, que llevan el sustantivo de la entidad relacionada
(`usuario()`, `detalles()`); (b) scopes locales, con el prefijo `scope` que exige el framework;
(c) estados de *factory*, con el adjetivo del estado (`vencido()`, `entregado()`); (d) predicados
booleanos, que empiezan con `es`, `tiene`, `puede`, `esta` o `existe` (`esSuperadmin()`,
`tieneIncidencia()`); (e) métodos que impone el framework o una interfaz (`handle`, `authorize`,
`rules`, `casts`, `toArray`). Los demás métodos, incluidos los auxiliares de formato, empiezan con verbo
en infinitivo (`formatearFechaCorta()`, `obtenerIniciales()`).»

### PM-MP-07 · Método `casts()` de Eloquent

| Campo | Contenido |
|---|---|
| Origen | MP/AM-07 |
| Sección | MP 1.7.4 frente a 1.7.5 Tabla 47 |
| Texto actual | 1.7.4: «// 3. Casts — protected $casts = [ … ];» |
| Problema | Desde Laravel 11 los casts se declaran con el método protegido `casts()`, y 1.7.5 obliga a colocar los métodos protegidos después de los públicos |
| DT relacionada | DT-019 |
| Prioridad | Baja |

**Redacción propuesta:** en 1.7.4, comentario de la sección 3: «3. Casts: como propiedad `$casts` o como
método protegido `casts()`. Si se usa el método, se ubica con los métodos protegidos, después de los
públicos (1.7.5, regla 6).»

### PM-MP-08 · Estándar de JavaScript y CSS del front-end web

| Campo | Contenido |
|---|---|
| Origen | MP/AM-11, MP-23 y MP-24 (OBSERVACIÓN) |
| Sección | MP cap. 1 (nueva sección 1.8) |
| Texto actual | El capítulo 1 cubre PHP, Dart y SQL; no hay reglas para JavaScript ni CSS |
| Problema | `public/js/spei.js` (772 líneas) y `public/css/spei.css` (4 107 líneas) son código propio sin norma, y se aplican reglas por analogía |
| DT relacionada | DT-028 (JavaScript nativo, sin Alpine ni Livewire) |
| Prioridad | Media |

**Redacción propuesta** (nueva sección)

«*1.8 JavaScript y CSS del front-end web.* Aplica a los archivos propios de `public/js` y `public/css`.
(1) Cabecera de prólogo en comentario de bloque, con las etiquetas de 1.1.4. (2) UTF-8 sin BOM, fin de
línea LF, cuatro espacios y un máximo de 120 caracteres por línea. (3) JavaScript nativo en modo
estricto, con `const` y `let` (sin `var`); funciones en camelCase, en español y con verbo en
infinitivo. (4) Toda función exportada o de más de 15 líneas lleva un comentario de documentación con
su propósito, `@param` y `@returns`. (5) Ningún `catch` queda vacío: se registra el error o se comenta
por qué se ignora. (6) Los scripts en línea de las vistas se limitan a una inicialización mínima. (7) En
CSS, clases en español con la convención BEM (`bloque__elemento--modificador`) y colores y medidas
mediante variables de la paleta (2.1), sin `!important` salvo en utilidades documentadas. (8) Los
límites de complejidad de 1.3 y 1.4 aplican por analogía.»

### PM-MP-09 · Tintes y sombras de la paleta

| Campo | Contenido |
|---|---|
| Origen | MP-21 (OBSERVACIÓN, pendiente de DT) |
| Sección | MP 2.1.5, Tabla 51 (reglas 1 y 5) |
| Texto actual | «1. El Azul cielo (#3B82F6) es el color primario institucional.» «5. No usar más de 3 colores por pantalla.» |
| Problema | El prototipo adoptado por DT-028 usa escalas claras y oscuras de los colores de las Tablas 48 y 49 (37 de los 50 colores del CSS). El manual no dice si un tinte cuenta como color nuevo ni si está permitido |
| DT relacionada | DT-028 (aprobada) y su enmienda del 25/10/2026 (propuesta) |
| Prioridad | Media |

**Redacción propuesta**

- Regla 5: «No usar más de tres familias de color por pantalla además de los neutros; los tintes y
  sombras de una misma familia cuentan como un solo color.»
- Regla 6, nueva: «Se admiten tintes (más claros) y sombras (más oscuras) de los colores de las
  Tablas 48 y 49 para fondos tenues, bordes, estados *hover* y texto sobre fondo tenue, siempre que cada
  par texto/fondo cumpla la Tabla 54 y que los iconos con significado alcancen 3:1. Los tintes se
  definen una sola vez como variables del sistema de diseño y se registran en DT-01; no se admiten
  colores de otras familias.»

### PM-MP-10 · Color activo accesible

| Campo | Contenido |
|---|---|
| Origen | MP/AM-03 (relacionado: MP-04 MAYOR) |
| Sección | MP 2.1.5 Tabla 51 (regla 1), 2.5.1 Tabla 62 y 2.5.3 Tabla 64 («Color activo») |
| Texto actual | Tabla 62: «Color de texto: Blanco #F0F9FF. Color activo: Azul cielo #3B82F6.» Tabla 64: «Color activo: Azul cielo #3B82F6.» |
| Problema | Blanco sobre `#3B82F6` da 3.68:1 y el MP exige 4.5:1 para texto normal (Tabla 54): el manual prescribe un incumplimiento propio. DT-028 (a) ya adopta `#2563EB` (5.17:1) en los botones |
| DT relacionada | DT-028 |
| Prioridad | Alta (accesibilidad) |

**Redacción propuesta**

- Tabla 51, regla 1: «…es el color primario institucional. Cuando se use como fondo de texto blanco,
  se emplea su tono derivado `#2563EB`.»
- Tabla 62: «Color activo: fondo `#2563EB` (tono derivado del primario) con texto blanco; `#3B82F6` se
  reserva para acentos, bordes, indicadores e iconos.»
- Tabla 64: «Color activo: icono y etiqueta en `#2563EB` sobre fondo blanco.»

### PM-MP-11 · Tipografía de pantalla en px y pesos disponibles

| Campo | Contenido |
|---|---|
| Origen | MP/AM-04 |
| Sección | MP 2.6.3 Tabla 68 y 2.6.1 Tabla 66 (columna «Fuente») |
| Texto actual | Tabla 68: «Título de pantalla Calibri 24 pt Bold … Subtítulo Calibri 18 pt SemiBold … Etiqueta de campo Calibri 12 pt Medium … Botón Calibri 14 pt SemiBold» |
| Problema | El punto es una unidad de impresión, y en pantalla se expresa en px. Carlito (el equivalente libre de Calibri, DT-014) solo ofrece pesos 400 y 700, así que SemiBold y Medium no existen |
| DT relacionada | DT-014, DT-028 (c) |
| Prioridad | Media |

**Redacción propuesta:** nota bajo la Tabla 68: «En la web los tamaños se expresan en px con la misma
jerarquía: título de pantalla 24 px, subtítulo 18 px, título de tarjeta 16 px, texto normal 14 px, texto
secundario 12 px, etiqueta de campo 12 px, botón 14 px y pie de página 10 px. Con Carlito, los pesos
SemiBold y Medium se sustituyen por 700 (Bold).» En la Tabla 66, la columna «Fuente» pasa de pt a px.

### PM-MP-12 · Botón primario compacto

| Campo | Contenido |
|---|---|
| Origen | MP/AM-05, MP-19 (MENOR, parte de botones) |
| Sección | MP 2.6.1 Tabla 66 y 2.6.2 (Botones) |
| Texto actual | Tabla 66: «Botón primario 48 px 24 px 14 pt». 2.6.2: «La altura es fija según el tipo (48px primario, 40px secundario, 32px pequeño).» |
| Problema | Un botón primario de 48 px no cabe bien en tablas, barras de filtros y tarjetas densas. El sistema usa 40 px por defecto y 48 px en las acciones principales |
| DT relacionada | DT-028 |
| Prioridad | Media |

**Redacción propuesta**

- Tabla 66, fila nueva: «Botón primario compacto | 40 px | 20 px | 14 px | Uso: tablas, barras de
  herramientas y filtros, tarjetas densas y diálogos».
- 2.6.2: «La altura es fija según el tipo (48 px primario, 40 px primario compacto y secundario, 32 px
  pequeño). La acción principal de una pantalla o formulario usa el botón primario de 48 px.»

Las demás diferencias de componentes de MP-19 (casilla de 20 px, relleno de tarjeta de 20 px, fondo
claro en el encabezado de tabla, *toast* oscuro con radio de 12 px y peso 700 en botones) no se
proponen como cambio al manual: Raúl decide si se corrigen en el CSS o se enumeran en la enmienda de
DT-028 (sección 9, C-07).

### PM-MP-13 · Material Symbols como set de iconos

| Campo | Contenido |
|---|---|
| Origen | MP/AM-08 |
| Sección | MP 2.5.1 Tabla 62 («Íconos»), 2.9 (título), 2.9.1 Tabla 75, B.5.1 |
| Texto actual | Tabla 75: «Material Icons — Apache 2.0 — Iconos generales de UI.» Tabla 62: «Íconos: Material Icons, 24 px.» |
| Problema | Material Symbols es el sucesor de Material Icons (misma familia de Google, Apache 2.0). El sistema usa un solo estilo, Outlined (DT-028 d) |
| DT relacionada | DT-028 |
| Prioridad | Media. Requiere aplicar antes PM-MD-09 |

**Redacción propuesta**

- Tabla 75 y B.5.1, fila nueva al inicio: «Material Symbols (Outlined, Rounded o Sharp) | Apache 2.0 |
  Iconos generales de UI; sucesor de Material Icons; set preferente | fonts.google.com/icons».
- Tabla 62: «Íconos: Material Symbols o Material Icons, 24 px, un solo estilo por pantalla.»
- Título de 2.9: «…(Uso de Iconos Creative Commons / Material Icons y Material Symbols)».

### PM-MP-14 · Navegación en la web responsiva

| Campo | Contenido |
|---|---|
| Origen | MP/AM-14, MP-20 (MENOR) |
| Sección | MP 2.5.3 Tabla 64 y 2.5.4 Tabla 65 (regla 5) |
| Texto actual | Regla 5: «En móvil, usar Bottom Navigation (máximo 5 elementos).» |
| Problema | La *bottom navigation* se pensó para la app Flutter, que queda fuera de alcance (DT-022). El manual no define la navegación de la web responsiva, que hoy contrae el menú lateral a 64 px por debajo de 640 px |
| DT relacionada | DT-022 |
| Prioridad | Media. Requiere que Raúl decida (sección 9, C-06) |

**Redacción propuesta** (regla 5)

«5. En la aplicación móvil nativa se usa Bottom Navigation (máximo 5 elementos). En la web responsiva,
por debajo de 640 px de ancho, se usa [opción A: una barra inferior de 56 px con los cinco destinos
principales del rol] / [opción B: el menú lateral contraído a 64 px, con etiquetas accesibles y un
botón para desplegarlo].»

### PM-MP-15 · Ramas y aprobación de pull requests

| Campo | Contenido |
|---|---|
| Origen | MP/AM-09 |
| Sección | MP 3.2.2 Tabla 81, 3.2.3 Tabla 82, 3.2.5 Tabla 84 (regla 5) |
| Texto actual | Tabla 81: «bugfix/* … release/* Preparación de release.» Tabla 84: «5. El PR requiere al menos 1 aprobación.» |
| Problema | La convención `bugfix/` y `release/` contradice el MD, de mayor precedencia (DT-001). La «1 aprobación» no se puede cumplir con desarrollador único (DT-018) |
| DT relacionada | DT-001, DT-018 |
| Prioridad | Alta. Depende de PM-MD-01 y PM-MD-11 |

**Redacción propuesta**

- 3.2.2 y 3.2.3: «La convención de ramas es la del Manual de Desarrollo 10.1, Tabla 21: `main`,
  `develop`, `feature/RF-XXX-descripcion`, `fix/DEF-XXX-descripcion`, `hotfix/vX.Y.Z-descripcion` y
  `docs/BBB-codigo-descripcion`. No se usan `bugfix/` ni `release/`: la versión se prepara en `develop`
  y se etiqueta en `main`.» Las Tablas 81 y 82 se ajustan a esa lista.
- Tabla 84, regla 5: «El PR requiere al menos una aprobación de un revisor distinto del autor; en modo
  de desarrollador único, el responsable aprueba después del dictamen RED-01 del revisor independiente
  (Manual de Desarrollo 3.3).»

### PM-MP-16 · Fragmentos de configuración vigentes

| Campo | Contenido |
|---|---|
| Origen | MP/AM-10 |
| Sección | MP 3.1.3 (`phpstan.neon`), 3.4.2 Tabla 89, 3.4.5 Tabla 90 (regla 2), 3.5.2 y 3.5.3 (`docker-compose.yml`) |
| Texto actual | 3.1.3: «checkMissingIterableValueType: false / checkGenericClassInNonGenericObjectType: false»; Tabla 89: «Integración — Pest PHP — >= 70%»; Tabla 90: «2. Cobertura mínima: 80% en PHP, 70% en Dart.»; 3.5.2: imágenes `latest` y `mysql:8.0` |
| Problema | Las opciones de PHPStan no existen en 2.x (DT-005); el umbral del 70 % contradice el 80 % del MD (DT-015); el compose usa imágenes flotantes y contraseñas literales (DT-002, DT-003, DT-017), y Dart queda fuera de alcance (DT-022) |
| DT relacionada | DT-002, DT-003, DT-005, DT-015, DT-017, DT-022 |
| Prioridad | Media |

**Redacción propuesta**

- 3.1.3: retirar las dos opciones y agregar la extensión de Larastan. El fragmento, en formato NEON,
  corresponde al archivo `phpstan.neon` de la raíz del proyecto:

```neon
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    level: 6
    paths:
        - app
        - database
        - tests
    excludePaths:
        - vendor
        - storage
```

- Tabla 89, fila «Integración»: «>= 80%» (Manual de Desarrollo, Tabla 17). Filas Flutter: «Solo cuando
  exista cliente móvil».
- Tabla 90, regla 2: «Cobertura mínima: 80 % en el backend (Manual de Desarrollo, Tabla 17); 70 % en
  Dart solo cuando exista cliente móvil.»
- 3.5.2 y 3.5.3, nota previa al ejemplo: «Las imágenes se fijan por etiqueta de versión y *digest*
  (Manual de Desarrollo 6.1); las credenciales se leen de `.env` mediante variables `${…}` y nunca se
  escriben en el compose; phpMyAdmin y MailHog solo se activan en el perfil de desarrollo; las versiones
  de referencia son PHP 8.3-FPM y MySQL 8.4 LTS.» El ejemplo se actualiza con esas reglas.

### PM-MP-17 · Autenticación con Sanctum

| Campo | Contenido |
|---|---|
| Origen | MP/AM-12, MP-27 (OBSERVACIÓN) |
| Sección | MP 3.3.2, Tabla 85 |
| Texto actual | «Duración del token: 24 horas (acceso), 7 días (refresh).» «Alternativa: JWT (para integraciones externas).» «SSO: LDAP / SSO institucional (RNF-001).» «Almacenamiento: Secure Storage (Flutter), HttpOnly Cookie (Web).» |
| Problema | Sanctum no emite tokens de renovación, y JWT y LDAP no forman parte de la liberación 1.0 (DT-007). MD 5.4, de mayor precedencia, deja SSO como extensión opcional por OIDC u OAuth 2.0 |
| DT relacionada | DT-007, DT-022 |
| Prioridad | Media |

**Redacción propuesta** (filas de la Tabla 85)

- «Duración del token: 24 horas (acceso). Sanctum no emite tokens de renovación: al expirar, el usuario
  inicia sesión de nuevo y se emite un token nuevo.»
- «Alternativa: no aplica en la liberación 1.0 (DT-007).»
- «SSO: extensión opcional mediante OpenID Connect u OAuth 2.0 (Manual de Desarrollo 5.4).»
- «Almacenamiento: cookie de sesión HttpOnly y Secure en la web; almacenamiento seguro del sistema
  operativo en un cliente móvil, cuando exista.»

---

## 7. Manual de Base de Datos BBB-BD-004

### PM-BD-01 · Política de cascadas

| Campo | Contenido |
|---|---|
| Origen | BD-25 |
| Sección | MBD 3.4.5 (punto 2), 5.7.6, 6.7.3 (regla 3) frente a la Tabla 27 y la Tabla 102 |
| Texto actual | 3.4.5: «2. Las cascadas se limitan a RESTRICT y SET NULL para evitar pérdida accidental de datos.» |
| Problema | La Tabla 27 define ON DELETE CASCADE en relaciones de dependencia (detalle, tokens, conversaciones, notificaciones). El propio manual se contradice; DT-009 resolvió aplicar la Tabla 27 |
| DT relacionada | DT-009 (aprobada) |
| Prioridad | Media |

**Redacción propuesta** (3.4.5 punto 2; igual en 5.7.6 y 6.7.3 regla 3)

«2. Las acciones ON DELETE siguen la Tabla 27: RESTRICT por defecto, SET NULL cuando el hijo puede
existir sin el padre y CASCADE solo en relaciones de dependencia (`detalle_prestamo`, `tokens_qr`,
`conversaciones` y `notificaciones`). Todas las FK usan ON UPDATE CASCADE.»

### PM-BD-02 · Relación `usuarios → logs_auditoria` con RESTRICT

| Campo | Contenido |
|---|---|
| Origen | BD-26 |
| Sección | MBD 3.4.2, Tabla 27 (fila `usuarios → logs_auditoria`), frente a 3.4.5 punto 4 |
| Texto actual | «usuarios — logs_auditoria — SET NULL — CASCADE — Los logs se conservan aunque el usuario se elimine.» |
| Problema | SET NULL modifica filas de una bitácora que 3.4.5 declara inalterable («no se permite UPDATE ni DELETE sobre logs_auditoria», RNF-005), y los triggers de inalterabilidad rechazarían la operación |
| DT relacionada | DT-010 (aprobada) |
| Prioridad | Alta (integridad de la auditoría) |

**Redacción propuesta** (fila de la Tabla 27)

«usuarios | logs_auditoria | RESTRICT | CASCADE | La bitácora es inalterable (3.4.5 punto 4,
RNF-005): los usuarios no se eliminan físicamente, se desactivan (`activo = FALSE`).»

### PM-BD-03 · `tokens_qr.token` como *hash* SHA-256

| Campo | Contenido |
|---|---|
| Origen | BD-27 (relacionado: MD-37) |
| Sección | MBD 1.5.3 (glosario, «Token QR»), 3.2.2 Tabla 11, 4.8 Tabla 43 (columna 4) |
| Texto actual | Tabla 43: «4 token VARCHAR(255) UK, NOT NULL — Token cifrado de un solo uso.»; 1.5.3: «Token QR: Cadena cifrada de un solo uso, con TTL de 30 a 60 segundos…» |
| Problema | Un token «cifrado» es reversible si se obtiene la clave. El sistema guarda el *hash* SHA-256 del código aleatorio (CHAR(64)), irreversible: una fuga de la tabla no expone códigos vigentes (RS-006) |
| DT relacionada | DT-030 (propuesta), DT-029 |
| Prioridad | Alta (seguridad). Se aplica junto con PM-MD-03 |

**Redacción propuesta**

- Tabla 43, fila 4: «token | CHAR(64) | UK, NOT NULL | — | *Hash* SHA-256 en hexadecimal del código
  de un solo uso; el código en claro no se persiste.» Igual en la Tabla 11 de 3.2.2.
- 1.5.3, «Token QR»: «Código aleatorio de un solo uso, con TTL de 30 a 60 segundos, que autoriza una
  entrega o devolución; la base de datos conserva solo su *hash*.»

### PM-BD-04 · Vigencia del token controlada en MySQL

| Campo | Contenido |
|---|---|
| Origen | BD-28 (relacionado: MD-37) |
| Sección | MBD 2.2 (viñeta «Tokens QR dinámicos»), Tabla 7 (RF-034 y RF-036), Tabla 35 (RN-004) |
| Texto actual | 2.2: «Tokens QR dinámicos (TTL 30-60 s): Redis expira automáticamente los tokens de un solo uso (RN-004, RF-034, RF-036).»; Tabla 7: «RN-004: TTL 30-60 s; UNIQUE(token); Redis para expiración» |
| Problema | Dos fuentes de verdad (Redis y `tokens_qr`). El sistema usa `fecha_expiracion` + `usado` + `FOR UPDATE` en MySQL: es más simple, auditable y no depende de la política de expulsión de Redis (PM-BD-14) |
| DT relacionada | DT-030 |
| Prioridad | Alta. Se aplica junto con PM-MD-03 |

**Redacción propuesta**

- 2.2: «Tokens QR dinámicos (TTL 30-60 s): la vigencia se controla en MySQL con
  `tokens_qr.fecha_expiracion` y `tokens_qr.usado`, que se verifican con `SELECT … FOR UPDATE` al
  validar; Redis puede servir como caché o candado complementario, no como única fuente (RN-004,
  RF-034, RF-036).»
- Tabla 7, RF-034 y RF-036: «RN-004: TTL 30-60 s; UNIQUE(token); vigencia por `fecha_expiracion` y
  `usado`».

### PM-BD-05 · COMMENT en triggers y vistas

| Campo | Contenido |
|---|---|
| Origen | BD-29 |
| Sección | MBD 3.3.5 Tabla 22 («Comentario»), 3.3.7 Tabla 24 («Comentario»), 5.4.7, 6.4.3 (regla 6) |
| Texto actual | Tabla 22: «Comentario — COMMENT 'Descripción del trigger'»; Tabla 24: «Comentario — COMMENT 'Descripción de la vista'» |
| Problema | `CREATE TRIGGER` de MySQL 8.4 no admite la cláusula COMMENT. **Ampliación del documentador, a verificar por el auditor:** `CREATE VIEW` tampoco la admite. Los procedimientos almacenados sí, así que la Tabla 23 se conserva |
| DT relacionada | Nueva DT sugerida |
| Prioridad | Media |

**Redacción propuesta**

- Tabla 22, «Comentario»: «MySQL no admite COMMENT en `CREATE TRIGGER`. El propósito del trigger se
  documenta en su formato DDL-04 y con un comentario SQL al inicio del cuerpo, en el script versionado
  (migración).» Igual en 5.4.7 y 6.4.3 regla 6.
- Tabla 24, «Comentario»: «MySQL no admite COMMENT en `CREATE VIEW`. El propósito de la vista se
  documenta en su formato DDL y en el diccionario de datos (DDL-06).»

### PM-BD-06 · Reglas automáticas con trigger o lógica de aplicación

| Campo | Contenido |
|---|---|
| Origen | BD-30 |
| Sección | MBD Tabla 7 (RF-004, RF-006, RF-014, RF-026, RF-030, RF-049), Tabla 35 (RN-003, RN-005), 3.4.1 Tabla 26 (principio 7), 3.4.5 (punto 3), frente a 7.2 y 7.3 |
| Texto actual | Tabla 7, RF-049: «Trigger al crear solicitud; notificación al Responsable»; Tabla 26: «7. Toda transacción crítica se audita — Trigger a logs_auditoria»; 3.4.5: «3. Las sanciones se aplican automáticamente mediante triggers, sin intervención manual.» |
| Problema | Un trigger de MySQL no puede enviar notificaciones ni correos, y 7.2 y 7.3 admiten la lógica de aplicación. El sistema implementa estas reglas en servicios, comandos y colas |
| DT relacionada | Nueva DT sugerida |
| Prioridad | Media |

**Redacción propuesta**

- 3.4, párrafo nuevo: «Las reglas automáticas (cambios de estado, sanciones, auditoría y alertas) se
  implementan mediante trigger, procedimiento almacenado o lógica transaccional de la aplicación
  (servicio, comando o trabajo programado). La regla se ejecuta dentro de la misma transacción que el
  cambio que la origina, o con reintento garantizado, y deja registro en `logs_auditoria`. Las
  notificaciones y los correos se generan en la aplicación mediante colas, nunca en un trigger.»
- Tabla 26, principio 7: «Toda transacción crítica se audita — registro en `logs_auditoria` por
  trigger o por el servicio de auditoría dentro de la transacción.»
- 3.4.5, punto 3: «Las sanciones se aplican automáticamente, sin intervención manual, mediante trigger
  o lógica transaccional de la aplicación.»
- Tabla 7, filas citadas: sustituir «Trigger…» por «Trigger, procedimiento o lógica transaccional…».

### PM-BD-07 · Excepciones al prefijo `id_institucion` en índices

| Campo | Contenido |
|---|---|
| Origen | BD-31, BD-24 (OBSERVACIÓN) |
| Sección | MBD 1.5.2, 5.3.8, 6.3.3 Tabla 83 (regla 7) |
| Texto actual | Tabla 83: «7. Los índices compuestos deben comenzar por id_institucion.» |
| Problema | InnoDB exige un índice cuyo prefijo sea la columna de cada FK, y las tablas globales (`instituciones`, `planes`) no tienen tenant. La regla literal no se puede cumplir (`idx_instituciones_estado_fecha_corte`) |
| DT relacionada | DT-026 (propuesta) |
| Prioridad | Media |

**Redacción propuesta** (Tabla 83 regla 7; igual en 5.3.8 y 1.5.2)

«7. Los índices de consulta de las tablas del tenant comienzan por `id_institucion`. Se exceptúan
(a) los índices de soporte de llaves foráneas, que InnoDB exige con la columna de la FK como prefijo y
que se nombran `idx_<tabla>_<columna>`, y (b) los índices de las tablas globales sin tenant
(`instituciones`, `planes`).»

### PM-BD-08 · `id_institucion` en las fichas del diccionario

| Campo | Contenido |
|---|---|
| Origen | BD-32; BD-14 (MENOR, parte de las figuras) |
| Sección | MBD 4.5, 4.7, 4.8, 4.9, 4.12 y 4.13; Tabla 7 (RF-010); Tabla 27; 3.1.3 y 3.2.4 (Figuras 1 y 2) |
| Texto actual | Las fichas de `recursos`, `detalle_prestamo`, `tokens_qr`, `penalizaciones`, `notificaciones` y `conversaciones` no tienen `id_institucion`; la Tabla 7, RF-010, define «UNIQUE(numero_serie, id_institucion)» sobre una columna inexistente |
| Problema | El diccionario contradice 3.4.1 (principio 4), 6.3.3 (regla 3) y MD 5.3, de mayor precedencia. DT-008 agregó la columna a las seis tablas |
| DT relacionada | DT-008 (aprobada) |
| Prioridad | Alta |

**Redacción propuesta**

- En cada una de las seis fichas, fila nueva después de la PK: «id_institucion | BIGINT UNSIGNED |
  FK, NOT NULL | — | Tenant al que pertenece (`fk_<tabla>_instituciones`, ON DELETE RESTRICT, ON
  UPDATE CASCADE).»
- Tabla 27: agregar las filas `instituciones → recursos`, `→ detalle_prestamo`, `→ tokens_qr`,
  `→ penalizaciones`, `→ notificaciones` y `→ conversaciones` con «RESTRICT | CASCADE | No se elimina
  una institución con registros dependientes».
- Figuras 1 y 2: redibujar el DER con `id_institucion` en esas tablas (nomenclatura de MC 5.5.13,
  DT-013).

### PM-BD-09 · `id_institucion` nulo para superadmin y eventos de plataforma

| Campo | Contenido |
|---|---|
| Origen | BD-33 |
| Sección | MBD 4.4 Tabla 39 (fila 2) y 4.11 Tabla 46 (fila 3) |
| Texto actual | Tabla 39: «2 id_institucion BIGINT UNSIGNED FK, NOT NULL — Tenant al que pertenece.» Tabla 46: «3 id_institucion BIGINT UNSIGNED FK, NOT NULL — Tenant al que pertenece.» |
| Problema | El superadmin (dueño del SaaS, RF-007) no pertenece a ningún tenant y sus acciones no son de ningún tenant. MySQL rechaza el CHECK que preveía DT-011 sobre una columna de FK con acción referencial (DT-026) |
| DT relacionada | DT-011 (aprobada); DT-024 inciso c y DT-026 (propuestas) |
| Prioridad | Alta |

**Redacción propuesta**

- Tabla 39, fila 2: «id_institucion | BIGINT UNSIGNED | FK, NULL | NULL | Tenant al que pertenece.
  Es NULL solo para el rol superadmin, lo que garantizan los triggers `trg_usuarios_before_insert` y
  `trg_usuarios_before_update` (MySQL no admite un CHECK sobre una columna de FK con acción
  referencial).»
- Tabla 46, fila 3: «id_institucion | BIGINT UNSIGNED | FK, NULL | NULL | Tenant del evento. Es NULL
  solo en eventos de plataforma del superadmin; el servicio de auditoría lo asigna siempre que exista
  contexto de tenant.»

### PM-BD-10 · Ejemplo `nombre_area` de la Tabla 16

| Campo | Contenido |
|---|---|
| Origen | BD-34 |
| Sección | MBD 3.3.3 Tabla 16, frente a 4.3 Tabla 38 y 6.3.4 Tabla 84 |
| Texto actual | «Sin prefijos redundantes — nombre en tabla areas — nombre_area en tabla areas» (correcto / incorrecto) |
| Problema | El diccionario usa `nombre_area` y la Tabla 84 usa `nombre_<entidad>`: el ejemplo marca como incorrecto lo que el propio manual exige |
| DT relacionada | — |
| Prioridad | Baja |

**Redacción propuesta:** fila de la Tabla 16: «Sin prefijo de tabla — `nombre_area` en tabla `areas` —
`areas_nombre`, `tbl_area_nombre`».

### PM-BD-11 · Nomenclatura y fechas de los formatos DDL

| Campo | Contenido |
|---|---|
| Origen | BD-35 |
| Sección | MBD 5.0.2 Tabla 51 (regla 1), 6.0.1 Tabla 72 (reglas 2 y 5) |
| Texto actual | «Todo formato se nombra como DDL-XX_AAAAMMDD_descripcion.sql.»; «Fechas: Formato ISO 8601: YYYY-MM-DD.» |
| Problema | Contradice MC 5.5.8 (`BBB_<Nombre>_vX.Y`) y las fechas DD/MM/AAAA de los formatos del MC, que tiene mayor precedencia. Los DDL del repositorio ya siguen al MC (`BBB_DDL03_TablasPrestamos_v1.0.md`), sin DT que lo registre. Además, un formato no es un archivo `.sql` |
| DT relacionada | Nueva DT sugerida (compartida con PM-MC-06) |
| Prioridad | Alta (conflicto con el manual de mayor precedencia) |

**Redacción propuesta** (recomendada; la alternativa está en la sección 9, C-03)

- Tabla 51, regla 1: «Nomenclatura: cada formato se guarda como documento
  `BBB_DDLXX_<Descripcion>_vX.Y.<ext>` conforme al Manual de Calidad 5.5.8; el script que contiene se
  versiona además como migración de Laravel (3.3.8).» Igual en la Tabla 72, regla 2.
- Tabla 72, regla 5: «Fechas: DD/MM/AAAA en el contenido del formato (Manual de Calidad); ISO 8601
  (AAAA-MM-DD) en scripts, código y nombres de migración.»

### PM-BD-12 · Errores editoriales de los formatos DDL

| Campo | Contenido |
|---|---|
| Origen | BD-36 (más dos erratas detectadas al preparar esta propuesta) |
| Sección | MBD 5.0.1 Tabla 50, 5.0.2 Tabla 51 (reglas 2 y 5), 5.2.1, 5.7, 6.0.1 Tabla 72 (regla 4) |
| Texto actual | Tabla 50: lista DDL-01…DDL-07 (falta DDL-08); Tabla 51: «Cada formato incluye un campo versión (v1.1, v1.1, etc.).» «Los cambios se aplican primero en desarrollo, luego en staging, luego en producción.»; Tabla 72: «v1.1 para la primera versión»; 5.2.1: «Tabla 50. Encabezado del formato DDL-02» (número duplicado) |
| Problema | Erratas e incoherencias: falta DDL-08 (llaves PK, FK y UK); la versión inicial no es la 1.0; el ambiente se llama «staging» y en el MD se llama QA (MD 7); el DDL-07 no tiene validaciones previas y posteriores, aunque la Tabla 52 fija una estructura uniforme y 6.7.3 (reglas 7 y 8) exige verificar huérfanos y tipos |
| DT relacionada | — |
| Prioridad | Media |

**Redacción propuesta**

- Tabla 50, fila 8: «DDL-08 | Creación, modificación y eliminación de llaves primarias, foráneas y
  únicas».
- Tabla 51, regla 2: «Cada formato incluye un campo versión (v1.0, v1.1, etc.).»
- Tabla 51, regla 5: «Los cambios se aplican primero en desarrollo, luego en QA y después en
  producción (Manual de Desarrollo 7).»
- Tabla 72, regla 4: «v1.0 para la primera versión; cambios menores v1.1, v1.2…; reestructuras v2.0
  (Manual de Calidad, Tabla 10).»
- 5.7 (DDL-07): agregar las secciones «Validaciones previas» (huérfanos, tipos compatibles entre FK y
  PK, índice de soporte) y «Validaciones posteriores» (restricción creada, acción ON DELETE y ON UPDATE
  esperada), como en los demás formatos.
- 5.2.1: renumerar la tabla duplicada y las siguientes, junto con el índice de tablas.

### PM-BD-13 · Firmas de los formatos DDL con desarrollador único

| Campo | Contenido |
|---|---|
| Origen | BD-13 (MENOR, parte de firmas); dependencia de MC/AM-01 |
| Sección | MBD 5.0.2 Tabla 51 (regla 3) y secciones «Firmas» de cada formato |
| Texto actual | «3. Aprobación: Ningún cambio se aplica sin firma del DBA y del líder técnico.» |
| Problema | Con DT-018, el DBA y el líder técnico son la misma persona, que además es el autor. Las firmas de los cuatro DDL siguen «Pendiente» |
| DT relacionada | DT-018 |
| Prioridad | Alta. Depende de PM-MC-01 |

**Redacción propuesta:** «3. Aprobación: ningún cambio se aplica sin la firma del DBA y del líder
técnico. En modo de desarrollador único (Manual de Calidad 2.3), el responsable firma en ambos papeles
después del dictamen del revisor independiente, que se cita en el formato.»

### PM-BD-14 · Política de memoria de Redis

| Campo | Contenido |
|---|---|
| Origen | BD-37; BD-15 (MENOR, parte de configuración) |
| Sección | MBD 2.2 («Configuración recomendada») |
| Texto actual | «Configuración recomendada: maxmemory 4GB, maxmemory-policy allkeys-lru, appendonly yes, appendfsync everysec, save 900/300/60, databases 16.» |
| Problema | Con `allkeys-lru`, en una instancia que también aloja colas, sesiones y candados de reserva, Redis puede expulsar trabajos pendientes y candados cuando se llena la memoria |
| DT relacionada | DT-031 (servicios de cola y planificador) |
| Prioridad | Media |

**Redacción propuesta**

«Configuración recomendada: `maxmemory` dimensionado según el ambiente (referencia de 4 GB en
producción); `maxmemory-policy noeviction` (o `volatile-lru`) en la instancia que aloja colas,
sesiones y candados; `allkeys-lru` solo en una instancia dedicada exclusivamente a caché;
`appendonly yes`, `appendfsync everysec`, `save 900 1`, `save 300 10`, `save 60 10000` y
`databases 16`.»

### PM-BD-15 · Evidencia del cambio estructural (DDL_CHANGE)

| Campo | Contenido |
|---|---|
| Origen | BD-38; BD-12 (MENOR) |
| Sección | MBD 5.0.2 Tabla 51 (regla 8) y reglas equivalentes de los formatos (6.0.1 r10, 6.1.3 r8, 6.4.3 r10, 6.5.3 r10, 6.6.3 r10) |
| Texto actual | «8. Registro en auditoría: Todo cambio se registra en logs_auditoria con acción DDL_CHANGE.» |
| Problema | `logs_auditoria` es una bitácora de negocio por tenant. Los cambios de esquema ya quedan en el formato DDL, en Git y en la tabla `migrations` |
| DT relacionada | Nueva DT sugerida |
| Prioridad | Media. Requiere que Raúl elija entre cambiar el manual o implementar el registro (sección 9, C-08) |

**Redacción propuesta** (regla 8 y equivalentes)

«8. Registro del cambio: el cambio estructural se evidencia con el formato DDL, la migración versionada
en Git y su registro en la tabla `migrations`. [Opcionalmente, la aplicación registra además un evento
`DDL_CHANGE` en `logs_auditoria` con `id_institucion` NULL, como evento de plataforma.]»

### PM-BD-16 · Transacciones y cliente móvil

| Campo | Contenido |
|---|---|
| Origen | BD-39 |
| Sección | MBD 3.4.5 (punto 1), 3.4.1 Tabla 26 (principio 5), 1.5.5, Tabla 7 (RF-038, RF-040, RF-047) |
| Texto actual | 3.4.5: «1. Todas las operaciones críticas son transaccionales. No se permite un INSERT sin BEGIN ... COMMIT.»; Tabla 7, RF-038: «Cola local SQLite/Hive en Flutter…»; RF-047: «Firebase Cloud Messaging (FCM) o similar» |
| Problema | La prohibición literal no tiene sentido para una inserción atómica de una sola sentencia (modo *autocommit*). Flutter, SQLite/Hive y FCM quedan fuera de alcance (DT-022) |
| DT relacionada | DT-022 (aprobada), DT-024 inciso d |
| Prioridad | Media. Depende de PM-MD-02 |

**Redacción propuesta**

- 3.4.5, punto 1: «Las operaciones críticas de varias sentencias o varias tablas se ejecutan en una
  transacción explícita (BEGIN … COMMIT / ROLLBACK). Una inserción atómica de una sola sentencia puede
  ejecutarse en modo *autocommit*.» Ajustar la Tabla 26, principio 5, en el mismo sentido.
- Tabla 7, RF-038 y 1.5.5 (términos móviles): agregar «Aplica cuando la liberación incluya cliente
  móvil (Manual de Desarrollo 1.3)».
- Tabla 7, RF-047: «`notificaciones.tipo_notificacion = 'push'` se interpreta como notificación dentro
  del sistema web mientras no exista cliente móvil; FCM o similar solo con cliente móvil.»

### PM-BD-17 · COMMENT en tablas de infraestructura

| Campo | Contenido |
|---|---|
| Origen | BD-21 (OBSERVACIÓN) |
| Sección | MBD 3.3.1 (principio 10) |
| Texto actual | «10. Documentación obligatoria: Toda tabla y columna debe incluir un COMMENT en el DDL con su descripción en español.» |
| Problema | `migrations`, `personal_access_tokens` y `failed_jobs` las crea el framework sin COMMENT. DT-016 las saca del diccionario de negocio, pero no las exime del COMMENT |
| DT relacionada | DT-016 (ampliar) |
| Prioridad | Baja |

**Redacción propuesta:** «…con su descripción en español. Se exceptúan las tablas de infraestructura
que crea el framework sin modificarlas (`migrations`, `personal_access_tokens`, `failed_jobs`), que se
listan en el diccionario como tablas de infraestructura.»

### PM-BD-18 · Patrones de nombre de las migraciones

| Campo | Contenido |
|---|---|
| Origen | BD-11 (MENOR) |
| Sección | MBD 3.3.8, Tabla 25 |
| Texto actual | «Nombre `<timestamp>_create_<tabla>_table.php`; Modificación `<timestamp>_add_<columna>_to_<tabla>_table.php`; Eliminación `<timestamp>_drop_<columna>_from_<tabla>_table.php`» |
| Problema | La tabla no cubre migraciones que crean varias tablas dependientes ni las que renombran columnas o índices, y no dice qué hacer con una migración ya ejecutada que no sigue la convención |
| DT relacionada | — (la excepción de las dos migraciones existentes se registra en DT, Anexo A) |
| Prioridad | Baja |

**Redacción propuesta** (filas nuevas de la Tabla 25 y regla)

- «Varias tablas de un mismo agregado: `<timestamp>_create_<tabla_principal>_tables.php`, sin términos
  de planificación (`mvp`, `temporal`).»
- «Renombrado: `<timestamp>_rename_<objeto>_in_<tabla>_table.php`.»
- Regla: «Una migración ya ejecutada en cualquier ambiente no se renombra; una desviación de esta tabla
  se registra en DT-01.»

### PM-BD-19 · Coherencia de tenant entre hijo y padre

| Campo | Contenido |
|---|---|
| Origen | BD-19 (OBSERVACIÓN) |
| Sección | MBD 3.4.1 Tabla 26 (principio 4) |
| Texto actual | «4. El aislamiento multitenant se respeta — id_institucion en todas las tablas.» |
| Problema | El manual no dice cómo se garantiza que el `id_institucion` de un hijo coincida con el de su padre. DT-008 lo valida en la aplicación |
| DT relacionada | DT-008 |
| Prioridad | Baja |

**Redacción propuesta:** principio 4, columna «Implementación»: «`id_institucion` en todas las tablas
del tenant. La coherencia entre una fila hija y su padre se garantiza en la capa de servicio, con
pruebas de acceso cruzado; cuando el modelo lo permita, se refuerza con FK compuestas
`(id_institucion, id_<padre>)` contra una UK del padre.»

---

## 8. Dependencias cruzadas

| Cambio de origen | Obliga a cambiar | Motivo |
|---|---|---|
| PM-MC-01 (MC 2.3, 3.3, 8.4) | PM-MD-01 (MD 3.3, 8.4, 10.2, 10.4), PM-MP-15 (MP Tabla 84 r5), PM-BD-13 (MBD Tabla 51 r3) | Los tres manuales asignan aprobaciones y revisiones a otras personas; el modo de integrante único debe definirse primero en el MC |
| PM-MC-02 (biblioteca en Git) | PM-MD-04 (MD 9.2, 13.3, 14.2, 14.3) | El MD repite la ubicación en SharePoint. Precondición: repositorio privado (MC-03) |
| PM-MC-03 (Markdown) | PM-BD-11 y PM-BD-12 (formatos DDL); MD Anexos B, C, F, H e I (formatos PI-01, EI-01, DG-01, RT-01 y DT-01) en cuanto a su soporte | Los formatos técnicos del MD y del MBD se llenan como registros técnicos |
| PM-MC-04 (scripts en formatos) | PM-BD-12 | Los formatos DDL deben remitir a la regla del MC sobre scripts largos |
| PM-MC-05 (hitos de auditoría) | PM-MD-07 (calendario) y el plan del proyecto | Los hitos A-01…A-04 se ligan a las iteraciones replanificadas |
| PM-MC-06 (registros de evento) | PM-BD-11 | Debe decidirse si los DDL son documentos versionados o registros de evento (C-03) |
| PM-MC-07 y PM-MC-08 (satisfacción y revisión por la dirección en EI-01) | MD Anexo C (formato EI-01): campos «Revisión por la dirección (MC 7.3)» y «Valoración de satisfacción (1–5) y quién valora» | Sin esos campos, el EI-01 no puede alojar lo que exige el MC. Se incluye en el control de cambios del MD v1.3 |
| PM-MC-09 (dependencias) | PM-MD-08 (paso de `composer audit` en el CI) | El criterio del MC se verifica en el CI que define el MD |
| PM-MD-02 (cliente móvil condicional) | PM-BD-16; PM-MP-14, PM-MP-16 y PM-MP-17; MP 1.1.2 Tabla 3 (fila `.dart`) y MP 3.5.6 Figura 10 (`lib/`) con la nota «cuando exista cliente móvil» (DT-004) | Todas las menciones a Flutter se condicionan igual |
| PM-MD-03 (tokens QR) | PM-BD-03 y PM-BD-04 | Deben tramitarse en el mismo lote para no dejar dos fuentes de verdad |
| PM-MD-05 (Laravel) | PM-MP-07 | El método `casts()` existe desde Laravel 11 |
| PM-MD-08 (CI) | PM-MP-16 (Tabla 90) y MBD 8.6 (pruebas de reversibilidad) | Umbrales y pasos de prueba coherentes |
| PM-MD-09 (iconos en MD Tabla 17) | PM-MP-13 | El MD tiene mayor precedencia: cambiar solo el MP no basta |
| PM-MD-11 (ramas multi-RF) | PM-MP-15 | El MP remite al MD |
| PM-MP-09 y PM-MP-10 (paleta de la interfaz) | Ninguno en el MC | MC 5.5.2 rige la paleta de los documentos, no la de la interfaz. Se recomienda que el MC lo aclare en una revisión futura |
| PM-BD-09 (`id_institucion` NULL en logs) | PM-BD-15 | El evento `DDL_CHANGE` opcional usa `id_institucion` NULL |

**Fuera de los manuales:** al aprobarse la v1.3, CLAUDE.md (§0, §2 «Material Icons (obligatorio)», §6)
y las instrucciones de los agentes en `.claude/agents/` deben alinearse con los nuevos textos. Este
documento no los modifica: el cambio corresponde a Raúl.

## 9. Decisiones que Raúl debe tomar (conflictos entre propuestas)

| ID | Conflicto | Opciones | Recomendación del documentador |
|---|---|---|---|
| C-01 | **Arranque del ciclo de cambios.** El SC-01 que aprueba PM-MC-01 se tramita con la regla vigente de 8.4, que impide que Raúl solicite y apruebe a la vez | a) Aprobar el primer SC-01 al amparo de DT-018, con RED-01 adjunto y la excepción anotada en el SC-01; b) pedir al docente que lo autorice | a), con la excepción registrada por escrito |
| C-02 | **Ratificación del docente** (corchete en PM-MC-01, 8.4) | a) Mantener «la autoridad de aprobación ratifica al cierre de la etapa»; b) omitirla (CLAUDE.md §0) | Decisión de Raúl. a) conserva la figura del MC 3.3, pero introduce un paso que depende de otra persona |
| C-03 | **DDL: ¿documento versionado o registro de evento?** (PM-MC-06 frente a PM-BD-11) | a) `BBB_DDLXX_<Descripcion>_vX.Y` (práctica actual); b) `BBB_DDLXX_<Objeto>_<AAAAMMDD>` sin versión | a): el diccionario (DDL-06) y las relaciones (DDL-07) evolucionan |
| C-04 | **Plantilla institucional frente a Markdown** (PM-MC-03 frente a la corrección de MC-07) | a) Aprobar PM-MC-03 y fijar qué documentos son entregables; b) rechazarla y generar `.docx` de todos los documentos | a). Raúl debe listar los entregables de cada etapa |
| C-05 | **Papel de Redis en los tokens QR.** El informe MD (MD-37) sugería «en Redis o en la tabla»; el MBD (BD-28), «Redis no como única fuente» | a) Tabla `tokens_qr` como fuente de verdad (PM-MD-03 y PM-BD-04, unificados); b) admitir Redis como almacén alternativo | a): es lo que implementa el sistema y lo que exige la auditoría del intento |
| C-06 | **Contingencia sin conexión en la web** (PM-MD-02, DT-022 punto 7) | a) Dejar el ejemplo de captura manual diferida; b) quitar el ejemplo hasta resolver la DT | Resolver primero DT-022 punto 7; el texto del manual solo remite a la DT |
| C-07 | **Navegación en la web móvil** (PM-MP-14) y **demás diferencias de componentes** (MP-19) | Opción A (barra inferior, requiere implementar) u opción B (menú contraído, documenta lo actual); componentes: corregir el CSS o enumerarlos en la enmienda de DT-028 | Decisión de Raúl; la opción B no exige código |
| C-08 | **DDL_CHANGE** (PM-BD-15 frente a BD-12) | a) Cambiar el manual (evento opcional); b) conservar la regla e implementar el registro al migrar | a) |
| C-09 | **Autovaloración de la satisfacción** (PM-MC-07) | a) La registra el responsable en el EI-01; b) solo la valora el docente en la entrega y, mientras tanto, N-A | Decisión de Raúl: a) es medible, pero no es independiente |
| C-10 | **«Tres colores por pantalla»** (PM-MP-09) | a) Contar familias de color; b) mantener la regla literal y reducir tintes | a), coherente con DT-028 |

## 10. Decisiones técnicas relacionadas

### 10.1 DT pendientes que condicionan propuestas

| DT | Estado | Propuestas condicionadas |
|---|---|---|
| DT-024 (extensiones del bloque A) | Propuesta | PM-BD-09 (logs con `id_institucion` NULL); PM-BD-16 (inciso d) |
| DT-026 (triggers en `usuarios` e índices de soporte) | Propuesta | PM-BD-07, PM-BD-09 |
| DT-027 (Pint conserva PHPDoc) | Propuesta | PM-MP-05 (dos de las tres reglas) |
| DT-028, enmienda del 25/10/2026 | Propuesta | PM-MP-09 |
| DT-029 (BaconQrCode) y DT-030 (esquema de préstamos) | Propuestas | PM-MD-03, PM-BD-03, PM-BD-04 |

Si Raúl rechaza una de estas DT, la propuesta condicionada se retira y el sistema debe ajustarse al
manual vigente.

### 10.2 DT nuevas sugeridas (numeración tentativa; no se crean en este documento)

| DT sugerida | Tema | Propuestas |
|---|---|---|
| DT-032 (o ampliación de DT-018) | Biblioteca documental en el repositorio Git privado | PM-MC-02, PM-MD-04 |
| DT-033 | Registros técnicos en Markdown y relación de entregables | PM-MC-03 |
| DT-034 | Nomenclatura de registros de evento y de formatos DDL | PM-MC-06, PM-BD-11 |
| DT-035 | COMMENT de triggers y vistas; reglas automáticas en la aplicación | PM-BD-05, PM-BD-06 |
| DT-036 | Evidencia del cambio estructural sin `DDL_CHANGE` obligatorio | PM-BD-15 |
| DT-037 | Navegación de la web responsiva | PM-MP-14 |

## 11. Control de cambios propuesto para la v1.3

Las cuatro actualizaciones son menores según MC Tabla 10 (correcciones y adiciones que no cambian la
estructura), así que corresponde la versión 1.3. La fecha se anota al aprobarse cada SC-01. Las tablas
siguen las columnas del historial de cada manual.

### 11.1 Manual de Calidad BBB-CA-001 (Tabla 2)

| Versión | Fecha | Descripción del cambio | Autor |
|---|---|---|---|
| 1.3 | DD/MM/AAAA (aprobación del SC-01) | Se agrega la adaptación por integrante único y el revisor independiente (1.8, 2.3, 3.3, 7.2, 8.4, 9, Anexo B). Se admite el repositorio Git privado como biblioteca documental (5.1, 5.1.2, 5.2, 5.4, 5.5.8, Figura 6, 5.5.13, 5.5.14, 6.4, 6.6). Se regulan los registros técnicos en Markdown (5.5.14), los scripts completos de los formatos DDL (5.5.12), los registros de evento y los documentos externos (5.5.8). Se interpretan los hitos de auditoría en un ciclo iterativo (7.2). Se adaptan la satisfacción, la competencia, la inducción y la revisión por la dirección (3.4, 5.2, 5.3, 7.3, Tabla 18). Se precisan el control de dependencias (6.4) y la declaración de herramientas de asistencia (6.1) | Raúl Treviño Licona (elaboró y aprobó, DT-018); revisión independiente: `spei-auditor` (RED-01 adjunto al SC-01) |

### 11.2 Manual de Desarrollo BBB-MD-002 (Tabla 2)

| Versión | Fecha | Descripción del cambio | Autor |
|---|---|---|---|
| 1.3 | DD/MM/AAAA (aprobación del SC-01) | Se agrega el modo de desarrollador único (3.3, 8.4, 10.2, 10.4, Tablas 9 y 27). El cliente móvil Flutter pasa a componente condicional (1.3, 2.2, 5.1, 5.5, Tabla 15, 6.1, 10.3, 13.1). Los tokens QR se persisten como *hash* en `tokens_qr` (5.5, Tabla 16, 13.1). Se admite la biblioteca documental en Git (9.2, 13.3, 14.2, 14.3). La versión de Laravel admite versiones mayores con soporte de seguridad (6, Tablas 14 y 17). Se admite Material Symbols (Tabla 17). Se permite replanificar el calendario sin reducir el alcance (4.1). Se agregan al CI la auditoría de dependencias y la verificación de reversibilidad (10.3). Se define la frontera entre DT-01 y SC-01 (14.4) y la nomenclatura de ramas con varios RF (10.1). El EI-01 incorpora la revisión por la dirección y la satisfacción (Anexo C). Se corrige el título del DA-01 (Anexo K) | Raúl Treviño Licona (elaboró y aprobó, DT-018); revisión independiente: `spei-auditor` (RED-01 adjunto al SC-01) |

### 11.3 Manual de Programación BBB-MP-003 (Tabla 2)

| Versión | Fecha | Descripción del cambio | Autor |
|---|---|---|---|
| 1.3 | DD/MM/AAAA (aprobación del SC-01) | Se corrigen el orden de la cabecera y `declare` (1.1.4, 1.2.4.1), los bloques de la cabecera (1.1.3) y los archivos que la llevan (1.1.2). Se aclara la herramienta de longitud de línea (1.2.3.2, 1.1.10) y se actualiza `pint.json` (1.2.7.1). Se agregan excepciones a la nomenclatura de métodos (1.5.5), el método `casts()` (1.7.4) y la nueva sección 1.8 (JavaScript y CSS). Se admiten tintes de la paleta (2.1.5), el color activo `#2563EB` (Tablas 51, 62 y 64), la tipografía en px (2.6.3), el botón primario compacto (2.6.1, 2.6.2), Material Symbols (2.9, Tabla 75, B.5.1) y la navegación de la web responsiva (2.5.4). Las ramas remiten al MD y la aprobación de PR admite el modo de desarrollador único (3.2). Se actualizan los fragmentos de PHPStan, cobertura y Docker (3.1.3, 3.4, 3.5) y la tabla de autenticación (3.3.2) | Raúl Treviño Licona (elaboró y aprobó, DT-018); revisión independiente: `spei-auditor` (RED-01 adjunto al SC-01) |

### 11.4 Manual de Base de Datos BBB-BD-004 (control de versiones)

| Versión | Fecha | Descripción del cambio | Elaboró | Revisó |
|---|---|---|---|---|
| 1.3 | DD/MM/AAAA (aprobación del SC-01) | Se unifican la política de cascadas (3.4.5, 5.7.6, 6.7.3) y la relación `usuarios → logs_auditoria` con RESTRICT (Tabla 27). `tokens_qr.token` pasa a *hash* SHA-256 CHAR(64) con vigencia en MySQL (1.5.3, 2.2, Tablas 7, 11 y 43). Se documentan los triggers y las vistas sin COMMENT (3.3.5, 3.3.7, 5.4.7, 6.4.3) y las reglas automáticas en la aplicación (3.4, Tablas 7 y 26). Se agregan excepciones al prefijo `id_institucion` en índices (1.5.2, 5.3.8, Tabla 83), `id_institucion` en seis fichas del diccionario y en el DER (4.5–4.13, Tablas 7 y 27, Figuras 1 y 2), y su nulidad para el superadmin y los eventos de plataforma (Tablas 39 y 46). Se corrigen el ejemplo de la Tabla 16, la nomenclatura y las fechas de los DDL (Tablas 51 y 72), las erratas de los formatos DDL (Tabla 50, 5.2.1, 5.7) y las firmas con desarrollador único (Tabla 51 r3). Se ajustan la configuración de Redis (2.2), la evidencia de los cambios estructurales (regla DDL_CHANGE), las transacciones y el cliente móvil (3.4.5, 1.5.5, Tabla 7), el COMMENT de las tablas de infraestructura (3.3.1), los nombres de migraciones (Tabla 25) y la coherencia de tenant (Tabla 26) | Raúl Treviño Licona (DT-018) | `spei-auditor` (revisor independiente, RED-01) |

## 12. Orden de implantación propuesto

1. Raúl resuelve los conflictos C-01…C-10 y acepta o rechaza las DT de la sección 10.1.
2. `spei-auditor` revisa esta propuesta (RED-01) contra los manuales v1.2 y los cuatro informes.
3. Se tramitan los SC-01 en orden de precedencia: SC del MC → SC del MD → SC del MP y SC del MBD. Cada
   SC-01 lleva su plan de validación (cotejar el texto publicado contra la redacción aprobada y revisar
   las referencias cruzadas de la sección 8) y su plan de reversión (restaurar la v1.2 vigente desde el
   historial).
4. Cada manual v1.3 se genera con la plantilla `BBB_Plantilla_v1.1.dotx` (los manuales son entregables,
   MC 5.5.14) y la v1.2 se conserva como obsoleta.
5. Raúl alinea CLAUDE.md y los agentes con la v1.3, y la siguiente auditoría verifica los hallazgos
   cerrados por cambio de manual.

---

## Anexo A. Correcciones menores del sistema pendientes

MENOR y OBSERVACIÓN que solo piden corregir el sistema o sus documentos. Cuando el hallazgo tiene además
una parte de manual, se indica la propuesta relacionada.

| ID | Descripción | Archivo o ubicación | Propuesta relacionada |
|---|---|---|---|
| MC-13 | Renombrar el SRS (`BBB_<Nombre>_v1.0`) y la carpeta y el archivo del prototipo con espacios | `docs/requisitos/SRS_Sistema_Prestamo_Equipo_Institucional_v2.pdf`; `Prototipo interactivo SPEI/`, `SPEI Prototipo.dc.html` | PM-MC-06, PM-MC-10 |
| MC-14 | Crear los formatos REF en blanco y con instructivo (SC-01, NC-01, LV-01) | `docs/formatos/` (por crear) | PM-MC-02 |
| MC-16 | Conciliar CP-01 y RT-01 (CP-001, CP-002, CP-024, RF-031), poner los hashes de commit y agregar filas RNF, RN y RS | `docs/trazabilidad/BBB_RT01_Matriz_v1.0.md`, `docs/pruebas/BBB_CP01_InterfazYBloqueA_v1.0.md` | — |
| MC-17 | Rehacer la matriz de riesgos con P×I 1–3, responsable, riesgo de satisfacción y oportunidades | `docs/plan/BBB_PlanDesarrollo_v1.0.md` §5 | — |
| MC-19 / MD-19 / MP-08 | Pruebas unitarias de `PerteneceAInstitucionTrait`, `RecursoPolicy`, `DisponibilidadRecursoService`, `ResumenAuditoria`, `FormatoFecha`, `PrestamoService` y `ReporteService` | `tests/Unit/` (vacío) | — |
| MC-20 | Registrar con DT el ambiente de producción y su evaluación; inventario de licencias de Composer | `docs/decisiones/`, `CREDITS.md` | PM-MC-09 |
| MC-21 / MD-21 | Cerrar DEF-001 con evidencia y crear el registro de defectos DEF-XXX | `docs/iteraciones/I0/BBB_EI01_I0_v1.0.md`; `docs/pruebas/` | — |
| MC-22 / MD-18 / MP-07 | Integrar con un PR que liste los RF cubiertos; commits atómicos en adelante | Rama `feature/RF-011-gestion-prestamos` | PM-MD-11 |
| MC-23 | Cursiva en *lock*, *hash*, *badges*, *dashboard* y *toasts*; definir RN, RS y CA en su primera aparición | `docs/decisiones/BBB_DT_Registro_v1.0.md` (DT-028), `docs/pruebas/BBB_CP01_InterfazYBloqueA_v1.0.md`, `docs/requisitos/BBB_CU*.md` | — |
| MC-26 / MP-17 | Confirmar el correo real del autor en las cabeceras o registrar la convención; revisarlo junto con MC-03 | 187 cabeceras (p. ej., `.github/workflows/ci.yml`) | — |
| MC-27 / MD-31 / MP-09 | Evitar Pest concurrente sobre `spei_testing` (una base por proceso o candado) y documentarlo | `phpunit.xml`, `tests/Pest.php`, README | — |
| MD-15 | Definir el alcance de la API v1 (préstamos y QR) y documentarla | `routes/api.php`; `docs/api/BBB_API_v1.0.md` (citado, no existe) | — |
| MD-17 | Cabeceras CSP, HSTS y Referrer-Policy; límites de peticiones en la API y en las operaciones de QR | `docker/nginx.conf`, `routes/web.php`, `routes/api.php` | — |
| MD-20 / BD-10 | Paso de CI `migrate:fresh` → `migrate:rollback` → `migrate`; pruebas de los triggers de `usuarios` (SQLSTATE 45000) | `.github/workflows/ci.yml`, `tests/Feature/` | PM-MD-08 |
| MD-22 | Planificar la retención RN-012 (1 + 3 años) con RF, CP y tarea programada | `docs/plan/BBB_PlanDesarrollo_v1.0.md` (bloque D) | — |
| MD-23 | Completar los campos 5, 6, 8, 9 y 10 de DT-013…018 y DT-020; ajustar el EI-01 de pantallas públicas al formato | `docs/decisiones/BBB_DT_Registro_v1.0.md`; `docs/iteraciones/…/BBB_EI01_Publicas_v1.0.md` | — |
| MD-24 | Registrar SC-01 retroactivos de DT-022 y DT-023 | `docs/` (SC-01 por crear) | PM-MD-10 |
| MD-25 | Elaborar el DA-01 o excluirlo con DT | `docs/requisitos/` | PM-MD-06 |
| MD-26 | Volver a ejecutar CP-01 sobre un commit y citar su hash | `docs/pruebas/BBB_CP01_InterfazYBloqueA_v1.0.md` | — |
| MD-28 | Llevar las consultas Eloquent de los controladores a servicios; agrupar servicios por módulo | `AdministradorController.php:70`, `PanelController.php:112`, `AuditoriaController.php:65` | — |
| MD-29 | Fijar las versiones de `pecl install redis`, `pcov` y los paquetes apk | `docker/Dockerfile:25,37` | — |
| MD-30 / BD-23 | Publicar MySQL y Redis en 127.0.0.1; no pasar la contraseña de root en el *healthcheck* | `docker-compose.yml:82,87,96` | — |
| MD-32 | *Lock* por recurso o por intervalo en lugar de por institución; medir con 200 usuarios | `app/Services/PrestamoService.php:504` | — |
| MD-34 | Fijar las acciones de GitHub por SHA | `.github/workflows/ci.yml:56,58` | PM-MD-08 |
| MP-05 | HTTPS en QA y producción: bloque TLS, esquema forzado, HSTS y cookie segura (bloqueante para producción) | `docker/nginx.conf`, `.env.example:69`, `app/Providers/AppServiceProvider.php` | — |
| MP-06 | Extraer 5 validaciones en línea a Form Requests | `AuditoriaController.php:129`, `InstitucionController.php:76`, `MostradorController.php:91`, `RecursoController.php:223`, `ReporteController.php:72` | — |
| MP-10 | Renombrar los métodos que no son predicados (`mis`, `token`, `corta`, `dia`, `periodo`, `conAnio`, `de`, `enlace`, `detalle`, `registro`, `paraUsuario`, `datosServicio`, `unicoEnInstitucion`) | `SolicitudController`, `FormatoFecha`, `Iniciales`, `MenuNavegacion`, `ResumenAuditoria`, `ConsultaPrestamoService`, Requests | PM-MP-06 |
| MP-11 | Sustituir `$q`, `$u`, `$r`, `$e`, `$d`, `$a`, `$b`, `$v` por nombres descriptivos | `CatalogoService.php:77-128`, `ConsultaPrestamoService.php:95,145`, `TableroService.php:178-191`, `AuditoriaController.php:184`, `ImportacionCatalogoService.php:299` | — |
| MP-12 | Partir las 6 líneas de más de 120 columnas; agregar PHP_CodeSniffer al *hook* y al CI | `CatalogoService.php:77,78,96,127,128`, `ConsultaPrestamoService.php:92` | PM-MP-04 |
| MP-13 | Extraer los ternarios multilínea a métodos privados o `if` | `Prestamo.php:125-127`, `DisponibilidadRecursoService.php:168-173`, `partials/campo.blade.php:45-46` | — |
| MP-14 | Reducir `@description` a 3 líneas o menos (35 cabeceras) | `public/js/spei.js`, `RecursoController.php`, `PrestamoService.php`, `ReporteService.php`, `routes/web.php`, migraciones, entre otros | — |
| MP-15 | Actualizar `@lastModified` y `@version` | `app/Exceptions/ReglaNegocioException.php`, `routes/console.php` | — |
| MP-16 | Completar `@project` y `@see`; agregar cabecera a `.env.example` | `docker/nginx.conf`, `docker/php.ini`, `.env.example` | PM-MP-03 |
| MP-18 | Completar `@return` y `@param` | `0001_01_01_000002_create_failed_jobs_table.php:42,59`, `tests/Pest.php:72` | — |
| MP-19 | Casilla de 24 px, relleno de tarjeta de 16 px, encabezado de tabla `#1F2937`, *toast* verde con radio 8, peso SemiBold: corregir el CSS o enumerarlos en DT-028 | `public/css/spei.css` | PM-MP-12, C-07 |
| MP-20 | Barra inferior en menos de 640 px, si Raúl elige la opción A | `public/css/spei.css:3984`, `layouts/app.blade.php` | PM-MP-14 |
| MP-21 / BD-08 | Aceptar o rechazar las DT pendientes (DT-024…027, la enmienda de DT-028, DT-029…031) | `docs/decisiones/BBB_DT_Registro_v1.0.md` | Sección 10.1 |
| MP-22 | Unificar «Dashboard» y «Panel», y «Regresar» y «Volver» | `app/Support/MenuNavegacion.php`, vistas | — |
| MP-23 | Comentar o registrar los `catch` vacíos y llevar el script en línea a `spei.js` | `layouts/app.blade.php:44`; `public/js/spei.js:41,386,710,728` | PM-MP-08 |
| MP-24 | Documentar las funciones JavaScript de más de 15 líneas | `public/js/spei.js` | PM-MP-08 |
| MP-25 | Eliminar el andamiaje sin uso o registrar por qué se conserva | `tailwind.config.js`, `package.json`, `resources/js/app.js`, `resources/js/bootstrap.js`, `resources/css/app.css` | — |
| MP-26 | Usar el logotipo SVG a 48 px en escritorio y un favicon de 16 a 512 px | `layouts/app.blade.php:36,56`, `public/img/` | — |
| MP-27 | Registrar en DT-007 la ausencia de *refresh token* | `docs/decisiones/BBB_DT_Registro_v1.0.md` | PM-MP-17 |
| BD-09 | `fecha_actualizacion` con `useCurrent()->useCurrentOnUpdate()` o justificación en DT-030 | Migración aditiva sobre `prestamos` y `detalle_prestamo` | — |
| BD-11 | Registrar en DT la excepción de las dos migraciones existentes (no se renombran) | `2026_10_18_200001_create_prestamos_mvp_tables.php`, `2026_10_25_000001_normalizar_nombres_prestamos.php` | PM-BD-18 |
| BD-12 | Registrar `DDL_CHANGE` al migrar, si Raúl elige la opción b de C-08 | Listener de `MigrationEnded` (por crear) | PM-BD-15 |
| BD-13 | Completar las consultas a `KEY_COLUMN_USAGE` y `STATISTICS`; firmar los cuatro DDL | `docs/bd/BBB_DDL06_DiccionarioPrestamos_v1.0.md:33-44`, `docs/bd/BBB_DDL0*.md` | PM-BD-13 |
| BD-14 | Elaborar el DER del modelo físico real | `docs/diagramas/` (vacío) | PM-BD-08 |
| BD-15 | Fijar `maxmemory`, la política y `save` en Redis | `docker-compose.yml:94` | PM-BD-14 |
| BD-16 | Índice con `prestamos.fecha_creacion` al implementar RF-027 y RF-030, si el plan de ejecución lo pide | Migración futura | — |
| BD-17 | Corregir la referencia «MBD 4.4 y Tabla 31» (los roles están en la Tabla 29) | `database/migrations/2026_10_18_000004_create_usuarios_table.php:42` | — |
| BD-18 | Hacer que el scope falle cerrado sin contexto (relacionado con MD-05, MAYOR) | `app/Models/Scopes/AmbitoInstitucionScope.php` | — |
| BD-20 | Confirmar si los límites 10/500 son personalizados o datos de demostración; documentar la regla | Datos de `instituciones` y `planes` | — |
| BD-21 | Ampliar DT-016 con la excepción de COMMENT | `docs/decisiones/BBB_DT_Registro_v1.0.md` | PM-BD-17 |
| BD-22 | Tarea de limpieza de `tokens_qr` y caché de catálogo, si RNF-009 lo exige | `routes/console.php`, `app/Services/CatalogoService.php` | — |
