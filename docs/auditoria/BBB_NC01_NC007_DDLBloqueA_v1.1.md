# No conformidad y acción correctiva · NC-01

| Control documental | Valor |
|---|---|
| Documento | BBB_NC01_NC007_DDLBloqueA_v1.1 |
| Versión | 1.1 |
| Fecha | 27/10/2026 |
| Elaboración | Responsable de documentación del SPEI, a partir del reporte de seguimiento de `spei-auditor`; no autor del cambio revisado. |
| Aprobación del registro | Pendiente; propuesta sin ratificación ni cierre. |
| Clasificación | Propuesta de no conformidad y acción correctiva |
| Base normativa | MC BBB-CA-001 v1.2; MD BBB-MD-002 v1.2; plan de desarrollo vigente |
| Alcance | Control y aceptación de los DDL del bloque A en `feature/RF-022-gestion-prestamos` |

| Campo | Valor |
|---|---|
| Folio (1) | NC-007 (propuesto; alta/ratificación formal pendiente) |
| Fecha de detección (2) | 27/10/2026 |
| Detectado por (3) | `spei-auditor`, según reporte de seguimiento independiente |
| Ámbito / Proceso (4) | Base de datos / control y aceptación de los DDL del bloque A |
| Severidad propuesta | Mayor |
| Registro relacionado | RED-01-RF022-20261027; LV-01-RF022-20261027; H-05 |

## Descripción de la no conformidad (5)

El reporte del auditor indica que los DDL del bloque A fueron elaborados, pero su ejecución y aceptación
formal permanecen pendientes. Existe evidencia reportada de una secuencia de migraciones en una base
temporal (`migrate`, `rollback --step=6`, `migrate`, limpieza); esa prueba acredita la secuencia ejecutada
que el auditor informa, pero no documenta por sí misma la ejecución/aceptación formal de los entregables
DDL del bloque A.

## Requisito incumplido (6)

- MD 9.4 y 10.4: cambios de estructura mediante migraciones reproducibles y reversibles, con evidencia de verificación y entregables del bloque aceptados antes de cerrar la iteración.
- MC 6.5 y 8.2: tratar y documentar las no conformidades hasta verificar la eficacia de la acción correctiva.
- Plan vigente, `docs/plan/BBB_PlanDesarrollo_v1.0.md`, § 3–4: salida del bloque requiere migraciones aplicadas, evidencia vinculada y aceptación.

## Corrección inmediata (contención) (7)

Mantener el DDL y el bloque A como pendientes de aceptación. No declarar cierre del bloque, aceptación de
la base ni habilitación para integración por esta evidencia aislada. Conservar la secuencia de base temporal
como evidencia diferenciada y no presentarla como aceptación formal del DDL.

## Análisis de causa raíz (8)

**Hipótesis por confirmar mediante análisis de causa:** la elaboración documental, la prueba técnica de
migraciones y la aceptación formal se gestionaron como hitos separados, sin que la aceptación final
quedara vinculada a la evidencia de ejecución del DDL. La causa raíz no se considera confirmada con el
resumen del reporte disponible; completar el análisis antes del cierre.

## Acción correctiva (9)

1. Identificar las versiones exactas de los DDL del bloque A sujetos a aceptación y el alcance de su ejecución.
2. Registrar ejecución controlada, validaciones previas/posteriores, resultado del rollback y evidencia de limpieza; si se usa una base temporal, mantener visible esa limitación y el alcance probado.
3. Obtener y registrar la aceptación formal correspondiente, con identidad, fecha y referencia del entregable.
4. Vincular los resultados en el registro de iteración y actualizar la evidencia de revisión solo cuando se haya completado la verificación.
5. Verificar eficacia: confirmar que la ejecución es reproducible, la reversión está comprobada, los DDL coinciden con la estructura esperada y la aceptación quedó archivada.

## Responsable / Fecha compromiso (10)

| Responsable | Fecha compromiso |
|---|---|
| Raúl Treviño Licona / responsable de datos por confirmar | Pendiente de acordar; no asignada por el auditor ni por quien documenta. |

## Verificación de la eficacia (11)

Pendiente. La verificará `spei-auditor` u otro revisor independiente cuando se disponga de la evidencia
de ejecución y aceptación. No hay resultado de eficacia registrado.

## Estado y cierre (12–13)

| Campo | Valor |
|---|---|
| Estado (12) | Abierta — propuesta de NC-01; no cerrada |
| Fecha de cierre (13) | No aplica; sin cierre ni eficacia verificada |

## Elaboración

Propuesta redactada a partir del reporte/resumen de seguimiento de `spei-auditor`. La causa raíz permanece
como hipótesis y la eficacia no ha sido verificada.

## Control de versiones

| Versión | Fecha | Descripción |
|---|---|---|
| 1.1 | 27/10/2026 | Se actualiza la rama y la referencia a los registros de seguimiento. |
