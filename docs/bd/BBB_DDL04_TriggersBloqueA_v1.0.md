# DDL-04 · Triggers del bloque A: usuarios y bitácora

## 1. Encabezado

| Campo | Valor |
|---|---|
| Formato | DDL-04 — Gestión de triggers |
| Nombre | Validación de tenant y protección de bitácora |
| Versión | v1.0 |
| Fecha | 27/10/2026 |
| Autor | Raúl Treviño Licona |
| Aprobador | Raúl Treviño Licona (líder técnico) — pendiente de aceptación |
| Requisitos asociados | RN-001, RNF-005, RF-052, RF-053, RS-006 |
| Migraciones | `2026_10_18_000004_create_usuarios_table.php`; `2026_10_18_000006_create_logs_auditoria_table.php` |
| Decisiones relacionadas | DT-010, DT-011, DT-026 |
| Estado de ejecución | Scripts documentados; sin prueba de instalación ni ejecución |

## 2. Identificación del cambio

| Trigger | Tabla | Momento/evento | Función |
|---|---|---|---|
| `trg_usuarios_before_insert` | `usuarios` | BEFORE INSERT | Rechaza superadmin con institución y rol no-superadmin sin institución. |
| `trg_usuarios_before_update` | `usuarios` | BEFORE UPDATE | Impone la misma regla en cambios de rol/institución. |
| `trg_logs_auditoria_before_update` | `logs_auditoria` | BEFORE UPDATE | Rechaza toda modificación de un registro de auditoría. |
| `trg_logs_auditoria_before_delete` | `logs_auditoria` | BEFORE DELETE | Rechaza toda eliminación de un registro de auditoría. |

La creación original tiene lugar dentro de las migraciones identificadas en el encabezado. Los triggers no
se añaden a tablas diferentes ni se reemplaza el comportamiento existente.

## 3. Justificación

DT-011 permite que solo el `superadmin` carezca de institución. DT-026 registra que MySQL 8.4 no admite el
CHECK propuesto sobre la columna involucrada en una FK con acción referencial y traslada esta regla a triggers
con `SIGNAL SQLSTATE '45000'`. DT-010, en cumplimiento de RNF-005, hace `logs_auditoria` de solo inserción.

Las rutinas son triggers de fila simples: no abren transacciones ni modifican otras tablas. Por tanto, las
cláusulas `START TRANSACTION` y `EXIT HANDLER` de procedimientos almacenados no aplican aquí (no se crea SP);
incluir `START TRANSACTION` en estos triggers sería incompatible con las restricciones de MySQL.

## 4. Script DDL

Script equivalente a los cuerpos de las migraciones. Se ejecuta en una sesión MySQL conectada al esquema
correcto y después de crear las tablas indicadas por DDL-03. Las cadenas con `COMMENT` en el cuerpo registran
el propósito dado que MySQL no permite una cláusula `COMMENT` en `CREATE TRIGGER`.

```sql
CREATE TRIGGER trg_usuarios_before_insert
BEFORE INSERT ON usuarios
FOR EACH ROW
BEGIN
    IF (NEW.rol = 'superadmin' AND NEW.id_institucion IS NOT NULL)
        OR (NEW.rol <> 'superadmin' AND NEW.id_institucion IS NULL) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Solo el superadmin carece de institucion (DT-011).';
    END IF;
END;

CREATE TRIGGER trg_usuarios_before_update
BEFORE UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF (NEW.rol = 'superadmin' AND NEW.id_institucion IS NOT NULL)
        OR (NEW.rol <> 'superadmin' AND NEW.id_institucion IS NULL) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Solo el superadmin carece de institucion (DT-011).';
    END IF;
END;

CREATE TRIGGER trg_logs_auditoria_before_update
BEFORE UPDATE ON logs_auditoria
FOR EACH ROW
BEGIN
    /* Bitácora de solo inserción: RNF-005, DT-010. */
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'logs_auditoria es de solo insercion (RNF-005).';
END;

CREATE TRIGGER trg_logs_auditoria_before_delete
BEFORE DELETE ON logs_auditoria
FOR EACH ROW
BEGIN
    /* Bitácora de solo inserción: RNF-005, DT-010. */
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'logs_auditoria es de solo insercion (RNF-005).';
END;
```

En un cliente que requiera delimitadores explícitos, encapsular cada `CREATE TRIGGER ... BEGIN ... END`
con su directiva de delimitador de cliente; `DELIMITER` no es una sentencia SQL enviada por PDO. Laravel
instala los triggers mediante `DB::unprepared()` en cada instrucción individual.

## 5. Script de rollback

La reversión elimina primero los triggers explícitamente en orden inverso de creación. Ejecutar solo en un
entorno permitido y tras confirmar que el rollback de la migración correspondiente es el objetivo. La
migración original realiza este mismo DROP antes de eliminar su tabla.

```sql
DROP TRIGGER IF EXISTS trg_logs_auditoria_before_delete;
DROP TRIGGER IF EXISTS trg_logs_auditoria_before_update;
DROP TRIGGER IF EXISTS trg_usuarios_before_update;
DROP TRIGGER IF EXISTS trg_usuarios_before_insert;
```

## 6. Validaciones previas

Antes de crear triggers, confirmar que no existen ya en el esquema y que las tablas/columnas están presentes.
La operación de creación no es idempotente: si ya existen los nombres, no ejecutar el DDL otra vez.

```sql
SELECT TRIGGER_NAME, EVENT_OBJECT_TABLE, ACTION_TIMING, EVENT_MANIPULATION
FROM INFORMATION_SCHEMA.TRIGGERS
WHERE TRIGGER_SCHEMA = DATABASE()
  AND TRIGGER_NAME IN (
      'trg_usuarios_before_insert', 'trg_usuarios_before_update',
      'trg_logs_auditoria_before_update', 'trg_logs_auditoria_before_delete'
  );

SELECT TABLE_NAME, COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND (
      (TABLE_NAME = 'usuarios' AND COLUMN_NAME IN ('rol', 'id_institucion'))
      OR TABLE_NAME = 'logs_auditoria'
  )
ORDER BY TABLE_NAME, COLUMN_NAME;
```

Resultado requerido: ninguna fila en la primera consulta; las columnas consultadas deben existir en la
segunda. No se consultó servidor para producir resultados.

## 7. Validaciones posteriores

El inventario esperado son cuatro triggers con tabla, tiempo y evento de la sección 2. Revisar además el
cuerpo almacenado con `SHOW CREATE TRIGGER` y verificar su ejecución únicamente en base desechable de pruebas.

```sql
SELECT TRIGGER_NAME, EVENT_OBJECT_TABLE, ACTION_TIMING, EVENT_MANIPULATION, ACTION_STATEMENT
FROM INFORMATION_SCHEMA.TRIGGERS
WHERE TRIGGER_SCHEMA = DATABASE()
  AND TRIGGER_NAME IN (
      'trg_usuarios_before_insert', 'trg_usuarios_before_update',
      'trg_logs_auditoria_before_update', 'trg_logs_auditoria_before_delete'
  )
ORDER BY TRIGGER_NAME;

SHOW CREATE TRIGGER trg_usuarios_before_insert;
SHOW CREATE TRIGGER trg_usuarios_before_update;
SHOW CREATE TRIGGER trg_logs_auditoria_before_update;
SHOW CREATE TRIGGER trg_logs_auditoria_before_delete;
```

Pruebas sugeridas (no ejecutadas aquí): (1) INSERT con `rol='superadmin'` e institución no nula y con rol de
tenant e institución nula deben fallar con SQLSTATE 45000; (2) UPDATE que produzca esas combinaciones debe
fallar; (3) UPDATE y DELETE de un log deben fallar. Las combinaciones coherentes deben seguir permitidas
por la regla del trigger, sujetas al resto de constraints.

## 8. Observaciones

- La fuente original no hace `START TRANSACTION` ni declara `EXIT HANDLER`; son innecesarios para estos
  triggers sin cambios de datos externos y no pueden añadirse de forma equivalente a la migración sin
  cambiar el código, que no forma parte del alcance.
- `logs_auditoria` conserva restricciones FK `RESTRICT` para evitar que una acción de borrado actualice
  el log, coherente con DT-010.
- La migración de logs señala que la depuración del ciclo de vida prevista por RN-012 podrá desactivar
  triggers; esa operación excepcional no se documenta como una forma ordinaria de UPDATE/DELETE.
- No se hizo `docker compose exec app php artisan migrate`, `migrate:rollback` ni prueba SQL. No se afirma
  existencia actual de los triggers ni resultado de ejecución.
- MySQL no ofrece `COMMENT` declarativo en triggers; la descripción se mantiene en este documento y en
  el comentario SQL del cuerpo de los triggers de logs.

## 9. Firmas

| Rol | Nombre | Firma | Fecha |
|---|---|---|---|
| Responsable DBA | Sin asignar | Pendiente | |
| Revisión técnica | Sin asignar | Pendiente | |
