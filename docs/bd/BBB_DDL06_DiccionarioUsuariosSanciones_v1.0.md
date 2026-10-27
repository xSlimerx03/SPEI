# DDL-06 · Diccionario de usuarios, sanciones, conversaciones e historial

## 1. Encabezado

| Campo | Valor |
|---|---|
| Formato | DDL-06 |
| Nombre | Actualización del diccionario de datos |
| Versión | v1.0 (documento BBB_DDL06_DiccionarioUsuariosSanciones_v1.0) |
| Fecha | 27/10/2026 |
| Autor | Raúl Treviño Licona (DBA del SPEI, DT-018) |
| Aprobador | Raúl Treviño Licona (líder técnico) — pendiente de firma |
| Requisito asociado | RF-016, RF-017, RF-033, RF-041 a RF-044, RF-052, RN-005, RN-012 |

## 2. Identificación del cambio

Alta de las fichas `dominios_rol`, `matriz_sanciones`, `penalizaciones`, `conversaciones` e `historial_comprimido`;
modificación de `usuarios` (columna `fecha_registro`) y de `detalle_prestamo` (valor `no_entregado`). Documento
actualizado: este diccionario (referencia MBD 4.4, 4.7, 4.9, 4.10, 4.13, 4.14). Ambiente: Documentación.

## 3. Justificación

Refleja el esquema implementado el 27/10/2026 (`BBB_DDL03_TablasUsuariosSanciones_v1.0.md`). Las columnas y tipos de
las tablas del MBD coinciden con sus Tablas 39, 40, 48 y 49 más `id_institucion` (DT-008) y
`fecha_actualizacion` donde aplica; `dominios_rol` y `fecha_registro` se justifican en DT-034 y DT-035.

## 4. Script de extracción

```sql
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('dominios_rol', 'matriz_sanciones', 'penalizaciones', 'conversaciones',
        'historial_comprimido')
ORDER BY TABLE_NAME, ORDINAL_POSITION;
```

## 5. Script de rollback

No se ejecuta SQL; se restaura la versión anterior de este documento desde Git.

## 6. Validaciones

Previa: las cinco tablas existen. Posterior: el resultado de la sección 4 coincide con las fichas y ninguna
columna tiene `COLUMN_COMMENT` vacío (27/10/2026, desarrollo).

---

## Ficha 1 · `dominios_rol` (DT-034)

**Identificación.** Reglas por institución que asignan alumno o docente según el dominio o subdominio del correo.
Requisitos: RF-016, RF-017, RN-001.

| Columna | Tipo | Restricción | Default | Descripción |
|---|---|---|---|---|
| id_dominio_rol | BIGINT UNSIGNED | PK, AUTO_INCREMENT | — | Identificador de la regla. |
| id_institucion | BIGINT UNSIGNED | NOT NULL, FK | — | Tenant. |
| dominio | VARCHAR(150) | NOT NULL, UK con id_institucion | — | Dominio contratado o subdominio, en minúsculas. |
| rol | ENUM('alumno','docente') | NOT NULL | — | Rol asignado por la regla. |
| fecha_creacion / fecha_actualizacion | TIMESTAMP | — | CURRENT_TIMESTAMP / NULL | Auditoría de la fila. |

**Integridad.** Gana la regla más específica; el rol explícito de una alta tiene precedencia. **Ejemplo:**
`{"id_institucion": 1, "dominio": "alumnos.escuela.edu.mx", "rol": "alumno"}`.

## Ficha 2 · `usuarios.fecha_registro` (DT-035)

DATETIME NULL. NULL = invitación pendiente; con fecha y `activo = 0` = cuenta dada de baja; con fecha y
`activo = 1` = cuenta activa. Se fija al completar el registro.

## Ficha 3 · `matriz_sanciones` (MBD 4.10, Tabla 40)

**Identificación.** Reglas configurables de sanción por institución. Requisitos: RF-041, RN-005.

| Columna | Tipo | Restricción | Default | Descripción |
|---|---|---|---|---|
| id_regla | BIGINT UNSIGNED | PK | — | Identificador. |
| id_institucion | BIGINT UNSIGNED | NOT NULL, FK, UK con tipo | — | Tenant. |
| tipo_incidente | ENUM('retraso','mal_estado','danado','no_entregado') | NOT NULL | — | Incidente. |
| marcas_por_incidente | INT UNSIGNED | NOT NULL | 1 | Marcas que suma (0–10 en la interfaz). |
| umbral_bloqueo | INT UNSIGNED | NOT NULL | 3 | Marcas que bloquean (1–20). |
| dias_sancion | INT UNSIGNED | NOT NULL | 7 | Días de bloqueo (1–90). |
| activo | TINYINT(1) | NOT NULL | 1 | Regla activa. |
| fecha_creacion / fecha_actualizacion | TIMESTAMP | — | — | Auditoría de la fila. |

**Integridad.** Se crea con los valores de `config/spei.php` la primera vez que se consulta (DT-036). **Ejemplo:**
`{"tipo_incidente": "danado", "marcas_por_incidente": 2, "umbral_bloqueo": 3, "dias_sancion": 7, "activo": 1}`.

## Ficha 4 · `penalizaciones` (MBD 4.9, Tabla 39)

**Identificación.** Sanciones aplicadas. Requisitos: RF-042 a RF-044, RF-056.

| Columna | Tipo | Restricción | Default | Descripción |
|---|---|---|---|---|
| id_penalizacion | BIGINT UNSIGNED | PK | — | Identificador. |
| id_institucion | BIGINT UNSIGNED | NOT NULL, FK | — | Tenant (DT-008). |
| id_usuario | BIGINT UNSIGNED | NOT NULL, FK | — | Usuario sancionado. |
| id_prestamo | BIGINT UNSIGNED | NULL, FK SET NULL | NULL | Préstamo; nulo tras comprimir el historial. |
| tipo_penalizacion | ENUM(...) | NOT NULL | — | Mismo dominio que `tipo_incidente`. |
| motivo | TEXT | NOT NULL | — | Descripción del incidente. |
| marcas_aplicadas | INT UNSIGNED | NOT NULL | 1 | Marcas de la regla. |
| fecha_inicio_sancion / fecha_fin_sancion | DATE | NULL | NULL | Bloqueo que provocó. |
| exentada | TINYINT(1) | NOT NULL | 0 | Exención (RF-044). |
| motivo_exencion | TEXT | NULL | NULL | Justificación ≥ 20 caracteres. |
| fecha_creacion / fecha_actualizacion | TIMESTAMP | — | — | Auditoría de la fila. |

**Integridad.** Triggers `trg_penalizaciones_after_insert/update` suman y restan `usuarios.marcas_acumuladas`
(DDL-04). **Ejemplo:** `{"tipo_penalizacion": "retraso", "motivo": "Devolución tardía de 30 minutos.",
"marcas_aplicadas": 1, "exentada": 0}`.

## Ficha 5 · `conversaciones` (MBD 4.13, Tabla 48)

**Identificación.** Mensajes previos a la entrega. Requisitos: RF-033, RNF-004.

| Columna | Tipo | Restricción | Default | Descripción |
|---|---|---|---|---|
| id_mensaje | BIGINT UNSIGNED | PK | — | Identificador. |
| id_institucion | BIGINT UNSIGNED | NOT NULL, FK | — | Tenant (DT-008). |
| id_prestamo | BIGINT UNSIGNED | NOT NULL, FK CASCADE | — | Préstamo pendiente. |
| id_usuario_emisor | BIGINT UNSIGNED | NOT NULL, FK | — | Solicitante u operador del área. |
| mensaje | TEXT | NOT NULL | — | 2 a 1000 caracteres. |
| leido | TINYINT(1) | NOT NULL | 0 | Leído por la otra parte. |
| fecha_hora | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | Envío. |

**Ejemplo:** `{"id_prestamo": 15, "id_usuario_emisor": 7, "mensaje": "¿Incluye el cable HDMI?", "leido": 0}`.

## Ficha 6 · `historial_comprimido` (MBD 4.14, Tabla 49)

**Identificación.** Archivos del historial comprimido. Requisitos: RF-052, RN-012.

| Columna | Tipo | Restricción | Default | Descripción |
|---|---|---|---|---|
| id_historial | BIGINT UNSIGNED | PK | — | Identificador. |
| id_institucion | BIGINT UNSIGNED | NOT NULL, FK | — | Tenant. |
| archivo_comprimido_url | VARCHAR(255) | NOT NULL | — | `historial/{institución}/{AAAA-MM}.json.gz`. |
| periodo_inicio / periodo_fin | DATE | NOT NULL, UK con institución | — | Mes comprimido. |
| fecha_eliminacion_programada | DATE | NOT NULL | — | periodo_fin + 3 años. |
| eliminado | TINYINT(1) | NOT NULL | 0 | Archivo ya eliminado. |
| fecha_creacion | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | Alta. |

**Ejemplo:** `{"archivo_comprimido_url": "historial/1/2025-08.json.gz", "periodo_inicio": "2025-08-01",
"periodo_fin": "2025-08-31", "fecha_eliminacion_programada": "2028-08-31", "eliminado": 0}`.

## Ficha 7 · `detalle_prestamo.condicion_devolucion`

ENUM('bueno','regular','malo','no_entregado','danado') NULL — `no_entregado` lo fija el cierre de un préstamo
atrasado sin devolución (CU-019, DT-030).

## 7. Firmas

| Rol | Nombre | Firma | Fecha |
|---|---|---|---|
| DBA responsable | Raúl Treviño Licona | Pendiente | |
| Líder técnico | Raúl Treviño Licona | Pendiente | |
