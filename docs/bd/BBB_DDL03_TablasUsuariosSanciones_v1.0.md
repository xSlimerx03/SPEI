# DDL-03 · Tablas de usuarios, sanciones, conversaciones e historial

## 1. Encabezado

| Campo | Valor |
|---|---|
| Formato | DDL-03 |
| Nombre | Gestión de tablas |
| Versión | v1.0 (documento BBB_DDL03_TablasUsuariosSanciones_v1.0) |
| Fecha | 27/10/2026 |
| Autor | Raúl Treviño Licona (DBA del SPEI, desarrollador único, DT-018) |
| Aprobador | Raúl Treviño Licona (líder técnico) — pendiente de firma |
| Requisito asociado | RF-016, RF-017, RF-018, RF-033, RF-041 a RF-044, RF-052, RN-005, RN-012, RS-006 |
| Migraciones | `2026_10_27_000001` a `2026_10_27_000006` |

## 2. Identificación del cambio

| # | Tipo | Tabla | Sección MBD | Migración |
|---|---|---|---|---|
| T1 | Creación | `dominios_rol` | — (DT-034) | `2026_10_27_000001_create_dominios_rol_table` |
| T2 | Modificación (columna) | `usuarios.fecha_registro` | 4.4 (DT-035) | `2026_10_27_000002_add_fecha_registro_to_usuarios_table` |
| T3 | Creación | `matriz_sanciones` | 4.10 | `2026_10_27_000003_create_sanciones_tables` |
| T4 | Creación | `penalizaciones` | 4.9 | `2026_10_27_000003_create_sanciones_tables` |
| T5 | Modificación (ENUM) | `detalle_prestamo.condicion_devolucion` + `no_entregado` | 4.7, Tabla 42 | `2026_10_27_000004_add_no_entregado_to_detalle_prestamo` |
| T6 | Creación | `conversaciones` | 4.13 | `2026_10_27_000005_create_conversaciones_table` |
| T7 | Creación | `historial_comprimido` | 4.14 | `2026_10_27_000006_create_historial_comprimido_table` |

Esquema `spei`, motor InnoDB, charset utf8mb4, collation utf8mb4_unicode_ci. Ambiente: Desarrollo (aplicadas,
revertidas y reaplicadas el 27/10/2026); pendiente Staging y Producción.

## 3. Justificación

Implementan los bloques B y D pendientes (auditoría del 26/10/2026, BD-01): alta de usuarios con mapeo de roles
por dominio (MD 5.4), estados de cuenta, sanciones automatizadas, diálogo previo y ciclo de vida del historial.
Las tablas siguen MBD 4.9, 4.10, 4.13 y 4.14 con `id_institucion` en las tablas del tenant (DT-008),
`fecha_actualizacion` donde hay cambios posteriores (MBD 5.3.8) y las cascadas de MBD Tabla 27 (DT-009).
`dominios_rol` y `usuarios.fecha_registro` no están en el MBD y se justifican en DT-034 y DT-035; el valor
`no_entregado` estaba diferido por DT-030.

## 4. Script DDL

```sql
CREATE TABLE dominios_rol (
    id_dominio_rol BIGINT UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador único de la regla.',
    id_institucion BIGINT UNSIGNED NOT NULL COMMENT 'Tenant al que pertenece (DT-008).',
    dominio VARCHAR(150) NOT NULL COMMENT 'Dominio o subdominio del correo, en minúsculas.',
    rol ENUM('alumno', 'docente') NOT NULL COMMENT 'Rol que se asigna a los correos del dominio.',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de alta.',
    fecha_actualizacion TIMESTAMP NULL COMMENT 'Último cambio.',
    PRIMARY KEY (id_dominio_rol),
    UNIQUE KEY uk_dominios_rol_institucion_dominio (id_institucion, dominio),
    CONSTRAINT fk_dominios_rol_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Reglas por institución que asignan el rol según el dominio del correo.';

ALTER TABLE usuarios
    ADD COLUMN fecha_registro DATETIME NULL
        COMMENT 'Fecha en que completó su registro; NULL = invitación pendiente.' AFTER activo;
UPDATE usuarios SET fecha_registro = fecha_creacion WHERE activo = 1;

CREATE TABLE matriz_sanciones (
    id_regla BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador único de la regla.',
    id_institucion BIGINT UNSIGNED NOT NULL COMMENT 'Tenant al que pertenece.',
    tipo_incidente ENUM('retraso', 'mal_estado', 'danado', 'no_entregado') NOT NULL
        COMMENT 'Tipo de incidente.',
    marcas_por_incidente INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Marcas por incidente.',
    umbral_bloqueo INT UNSIGNED NOT NULL DEFAULT 3 COMMENT 'Marcas acumuladas que bloquean.',
    dias_sancion INT UNSIGNED NOT NULL DEFAULT 7 COMMENT 'Días de bloqueo al alcanzar el umbral.',
    activo TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Si la regla está activa.',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de alta.',
    fecha_actualizacion TIMESTAMP NULL COMMENT 'Último cambio.',
    PRIMARY KEY (id_regla),
    UNIQUE KEY uk_matriz_sanciones_institucion_tipo_incidente (id_institucion, tipo_incidente),
    CONSTRAINT fk_matriz_sanciones_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Reglas configurables de sanción por institución y tipo de incidente.';

CREATE TABLE penalizaciones (
    id_penalizacion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador único de la penalización.',
    id_institucion BIGINT UNSIGNED NOT NULL COMMENT 'Tenant al que pertenece (DT-008).',
    id_usuario BIGINT UNSIGNED NOT NULL COMMENT 'Usuario sancionado.',
    id_prestamo BIGINT UNSIGNED NULL COMMENT 'Préstamo asociado.',
    tipo_penalizacion ENUM('retraso', 'mal_estado', 'danado', 'no_entregado') NOT NULL
        COMMENT 'Tipo de penalización.',
    motivo TEXT NOT NULL COMMENT 'Motivo de la sanción.',
    marcas_aplicadas INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Marcas aplicadas.',
    fecha_inicio_sancion DATE NULL COMMENT 'Inicio del bloqueo que provocó, si lo hubo.',
    fecha_fin_sancion DATE NULL COMMENT 'Fin del bloqueo que provocó, si lo hubo.',
    exentada TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Si fue exentada (RF-044).',
    motivo_exencion TEXT NULL COMMENT 'Motivo de la exención.',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de alta.',
    fecha_actualizacion TIMESTAMP NULL COMMENT 'Último cambio (exención).',
    PRIMARY KEY (id_penalizacion),
    KEY idx_penalizaciones_institucion_usuario_fecha (id_institucion, id_usuario, fecha_creacion),
    KEY idx_penalizaciones_institucion_tipo_fecha
        (id_institucion, tipo_penalizacion, fecha_creacion),
    KEY idx_penalizaciones_usuario (id_usuario),
    KEY idx_penalizaciones_prestamo (id_prestamo),
    CONSTRAINT fk_penalizaciones_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_penalizaciones_usuarios FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_penalizaciones_prestamos FOREIGN KEY (id_prestamo)
        REFERENCES prestamos (id_prestamo) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Sanciones aplicadas a usuarios finales por incidencias en sus préstamos.';

ALTER TABLE detalle_prestamo MODIFY condicion_devolucion
    ENUM('bueno', 'regular', 'malo', 'no_entregado', 'danado') NULL DEFAULT NULL
    COMMENT 'Condición final; no_entregado si el recurso no se devolvió.';

CREATE TABLE conversaciones (
    id_mensaje BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del mensaje.',
    id_institucion BIGINT UNSIGNED NOT NULL COMMENT 'Tenant al que pertenece (DT-008).',
    id_prestamo BIGINT UNSIGNED NOT NULL COMMENT 'Préstamo asociado.',
    id_usuario_emisor BIGINT UNSIGNED NOT NULL COMMENT 'Usuario que envía el mensaje.',
    mensaje TEXT NOT NULL COMMENT 'Contenido del mensaje.',
    leido TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Si fue leído por el destinatario.',
    fecha_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del mensaje.',
    PRIMARY KEY (id_mensaje),
    KEY idx_conversaciones_institucion_prestamo_fecha (id_institucion, id_prestamo, fecha_hora),
    KEY idx_conversaciones_prestamo (id_prestamo),
    KEY idx_conversaciones_usuario_emisor (id_usuario_emisor),
    CONSTRAINT fk_conversaciones_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_conversaciones_prestamos FOREIGN KEY (id_prestamo)
        REFERENCES prestamos (id_prestamo) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_conversaciones_usuarios FOREIGN KEY (id_usuario_emisor)
        REFERENCES usuarios (id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Mensajes entre solicitante y operadores previos a la entrega de una solicitud.';

CREATE TABLE historial_comprimido (
    id_historial BIGINT UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador único del histórico comprimido.',
    id_institucion BIGINT UNSIGNED NOT NULL COMMENT 'Tenant al que pertenece.',
    archivo_comprimido_url VARCHAR(255) NOT NULL
        COMMENT 'Ruta del archivo comprimido en el disco configurado.',
    periodo_inicio DATE NOT NULL COMMENT 'Inicio del periodo comprimido.',
    periodo_fin DATE NOT NULL COMMENT 'Fin del periodo comprimido.',
    fecha_eliminacion_programada DATE NOT NULL COMMENT 'periodo_fin + 3 años.',
    eliminado TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Si el archivo ya fue eliminado.',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de alta.',
    PRIMARY KEY (id_historial),
    UNIQUE KEY uk_historial_comprimido_institucion_periodo
        (id_institucion, periodo_inicio, periodo_fin),
    KEY idx_historial_comprimido_id_institucion_eliminacion
        (id_institucion, eliminado, fecha_eliminacion_programada),
    CONSTRAINT fk_historial_comprimido_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Archivos comprimidos del historial operativo y su eliminación programada (RN-012).';
```

Los triggers de `penalizaciones` están en `BBB_DDL04_TriggersPenalizaciones_v1.0.md`.

## 5. Script de rollback

`php artisan migrate:rollback --step=6` (métodos `down()`), en orden inverso. Antes del rollback, el conteo de
registros `no_entregado` debe ser cero; las migraciones 000006 y 000004 abortan antes de modificar el esquema si
aún existen. Respaldo previo obligatorio en Staging y Producción (sección 8).

```sql
SELECT COUNT(*) AS registros_no_entregado FROM detalle_prestamo
WHERE condicion_devolucion = 'no_entregado';
-- Continuar solo si el conteo es 0; no convertir no_entregado en danado.
DROP TABLE IF EXISTS historial_comprimido;
DROP TABLE IF EXISTS conversaciones;
ALTER TABLE detalle_prestamo MODIFY condicion_devolucion
    ENUM('bueno', 'regular', 'malo', 'danado') NULL DEFAULT NULL COMMENT 'Condición final.';
DROP TRIGGER IF EXISTS trg_penalizaciones_after_update;
DROP TRIGGER IF EXISTS trg_penalizaciones_after_insert;
DROP TABLE IF EXISTS penalizaciones;
DROP TABLE IF EXISTS matriz_sanciones;
ALTER TABLE usuarios DROP COLUMN fecha_registro;
DROP TABLE IF EXISTS dominios_rol;
```

## 6. Validaciones previas

```sql
SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('dominios_rol', 'matriz_sanciones', 'penalizaciones', 'conversaciones',
        'historial_comprimido');

SELECT COUNT(*) AS columnas FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'fecha_registro';
```

Resultado esperado: sin filas y 0 columnas.

## 7. Validaciones posteriores

```sql
SELECT TABLE_NAME, ENGINE, TABLE_COLLATION, TABLE_COMMENT FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('dominios_rol', 'matriz_sanciones', 'penalizaciones', 'conversaciones',
        'historial_comprimido');

SELECT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('dominios_rol', 'matriz_sanciones', 'penalizaciones', 'conversaciones',
        'historial_comprimido', 'usuarios')
    AND COLUMN_COMMENT = '';
```

Evidencia del 27/10/2026 (desarrollo, base temporal `spei_migration_check_20261027`): `migrate` →
`migrate:rollback --step=6` → `migrate` sin errores y limpieza de la base temporal. Las cinco tablas en InnoDB
`utf8mb4_unicode_ci` con comentario; Pest (85 pruebas, `RefreshDatabase`) crea y usa el esquema completo. La
prueba `cierra como no devuelto...` confirma que ambos `down()` se detienen sin borrar tablas ni cambiar el dato
cuando existe `no_entregado`.

## 8. Observaciones

- Todas las tablas del tenant llevan `id_institucion` (RS-006) y sus modelos aplican `PerteneceAInstitucionTrait`.
- `historial_comprimido` no tiene `fecha_actualizacion`: solo cambia `eliminado` y el cambio queda en auditoría.
- El índice `idx_historial_comprimido_id_institucion_eliminacion` inicia por `id_institucion` para respetar el
  prefijo tenant requerido por MBD.
- Índices de soporte de FK que no empiezan por `id_institucion` (DT-026): `idx_penalizaciones_usuario`,
  `idx_penalizaciones_prestamo`, `idx_conversaciones_prestamo`, `idx_conversaciones_usuario_emisor`.
- `historial_comprimido.archivo_comprimido_url` guarda la ruta relativa en el disco configurado
  (`SPEI_HISTORIAL_DISCO`); en producción se usará S3/MinIO como indica MBD 4.14.
- No se ejecuta `DROP TABLE` en Staging o Producción sin respaldo y aprobación del líder técnico.

## 9. Firmas

| Rol | Nombre | Firma | Fecha |
|---|---|---|---|
| DBA responsable | Raúl Treviño Licona | Pendiente | |
| Líder técnico | Raúl Treviño Licona | Pendiente | |
