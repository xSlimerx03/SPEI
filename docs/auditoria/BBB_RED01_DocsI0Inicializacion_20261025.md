# RED-01 · Revisión del rediseño Blade y del MVP de préstamos

| Campo | Valor |
|---|---|
| Documento | BBB_RED01_DocsI0Inicializacion_20261025 |
| Formato | RED-01 (Manual de Desarrollo v1.2) |
| Rama revisada | `docs/BBB-I0-inicializacion-repositorio` (cambios sin commit) |
| Revisor | Subagente `spei-auditor` (independiente, solo lectura; no es autor de los cambios) |
| Autor de los cambios | Raúl Treviño Licona (asistido) |
| Fecha | 25/10/2026: revisión inicial y re-verificación |
| Dictamen del revisor | Aceptar con correcciones. La decisión es de Raúl. |

Este registro archiva el dictamen del revisor para dar trazabilidad a las cabeceras que citan
"RED-01 del 25/10/2026" (MC 7.2). El revisor no aprueba ni declara terminado nada.

## 1. Herramientas (re-ejecutadas por el revisor)

| Herramienta | Resultado |
|---|---|
| Pint `--test` | PASS, 142 archivos |
| PHPStan nivel 6 | Sin errores |
| Pest | 63 pruebas, 529 aserciones, cobertura 94.1 % |
| `composer audit` | Sin vulnerabilidades |

## 2. Hallazgos de la revisión inicial y su estado en la re-verificación

| ID | Hallazgo | Severidad | Re-verificación | Atención posterior del autor |
|---|---|---|---|---|
| H-01 | Migración del MVP sin formatos DDL | NC mayor | Parcial: faltan los DDL del bloque A y las desviaciones frente a MBD (N-01) | DT-030; DDL-03 y DDL-06 corregidos; riesgo "DDL del bloque A" en el plan |
| H-02 | Sin CU-01 para los RF de préstamo; RT-01 desactualizada | NC mayor | Cerrado | — |
| H-03 | Trabajo de funcionalidad en una rama `docs/` | NC menor | Abierto | Raúl creó la rama `feature/RF-011-gestion-prestamos` y el commit `593db78` (25/10/2026) |
| H-04 | `bacon/bacon-qr-code` sin DT ni CREDITS | NC menor | Cerrado* | — |
| H-05 | Modal de "prioridad institucional" sin respaldo | NC menor | Cerrado | — |
| H-06 | Interfaz de marcas y sanciones sin RF | NC menor | Cerrado | — |
| H-07 | Contador del menú fuera del ámbito del operador | NC menor | Cerrado | — |
| H-08 | El administrador institucional podía cancelar | NC mayor | Cerrado | — |
| H-09 | Faltaban pruebas de ámbito, aislamiento y CSV | NC menor | Cerrado | — |
| H-10 | Cabeceras y PHPDoc incompletos en archivos del MVP | NC menor | Cerrado | — |
| H-11 | Líneas de más de 120 columnas | NC menor | Parcial: PHP corregido; 265 líneas en vistas Blade (MP 1.2.3.2, Tabla 10) | 0 líneas de más de 120 columnas en Blade; las 18 páginas renderizadas dan el mismo HTML salvo espacios en blanco; `view:cache` compila todas las vistas |
| H-12 | Closures sin tipos | NC menor | Parcial | Tipos de retorno agregados en servicios, modelo, `bootstrap/app.php` y migraciones del bloque A |
| H-13 | Funciones largas en `spei.js` | NC menor | Cerrado | — |
| H-14 | Textos de botones fuera de MP | NC menor | Cerrado | — |
| H-15 | Diálogos que dependen de JavaScript | Observación | Cerrado* | — |
| H-16 | Validación de mostrador sin `no-store` | NC menor | Cerrado | — |
| H-17 | `DB::table` sin filtro de institución | NC menor | Cerrado | — |
| H-18 | Tonos fuera de DT-028 | Observación | Cerrado* | — |
| H-19 | Foco invisible en el buscador; contraste del icono verde | NC menor | Cerrado | — |
| H-20 | Botón "Cancelar" en el diálogo; "Volver" a una ruta 403; literales de estado | NC menor | Parcial: literal en `FIELD(...)` | `orderByRaw` con parámetros de `EstadoPrestamo` |
| H-21 | Nombres de índices y FK fuera de la nomenclatura | NC menor | Cerrado | — |

\* Cierre formal sujeto a que Raúl acepte DT-026 (ampliada), DT-028 (enmienda) y DT-029.

## 3. Hallazgos nuevos de la re-verificación

| ID | Hallazgo | Severidad | Atención posterior del autor |
|---|---|---|---|
| N-01 | El esquema de préstamos se aparta de MBD 4.6–4.8 sin DT (`motivo`, `token` CHAR(64), faltan `no_entregado`, `evidencia_url` y `justificacion_prioridad`) | NC menor | DT-030 (propuesta); DDL-03 y DDL-06 citan la DT; RF-039 queda parcial en RT-01 |
| N-02 | CU-011 CA3 (concurrencia) sin prueba | NC menor | CP-036 en `AlcancePrestamosTest` (lock ocupado y doble reserva) |
| N-03 | Constructores públicos sin PHPDoc | NC menor | PHPDoc agregado a 34 constructores |
| N-04 | `@lastModified` desactualizados; descripción con "marcas" | Observación | Fechas sincronizadas; descripción corregida |
| N-05 | El RED-01 no estaba archivado | Observación | Este documento |
| N-06 | Cobertura baja por archivo (`ResumenAuditoria` 54.2 %, `DisponibilidadRecursoService` 72.2 %, `FormatoFecha` 78.6 %) | Observación | Pendiente |

La columna "Atención posterior del autor" describe correcciones hechas **después** del dictamen. El
revisor aún no las ha verificado.

## 4. Condiciones para proponer la integración (según el revisor)

1. Mover el trabajo a `feature/RF-022-…` y hacer commits con Conventional Commits (H-03). Raúl ya lo hizo en `feature/RF-011-gestion-prestamos`.
2. Partir las líneas Blade de más de 120 caracteres (H-11). Atendido por el autor, falta verificarlo.
3. Que Raúl acepte DT-026, DT-028 (enmienda), DT-029 y DT-030.
4. Tener un plan para los DDL del bloque A.
5. Que el revisor vuelva a verificar las correcciones posteriores.
