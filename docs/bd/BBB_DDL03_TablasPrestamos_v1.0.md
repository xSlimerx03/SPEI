# DDL-03 · Creación de las tablas de préstamo (`prestamos`, `detalle_prestamo`, `tokens_qr`)

## 1. Encabezado

| Campo | Valor |
|---|---|
| Formato | DDL-03 |
| Nombre | Gestión de tablas |
| Versión | v1.0 (documento BBB_DDL03_TablasPrestamos_v1.0) |
| Fecha | 25/10/2026 |
| Autor | Raúl Treviño Licona (DBA del SPEI, desarrollador único, DT-018) |
| Aprobador | Raúl Treviño Licona (líder técnico) — pendiente de firma |
| Requisito asociado | RF-022, RF-023, RF-024, RF-028, RF-032, RF-034, RF-036, RS-006, RN-004 |
| Migración | `database/migrations/2026_10_18_200001_create_prestamos_mvp_tables.php` |

## 2. Identificación del cambio

| Campo | `prestamos` | `detalle_prestamo` | `tokens_qr` |
|---|---|---|---|
| Tipo de cambio | Creación | Creación | Creación |
| Esquema | spei | spei | spei |
| Motor | InnoDB | InnoDB | InnoDB |
| Charset | utf8mb4 | utf8mb4 | utf8mb4 |
| Collation | utf8mb4_unicode_ci | utf8mb4_unicode_ci | utf8mb4_unicode_ci |
| Ambiente | Desarrollo (aplicada el 18/10/2026); pendiente de Staging y Producción | Igual | Igual |
| Sección MBD | 4.6 | 4.7 | 4.8 |

## 3. Justificación

El MVP de préstamos (`docs/plan/BBB_MVP_v1.0.md`) necesita registrar solicitudes con varios recursos
(RF-022, RF-024), bloquear el inventario mientras la reserva está vigente (RF-025, RF-026), cancelar
y vencer reservas (RF-028, RF-032) y confirmar la entrega y la devolución con un código QR de un
solo uso (RF-034, RF-036). Las tres tablas siguen MBD 4.6–4.8 (Tablas 41–43) con las diferencias
registradas en DT-030: `id_institucion` en las tablas hijas (DT-008), `prestamos.motivo`,
`fecha_actualizacion` en `prestamos` y `detalle_prestamo`, y `tokens_qr.token` CHAR(64) con el hash
SHA-256 en lugar de VARCHAR(255). Se difieren a su RF `prestamos.justificacion_prioridad` (RF-030),
`detalle_prestamo.evidencia_url` (RF-039, evidencia fotográfica) y el valor `no_entregado` de
`condicion_devolucion` (RF-042); se agregarán con migración aditiva y su propio DDL-03. Las cascadas
siguen MBD Tabla 27 (DT-009).

## 4. Script DDL

Equivalente SQL de la migración, obtenido con `SHOW CREATE TABLE` en desarrollo. Los nombres de llaves
e índices de `detalle_prestamo` y `tokens_qr` se muestran con su nombre oficial; la corrección de
nombres está documentada en `BBB_DDL08_LlavesPrestamos_v1.0.md`.

```sql
CREATE TABLE prestamos (
    id_prestamo BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador del préstamo.',
    id_institucion BIGINT UNSIGNED NOT NULL COMMENT 'Institución propietaria.',
    id_usuario BIGINT UNSIGNED NOT NULL COMMENT 'Usuario solicitante.',
    fecha_hora_inicio DATETIME NOT NULL COMMENT 'Inicio programado, zona horaria de la aplicación.',
    fecha_hora_fin DATETIME NOT NULL COMMENT 'Devolución programada.',
    estado ENUM('pendiente', 'activo', 'devuelto', 'vencido', 'cancelado') NOT NULL
        DEFAULT 'pendiente' COMMENT 'Estado del préstamo.',
    motivo TEXT NOT NULL COMMENT 'Finalidad del préstamo.',
    fecha_hora_entrega_real DATETIME NULL COMMENT 'Confirmación de entrega.',
    fecha_hora_devolucion_real DATETIME NULL COMMENT 'Confirmación de devolución.',
    motivo_cancelacion TEXT NULL COMMENT 'Justificación de cancelación.',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de alta.',
    fecha_actualizacion TIMESTAMP NULL COMMENT 'Último cambio.',
    PRIMARY KEY (id_prestamo),
    KEY idx_prestamos_institucion_estado_inicio (id_institucion, estado, fecha_hora_inicio),
    KEY idx_prestamos_usuario (id_usuario),
    CONSTRAINT fk_prestamos_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_prestamos_usuarios FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Solicitudes y movimientos de préstamo por institución.';

ALTER TABLE prestamos
    ADD CONSTRAINT chk_prestamos_periodo CHECK (fecha_hora_fin > fecha_hora_inicio);

CREATE TABLE detalle_prestamo (
    id_detalle BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador del detalle.',
    id_institucion BIGINT UNSIGNED NOT NULL COMMENT 'Institución propietaria.',
    id_prestamo BIGINT UNSIGNED NOT NULL COMMENT 'Solicitud asociada.',
    id_recurso BIGINT UNSIGNED NOT NULL COMMENT 'Recurso solicitado.',
    condicion_entrega ENUM('bueno', 'regular', 'malo') NOT NULL DEFAULT 'bueno'
        COMMENT 'Condición inicial.',
    condicion_devolucion ENUM('bueno', 'regular', 'malo', 'danado') NULL COMMENT 'Condición final.',
    incidencia TEXT NULL COMMENT 'Observaciones de devolución.',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de alta.',
    fecha_actualizacion TIMESTAMP NULL COMMENT 'Último cambio.',
    PRIMARY KEY (id_detalle),
    UNIQUE KEY uk_detalle_prestamo_institucion_prestamo_recurso
        (id_institucion, id_prestamo, id_recurso),
    KEY idx_detalle_prestamo_prestamo (id_prestamo),
    KEY idx_detalle_prestamo_recurso (id_recurso),
    CONSTRAINT fk_detalle_prestamo_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_detalle_prestamo_prestamos FOREIGN KEY (id_prestamo)
        REFERENCES prestamos (id_prestamo) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detalle_prestamo_recursos FOREIGN KEY (id_recurso)
        REFERENCES recursos (id_recurso) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Recursos y condiciones de entrega y devolución.';

CREATE TABLE tokens_qr (
    id_token BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador del token.',
    id_institucion BIGINT UNSIGNED NOT NULL COMMENT 'Institución propietaria.',
    id_prestamo BIGINT UNSIGNED NOT NULL COMMENT 'Préstamo asociado.',
    tipo_token ENUM('entrega', 'devolucion') NOT NULL COMMENT 'Operación autorizada.',
    token CHAR(64) NOT NULL COMMENT 'Hash SHA-256 del código aleatorio.',
    fecha_expiracion DATETIME NOT NULL COMMENT 'Fin de vigencia del token.',
    usado TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Token consumido o sustituido.',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de emisión.',
    PRIMARY KEY (id_token),
    UNIQUE KEY uk_tokens_qr_token (token),
    KEY idx_tokens_qr_institucion_prestamo (id_institucion, id_prestamo),
    KEY idx_tokens_qr_prestamo (id_prestamo),
    CONSTRAINT fk_tokens_qr_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_tokens_qr_prestamos FOREIGN KEY (id_prestamo)
        REFERENCES prestamos (id_prestamo) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Hash de tokens de custodia de un solo uso.';
```

## 5. Script de rollback

Equivale a `php artisan migrate:rollback` de la migración (método `down()`), en orden de
dependencias. **Antes** se respalda la información (sección 8).

```sql
DROP TABLE IF EXISTS tokens_qr;
DROP TABLE IF EXISTS detalle_prestamo;
DROP TABLE IF EXISTS prestamos;
```

## 6. Validaciones previas

```sql
-- Las tablas no existen todavía.
SELECT TABLE_NAME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('prestamos', 'detalle_prestamo', 'tokens_qr');

-- Las tablas referenciadas existen y usan InnoDB.
SELECT TABLE_NAME, ENGINE
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('instituciones', 'usuarios', 'recursos');

-- Espacio disponible en el volumen de datos.
SELECT SUM(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024 AS mb_usados
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE();
```

Resultado esperado: la primera consulta sin filas; la segunda con las tres tablas en InnoDB.

## 7. Validaciones posteriores

```sql
-- Estructura, comentarios y motor.
SELECT TABLE_NAME, ENGINE, TABLE_COLLATION, TABLE_COMMENT
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('prestamos', 'detalle_prestamo', 'tokens_qr');

-- Ninguna columna sin comentario.
SELECT TABLE_NAME, COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('prestamos', 'detalle_prestamo', 'tokens_qr')
    AND COLUMN_COMMENT = '';

-- Llaves, CHECK e índices.
SELECT TABLE_NAME, CONSTRAINT_NAME, CONSTRAINT_TYPE
FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('prestamos', 'detalle_prestamo', 'tokens_qr')
ORDER BY TABLE_NAME, CONSTRAINT_NAME;
```

Evidencia del 25/10/2026 (desarrollo): tres tablas InnoDB `utf8mb4_unicode_ci` con comentario; cero
columnas sin comentario; restricciones PRIMARY, UNIQUE, FOREIGN KEY y `chk_prestamos_periodo` como en
la sección 4. La suite Pest (63 pruebas, `RefreshDatabase`) crea y usa las tablas en cada ejecución.

## 8. Observaciones

- Las tres tablas guardan datos institucionales e incluyen `id_institucion` (RS-006, DT-008); el
  modelo Eloquent aplica el Global Scope `PerteneceAInstitucionTrait`.
- Todas tienen `fecha_creacion`; `tokens_qr` no tiene `fecha_actualizacion` porque solo cambia `usado`
  al consumirse y el cambio queda en `logs_auditoria`.
- Los índices de consulta empiezan por `id_institucion`. `idx_prestamos_usuario`,
  `idx_detalle_prestamo_prestamo`, `idx_detalle_prestamo_recurso` e `idx_tokens_qr_prestamo` son
  índices de soporte de FK exceptuados por DT-026.
- `tokens_qr.token` guarda solo el hash SHA-256; el código en claro nunca se persiste (RS-006).
- No se ejecuta `DROP TABLE` en Staging o Producción sin respaldo previo
  (`mysqldump --single-transaction spei prestamos detalle_prestamo tokens_qr`) y aprobación del líder
  técnico.

## 9. Firmas

| Rol | Nombre | Firma | Fecha |
|---|---|---|---|
| DBA responsable | Raúl Treviño Licona | Pendiente | |
| Líder técnico | Raúl Treviño Licona | Pendiente | |
