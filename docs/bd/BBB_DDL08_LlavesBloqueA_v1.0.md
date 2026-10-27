# DDL-08 · Llaves de las tablas originales del bloque A

## 1. Encabezado

| Campo | Valor |
|---|---|
| Formato | DDL-08 — Gestión de llaves primarias, foráneas y únicas |
| Nombre | Llaves originales del bloque A |
| Versión | v1.0 |
| Fecha | 27/10/2026 |
| Autor | Raúl Treviño Licona |
| Aprobador | Raúl Treviño Licona (líder técnico) — pendiente de aceptación |
| Requisitos asociados | RS-006, RF-007, RF-009, RF-010, RF-014, RF-016, RF-017, RF-019, RF-045 a RF-050 |
| Migraciones | `2026_10_18_000001` a `2026_10_18_000007` |
| Decisiones relacionadas | DT-008, DT-009, DT-010, DT-012, DT-024, DT-026 |

## 2. Identificación de llaves

| Tabla | PK | UK | Índices y soporte FK |
|---|---|---|---|
| `planes` | `id_plan` | `uk_planes_clave (clave)` | — |
| `instituciones` | `id_institucion` | `uk_instituciones_dominio_correo (dominio_correo)` | `idx_instituciones_estado_fecha_corte (estado_suscripcion, fecha_corte)` |
| `areas` | `id_area` | `uk_areas_institucion_nombre_area (id_institucion, nombre_area)` | `idx_areas_institucion_activo (id_institucion, activo)`; UK cubre FK institución |
| `usuarios` | `id_usuario` | `uk_usuarios_correo_institucional (correo_institucional)` | `idx_usuarios_institucion_rol (id_institucion, rol)`; `idx_usuarios_area_asignada (id_area_asignada)` |
| `recursos` | `id_recurso` | `uk_recursos_institucion_numero_inventario (id_institucion, numero_inventario)`; `uk_recursos_institucion_numero_serie (id_institucion, numero_serie)` | `idx_recursos_institucion_estado (id_institucion, estado)`; `idx_recursos_area (id_area)` |
| `logs_auditoria` | `id_log` | — | `idx_logs_auditoria_institucion_fecha_hora (id_institucion, fecha_hora)`; `idx_logs_auditoria_usuario (id_usuario)` |
| `notificaciones` | `id_notificacion` | — | `idx_notificaciones_institucion_usuario_leida (id_institucion, id_usuario, leida)`; `idx_notificaciones_usuario_leida (id_usuario, leida)` |

Toda FK tiene índice cuyo prefijo incluye su columna. Índices que no inician por `id_institucion` son los
índices de soporte exceptuados por DT-026. La tabla global `planes` no contiene columna tenant y no se le
aplica la regla de prefijo.

## 3. Justificación

La matriz transcribe los nombres y columnas de los índices/keys declarados en las migraciones originales.
Se aplican identificadores `uk_<tabla>_<columnas>` y `fk_<origen>_<destino>`. La unicidad global de correo
se apoya en DT-012. Las UK tenant-scoped de áreas y recursos evitan duplicados dentro de una institución,
conforme a DT-008 y DT-024.

## 4. Script DDL

Las llaves están integradas en `CREATE TABLE` de DDL-03. Este script es una alternativa de creación de
llaves sobre tablas que ya tengan columnas compatibles y no tengan todavía esas llaves. No ejecutarlo
después de DDL-03; las validaciones anteriores de duplicados, nulos y huérfanos son obligatorias. Para
constraints FK, consultar además las validaciones previas de DDL-07.

```sql
ALTER TABLE planes
    ADD PRIMARY KEY (id_plan),
    ADD CONSTRAINT uk_planes_clave UNIQUE (clave);

ALTER TABLE instituciones
    ADD PRIMARY KEY (id_institucion),
    ADD CONSTRAINT uk_instituciones_dominio_correo UNIQUE (dominio_correo),
    ADD KEY idx_instituciones_estado_fecha_corte (estado_suscripcion, fecha_corte);

ALTER TABLE areas
    ADD PRIMARY KEY (id_area),
    ADD CONSTRAINT uk_areas_institucion_nombre_area UNIQUE (id_institucion, nombre_area),
    ADD KEY idx_areas_institucion_activo (id_institucion, activo),
    ADD CONSTRAINT fk_areas_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE usuarios
    ADD PRIMARY KEY (id_usuario),
    ADD CONSTRAINT uk_usuarios_correo_institucional UNIQUE (correo_institucional),
    ADD KEY idx_usuarios_institucion_rol (id_institucion, rol),
    ADD KEY idx_usuarios_area_asignada (id_area_asignada),
    ADD CONSTRAINT fk_usuarios_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_usuarios_areas FOREIGN KEY (id_area_asignada)
        REFERENCES areas (id_area) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE recursos
    ADD PRIMARY KEY (id_recurso),
    ADD CONSTRAINT uk_recursos_institucion_numero_inventario
        UNIQUE (id_institucion, numero_inventario),
    ADD CONSTRAINT uk_recursos_institucion_numero_serie
        UNIQUE (id_institucion, numero_serie),
    ADD KEY idx_recursos_institucion_estado (id_institucion, estado),
    ADD KEY idx_recursos_area (id_area),
    ADD CONSTRAINT fk_recursos_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_recursos_areas FOREIGN KEY (id_area)
        REFERENCES areas (id_area) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE logs_auditoria
    ADD PRIMARY KEY (id_log),
    ADD KEY idx_logs_auditoria_institucion_fecha_hora (id_institucion, fecha_hora),
    ADD KEY idx_logs_auditoria_usuario (id_usuario),
    ADD CONSTRAINT fk_logs_auditoria_usuarios FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_logs_auditoria_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE notificaciones
    ADD PRIMARY KEY (id_notificacion),
    ADD KEY idx_notificaciones_institucion_usuario_leida (id_institucion, id_usuario, leida),
    ADD KEY idx_notificaciones_usuario_leida (id_usuario, leida),
    ADD CONSTRAINT fk_notificaciones_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_notificaciones_usuarios FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE;
```

Este DDL presupone que las columnas son `NOT NULL` donde corresponde y que las PK aún no existen. En
columnas opcionales, los índices `UNIQUE` MySQL permiten múltiples NULL (p. ej. `numero_serie`), según
la semántica pretendida en la migración.

## 5. Script de rollback

Para revertir el script alternativo anterior, eliminar primero las FK, luego índices secundarios/únicos y
al final las PK. La reversión completa de DDL-03 elimina tablas; no combinar los dos enfoques. Los nombres
de `DROP INDEX` se especifican con `ON <tabla>`.

```sql
ALTER TABLE notificaciones
    DROP FOREIGN KEY fk_notificaciones_usuarios,
    DROP FOREIGN KEY fk_notificaciones_instituciones,
    DROP INDEX idx_notificaciones_usuario_leida,
    DROP INDEX idx_notificaciones_institucion_usuario_leida,
    DROP PRIMARY KEY;

ALTER TABLE logs_auditoria
    DROP FOREIGN KEY fk_logs_auditoria_instituciones,
    DROP FOREIGN KEY fk_logs_auditoria_usuarios,
    DROP INDEX idx_logs_auditoria_usuario,
    DROP INDEX idx_logs_auditoria_institucion_fecha_hora,
    DROP PRIMARY KEY;

ALTER TABLE recursos
    DROP FOREIGN KEY fk_recursos_areas,
    DROP FOREIGN KEY fk_recursos_instituciones,
    DROP INDEX idx_recursos_area,
    DROP INDEX idx_recursos_institucion_estado,
    DROP INDEX uk_recursos_institucion_numero_serie,
    DROP INDEX uk_recursos_institucion_numero_inventario,
    DROP PRIMARY KEY;

ALTER TABLE usuarios
    DROP FOREIGN KEY fk_usuarios_areas,
    DROP FOREIGN KEY fk_usuarios_instituciones,
    DROP INDEX idx_usuarios_area_asignada,
    DROP INDEX idx_usuarios_institucion_rol,
    DROP INDEX uk_usuarios_correo_institucional,
    DROP PRIMARY KEY;

ALTER TABLE areas
    DROP FOREIGN KEY fk_areas_instituciones,
    DROP INDEX idx_areas_institucion_activo,
    DROP INDEX uk_areas_institucion_nombre_area,
    DROP PRIMARY KEY;

ALTER TABLE instituciones
    DROP INDEX idx_instituciones_estado_fecha_corte,
    DROP INDEX uk_instituciones_dominio_correo,
    DROP PRIMARY KEY;

ALTER TABLE planes
    DROP INDEX uk_planes_clave,
    DROP PRIMARY KEY;
```

## 6. Validaciones previas

Antes de añadir PK/UK, comprobar duplicados; antes de PK y FK, detectar NULLs donde la columna es
obligatoria; antes de FK, comprobar huérfanos. Consultar aquí las tablas ya existentes. No ejecutar
ALTER si una restricción ya está creada. Todas las consultas deben devolver cero filas para habilitar
la restricción respectiva.

```sql
-- Duplicados de UK.
SELECT clave, COUNT(*) AS n FROM planes GROUP BY clave HAVING COUNT(*) > 1;
SELECT dominio_correo, COUNT(*) AS n FROM instituciones
GROUP BY dominio_correo HAVING COUNT(*) > 1;
SELECT id_institucion, nombre_area, COUNT(*) AS n FROM areas
GROUP BY id_institucion, nombre_area HAVING COUNT(*) > 1;
SELECT correo_institucional, COUNT(*) AS n FROM usuarios
GROUP BY correo_institucional HAVING COUNT(*) > 1;
SELECT id_institucion, numero_inventario, COUNT(*) AS n FROM recursos
GROUP BY id_institucion, numero_inventario HAVING COUNT(*) > 1;
SELECT id_institucion, numero_serie, COUNT(*) AS n FROM recursos
WHERE numero_serie IS NOT NULL
GROUP BY id_institucion, numero_serie HAVING COUNT(*) > 1;

-- Duplicados de las columnas que serán PK.
SELECT id_plan, COUNT(*) AS n FROM planes GROUP BY id_plan HAVING COUNT(*) > 1;
SELECT id_institucion, COUNT(*) AS n FROM instituciones
GROUP BY id_institucion HAVING COUNT(*) > 1;
SELECT id_area, COUNT(*) AS n FROM areas GROUP BY id_area HAVING COUNT(*) > 1;
SELECT id_usuario, COUNT(*) AS n FROM usuarios GROUP BY id_usuario HAVING COUNT(*) > 1;
SELECT id_recurso, COUNT(*) AS n FROM recursos GROUP BY id_recurso HAVING COUNT(*) > 1;
SELECT id_log, COUNT(*) AS n FROM logs_auditoria GROUP BY id_log HAVING COUNT(*) > 1;
SELECT id_notificacion, COUNT(*) AS n FROM notificaciones
GROUP BY id_notificacion HAVING COUNT(*) > 1;

-- NULLs en identificadores que serán PK y columnas NOT NULL de claves únicas/FK.
SELECT 'planes.id_plan' AS columna, COUNT(*) AS n FROM planes WHERE id_plan IS NULL
UNION ALL SELECT 'planes.clave', COUNT(*) FROM planes WHERE clave IS NULL
UNION ALL SELECT 'instituciones.id_institucion', COUNT(*) FROM instituciones WHERE id_institucion IS NULL
UNION ALL SELECT 'instituciones.dominio_correo', COUNT(*) FROM instituciones WHERE dominio_correo IS NULL
UNION ALL SELECT 'areas.id_area', COUNT(*) FROM areas WHERE id_area IS NULL
UNION ALL SELECT 'areas.id_institucion', COUNT(*) FROM areas WHERE id_institucion IS NULL
UNION ALL SELECT 'areas.nombre_area', COUNT(*) FROM areas WHERE nombre_area IS NULL
UNION ALL SELECT 'usuarios.id_usuario', COUNT(*) FROM usuarios WHERE id_usuario IS NULL
UNION ALL SELECT 'usuarios.correo_institucional', COUNT(*) FROM usuarios WHERE correo_institucional IS NULL
UNION ALL SELECT 'recursos.id_recurso', COUNT(*) FROM recursos WHERE id_recurso IS NULL
UNION ALL SELECT 'recursos.id_institucion', COUNT(*) FROM recursos WHERE id_institucion IS NULL
UNION ALL SELECT 'recursos.id_area', COUNT(*) FROM recursos WHERE id_area IS NULL
UNION ALL SELECT 'recursos.numero_inventario', COUNT(*) FROM recursos WHERE numero_inventario IS NULL
UNION ALL SELECT 'logs_auditoria.id_log', COUNT(*) FROM logs_auditoria WHERE id_log IS NULL
UNION ALL SELECT 'notificaciones.id_notificacion', COUNT(*) FROM notificaciones WHERE id_notificacion IS NULL
UNION ALL SELECT 'notificaciones.id_institucion', COUNT(*) FROM notificaciones WHERE id_institucion IS NULL
UNION ALL SELECT 'notificaciones.id_usuario', COUNT(*) FROM notificaciones WHERE id_usuario IS NULL;

-- Colisiones / restricciones ya existentes.
SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME, NON_UNIQUE
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'planes', 'instituciones', 'areas', 'usuarios', 'recursos',
      'logs_auditoria', 'notificaciones'
  )
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
```

Las validaciones de huérfanos por FK están en DDL-07 sección 6. Estas consultas no se ejecutaron.

## 7. Validaciones posteriores

Confirmar PK, UK y nombres de índices/columnas con `STATISTICS`; confirmar constraints referenciales con
`KEY_COLUMN_USAGE`. Verificar que todas las llaves esperadas estén presentes una vez y que no haya índices
de soporte omitidos.

```sql
SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'planes', 'instituciones', 'areas', 'usuarios', 'recursos',
      'logs_auditoria', 'notificaciones'
  )
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME,
       REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE CONSTRAINT_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'areas', 'usuarios', 'recursos', 'logs_auditoria', 'notificaciones'
  )
ORDER BY TABLE_NAME, CONSTRAINT_NAME, ORDINAL_POSITION;
```

Resultado esperado: las claves de la sección 2 y las relaciones de DDL-07. No hay evidencia de ejecución
adjunta en este documento.

## 8. Observaciones

- El DDL alternativo de sección 4 presupone tablas sin PK ni constraints; no se debe ejecutar tal cual
  sobre el esquema creado por DDL-03.
- `id_institucion` inicia los índices compuestos tenant-scoped. Los índices monocolumna de soporte de FK
  listados en DT-026 son excepciones, y no deben confundirse con índices de consulta.
- El nombre `idx_recursos_area` es el nombre literal de la migración aunque la regla general de MBD sugiere
  iniciar índices de consulta por institución; DT-026 lo identifica como índice de soporte de FK.
- La migración declara dos UK tenant-scoped en `recursos`; `numero_serie` nullable permite valores NULL
  múltiples. No se infiere que la base aplique normalización/case folding adicional a los datos.
- No se pudo extraer datos del servidor ni confirmar duplicados, nulos, huérfanos o llaves existentes.
  Los comandos incluidos son validaciones pendientes, no resultados.
- Los nombres y el detalle se basan en los archivos de migración; el ejemplar BBB-BD-004 v1.2 no estaba
  presente entre los documentos inspeccionados, así que no se afirma cotejo literal con sus Tablas 37–49.

## 9. Firmas

| Rol | Nombre | Firma | Fecha |
|---|---|---|---|
| Responsable DBA | Sin asignar | Pendiente | |
| Revisión técnica | Sin asignar | Pendiente | |
