# Plan de Desarrollo — SPEI liberación 1.0

| Campo | Valor |
|---|---|
| Documento | BBB_PlanDesarrollo_v1.0 |
| Versión | 1.0 |
| Fecha | 18/10/2026 |
| Autor y aprobador | Raúl Treviño Licona (desarrollador único, DT-018) |
| Base normativa | MD 2–4 y 10–15; MC 6–8; MP; MBD |

## 1. Situación de partida
- No existe código previo. El calendario original del MD (Tabla 10) situaba hoy la iteración I4.
- El alcance **no se reduce**: los 58 RF y los RNF, RN y RS aplicables forman parte de la 1.0 (MD 4.1).
- Se comprimen dos iteraciones del MD por semana, conservando el orden de dependencias de MD Tabla 13
  y la cadencia de martes a lunes (MD 4.1). La Transición se mantiene del 17 al 21 de noviembre.

## 2. Calendario replanificado

| Bloque | Periodo | Iteraciones MD | RF | Objetivo y tablas nuevas |
|---|---|---|---|---|
| **I0** | 18–20 oct | — (Elaboración) | — | Repositorio, Docker, CI, estándares, agentes, DT-001…DT-022 |
| **A** | 21–27 oct | I1 + I2 | RF-001…RF-015 | SaaS, suscripción, autenticación Sanctum, estructura y catálogo. `instituciones`, `usuarios`, `areas`, `recursos`, `logs_auditoria`. Vistas web responsivas de login y catálogo |
| **B** | 28 oct–3 nov | I3 + I4 | RF-016…RF-027 | Carga CSV de usuarios, roles, asistentes; solicitudes, disponibilidad, reservas concurrentes (RN-008). `prestamos`, `detalle_prestamo` |
| **C** | 4–10 nov | I5 + I6 | RF-028…RF-040 | Cancelaciones, diálogo; entrega y devolución con QR (escáner web con cámara del navegador, DT-022), comprobante PDF. `conversaciones`, `tokens_qr` |
| **D** | 11–16 nov | I7 + I8 | RF-041…RF-058 | Sanciones, notificaciones en el sistema y por correo, historial, reportes, exportaciones, pruebas de RNF y regresión total. `matriz_sanciones`, `penalizaciones`, `notificaciones`, `historial_comprimido` |
| **T** | 17–21 nov | Transición | — | Auditoría A-04, correcciones, prueba de humo, LI-01 y etiqueta `v1.0.0` |

Auditorías internas (MC Tabla 19), a cargo de `spei-auditor`: A-01 al cerrar los CU-01 del bloque A,
A-02 al cerrar el diseño de datos (fin del bloque B), A-03 al cerrar el desarrollo (fin del bloque D) y
A-04 antes de la entrega final.

## 3. Flujo de cada bloque

| Momento | Agente | Salida |
|---|---|---|
| Martes, inicio | `spei-orquestador` | `docs/iteraciones/<bloque>/BBB_PI01_<iter>_v1.0.md`, criterios de entrada |
| Análisis | `spei-analista` | CU-01 por RF y criterios de aceptación |
| Diseño de datos | `spei-datos` | Migraciones, DDL-03/07/08, diccionario (DDL-06), DER |
| Diagramas | `spei-documentador` | DCU, DSE, DES, DER + DG-01 |
| Construcción | `spei-backend` | Código en `feature/RF-XXX-*` |
| Pruebas | `spei-qa` | Pest, CP-01, cobertura, DEF-XXX |
| Revisión | `spei-auditor` | RED-01 por rama; NC-01 si aplica |
| Integración | `spei-devops` | Merge a `develop` con CI verde, despliegue a QA, prueba de humo |
| Lunes, cierre | `spei-orquestador` | EI-01, RT-01 actualizada, indicadores MC Tabla 18 |

Criterio de salida de cada bloque (MD Tabla 12): código integrado, migraciones aplicadas, pruebas
ejecutadas, cobertura ≥ 80 %, cero defectos bloqueantes, evidencia vinculada, documentación al día y
aceptación de Raúl en el EI-01.

## 4. Orden de implementación dentro de cada RF
1. CU-01 → 2. migración + DDL + diccionario → 3. modelo, policy, form request, servicio, controlador,
ruta → 4. vista Blade → 5. pruebas (unitaria, feature, aislamiento, concurrencia si
aplica) → 6. auditor → 7. RT-01.

## 5. Riesgos principales (MD Tabla 28 adaptada)

| Riesgo | P | I | Tratamiento |
|---|---|---|---|
| Alcance completo en 4 semanas con una persona | Alta | Alta | Dos iteraciones por semana, plantillas MP Anexo A, agentes en paralelo por capa, nada fuera de RT-01 |
| Laravel 11.x sin parches de seguridad | Alta | Media | **Cerrado**: migrado a 12.70 (DT-019) |
| Concurrencia en reservas | Media | Alta | Transacción + `lockForUpdate` + lock Redis + prueba concurrente desde el bloque B |
| QR temporal sin app móvil | Media | Alta | Escáner web (cámara del navegador, HTTPS) y captura manual del código; modo sin conexión pendiente de decisión (DT-022) |
| Formatos DDL del bloque A sin elaborar (7 migraciones, triggers `trg_usuarios_*`) | Alta | Media | Abierto (RED-01 25/10/2026): elaborar DDL-03/04/06/07/08 del bloque A con `spei-datos` antes de cerrar el EI-01 del bloque A; los de préstamos ya existen en `docs/bd/` |
| ERS no disponible en el repositorio | — | — | **Cerrado 18/10/2026:** SRS v1.0 copiado a `docs/requisitos/` y línea base fijada en DT-023 (`BBB_REQ01_LineaBase_v1.0.md`) |

## 6. Indicadores (MC Tabla 18)
Requisitos implementados (meta 100 % por bloque), casos de prueba superados (≥ 95 %), cobertura
backend (≥ 80 %), no conformidades cerradas (≥ 90 %), defectos tras la liberación (0).
