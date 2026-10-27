# DDL-04 · Triggers de marcas en `penalizaciones`

## 1. Encabezado

| Campo | Valor |
|---|---|
| Formato | DDL-04 |
| Nombre | Gestión de triggers |
| Versión | v1.0 (documento BBB_DDL04_TriggersPenalizaciones_v1.0) |
| Fecha | 27/10/2026 |
| Autor | Raúl Treviño Licona (DBA del SPEI, DT-018) |
| Aprobador | Raúl Treviño Licona (líder técnico) — pendiente de firma |
| Requisito asociado | RF-042, RF-044, RN-005 |
| Migración | `2026_10_27_000003_create_sanciones_tables` |

## 2. Identificación del cambio

| Campo | `trg_penalizaciones_after_insert` | `trg_penalizaciones_after_update` |
|---|---|---|
| Tipo de cambio | Creación | Creación |
| Tabla | penalizaciones | penalizaciones |
| Momento / evento | AFTER INSERT | AFTER UPDATE |
| Tabla afectada | usuarios.marcas_acumuladas | usuarios.marcas_acumuladas |
| Ambiente | Desarrollo (27/10/2026) | Desarrollo (27/10/2026) |

## 3. Justificación

MBD Tabla 7 indica para RF-042 "Trigger automático; UPDATE atómico en usuarios". El trigger garantiza que el
contador de marcas cambie en la misma sentencia que la penalización, sin depender de la aplicación. El bloqueo por
umbral, que depende de la matriz configurable, lo decide `SancionService` dentro de la misma transacción
(DT-036).

## 4. Script DDL

```sql
CREATE TRIGGER trg_penalizaciones_after_insert AFTER INSERT ON penalizaciones
FOR EACH ROW BEGIN
    IF NEW.exentada = 0 THEN
        UPDATE usuarios SET marcas_acumuladas = marcas_acumuladas + NEW.marcas_aplicadas
        WHERE id_usuario = NEW.id_usuario;
    END IF;
END;

CREATE TRIGGER trg_penalizaciones_after_update AFTER UPDATE ON penalizaciones
FOR EACH ROW BEGIN
    IF OLD.exentada = 0 AND NEW.exentada = 1 THEN
        UPDATE usuarios SET marcas_acumuladas = IF(
            marcas_acumuladas > NEW.marcas_aplicadas, marcas_acumuladas - NEW.marcas_aplicadas, 0
        )
        WHERE id_usuario = NEW.id_usuario;
    END IF;
END;
```

El `IF(...)` evita el desbordamiento de la columna `UNSIGNED` cuando el contador ya se reinició al cumplir una
sanción.

## 5. Script de rollback

```sql
DROP TRIGGER IF EXISTS trg_penalizaciones_after_update;
DROP TRIGGER IF EXISTS trg_penalizaciones_after_insert;
```

## 6. Validaciones previas

```sql
SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS
WHERE TRIGGER_SCHEMA = DATABASE() AND EVENT_OBJECT_TABLE = 'penalizaciones';
```

Resultado esperado: sin filas.

## 7. Validaciones posteriores

```sql
SELECT TRIGGER_NAME, ACTION_TIMING, EVENT_MANIPULATION FROM INFORMATION_SCHEMA.TRIGGERS
WHERE TRIGGER_SCHEMA = DATABASE() AND EVENT_OBJECT_TABLE = 'penalizaciones';
```

Evidencia del 27/10/2026: los dos triggers existen; las pruebas CP-045 (2 penalizaciones → 3 marcas) y CP-047
(exención → 0 marcas) verifican su efecto.

## 8. Observaciones

- MySQL no admite `COMMENT` en triggers; la descripción vive en este formato y en el diccionario (propuesta de
  ajuste al MBD en `docs/auditoria/BBB_PropuestaActualizacionManuales_v1.0.md`).
- El reinicio del contador al levantar una sanción lo hace `SancionService` (DT-036), no el trigger.

## 9. Firmas

| Rol | Nombre | Firma | Fecha |
|---|---|---|---|
| DBA responsable | Raúl Treviño Licona | Pendiente | |
| Líder técnico | Raúl Treviño Licona | Pendiente | |
