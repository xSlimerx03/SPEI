# No conformidad y acción correctiva · NC-01

| Control documental | Valor |
|---|---|
| Documento | BBB_NC01_NC008_CIIntegracion |
| Versión | 1.0 |
| Fecha | 27/10/2026 |
| Elaboración | Responsable de documentación del SPEI, a partir del reporte de seguimiento de `spei-auditor`; no autor del cambio revisado. |
| Aprobación del registro | Pendiente; propuesta sin ratificación ni cierre. |
| Clasificación | Propuesta de no conformidad y acción correctiva |
| Base normativa | MC BBB-CA-001 v1.2; MD BBB-MD-002 v1.2; flujo operativo del proyecto |
| Alcance | CI y controles de integración de `feature/RF-011-gestion-prestamos` |

| Campo | Valor |
|---|---|
| Folio (1) | NC-008 (propuesto; alta/ratificación formal pendiente) |
| Fecha de detección (2) | 27/10/2026 |
| Detectado por (3) | `spei-auditor`, según reporte de seguimiento independiente |
| Ámbito / Proceso (4) | CI, control de configuración e integración de cambios |
| Severidad propuesta | Mayor |
| Registro relacionado | RED-01-RF011-20261027; LV-01-RF011-20261027; seguimiento CI / H-01 |

## Descripción de la no conformidad (5)

El reporte indica que las ejecuciones de CI GitHub en `main`/`develop` fallaron en Pest porque
`phpunit.xml` apuntaba al directorio `tests/Unit`, que no existe. La referencia fue corregida en el árbol
de trabajo, pero no hay una ejecución remota posterior con esa corrección. El reporte también deja pendientes
las ramas, los PR, la protección de ramas y la integración. Por tanto, no existe evidencia de CI verde
posterior a la corrección ni de integración autorizada para este entregable.

## Requisito incumplido (6)

- MD 10.1–10.3 y 10.4: integrar cambios mediante rama y PR, revisión independiente y CI satisfactorio; no integrar directamente a ramas protegidas; satisfacer los criterios de salida del incremento.
- CLAUDE.md, § 6–7, como resumen operativo del flujo del MD: CI verde y revisión antes de integración; Raúl registra la aceptación en EI-01.
- MC 6.5 y 8.2: documentar el incumplimiento y dar seguimiento a la acción correctiva hasta verificar eficacia.

## Corrección inmediata (contención) (7)

No presentar la corrección local como CI validado. No declarar CI remoto satisfactorio ni autorizar/afirmar
merge o integración mientras no exista ejecución remota verde y evidencia del flujo de revisión aplicable.
Conservar los fallos previos como antecedente y distinguirlos del resultado pendiente posterior a la corrección.

## Análisis de causa raíz (8)

**Hipótesis por confirmar mediante análisis de causa:** la configuración de Pest que referencia un
directorio inexistente no fue detectada antes de ejecutar el workflow, y la validación del arreglo no se
cerró con una ejecución remota. Los controles de ramas/PR/protección aún no están demostrados en el
expediente. La causa raíz definitiva requiere análisis y contraste con la configuración del repositorio y
los registros de GitHub; no se considera confirmada por el resumen disponible.

## Acción correctiva (9)

1. Ejecutar el workflow remoto con la corrección de `phpunit.xml` y conservar URL/identificador, rama,
   commit y resultado de cada job.
2. Si el workflow falla, corregir por el proceso controlado y repetirlo; no marcar el CI como aprobado
   hasta un resultado verde.
3. Completar y documentar ramas, PR, protección y reglas de integración requeridas, incluyendo la
   revisión independiente y las verificaciones obligatorias.
4. Integrar únicamente mediante el proceso autorizado una vez satisfechos los controles, y registrar
   la aceptación correspondiente sin retrofecharla.
5. Verificar eficacia: una ejecución remota verde sobre el commit corregido y evidencia verificable de
   que el proceso de PR/protección se aplica al cambio.

## Responsable / Fecha compromiso (10)

| Responsable | Fecha compromiso |
|---|---|
| Raúl Treviño Licona | Pendiente de acordar; no asignada por el auditor ni por quien documenta. |

## Verificación de la eficacia (11)

Pendiente. Requiere ejecución remota posterior al arreglo y evidencia de ramas/PR/protección/integración.
El auditor independiente deberá comprobar los resultados antes de proponer cierre. No hay verificación de
eficacia registrada.

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
| 1.0 | 27/10/2026 | Propuesta inicial de NC-01 por CI remoto y controles de integración pendientes. |
