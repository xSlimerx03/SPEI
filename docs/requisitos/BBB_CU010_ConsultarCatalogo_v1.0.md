# CU-010 · Consultar catálogo e inventario

Formato CU-01 (MD Anexo D) · Versión 1.0 · 18/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-010 Consultar catálogo e inventario |
| Objetivo (2) | Cada rol ve la información que le corresponde: el usuario final, lo que puede solicitar; el administrador, el inventario completo. |
| Actores (3) | Principales: usuario final (alumno, docente) y administrador del sistema. Secundario: cliente de la API. |
| Disparador (4) | Apertura de "Catálogo" (`/catalogo`, `GET /api/v1/catalogo`) o de "Inventario" (`/recursos`). |
| Precondiciones (5) | P1. Sesión iniciada. |
| Postcondiciones (6) | Q1. Lista paginada (12 por página) filtrada según el rol. |
| Flujo principal (7) | **Catálogo (usuario final):** 1. El sistema lista en tarjetas los recursos `disponible`, activos y de áreas activas de su institución (RN-002). 2. Permite buscar por nombre o marca y filtrar por tipo y área. **Inventario (administrador):** 3. Lista todos los recursos de la institución con su etiqueta de estado, filtrable por estado, área, tipo y texto. El responsable de un área solo ve su área. |
| Flujos alternos (8) | A1. Sin resultados → estado vacío con el icono `inbox` y "No hay recursos disponibles". |
| Excepciones (9) | E1. Usuario final que abre el inventario → 403. E2. Detalle de un recurso no disponible para el usuario final → 404. |
| Reglas relacionadas (10) | RN-002. |
| Requisitos relacionados (11) | RF-015, RF-013, RNF-002, RNF-009, RS-006. |
| Criterios de aceptación (12) | CA1. El usuario final nunca ve recursos `prestado`, `bloqueado`, `mantenimiento`, inactivos o de áreas inactivas. CA2. El administrador ve los cuatro estados. CA3. Ningún rol ve recursos de otra institución. CA4. El catálogo responde en promedio en ≤ 2 s con 500 recursos (RNF-009). |
