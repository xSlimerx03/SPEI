# CU-006 · Gestionar áreas

Formato CU-01 (MD Anexo D) · Versión 1.0 · 18/10/2026 · Autor: Raúl Treviño Licona · Estado: propuesto

| Campo | Contenido |
|---|---|
| ID y nombre (1) | CU-006 Gestionar áreas |
| Objetivo (2) | Definir las dependencias que resguardan recursos prestables. |
| Actores (3) | Principal: administrador del sistema sin área asignada. |
| Disparador (4) | El administrador abre "Áreas". |
| Precondiciones (5) | P1. Sesión con rol `admin_sistema` e `id_area_asignada` nulo. P2. Suscripción `activa` para escribir. |
| Postcondiciones (6) | Q1. Área creada, editada, eliminada o con `activo` cambiado, siempre en la institución del actor. Q2. Auditoría `CREAR`, `ACTUALIZAR`, `ELIMINAR` o `CAMBIAR_ESTADO`. |
| Flujo principal (7) | 1. El sistema lista las áreas de la institución con su número de recursos. 2. "Crear": nombre y descripción. 3. El sistema valida el límite del plan (RN-010) y que el nombre sea único en la institución. 4. Guarda y audita. 5. "Editar": cambia nombre o descripción con las mismas validaciones. 6. "Desactivar" o "Activar": cambia `activo`; los recursos de un área inactiva no se muestran en el catálogo. |
| Flujos alternos (8) | A1. Eliminar un área sin recursos la borra físicamente; los usuarios asignados quedan sin área (MBD Tabla 27). |
| Excepciones (9) | E1. Límite alcanzado → "El plan contratado no permite más áreas." E2. Nombre repetido → "Ya existe un área con ese nombre." E3. Eliminar con recursos → "No se puede eliminar un área con recursos." E4. Área de otra institución → 404. E5. Responsable de área u otro rol → 403. |
| Reglas relacionadas (10) | RN-010, RN-011. |
| Requisitos relacionados (11) | RF-009, RNF-005, RS-006; DT-024. |
| Criterios de aceptación (12) | CA1. No se crea un área por encima de `limite_areas`. CA2. El nombre es único por institución, pero puede repetirse entre instituciones. CA3. El acceso a un área de otra institución responde 404. CA4. Un área inactiva oculta sus recursos del catálogo. CA5. No se elimina un área con recursos. |
