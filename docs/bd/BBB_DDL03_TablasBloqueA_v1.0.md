# DDL-03 · Tablas originales del bloque A

## 1. Encabezado

| Campo | Valor |
|---|---|
| Formato | DDL-03 — Gestión de tablas |
| Nombre | Creación de tablas originales del bloque A |
| Versión | v1.0 |
| Fecha | 27/10/2026 |
| Autor | Raúl Treviño Licona |
| Aprobador | Raúl Treviño Licona (líder técnico) — pendiente de aceptación |
| Requisitos asociados | RF-001 a RF-015, RF-019, RF-020, RF-021, RF-045 a RF-050, RF-052, RF-053, RN-001, RN-010, RN-011, RN-013, RNF-004, RNF-005, RNF-006, RS-006 |
| Migraciones de origen | `2026_10_18_000001_create_planes_table.php` a `2026_10_18_000007_create_notificaciones_table.php` |
| Base de elaboración | Plan de Desarrollo §2 y §5; migraciones originales; DT-008 a DT-012, DT-016 y DT-024 a DT-026 |
| Estado de ejecución | Documento elaborado; este formato no afirma que los scripts se ejecutaron |

## 2. Identificación del cambio

Este formato documenta las siete migraciones originales consecutivas del bloque A: creación de `planes`,
`instituciones`, `areas`, `usuarios`, `recursos`, `logs_auditoria` y `notificaciones`. `planes` es una tabla
global; las demás incluyen las relaciones multi-tenant prescritas o justificadas por las decisiones citadas.
Las dos migraciones que crean triggers se detallan además en DDL-04.

El SQL representa la estructura observada en los archivos de migración, no una extracción certificada de
`INFORMATION_SCHEMA`. Los únicos datos de ambiente disponibles aquí son los declarados en la documentación
del repositorio; no se consultó servidor.

## 3. Justificación

El plan del bloque A identifica las tablas de operación del SaaS y el catálogo. DT-024 justifica `planes`,
el dominio institucional, las notificaciones adelantadas al bloque A y `fecha_hora` como instante de auditoría.
DT-008 incorpora `id_institucion` en las tablas tenant, DT-009 establece las acciones referenciales, DT-010
protege la inalterabilidad de la bitácora, DT-011 reserva la institución NULL para `superadmin`, DT-012 fija
unicidad global del correo y DT-016 elimina las tablas Laravel ajenas al modelo (incluidas las de reset tokens).
DT-024 a DT-026 contienen extensiones/ajustes del esquema y los triggers/índices de soporte. Las decisiones
identificadas como propuestas pendientes en el registro no se presentan aquí como aprobaciones nuevas.

## 4. Script DDL

El siguiente SQL consolidado es reproducible en un esquema vacío, en el orden mostrado. Se separan las
creaciones con comentarios explicativos; deben crearse primero `planes` e `instituciones`, luego sus tablas
dependientes. Los triggers no se incluyen en este script; véase DDL-04. Cada constraint e índice se define
una sola vez aquí.

```sql
CREATE TABLE planes (
    id_plan BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del plan.',
    clave ENUM('basico', 'pro', 'enterprise') NOT NULL COMMENT 'Clave del plan de suscripción.',
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre comercial del plan.',
    monto_mensual DECIMAL(10, 2) NOT NULL COMMENT 'Precio mensual en pesos mexicanos.',
    limite_areas INT UNSIGNED NOT NULL COMMENT 'Número máximo de áreas permitidas.',
    limite_recursos INT UNSIGNED NOT NULL COMMENT 'Número máximo de recursos permitidos.',
    duracion_dias INT UNSIGNED NOT NULL DEFAULT 30 COMMENT 'Días de vigencia por periodo.',
    activo TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Si el plan se ofrece en la contratación.',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de alta del plan.',
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última modificación del plan.',
    PRIMARY KEY (id_plan),
    UNIQUE KEY uk_planes_clave (clave),
    CONSTRAINT chk_planes_limites
        CHECK (limite_areas > 0 AND limite_recursos > 0 AND duracion_dias > 0)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Planes de suscripción del SaaS con precio, límites y duración (RF-007).';

CREATE TABLE instituciones (
    id_institucion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del tenant.',
    nombre_institucion VARCHAR(150) NOT NULL COMMENT 'Nombre oficial de la institución.',
    dominio_correo VARCHAR(100) NOT NULL
        COMMENT 'Dominio de correo institucional sin arroba; determina tenant y rol.',
    plan_suscripcion ENUM('basico', 'pro', 'enterprise') NOT NULL DEFAULT 'basico'
        COMMENT 'Plan contratado.',
    fecha_inicio DATE NOT NULL COMMENT 'Fecha de inicio de vigencia.',
    fecha_corte DATE NOT NULL COMMENT 'Fecha de corte de la suscripción.',
    estado_suscripcion ENUM('activa', 'solo_lectura', 'suspendida', 'cancelada') NOT NULL
        DEFAULT 'activa' COMMENT 'Estado actual del servicio.',
    limite_areas INT UNSIGNED NOT NULL DEFAULT 3 COMMENT 'Límite de áreas permitidas.',
    limite_recursos INT UNSIGNED NOT NULL DEFAULT 100 COMMENT 'Límite de recursos permitidos.',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de alta.',
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última modificación.',
    PRIMARY KEY (id_institucion),
    UNIQUE KEY uk_instituciones_dominio_correo (dominio_correo),
    KEY idx_instituciones_estado_fecha_corte (estado_suscripcion, fecha_corte),
    CONSTRAINT chk_instituciones_vigencia CHECK (
        fecha_corte >= fecha_inicio AND limite_areas > 0 AND limite_recursos > 0
    )
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Tenants del SaaS: plan, vigencia, límites y estado de suscripción.';

CREATE TABLE areas (
    id_area BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del área.',
    id_institucion BIGINT UNSIGNED NOT NULL COMMENT 'Tenant al que pertenece.',
    nombre_area VARCHAR(150) NOT NULL COMMENT 'Nombre del área.',
    descripcion TEXT NULL COMMENT 'Descripción del área.',
    activo TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Si el área está activa.',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de alta.',
    PRIMARY KEY (id_area),
    UNIQUE KEY uk_areas_institucion_nombre_area (id_institucion, nombre_area),
    KEY idx_areas_institucion_activo (id_institucion, activo),
    CONSTRAINT fk_areas_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Dependencias institucionales que resguardan recursos prestables.';

CREATE TABLE usuarios (
    id_usuario BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del usuario.',
    id_institucion BIGINT UNSIGNED NULL
        COMMENT 'Tenant; nulo solo para el superadmin.',
    nombre_completo VARCHAR(150) NOT NULL COMMENT 'Nombre completo del usuario.',
    correo_institucional VARCHAR(150) NOT NULL COMMENT 'Correo institucional único.',
    contrasena_hash VARCHAR(255) NOT NULL COMMENT 'Contraseña cifrada con bcrypt.',
    rol ENUM('superadmin', 'admin_institucional', 'admin_sistema', 'asistente', 'alumno', 'docente')
        NOT NULL DEFAULT 'alumno' COMMENT 'Rol derivado del dominio y de la asignación.',
    id_area_asignada BIGINT UNSIGNED NULL COMMENT 'Área asignada a responsables y asistentes.',
    marcas_acumuladas INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Marcas por incidencias.',
    bloqueado TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Si el usuario está bloqueado por sanción.',
    fecha_bloqueo DATETIME NULL COMMENT 'Inicio del bloqueo.',
    fecha_fin_bloqueo DATETIME NULL COMMENT 'Fin del bloqueo.',
    activo TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Si el usuario está activo.',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de alta.',
    PRIMARY KEY (id_usuario),
    UNIQUE KEY uk_usuarios_correo_institucional (correo_institucional),
    KEY idx_usuarios_institucion_rol (id_institucion, rol),
    KEY idx_usuarios_area_asignada (id_area_asignada),
    CONSTRAINT fk_usuarios_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_usuarios_areas FOREIGN KEY (id_area_asignada)
        REFERENCES areas (id_area) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Usuarios del sistema con rol, área asignada, marcas y bloqueo.';

CREATE TABLE recursos (
    id_recurso BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del recurso.',
    id_institucion BIGINT UNSIGNED NOT NULL COMMENT 'Tenant al que pertenece.',
    id_area BIGINT UNSIGNED NOT NULL COMMENT 'Área de resguardo.',
    nombre_recurso VARCHAR(150) NOT NULL COMMENT 'Nombre del recurso.',
    numero_serie VARCHAR(100) NULL COMMENT 'Número de serie del fabricante.',
    numero_inventario VARCHAR(100) NOT NULL COMMENT 'Número de inventario institucional.',
    tipo_recurso ENUM('equipo_fisico', 'espacio') NOT NULL DEFAULT 'equipo_fisico'
        COMMENT 'Tipo de recurso.',
    marca VARCHAR(100) NULL COMMENT 'Marca del recurso.',
    anio_adquisicion YEAR NULL COMMENT 'Año de adquisición.',
    estado ENUM('disponible', 'prestado', 'bloqueado', 'mantenimiento') NOT NULL
        DEFAULT 'disponible' COMMENT 'Estado actual del recurso.',
    activo TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Si el recurso está activo.',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de alta.',
    PRIMARY KEY (id_recurso),
    UNIQUE KEY uk_recursos_institucion_numero_inventario (id_institucion, numero_inventario),
    UNIQUE KEY uk_recursos_institucion_numero_serie (id_institucion, numero_serie),
    KEY idx_recursos_institucion_estado (id_institucion, estado),
    KEY idx_recursos_area (id_area),
    CONSTRAINT fk_recursos_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_recursos_areas FOREIGN KEY (id_area)
        REFERENCES areas (id_area) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Inventario de equipos, cables y espacios prestables por institución.';

CREATE TABLE logs_auditoria (
    id_log BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del log.',
    id_usuario BIGINT UNSIGNED NULL COMMENT 'Responsable; nulo si la acción la ejecuta el sistema.',
    id_institucion BIGINT UNSIGNED NULL
        COMMENT 'Tenant del evento; nulo solo en eventos de plataforma.',
    accion VARCHAR(100) NOT NULL COMMENT 'Acción realizada.',
    tabla_afectada VARCHAR(100) NOT NULL COMMENT 'Tabla afectada por la acción.',
    registro_id BIGINT UNSIGNED NOT NULL COMMENT 'Identificador del registro afectado.',
    detalle_anterior JSON NULL COMMENT 'Estado anterior del registro.',
    detalle_nuevo JSON NULL COMMENT 'Estado nuevo del registro.',
    direccion_ip VARCHAR(45) NULL COMMENT 'Dirección IP de origen IPv4 o IPv6.',
    fecha_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        COMMENT 'Fecha y hora del evento; cumple la función de fecha_creacion.',
    PRIMARY KEY (id_log),
    KEY idx_logs_auditoria_institucion_fecha_hora (id_institucion, fecha_hora),
    KEY idx_logs_auditoria_usuario (id_usuario),
    CONSTRAINT fk_logs_auditoria_usuarios FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_logs_auditoria_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Bitácora inalterable de transacciones críticas.';

CREATE TABLE notificaciones (
    id_notificacion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador único de la notificación.',
    id_institucion BIGINT UNSIGNED NOT NULL COMMENT 'Tenant al que pertenece.',
    id_usuario BIGINT UNSIGNED NOT NULL COMMENT 'Usuario destinatario.',
    tipo_notificacion ENUM('push', 'email', 'ambas') NOT NULL DEFAULT 'push'
        COMMENT 'Canal: push en el sistema web, email o ambos.',
    titulo VARCHAR(150) NOT NULL COMMENT 'Título de la notificación.',
    mensaje TEXT NOT NULL COMMENT 'Contenido del mensaje.',
    leida TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Si fue leída por el usuario.',
    fecha_envio DATETIME NULL COMMENT 'Fecha y hora de envío efectivo.',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de alta.',
    PRIMARY KEY (id_notificacion),
    KEY idx_notificaciones_institucion_usuario_leida (id_institucion, id_usuario, leida),
    KEY idx_notificaciones_usuario_leida (id_usuario, leida),
    CONSTRAINT fk_notificaciones_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_notificaciones_usuarios FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    COMMENT = 'Notificaciones enviadas a los usuarios en el sistema y por correo.';
```

## 5. Script de rollback

El rollback SQL elimina las tablas en orden inverso de dependencia. Es destructivo respecto de los datos y
se presenta como equivalente documental de los `down()` originales, no como una instrucción ejecutada.
El script elimina también los triggers asociados al hacer `DROP TABLE`; el rollback explícito de triggers
se encuentra en DDL-04.

```sql
DROP TABLE IF EXISTS notificaciones;
DROP TABLE IF EXISTS logs_auditoria;
DROP TABLE IF EXISTS recursos;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS areas;
DROP TABLE IF EXISTS instituciones;
DROP TABLE IF EXISTS planes;
```

## 6. Validaciones previas

Ejecutar en el esquema objetivo antes de la creación. Resultado esperado para la primera consulta: cero filas.
La segunda verifica dependencias si se está aplicando solo una parte del script. No ejecutar sobre QA ni
producción manualmente (MD 9.4); utilizar el flujo de migraciones aprobado en esos ambientes.

```sql
SELECT TABLE_NAME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'planes', 'instituciones', 'areas', 'usuarios', 'recursos',
      'logs_auditoria', 'notificaciones'
  );

SELECT TABLE_NAME, ENGINE
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('instituciones', 'areas', 'usuarios', 'recursos')
ORDER BY TABLE_NAME;

SELECT TRIGGER_NAME, EVENT_OBJECT_TABLE
FROM INFORMATION_SCHEMA.TRIGGERS
WHERE TRIGGER_SCHEMA = DATABASE()
  AND TRIGGER_NAME IN (
      'trg_usuarios_before_insert', 'trg_usuarios_before_update',
      'trg_logs_auditoria_before_update', 'trg_logs_auditoria_before_delete'
  );
```

Para un esquema vacío, la consulta de tablas referenciadas también devolverá cero filas y deberá
interpretarse como requisito de crear primero las tablas base según el orden del script; no se afirma
resultado de ejecución.

## 7. Validaciones posteriores

Ejecutar después de aplicar el esquema de forma controlada. Verificar los resultados contra las secciones 2 y
4, especialmente motor, charset/collation, comentario no vacío, FK, índices, CHECK y NULL permitidos. En el
presente trabajo no se ejecutaron estos comandos.

```sql
SELECT TABLE_NAME, ENGINE, TABLE_COLLATION, TABLE_COMMENT
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'planes', 'instituciones', 'areas', 'usuarios', 'recursos',
      'logs_auditoria', 'notificaciones'
  )
ORDER BY TABLE_NAME;

SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'planes', 'instituciones', 'areas', 'usuarios', 'recursos',
      'logs_auditoria', 'notificaciones'
  )
ORDER BY TABLE_NAME, ORDINAL_POSITION;

SELECT TABLE_NAME, CONSTRAINT_NAME, CONSTRAINT_TYPE
FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'planes', 'instituciones', 'areas', 'usuarios', 'recursos',
      'logs_auditoria', 'notificaciones'
  )
ORDER BY TABLE_NAME, CONSTRAINT_TYPE, CONSTRAINT_NAME;
```

Validaciones funcionales sugeridas, en base de pruebas desechable y con registros preparados: intentar
INSERT/UPDATE de `usuarios` con combinación rol/institución inválida (debe rechazarse por triggers);
intentar UPDATE/DELETE de un log (debe rechazarse por triggers); comprobar que borrar un usuario conserva
sus logs (RESTRICT) y elimina sus notificaciones (CASCADE). Estas pruebas no fueron ejecutadas en esta tarea.

## 8. Observaciones

- Los siete archivos no contienen un prólogo conjunto de migración; sus cabeceras individuales y requisitos
  se conservan como fuente de identificación. El orden numérico 000001–000007 es el alcance tomado del plan.
- Las columnas/índices de este SQL se derivan de las migraciones inspeccionadas. Para las diferencias entre
  el MBD literal y la implementación, rigen las decisiones citadas y el código efectivamente encontrado.
- `id_institucion` en `usuarios` y `logs_auditoria` es nullable por DT-011 y DT-024; `id_usuario` en
  `logs_auditoria` también es nullable para acciones del sistema.
- No se añade a este formato una FK `recursos.id_institucion`/`areas.id_institucion` compuesta: el control
  de coincidencia tenant-padre queda en la aplicación, tal como se describe en las decisiones y documentos.
- En MySQL `CREATE TRIGGER` no ofrece cláusula `COMMENT`; sus descripciones y comentarios del cuerpo se
  documentan en DDL-04.
- Los constraints CHECK usan sintaxis MySQL 8.4. No se verificó la configuración ni el motor activo.
- No se pudo cotejar literalmente cada campo con el manual instalado fuera del repositorio; el MBD v1.2 no
  estaba incluido en el material consultado. El inventario refleja fielmente las migraciones observadas.

## 9. Firmas

| Rol | Nombre | Firma | Fecha |
|---|---|---|---|
| Responsable DBA | Sin asignar | Pendiente | |
| Revisión técnica | Sin asignar | Pendiente | |
