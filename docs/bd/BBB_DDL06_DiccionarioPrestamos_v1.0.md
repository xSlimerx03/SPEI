# DDL-06 · Diccionario de datos de las tablas de préstamo

## 1. Encabezado

| Campo | Valor |
|---|---|
| Formato | DDL-06 |
| Nombre | Actualización del diccionario de datos |
| Versión | v1.0 (documento BBB_DDL06_DiccionarioPrestamos_v1.0) |
| Fecha | 25/10/2026 |
| Autor | Raúl Treviño Licona (DBA del SPEI, DT-018) |
| Aprobador | Raúl Treviño Licona (líder técnico) — pendiente de firma |
| Requisito asociado | RF-022, RF-024, RF-028, RF-032, RF-034, RF-036, RS-006 |

## 2. Identificación del cambio

| Campo | Valor |
|---|---|
| Tipo de cambio | Alta |
| Tablas afectadas | `prestamos`, `detalle_prestamo`, `tokens_qr` (todas sus columnas) |
| Documento actualizado | Diccionario del proyecto (este documento); referencia MBD v1.2, secciones 4.6, 4.7 y 4.8 |
| Ambiente | Documentación |

## 3. Justificación

Las tablas se crearon con la migración del MVP (DDL-03) y sus llaves se normalizaron el 25/10/2026
(DDL-08). El diccionario documenta la estructura real implementada. Sus diferencias con MBD 4.6–4.8
(columnas agregadas, tipo de `token` y columnas diferidas a RF-030, RF-039 y RF-042) están justificadas
en DT-030.

## 4. Script de extracción

```sql
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('prestamos', 'detalle_prestamo', 'tokens_qr')
ORDER BY TABLE_NAME, ORDINAL_POSITION;

SELECT TABLE_NAME, CONSTRAINT_NAME, CONSTRAINT_TYPE
FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('prestamos', 'detalle_prestamo', 'tokens_qr');
```

## 5. Script de rollback

No se ejecuta SQL. El rollback consiste en restaurar la versión anterior de este documento desde Git.

## 6. Validaciones previas y posteriores

- Previa: `SHOW TABLES LIKE 'prestamos'` (y las otras dos) devuelve la tabla.
- Posterior: el resultado de la sección 4 coincide columna por columna con las fichas siguientes y
  ninguna columna tiene `COLUMN_COMMENT` vacío (comprobado el 25/10/2026 en desarrollo).

---

## Ficha 1 · `prestamos`

**1. Identificación.** Solicitudes de préstamo y sus movimientos de custodia por institución. InnoDB,
utf8mb4_unicode_ci. Requisitos: RF-022, RF-023, RF-025, RF-026, RF-028, RF-032, RF-034, RF-036.

**2. Columnas**

| Columna | Tipo | Restricción | Default | Descripción |
|---|---|---|---|---|
| id_prestamo | BIGINT UNSIGNED | PK, AUTO_INCREMENT | — | Identificador del préstamo; el folio visible es `PR-AAAA-NNNN`. |
| id_institucion | BIGINT UNSIGNED | NOT NULL, FK | — | Institución propietaria (tenant). |
| id_usuario | BIGINT UNSIGNED | NOT NULL, FK | — | Alumno o docente solicitante. |
| fecha_hora_inicio | DATETIME | NOT NULL | — | Inicio programado (zona de la aplicación). |
| fecha_hora_fin | DATETIME | NOT NULL, CHECK | — | Devolución programada; posterior al inicio. |
| estado | ENUM('pendiente','activo','devuelto','vencido','cancelado') | NOT NULL | 'pendiente' | Estado del préstamo. |
| motivo | TEXT | NOT NULL | — | Finalidad declarada por el solicitante. |
| fecha_hora_entrega_real | DATETIME | NULL | NULL | Confirmación de entrega en mostrador. |
| fecha_hora_devolucion_real | DATETIME | NULL | NULL | Confirmación de devolución. |
| motivo_cancelacion | TEXT | NULL | NULL | Justificación de la cancelación o del vencimiento. |
| fecha_creacion | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | Fecha de alta; desempate RN-008. |
| fecha_actualizacion | TIMESTAMP | NULL | NULL | Último cambio. |

**3. Llaves y restricciones.** `PRIMARY (id_prestamo)`; `fk_prestamos_instituciones`;
`fk_prestamos_usuarios`; `chk_prestamos_periodo CHECK (fecha_hora_fin > fecha_hora_inicio)`.

**4. Índices**

| Nombre | Columnas | Tipo | Justificación |
|---|---|---|---|
| idx_prestamos_institucion_estado_inicio | id_institucion, estado, fecha_hora_inicio | BTREE | Pestañas por estado, agenda del día y tarea de vencimiento. |
| idx_prestamos_usuario | id_usuario | BTREE | Soporte de FK y "Mis préstamos" (excepción DT-026). |

**5. Reglas de integridad.** FK RESTRICT / ON UPDATE CASCADE (DT-009). Transiciones permitidas
(`PrestamoService`): pendiente → activo (entrega con QR), pendiente → cancelado (propietario o
administrador del sistema del área, con justificación), pendiente → vencido (tarea programada tras
15–20 min de tolerancia), activo → devuelto (devolución con QR). Cada transición se registra en
`logs_auditoria`.

**6. Requisitos cubiertos.** RF-022, RF-023, RF-025, RF-026, RF-028, RF-032, RF-034, RF-036, RN-004,
RS-006.

**7. Ejemplo**

```json
{"id_prestamo": 15, "id_institucion": 1, "id_usuario": 7, "fecha_hora_inicio": "2026-10-27 10:00:00",
 "fecha_hora_fin": "2026-10-27 12:00:00", "estado": "pendiente", "motivo": "Presentación de tesis.",
 "fecha_hora_entrega_real": null, "fecha_hora_devolucion_real": null, "motivo_cancelacion": null,
 "fecha_creacion": "2026-10-25 09:00:00", "fecha_actualizacion": null}
```

## Ficha 2 · `detalle_prestamo`

**1. Identificación.** Recursos incluidos en cada préstamo y su condición al entregar y devolver.
InnoDB, utf8mb4_unicode_ci. Requisitos: RF-024, RF-034, RF-036, RF-039.

**2. Columnas**

| Columna | Tipo | Restricción | Default | Descripción |
|---|---|---|---|---|
| id_detalle | BIGINT UNSIGNED | PK, AUTO_INCREMENT | — | Identificador del detalle. |
| id_institucion | BIGINT UNSIGNED | NOT NULL, FK | — | Institución propietaria; igual a la del préstamo. |
| id_prestamo | BIGINT UNSIGNED | NOT NULL, FK | — | Préstamo al que pertenece. |
| id_recurso | BIGINT UNSIGNED | NOT NULL, FK | — | Recurso solicitado. |
| condicion_entrega | ENUM('bueno','regular','malo') | NOT NULL | 'bueno' | Condición registrada al entregar. |
| condicion_devolucion | ENUM('bueno','regular','malo','danado') | NULL | NULL | Condición al devolver; `malo` o `danado` envía el recurso a mantenimiento. |
| incidencia | TEXT | NULL | NULL | Observaciones del operador. |
| fecha_creacion | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | Fecha de alta. |
| fecha_actualizacion | TIMESTAMP | NULL | NULL | Último cambio. |

**3. Llaves y restricciones.** `PRIMARY (id_detalle)`;
`uk_detalle_prestamo_institucion_prestamo_recurso (id_institucion, id_prestamo, id_recurso)`;
`fk_detalle_prestamo_instituciones`; `fk_detalle_prestamo_prestamos`; `fk_detalle_prestamo_recursos`.

**4. Índices**

| Nombre | Columnas | Tipo | Justificación |
|---|---|---|---|
| uk_detalle_prestamo_institucion_prestamo_recurso | id_institucion, id_prestamo, id_recurso | UNIQUE | Un recurso no se repite en un préstamo. |
| idx_detalle_prestamo_prestamo | id_prestamo | BTREE | Soporte de FK (DT-026). |
| idx_detalle_prestamo_recurso | id_recurso | BTREE | Soporte de FK y disponibilidad por recurso (DT-026). |

**5. Reglas de integridad.** ON DELETE CASCADE desde `prestamos` (MBD Tabla 27, DT-009); RESTRICT
hacia `recursos` e `instituciones`. Entre 1 y 10 recursos de una misma área por préstamo (validado en
`PrestamoService`).

**6. Requisitos cubiertos.** RF-024, RF-034, RF-036, RF-039, RS-006.

**7. Ejemplo**

```json
{"id_detalle": 31, "id_institucion": 1, "id_prestamo": 15, "id_recurso": 4,
 "condicion_entrega": "bueno", "condicion_devolucion": "regular", "incidencia": null,
 "fecha_creacion": "2026-10-25 09:00:00", "fecha_actualizacion": "2026-10-27 12:05:00"}
```

## Ficha 3 · `tokens_qr`

**1. Identificación.** Hash de los códigos QR de un solo uso que autorizan una entrega o una
devolución. InnoDB, utf8mb4_unicode_ci. Requisitos: RF-034, RF-036, RS-006.

**2. Columnas**

| Columna | Tipo | Restricción | Default | Descripción |
|---|---|---|---|---|
| id_token | BIGINT UNSIGNED | PK, AUTO_INCREMENT | — | Identificador del token. |
| id_institucion | BIGINT UNSIGNED | NOT NULL, FK | — | Institución propietaria. |
| id_prestamo | BIGINT UNSIGNED | NOT NULL, FK | — | Préstamo que autoriza. |
| tipo_token | ENUM('entrega','devolucion') | NOT NULL | — | Movimiento autorizado. |
| token | CHAR(64) | NOT NULL, UNIQUE | — | Hash SHA-256 del código aleatorio de 128 bits; el código en claro no se guarda. |
| fecha_expiracion | DATETIME | NOT NULL | — | Fin de vigencia (30 a 60 s, `spei.qr.ttl`). |
| usado | TINYINT(1) | NOT NULL | 0 | Consumido o sustituido por un código nuevo. |
| fecha_creacion | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | Fecha de emisión. |

**3. Llaves y restricciones.** `PRIMARY (id_token)`; `uk_tokens_qr_token (token)`;
`fk_tokens_qr_instituciones`; `fk_tokens_qr_prestamos`.

**4. Índices**

| Nombre | Columnas | Tipo | Justificación |
|---|---|---|---|
| uk_tokens_qr_token | token | UNIQUE | Búsqueda del código en mostrador. |
| idx_tokens_qr_institucion_prestamo | id_institucion, id_prestamo | BTREE | Revocar los códigos previos del préstamo. |
| idx_tokens_qr_prestamo | id_prestamo | BTREE | Soporte de FK (DT-026). |

**5. Reglas de integridad.** ON DELETE CASCADE desde `prestamos` (DT-009). Al emitir un código se
marcan como usados los anteriores del préstamo; al confirmar se marca el vigente. Un código expirado,
usado o de otro tipo se rechaza.

**6. Requisitos cubiertos.** RF-034, RF-036, RS-006, RN-004.

**7. Ejemplo**

```json
{"id_token": 2, "id_institucion": 1, "id_prestamo": 15, "tipo_token": "entrega",
 "token": "9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08",
 "fecha_expiracion": "2026-10-27 10:05:45", "usado": 0, "fecha_creacion": "2026-10-27 10:05:00"}
```

## 7. Observaciones

- Columnas de MBD 4.6–4.8 aún no implementadas (`justificacion_prioridad`, `evidencia_url` y el valor
  `no_entregado`) se agregarán con su DDL-03 y una nueva versión de este diccionario (DT-030).
- El diccionario de las tablas del bloque A (planes, instituciones, areas, usuarios, recursos,
  logs_auditoria, notificaciones) está pendiente de documentarse en este mismo formato.

## 8. Firmas

| Rol | Nombre | Firma | Fecha |
|---|---|---|---|
| DBA responsable | Raúl Treviño Licona | Pendiente | |
| Líder técnico | Raúl Treviño Licona | Pendiente | |
