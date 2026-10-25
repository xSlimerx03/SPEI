# Matriz de Trazabilidad RT-01 — SPEI

Formato RT-01 (MD Anexo H). Una fila solo se marca **Aprobado** cuando tiene todas sus evidencias (MD 8.3).
Las filas de RNF, RN y RS se agregan conforme el analista las detalla.

Actualizada el 25/10/2026 (RED-01 H-02): RF del bloque A y del MVP de préstamos implementados con su CU-01 y
casos CP-01 (`docs/pruebas/BBB_CP01_InterfazYBloqueA_v1.0.md`). Ninguna fila está **Aprobada**: faltan la
revisión del auditor, el commit en rama `feature/` y la aceptación de Raúl en el EI-01. Los RF de préstamo se
adelantaron a su iteración por el MVP (`docs/plan/BBB_MVP_v1.0.md`).

| Req. (1) | Caso uso (2) | Módulo (3) | Diagrama (4) | Iteración (5) | PR/commit (6) | Prueba (7) | Resultado (8) | Liberación (9) |
|---|---|---|---|---|---|---|---|---|
| RF-001 | CU-001 | SaaS y suscripción | — | I1 (A) | Sin commit | PantallasPublicasTest | Implementado; evidencia parcial en EI01_Publicas | 1.0.0 |
| RF-002 | CU-001 | SaaS y suscripción | — | I1 (A) | Sin commit | PantallasPublicasTest | Implementado; evidencia parcial en EI01_Publicas | 1.0.0 |
| RF-003 | CU-002 | SaaS y suscripción | — | I1 (A) | Sin commit (rama de trabajo) | BloqueATest (CP-021) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-004 | CU-002 | SaaS y suscripción | — | I1 (A) | Sin commit (rama de trabajo) | BloqueATest, InterfazPrototipoTest (CP-015, CP-021) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-005 | CU-002 | SaaS y suscripción | — | I1 (A) | Sin commit (rama de trabajo) | BloqueATest (CP-024) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-006 | CU-002 | SaaS y suscripción | — | I1 (A) | Sin commit (rama de trabajo) | BloqueATest, InterfazPrototipoTest (CP-014, CP-021, CP-022, CP-024) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-007 | CU-003 | SaaS y suscripción | — | I1 (A) | Sin commit (rama de trabajo) | BloqueATest (CP-023) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-008 | CU-004 | SaaS y suscripción | — | I1 (A) | Sin commit | PantallasPublicasTest | Invitación y activación probadas; aislamiento pendiente | 1.0.0 |
| RF-009 | CU-006 | Estructura y catálogo | — | I2 (A) | Sin commit (rama de trabajo) | BloqueATest (CP-016, CP-017) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-010 | CU-007 | Estructura y catálogo | — | I2 (A) | Sin commit (rama de trabajo) | BloqueATest (CP-018, CP-019) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-011 | CU-007 | Estructura y catálogo | — | I2 (A) | Sin commit (rama de trabajo) | BloqueATest (CP-018, CP-025) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-012 | CU-008 | Estructura y catálogo | — | I2 (A) | Sin commit (rama de trabajo) | BloqueATest (CP-020) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-013 | CU-009, CU-010 | Estructura y catálogo | — | I2 (A) | Sin commit (rama de trabajo) | BloqueATest, InterfazPrototipoTest (CP-011, CP-018) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-014 | CU-009 | Estructura y catálogo | — | I2 (A) | Sin commit (rama de trabajo) | BloqueATest (CP-018) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-015 | CU-010 | Estructura y catálogo | — | I2 (A) | Sin commit (rama de trabajo) | InterfazPrototipoTest, BloqueATest (CP-011, CP-025) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-016 | — | Usuarios y roles | — | I3 (B) | — | — | Pendiente | 1.0.0 |
| RF-017 | — | Usuarios y roles | — | I3 (B) | — | — | Pendiente | 1.0.0 |
| RF-018 | — | Usuarios y roles | — | I3 (B) | — | — | Pendiente | 1.0.0 |
| RF-019 | — | Usuarios y roles | — | I3 (B) | — | — | Pendiente | 1.0.0 |
| RF-020 | — | Usuarios y roles | — | I3 (B) | — | — | Pendiente | 1.0.0 |
| RF-021 | — | Usuarios y roles | — | I3 (B) | — | — | Pendiente | 1.0.0 |
| RF-022 | CU-011 | Solicitudes y reservas | — | I4 (B) | Sin commit (rama de trabajo) | InterfazPrototipoTest (CP-002, CP-006 a CP-010) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-023 | CU-011 | Solicitudes y reservas | — | I4 (B) | Sin commit (rama de trabajo) | InterfazPrototipoTest (CP-006 a CP-010) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-024 | CU-011 | Solicitudes y reservas | — | I4 (B) | Sin commit (rama de trabajo) | InterfazPrototipoTest (CP-002) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-025 | CU-011 | Solicitudes y reservas | — | I4 (B) | Sin commit (rama de trabajo) | InterfazPrototipoTest (CP-002) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-026 | CU-011 | Solicitudes y reservas | — | I4 (B) | Sin commit (rama de trabajo) | InterfazPrototipoTest (CP-002) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-027 | — | Solicitudes y reservas | — | I4 (B) | — | — | Pendiente | 1.0.0 |
| RF-028 | CU-013 | Solicitudes y reservas | — | I5 (C) | Sin commit (rama de trabajo) | InterfazPrototipoTest, AlcancePrestamosTest (CP-005, CP-033) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-029 | CU-013 | Solicitudes y reservas | — | I5 (C) | Sin commit (rama de trabajo) | InterfazPrototipoTest, AlcancePrestamosTest (CP-005, CP-033) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-030 | — | Solicitudes y reservas | — | I5 (C) | — | — | Pendiente | 1.0.0 |
| RF-031 | CU-013 | Solicitudes y reservas | — | I5 (C) | Sin commit (rama de trabajo) | Sin prueba específica (la interfaz no ofrece prórroga) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-032 | CU-013 | Solicitudes y reservas | — | I5 (C) | Sin commit (rama de trabajo) | BloqueATest (CP-024) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-033 | — | Solicitudes y reservas | — | I5 (C) | — | — | Pendiente | 1.0.0 |
| RF-034 | CU-012 | Entrega y devolución | — | I6 (C) | Sin commit (rama de trabajo) | InterfazPrototipoTest, AlcancePrestamosTest (CP-002, CP-003, CP-032) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-035 | CU-012 | Entrega y devolución | — | I6 (C) | Sin commit (rama de trabajo) | InterfazPrototipoTest (CP-002, CP-003) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-036 | CU-012 | Entrega y devolución | — | I6 (C) | Sin commit (rama de trabajo) | InterfazPrototipoTest (CP-002) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-037 | CU-012 | Entrega y devolución | — | I6 (C) | Sin commit (rama de trabajo) | InterfazPrototipoTest (CP-002) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-038 | — | Entrega y devolución | — | I6 (C) | — | — | Pendiente | 1.0.0 |
| RF-039 | CU-012 | Entrega y devolución | — | I6 (C) | Sin commit (rama de trabajo) | InterfazPrototipoTest (CP-002) | Parcial: condición e incidencia; falta evidencia fotográfica (`evidencia_url`, DT-030) | 1.0.0 |
| RF-040 | — | Entrega y devolución | — | I6 (C) | — | — | Pendiente | 1.0.0 |
| RF-041 | — | Sanciones | — | I7 (D) | — | — | Pendiente | 1.0.0 |
| RF-042 | — | Sanciones | — | I7 (D) | — | — | Pendiente | 1.0.0 |
| RF-043 | — | Sanciones | — | I7 (D) | — | — | Pendiente | 1.0.0 |
| RF-044 | — | Sanciones | — | I7 (D) | — | — | Pendiente | 1.0.0 |
| RF-045 | — | Sanciones | — | I7 (D) | — | — | Pendiente | 1.0.0 |
| RF-046 | — | Notificaciones | — | I7 (D) | Sin commit (rama de trabajo) | InterfazPrototipoTest, BloqueATest (CP-013, CP-026) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-047 | — | Notificaciones | — | I7 (D) | — | — | Pendiente | 1.0.0 |
| RF-048 | — | Notificaciones | — | I7 (D) | — | — | Pendiente | 1.0.0 |
| RF-049 | — | Notificaciones | — | I7 (D) | — | — | Pendiente | 1.0.0 |
| RF-050 | — | Notificaciones | — | I7 (D) | — | — | Pendiente | 1.0.0 |
| RF-051 | CU-014 | Historial y reportes | — | I8 (D) | Sin commit (rama de trabajo) | InterfazPrototipoTest, AlcancePrestamosTest (CP-004, CP-032) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-052 | CU-015 | Historial y reportes | — | I8 (D) | Sin commit (rama de trabajo) | InterfazPrototipoTest, AlcancePrestamosTest, BloqueATest (CP-012, CP-027, CP-035) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-053 | CU-015 | Historial y reportes | — | I8 (D) | Sin commit (rama de trabajo) | InterfazPrototipoTest, AlcancePrestamosTest (CP-012, CP-035) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-054 | CU-015 | Historial y reportes | — | I8 (D) | Sin commit (rama de trabajo) | InterfazPrototipoTest, AlcancePrestamosTest (CP-012, CP-035) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
| RF-055 | — | Historial y reportes | — | I8 (D) | — | — | Pendiente | 1.0.0 |
| RF-056 | — | Historial y reportes | — | I8 (D) | — | — | Pendiente | 1.0.0 |
| RF-057 | — | Historial y reportes | — | I8 (D) | — | — | Pendiente | 1.0.0 |
| RF-058 | CU-014, CU-015 | Historial y reportes | — | I8 (D) | Sin commit (rama de trabajo) | AlcancePrestamosTest, InterfazPrototipoTest (CP-012, CP-034) | Implementado; pendiente de revisión del auditor y aceptación en EI-01 | 1.0.0 |
