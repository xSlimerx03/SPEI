# DDL-07 · Relaciones de las tablas originales del bloque A

## 1. Encabezado

| Campo | Valor |
|---|---|
| Formato | DDL-07 — Gestión de relaciones entre tablas |
| Nombre | Relaciones originales del bloque A |
| Versión | v1.0 |
| Fecha | 27/10/2026 |
| Autor | Raúl Treviño Licona |
| Aprobador | Raúl Treviño Licona (líder técnico) — pendiente de aceptación |
| Requisitos asociados | RS-006, RF-001, RF-002, RF-008, RF-009, RF-010, RF-014, RF-019, RF-045 a RF-050, RNF-005 |
| Migraciones | `2026_10_18_000001` a `2026_10_18_000007` |
| Decisiones relacionadas | DT-008, DT-009, DT-010, DT-011, DT-024, DT-026 |

## 2. Identificación de las relaciones

| # | Tabla origen | Tabla destino | Cardinalidad | Restricción | ON DELETE / ON UPDATE |
|---:|---|---|---|---|---|
| R1 | `areas` | `instituciones` | N:1 | `fk_areas_instituciones` | RESTRICT / CASCADE |
| R2 | `usuarios` | `instituciones` | N:1, nullable | `fk_usuarios_instituciones` | RESTRICT / CASCADE |
| R3 | `usuarios` | `areas` | N:1, nullable | `fk_usuarios_areas` | SET NULL / CASCADE |
| R4 | `recursos` | `instituciones` | N:1 | `fk_recursos_instituciones` | RESTRICT / CASCADE |
| R5 | `recursos` | `areas` | N:1 | `fk_recursos_areas` | RESTRICT / CASCADE |
| R6 | `logs_auditoria` | `usuarios` | N:1, nullable | `fk_logs_auditoria_usuarios` | RESTRICT / CASCADE |
| R7 | `logs_auditoria` | `instituciones` | N:1, nullable | `fk_logs_auditoria_instituciones` | RESTRICT / CASCADE |
| R8 | `notificaciones` | `instituciones` | N:1 | `fk_notificaciones_instituciones` | RESTRICT / CASCADE |
| R9 | `notificaciones` | `usuarios` | N:1 | `fk_notificaciones_usuarios` | CASCADE / CASCADE |
| — | `planes` | — | Tabla global | No tiene FK | — |

`areas.id_institucion` y `recursos.id_institucion` vinculan directamente con su institución. La coincidencia
entre tenant de `recursos` y el tenant de su área no está garantizada por FK compuesta en la migración:
debe validarse desde la capa de aplicación, tal como indica DT-008.

## 3. Justificación

Las relaciones y acciones se transcriben de las constraints de las migraciones. ON UPDATE CASCADE y las
acciones ON DELETE siguen MBD Tabla 27 conforme a DT-009. La relación a `usuarios` desde logs usa RESTRICT
(DT-010) y preserva la inalterabilidad de la bitácora. La relación nullable de `usuarios` a instituciones
es la excepción de `superadmin` definida por DT-011. `notificaciones` se elimina al borrar al usuario,
según Tabla 27.

## 4. Script DDL

En el SQL ejecutable principal, las restricciones se crean dentro de `CREATE TABLE` (DDL-03). A continuación
se deja la forma equivalente con `ALTER TABLE` para instalación de constraints sobre tablas ya creadas sin
ellas. No ejecutar ambos caminos sobre las mismas tablas.

```sql
ALTER TABLE areas
    ADD CONSTRAINT fk_areas_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion)
        ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE usuarios
    ADD CONSTRAINT fk_usuarios_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_usuarios_areas FOREIGN KEY (id_area_asignada)
        REFERENCES areas (id_area)
        ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE recursos
    ADD CONSTRAINT fk_recursos_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_recursos_areas FOREIGN KEY (id_area)
        REFERENCES areas (id_area)
        ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE logs_auditoria
    ADD CONSTRAINT fk_logs_auditoria_usuarios FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_logs_auditoria_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion)
        ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE notificaciones
    ADD CONSTRAINT fk_notificaciones_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_notificaciones_usuarios FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE;
```

## 5. Script de rollback

Si se instaló usando el script de ALTER de la sección 4, revertir en orden inverso. MySQL realiza commit
implícito con `ALTER TABLE`; no usar `ROLLBACK` de transacción como sustituto. Si se aplicó la definición
completa de DDL-03, la reversión de estructura se hace con `DROP TABLE` en DDL-03 y no se debe ejecutar
este script por separado.

```sql
ALTER TABLE notificaciones
    DROP FOREIGN KEY fk_notificaciones_usuarios,
    DROP FOREIGN KEY fk_notificaciones_instituciones;

ALTER TABLE logs_auditoria
    DROP FOREIGN KEY fk_logs_auditoria_instituciones,
    DROP FOREIGN KEY fk_logs_auditoria_usuarios;

ALTER TABLE recursos
    DROP FOREIGN KEY fk_recursos_areas,
    DROP FOREIGN KEY fk_recursos_instituciones;

ALTER TABLE usuarios
    DROP FOREIGN KEY fk_usuarios_areas,
    DROP FOREIGN KEY fk_usuarios_instituciones;

ALTER TABLE areas
    DROP FOREIGN KEY fk_areas_instituciones;
```

## 6. Validaciones previas

Antes de incorporar FK en tablas con filas preexistentes, validar duplicados para cualquier clave candidata,
nulos en columnas obligatorias y huérfanos. PK/UK, incluso cuando se representan en DDL-08, requieren
validación previa. Para tablas recién creadas y vacías, confirmar primero que no haya filas.

```sql
-- Verificar existencia de tablas involucradas.
SELECT TABLE_NAME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'instituciones', 'areas', 'usuarios', 'recursos',
      'logs_auditoria', 'notificaciones'
  )
ORDER BY TABLE_NAME;

-- Huérfanos por cada FK; deben ser cero. Los NULL opcionales no se consideran huérfanos.
SELECT COUNT(*) AS huerfanos_areas_instituciones
FROM areas a LEFT JOIN instituciones i ON i.id_institucion = a.id_institucion
WHERE a.id_institucion IS NOT NULL AND i.id_institucion IS NULL;

SELECT COUNT(*) AS huerfanos_usuarios_instituciones
FROM usuarios u LEFT JOIN instituciones i ON i.id_institucion = u.id_institucion
WHERE u.id_institucion IS NOT NULL AND i.id_institucion IS NULL;

SELECT COUNT(*) AS huerfanos_usuarios_areas
FROM usuarios u LEFT JOIN areas a ON a.id_area = u.id_area_asignada
WHERE u.id_area_asignada IS NOT NULL AND a.id_area IS NULL;

SELECT COUNT(*) AS huerfanos_recursos_instituciones
FROM recursos r LEFT JOIN instituciones i ON i.id_institucion = r.id_institucion
WHERE r.id_institucion IS NOT NULL AND i.id_institucion IS NULL;

SELECT COUNT(*) AS huerfanos_recursos_areas
FROM recursos r LEFT JOIN areas a ON a.id_area = r.id_area
WHERE r.id_area IS NOT NULL AND a.id_area IS NULL;

SELECT COUNT(*) AS huerfanos_logs_usuarios
FROM logs_auditoria l LEFT JOIN usuarios u ON u.id_usuario = l.id_usuario
WHERE l.id_usuario IS NOT NULL AND u.id_usuario IS NULL;

SELECT COUNT(*) AS huerfanos_logs_instituciones
FROM logs_auditoria l LEFT JOIN instituciones i ON i.id_institucion = l.id_institucion
WHERE l.id_institucion IS NOT NULL AND i.id_institucion IS NULL;

SELECT COUNT(*) AS huerfanos_notificaciones_instituciones
FROM notificaciones n LEFT JOIN instituciones i ON i.id_institucion = n.id_institucion
WHERE n.id_institucion IS NOT NULL AND i.id_institucion IS NULL;

SELECT COUNT(*) AS huerfanos_notificaciones_usuarios
FROM notificaciones n LEFT JOIN usuarios u ON u.id_usuario = n.id_usuario
WHERE n.id_usuario IS NOT NULL AND u.id_usuario IS NULL;
```

La consulta depende de la existencia de las siete tablas. Si se ejecuta en un esquema aún no migrado,
detenerse y ejecutar las comprobaciones aplicables de DDL-03; no interpretar error por tabla ausente como
validación superada.

## 7. Validaciones posteriores

El resultado esperado son nueve relaciones con las acciones de la sección 2. Se recomienda revisar las
columnas de `KEY_COLUMN_USAGE` y reglas de `REFERENTIAL_CONSTRAINTS`.

```sql
SELECT k.TABLE_NAME, k.CONSTRAINT_NAME, k.COLUMN_NAME, k.REFERENCED_TABLE_NAME,
       k.REFERENCED_COLUMN_NAME, r.DELETE_RULE, r.UPDATE_RULE
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS r
  ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
 AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
 AND r.TABLE_NAME = k.TABLE_NAME
WHERE k.CONSTRAINT_SCHEMA = DATABASE()
  AND k.TABLE_NAME IN (
      'areas', 'usuarios', 'recursos', 'logs_auditoria', 'notificaciones'
  )
  AND k.REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY k.TABLE_NAME, k.CONSTRAINT_NAME, k.ORDINAL_POSITION;
```

No se ejecutó ni se observó el resultado de esta consulta.

## 8. Observaciones

- La cardinalidad indica que varias filas origen pueden referenciar una fila destino; las columnas nullable
  permiten cero relación, no alteran la cardinalidad máxima.
- `planes.clave` y `instituciones.plan_suscripcion` tienen dominios ENUM compatibles, pero no existe FK
  entre tablas en la migración; validación en servicio según DT-024.
- Índices de soporte de FKs que no comienzan por `id_institucion` son excepciones listadas en DT-026:
  `idx_usuarios_area_asignada`, `idx_recursos_area`, `idx_logs_auditoria_usuario`,
  `idx_notificaciones_usuario_leida`.
- DDL describe estructura conforme a las migraciones originales; cambios en ambiente deben tramitarse
  con migración y el procedimiento definido, no manualmente en QA o producción.
- No se obtuvo extracción del servidor, por lo que las reglas indicadas provienen de los métodos Laravel
  `restrictOnDelete`, `nullOnDelete`, `cascadeOnDelete` y `cascadeOnUpdate` en los archivos fuente.

## 9. Firmas

| Rol | Nombre | Firma | Fecha |
|---|---|---|---|
| Responsable DBA | Sin asignar | Pendiente | |
| Revisión técnica | Sin asignar | Pendiente | |
