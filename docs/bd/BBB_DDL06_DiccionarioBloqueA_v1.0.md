# DDL-06 · Diccionario de datos del bloque A

## 1. Encabezado

| Campo | Valor |
|---|---|
| Formato | DDL-06 — Actualización del diccionario de datos |
| Nombre | Diccionario de las siete tablas originales del bloque A |
| Versión | v1.0 |
| Fecha | 27/10/2026 |
| Autor | Raúl Treviño Licona |
| Aprobador | Raúl Treviño Licona (líder técnico) — pendiente de aceptación |
| Requisitos asociados | RF-001 a RF-015, RF-019 a RF-021, RF-045 a RF-050, RF-052, RF-053, RN-001, RN-010, RNF-004, RNF-005, RNF-006, RS-006 |
| Fuente | Migraciones `2026_10_18_000001` a `2026_10_18_000007`; DDL-03 de este bloque |
| Estado de extracción | Fichas derivadas del código fuente; no generadas en una conexión `INFORMATION_SCHEMA` |

## 2. Identificación del cambio

Este diccionario documenta `planes`, `instituciones`, `areas`, `usuarios`, `recursos`, `logs_auditoria` y
`notificaciones`. No modifica otros diccionarios del repositorio. La DDL-06 normativa debe reflejar las
columnas, tipos, defaults, comentarios, claves e índices presentes en el esquema efectivo; en esta
elaboración no se dispuso de una extracción de base de datos. Para cerrar esa diferencia, ejecutar las
consultas de la sección 4 en el ambiente autorizado y cotejar el resultado contra estas fichas.

## 3. Justificación

La descripción es necesaria para trazabilidad de tablas y constraints de las siete migraciones del bloque A.
Se reflejan las decisiones documentadas: extensión de suscripción y dominio (DT-024), multi-tenancy
(DT-008), cascadas (DT-009), inalterabilidad (DT-010), excepción de NULL para superadmin (DT-011),
unicidad de correo (DT-012), exclusión de tablas de autenticación no requeridas (DT-016) y triggers/
índices de soporte (DT-026).

## 4. Script de extracción

Estas consultas extraen estructura del esquema en el que se ejecutan. La consulta de índices usa
`STATISTICS`; las relaciones usan `KEY_COLUMN_USAGE`. La extracción resultante debe adjuntarse como
evidencia cuando se ejecute; no hay resultado adjunto en este documento.

```sql
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT,
       EXTRA, COLUMN_COMMENT, ORDINAL_POSITION
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'planes', 'instituciones', 'areas', 'usuarios', 'recursos',
      'logs_auditoria', 'notificaciones'
  )
ORDER BY TABLE_NAME, ORDINAL_POSITION;

SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME,
       REFERENCED_COLUMN_NAME, ORDINAL_POSITION
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE CONSTRAINT_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'planes', 'instituciones', 'areas', 'usuarios', 'recursos',
      'logs_auditoria', 'notificaciones'
  )
ORDER BY TABLE_NAME, CONSTRAINT_NAME, ORDINAL_POSITION;

SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME,
       COLLATION, CARDINALITY, INDEX_TYPE
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'planes', 'instituciones', 'areas', 'usuarios', 'recursos',
      'logs_auditoria', 'notificaciones'
  )
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
```

### Ejemplo de JSON (ilustrativo; no es evidencia extraída)

```json
{
  "table": "areas",
  "columns": [
    {
      "name": "id_area",
      "column_type": "bigint unsigned",
      "nullable": false,
      "default": null,
      "extra": "auto_increment",
      "comment": "Identificador único del área."
    },
    {
      "name": "id_institucion",
      "column_type": "bigint unsigned",
      "nullable": false,
      "default": null,
      "extra": "",
      "comment": "Tenant al que pertenece."
    }
  ],
  "keys": [
    {
      "name": "fk_areas_instituciones",
      "column": "id_institucion",
      "references": "instituciones.id_institucion",
      "delete_rule": "RESTRICT",
      "update_rule": "CASCADE"
    },
    {
      "name": "uk_areas_institucion_nombre_area",
      "columns": ["id_institucion", "nombre_area"],
      "unique": true
    }
  ],
  "evidence_status": "example_only_not_extracted"
}
```

## 5. Script de rollback

El diccionario es documental. El rollback consiste en retirar este archivo si así se autoriza; no debe
ejecutarse DDL de base de datos para revertirlo. El esquema se revierte únicamente mediante las migraciones
reversibles correspondientes y siguiendo MD 9.4.

```text
Acción documental de rollback: restaurar/eliminar este archivo mediante control de versiones.
Acción de base de datos: ninguna.
```

## 6. Validaciones previas

Antes de obtener una extracción, confirmar esquema y existencia de tablas sin suponer el resultado. Si las
tablas no existen, no se puede generar un diccionario físico de ellas.

```sql
SELECT DATABASE() AS esquema_actual;

SELECT TABLE_NAME, ENGINE, TABLE_COLLATION
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'planes', 'instituciones', 'areas', 'usuarios', 'recursos',
      'logs_auditoria', 'notificaciones'
  )
ORDER BY TABLE_NAME;
```

Resultado esperado para completar el documento: siete tablas y motor InnoDB. No se ejecutó la validación.

## 7. Validaciones posteriores

Tras ejecutar la extracción en el ambiente autorizado, verificar siete fichas, comentario no vacío para
cada tabla y columna, coincidencia de tipos/default/nullability, llaves y las acciones referenciales
indicadas en DDL-07/08. La consulta de columnas debe devolver todas las columnas documentadas en la sección 8.

```sql
SELECT TABLE_NAME, COUNT(*) AS columnas,
       SUM(CASE WHEN COLUMN_COMMENT IS NULL OR COLUMN_COMMENT = '' THEN 1 ELSE 0 END) AS sin_comentario
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'planes', 'instituciones', 'areas', 'usuarios', 'recursos',
      'logs_auditoria', 'notificaciones'
  )
GROUP BY TABLE_NAME
ORDER BY TABLE_NAME;

SELECT TABLE_NAME, TRIGGER_NAME
FROM INFORMATION_SCHEMA.TRIGGERS
WHERE TRIGGER_SCHEMA = DATABASE()
  AND EVENT_OBJECT_TABLE IN ('usuarios', 'logs_auditoria')
ORDER BY TABLE_NAME, TRIGGER_NAME;
```

No se anexan resultados y no se afirma que estas validaciones hayan pasado.

## 8. Observaciones

Fichas a continuación: tipos/nullability/default derivan del código de migraciones; «—» significa que no
se especificó default explícito en el código. Para la equivalencia física MySQL, las consultas de la sección
4 son la fuente final, por ejemplo `COLUMN_DEFAULT` de timestamps con `useCurrent()`.

### Ficha 1 · `planes`

| Columna | Tipo | Nulo | Default | Restricción/nota |
|---|---|---:|---|---|
| `id_plan` | BIGINT UNSIGNED | No | AUTO_INCREMENT | PK |
| `clave` | ENUM('basico','pro','enterprise') | No | — | UK `uk_planes_clave` |
| `nombre` | VARCHAR(100) | No | — | Nombre comercial |
| `monto_mensual` | DECIMAL(10,2) | No | — | Precio mensual MXN |
| `limite_areas` | INT UNSIGNED | No | — | CHECK > 0 |
| `limite_recursos` | INT UNSIGNED | No | — | CHECK > 0 |
| `duracion_dias` | INT UNSIGNED | No | 30 | CHECK > 0 |
| `activo` | TINYINT(1) | No | 1 | Disponible para contratación |
| `fecha_creacion` | TIMESTAMP | No | CURRENT_TIMESTAMP | Alta |
| `fecha_actualizacion` | TIMESTAMP | No | CURRENT_TIMESTAMP, ON UPDATE CURRENT_TIMESTAMP | Actualización |

### Ficha 2 · `instituciones`

| Columna | Tipo | Nulo | Default | Restricción/nota |
|---|---|---:|---|---|
| `id_institucion` | BIGINT UNSIGNED | No | AUTO_INCREMENT | PK |
| `nombre_institucion` | VARCHAR(150) | No | — | Nombre oficial |
| `dominio_correo` | VARCHAR(100) | No | — | UK `uk_instituciones_dominio_correo` |
| `plan_suscripcion` | ENUM('basico','pro','enterprise') | No | 'basico' | Copia del plan |
| `fecha_inicio` | DATE | No | — | Vigencia |
| `fecha_corte` | DATE | No | — | CHECK `>= fecha_inicio` |
| `estado_suscripcion` | ENUM('activa','solo_lectura','suspendida','cancelada') | No | 'activa' | Estado |
| `limite_areas` | INT UNSIGNED | No | 3 | CHECK > 0 |
| `limite_recursos` | INT UNSIGNED | No | 100 | CHECK > 0 |
| `fecha_creacion` | TIMESTAMP | No | CURRENT_TIMESTAMP | Alta |
| `fecha_actualizacion` | TIMESTAMP | No | CURRENT_TIMESTAMP, ON UPDATE CURRENT_TIMESTAMP | Actualización |

### Ficha 3 · `areas`

| Columna | Tipo | Nulo | Default | Restricción/nota |
|---|---|---:|---|---|
| `id_area` | BIGINT UNSIGNED | No | AUTO_INCREMENT | PK |
| `id_institucion` | BIGINT UNSIGNED | No | — | FK `fk_areas_instituciones` |
| `nombre_area` | VARCHAR(150) | No | — | UK con `id_institucion` |
| `descripcion` | TEXT | Sí | NULL | Descripción |
| `activo` | TINYINT(1) | No | 1 | Estado |
| `fecha_creacion` | TIMESTAMP | No | CURRENT_TIMESTAMP | Alta |

### Ficha 4 · `usuarios`

| Columna | Tipo | Nulo | Default | Restricción/nota |
|---|---|---:|---|---|
| `id_usuario` | BIGINT UNSIGNED | No | AUTO_INCREMENT | PK |
| `id_institucion` | BIGINT UNSIGNED | Sí | NULL | FK; NULL solo `superadmin`, trigger |
| `nombre_completo` | VARCHAR(150) | No | — | Nombre completo |
| `correo_institucional` | VARCHAR(150) | No | — | UK global |
| `contrasena_hash` | VARCHAR(255) | No | — | Hash |
| `rol` | ENUM('superadmin','admin_institucional','admin_sistema','asistente','alumno','docente') | No | 'alumno' | Regla institución validada por trigger |
| `id_area_asignada` | BIGINT UNSIGNED | Sí | NULL | FK, SET NULL |
| `marcas_acumuladas` | INT UNSIGNED | No | 0 | Contador |
| `bloqueado` | TINYINT(1) | No | 0 | Estado de bloqueo |
| `fecha_bloqueo` | DATETIME | Sí | NULL | Inicio |
| `fecha_fin_bloqueo` | DATETIME | Sí | NULL | Fin |
| `activo` | TINYINT(1) | No | 1 | Cuenta activa |
| `fecha_creacion` | TIMESTAMP | No | CURRENT_TIMESTAMP | Alta |

### Ficha 5 · `recursos`

| Columna | Tipo | Nulo | Default | Restricción/nota |
|---|---|---:|---|---|
| `id_recurso` | BIGINT UNSIGNED | No | AUTO_INCREMENT | PK |
| `id_institucion` | BIGINT UNSIGNED | No | — | FK a instituciones |
| `id_area` | BIGINT UNSIGNED | No | — | FK a areas |
| `nombre_recurso` | VARCHAR(150) | No | — | Nombre |
| `numero_serie` | VARCHAR(100) | Sí | NULL | UK compuesta tenant/serie |
| `numero_inventario` | VARCHAR(100) | No | — | UK compuesta tenant/inventario |
| `tipo_recurso` | ENUM('equipo_fisico','espacio') | No | 'equipo_fisico' | Tipo |
| `marca` | VARCHAR(100) | Sí | NULL | Marca |
| `anio_adquisicion` | YEAR | Sí | NULL | Año |
| `estado` | ENUM('disponible','prestado','bloqueado','mantenimiento') | No | 'disponible' | Estado |
| `activo` | TINYINT(1) | No | 1 | Activo |
| `fecha_creacion` | TIMESTAMP | No | CURRENT_TIMESTAMP | Alta |

### Ficha 6 · `logs_auditoria`

| Columna | Tipo | Nulo | Default | Restricción/nota |
|---|---|---:|---|---|
| `id_log` | BIGINT UNSIGNED | No | AUTO_INCREMENT | PK |
| `id_usuario` | BIGINT UNSIGNED | Sí | NULL | FK RESTRICT |
| `id_institucion` | BIGINT UNSIGNED | Sí | NULL | FK RESTRICT; eventos de plataforma |
| `accion` | VARCHAR(100) | No | — | Acción |
| `tabla_afectada` | VARCHAR(100) | No | — | Tabla objetivo |
| `registro_id` | BIGINT UNSIGNED | No | — | Registro objetivo |
| `detalle_anterior` | JSON | Sí | NULL | Estado anterior |
| `detalle_nuevo` | JSON | Sí | NULL | Estado nuevo |
| `direccion_ip` | VARCHAR(45) | Sí | NULL | IPv4/IPv6 |
| `fecha_hora` | TIMESTAMP | No | CURRENT_TIMESTAMP | Equivale funcionalmente a fecha_creacion (DT-024) |

### Ficha 7 · `notificaciones`

| Columna | Tipo | Nulo | Default | Restricción/nota |
|---|---|---:|---|---|
| `id_notificacion` | BIGINT UNSIGNED | No | AUTO_INCREMENT | PK |
| `id_institucion` | BIGINT UNSIGNED | No | — | FK RESTRICT |
| `id_usuario` | BIGINT UNSIGNED | No | — | FK CASCADE |
| `tipo_notificacion` | ENUM('push','email','ambas') | No | 'push' | Canal |
| `titulo` | VARCHAR(150) | No | — | Título |
| `mensaje` | TEXT | No | — | Contenido |
| `leida` | TINYINT(1) | No | 0 | Estado |
| `fecha_envio` | DATETIME | Sí | NULL | Envío efectivo |
| `fecha_creacion` | TIMESTAMP | No | CURRENT_TIMESTAMP | Alta |

**Índices y constraints documentados:** ver DDL-08. **Ejemplo de uso:** `{"id_institucion": 4,
"tipo_notificacion": "push", "titulo": "Préstamo actualizado", "leida": 0}` es ilustrativo, no extraído.

La auditoría se limita a INSERT: triggers `trg_logs_auditoria_before_update` y
`trg_logs_auditoria_before_delete` rechazan cambios. Los triggers `trg_usuarios_before_insert/update`
imponen el uso coherente de institución con rol. Ver DDL-04.

### Evidencia y limitaciones

El repositorio no incluye una salida de `INFORMATION_SCHEMA` correspondiente al instante de esta
documentación; por ello, el encabezado no declara que las fichas hayan sido extraídas. El MBD BBB-BD-004
v1.2 no está disponible en los archivos inspeccionados; la fuente concreta de los detalles físicos es el
código de las siete migraciones originales y las decisiones técnicas registradas. Cualquier divergencia
del esquema en vivo debe resolverse mediante extracción y procedimiento de cambio, no por una edición
manual de QA/producción.

## 9. Firmas

| Rol | Nombre | Firma | Fecha |
|---|---|---|---|
| Responsable DBA | Sin asignar | Pendiente | |
| Revisión técnica | Sin asignar | Pendiente | |
