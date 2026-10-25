# DDL-08 · Normalización de nombres de llaves e índices de `detalle_prestamo` y `tokens_qr`

## 1. Encabezado

| Campo | Valor |
|---|---|
| Formato | DDL-08 |
| Nombre | Gestión de llaves primarias, foráneas y únicas |
| Versión | v1.0 (documento BBB_DDL08_LlavesPrestamos_v1.0) |
| Fecha | 25/10/2026 |
| Autor | Raúl Treviño Licona (DBA del SPEI, DT-018) |
| Aprobador | Raúl Treviño Licona (líder técnico) — pendiente de firma |
| Requisito asociado | RS-006, RF-024, RF-034, RF-036 |
| Migración | `database/migrations/2026_10_25_000001_normalizar_nombres_prestamos.php` |
| Origen | RED-01 del 25/10/2026, hallazgo H-21 |

## 2. Identificación del cambio

Tipo de cambio: **Modificación** (eliminación y creación controladas de cada FK; renombre de índices).
Ambiente: Desarrollo (aplicada y revertida el 25/10/2026); pendiente Staging y Producción.

| Tabla | Tipo | Nombre anterior | Nombre oficial | Columnas | Referencia (solo FK) |
|---|---|---|---|---|---|
| detalle_prestamo | UK | `uk_detalle_institucion_prestamo_recurso` | `uk_detalle_prestamo_institucion_prestamo_recurso` | id_institucion, id_prestamo, id_recurso | — |
| detalle_prestamo | FK | `fk_detalle_instituciones` | `fk_detalle_prestamo_instituciones` | id_institucion | instituciones.id_institucion |
| detalle_prestamo | FK | `fk_detalle_prestamos` | `fk_detalle_prestamo_prestamos` | id_prestamo | prestamos.id_prestamo |
| detalle_prestamo | FK | `fk_detalle_recursos` | `fk_detalle_prestamo_recursos` | id_recurso | recursos.id_recurso |
| detalle_prestamo | Índice | `idx_detalle_prestamo` | `idx_detalle_prestamo_prestamo` | id_prestamo | — |
| detalle_prestamo | Índice | `idx_detalle_recurso` | `idx_detalle_prestamo_recurso` | id_recurso | — |
| tokens_qr | FK | `fk_tokens_instituciones` | `fk_tokens_qr_instituciones` | id_institucion | instituciones.id_institucion |
| tokens_qr | FK | `fk_tokens_prestamos` | `fk_tokens_qr_prestamos` | id_prestamo | prestamos.id_prestamo |
| tokens_qr | Índice | `idx_tokens_institucion_prestamo` | `idx_tokens_qr_institucion_prestamo` | id_institucion, id_prestamo | — |
| tokens_qr | Índice | `idx_tokens_prestamo` | `idx_tokens_qr_prestamo` | id_prestamo | — |

Sin cambio: `pk` de las tres tablas, `uk_tokens_qr_token`, `fk_prestamos_*`, índices de `prestamos`
y `chk_prestamos_periodo`, que ya seguían la nomenclatura.

## 3. Justificación

MBD 6.3.3 exige `fk_<origen>_<destino>`, `uk_<tabla>_<cols>` e `idx_<tabla>_<cols>` con el nombre
completo de la tabla. La migración del MVP abrevió `detalle_prestamo` como `detalle` y `tokens_qr`
como `tokens`. El cambio es solo de nombres: no altera columnas, datos, unicidad, cascadas ni
cobertura de índices, y no afecta el código (ninguna consulta usa nombres de índice). Se hace con una
migración nueva para no reescribir una migración ya aplicada y conservar los datos de desarrollo.

## 4. Script DDL

MySQL no permite renombrar una FOREIGN KEY: se elimina y se crea con el nombre oficial sobre el mismo
índice. Los índices se renombran con `RENAME INDEX` (operación de metadatos).

```sql
ALTER TABLE detalle_prestamo
    DROP FOREIGN KEY fk_detalle_instituciones,
    ADD CONSTRAINT fk_detalle_prestamo_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    DROP FOREIGN KEY fk_detalle_prestamos,
    ADD CONSTRAINT fk_detalle_prestamo_prestamos FOREIGN KEY (id_prestamo)
        REFERENCES prestamos (id_prestamo) ON DELETE CASCADE ON UPDATE CASCADE,
    DROP FOREIGN KEY fk_detalle_recursos,
    ADD CONSTRAINT fk_detalle_prestamo_recursos FOREIGN KEY (id_recurso)
        REFERENCES recursos (id_recurso) ON DELETE RESTRICT ON UPDATE CASCADE,
    RENAME INDEX uk_detalle_institucion_prestamo_recurso
        TO uk_detalle_prestamo_institucion_prestamo_recurso,
    RENAME INDEX idx_detalle_prestamo TO idx_detalle_prestamo_prestamo,
    RENAME INDEX idx_detalle_recurso TO idx_detalle_prestamo_recurso;

ALTER TABLE tokens_qr
    DROP FOREIGN KEY fk_tokens_instituciones,
    ADD CONSTRAINT fk_tokens_qr_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    DROP FOREIGN KEY fk_tokens_prestamos,
    ADD CONSTRAINT fk_tokens_qr_prestamos FOREIGN KEY (id_prestamo)
        REFERENCES prestamos (id_prestamo) ON DELETE CASCADE ON UPDATE CASCADE,
    RENAME INDEX idx_tokens_institucion_prestamo TO idx_tokens_qr_institucion_prestamo,
    RENAME INDEX idx_tokens_prestamo TO idx_tokens_qr_prestamo;
```

## 5. Script de rollback

`php artisan migrate:rollback --step=1` ejecuta `down()`, que aplica las mismas sentencias en sentido
inverso. Un `ALTER TABLE` en MySQL confirma implícitamente: el rollback es este script, no `ROLLBACK`.

```sql
ALTER TABLE detalle_prestamo
    DROP FOREIGN KEY fk_detalle_prestamo_instituciones,
    ADD CONSTRAINT fk_detalle_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    DROP FOREIGN KEY fk_detalle_prestamo_prestamos,
    ADD CONSTRAINT fk_detalle_prestamos FOREIGN KEY (id_prestamo)
        REFERENCES prestamos (id_prestamo) ON DELETE CASCADE ON UPDATE CASCADE,
    DROP FOREIGN KEY fk_detalle_prestamo_recursos,
    ADD CONSTRAINT fk_detalle_recursos FOREIGN KEY (id_recurso)
        REFERENCES recursos (id_recurso) ON DELETE RESTRICT ON UPDATE CASCADE,
    RENAME INDEX uk_detalle_prestamo_institucion_prestamo_recurso
        TO uk_detalle_institucion_prestamo_recurso,
    RENAME INDEX idx_detalle_prestamo_prestamo TO idx_detalle_prestamo,
    RENAME INDEX idx_detalle_prestamo_recurso TO idx_detalle_recurso;

ALTER TABLE tokens_qr
    DROP FOREIGN KEY fk_tokens_qr_instituciones,
    ADD CONSTRAINT fk_tokens_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    DROP FOREIGN KEY fk_tokens_qr_prestamos,
    ADD CONSTRAINT fk_tokens_prestamos FOREIGN KEY (id_prestamo)
        REFERENCES prestamos (id_prestamo) ON DELETE CASCADE ON UPDATE CASCADE,
    RENAME INDEX idx_tokens_qr_institucion_prestamo TO idx_tokens_institucion_prestamo,
    RENAME INDEX idx_tokens_qr_prestamo TO idx_tokens_prestamo;
```

## 6. Validaciones previas

```sql
-- Existen las restricciones con el nombre anterior.
SELECT TABLE_NAME, CONSTRAINT_NAME, CONSTRAINT_TYPE
FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('detalle_prestamo', 'tokens_qr')
ORDER BY TABLE_NAME, CONSTRAINT_NAME;

-- Sin huérfanos que impidan recrear las FK.
SELECT COUNT(*) AS huerfanos
FROM detalle_prestamo AS d
LEFT JOIN prestamos AS p ON p.id_prestamo = d.id_prestamo
WHERE p.id_prestamo IS NULL;

SELECT COUNT(*) AS huerfanos
FROM tokens_qr AS t
LEFT JOIN prestamos AS p ON p.id_prestamo = t.id_prestamo
WHERE p.id_prestamo IS NULL;

-- Conteo de filas para comparar después.
SELECT
    (SELECT COUNT(*) FROM prestamos) AS prestamos,
    (SELECT COUNT(*) FROM detalle_prestamo) AS detalles,
    (SELECT COUNT(*) FROM tokens_qr) AS tokens;
```

Resultado en desarrollo (25/10/2026): nombres anteriores presentes y 27 / 27 / 2 filas antes del
cambio; la consulta de huérfanos se ejecutó después del cambio y devolvió 0 en ambas tablas (las FK
existentes ya impedían huérfanos).

## 7. Validaciones posteriores

```sql
SELECT TABLE_NAME, CONSTRAINT_NAME, UPDATE_RULE, DELETE_RULE
FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('prestamos', 'detalle_prestamo', 'tokens_qr')
ORDER BY TABLE_NAME, CONSTRAINT_NAME;

SELECT TABLE_NAME, INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columnas
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('detalle_prestamo', 'tokens_qr')
GROUP BY TABLE_NAME, INDEX_NAME
ORDER BY TABLE_NAME, INDEX_NAME;
```

Evidencia en desarrollo (25/10/2026):

1. `migrate` → todos los nombres oficiales de la sección 2; reglas CASCADE / RESTRICT iguales a las
   anteriores; mismas columnas por índice; 27 / 27 / 2 filas.
2. `migrate:rollback --step=1` → nombres anteriores restaurados.
3. `migrate` de nuevo → nombres oficiales.
4. Pest 63 pruebas en verde con `RefreshDatabase` (crea el esquema desde cero, incluida esta
   migración) y PHPStan nivel 6 sin errores.

## 8. Observaciones

- No hay pérdida de datos ni bloqueo prolongado: las tablas son pequeñas y `RENAME INDEX` solo cambia
  metadatos. En Producción se ejecuta en ventana de mantenimiento con respaldo previo.
- Los índices de soporte de FK que no empiezan por `id_institucion` quedan registrados en DT-026.
- Se actualizan DDL-03, DDL-07 y el diccionario (DDL-06).

## 9. Firmas

| Rol | Nombre | Firma | Fecha |
|---|---|---|---|
| DBA responsable | Raúl Treviño Licona | Pendiente | |
| Líder técnico | Raúl Treviño Licona | Pendiente | |
