# DDL-07 y DDL-08 · Relaciones y llaves de las tablas de usuarios, sanciones, conversaciones e historial

Se agrupan los dos formatos porque todas las llaves y relaciones nacen con la creación de las tablas de
`BBB_DDL03_TablasUsuariosSanciones_v1.0.md` (sección 4, script completo).

## 1. Encabezado

| Campo | DDL-07 | DDL-08 |
|---|---|---|
| Nombre | Gestión de relaciones entre tablas | Gestión de llaves primarias, foráneas y únicas |
| Versión | v1.0 | v1.0 |
| Fecha | 27/10/2026 | 27/10/2026 |
| Autor | Raúl Treviño Licona (DBA, DT-018) | Raúl Treviño Licona (DBA, DT-018) |
| Aprobador | Raúl Treviño Licona — pendiente de firma | Raúl Treviño Licona — pendiente de firma |
| Requisito asociado | RS-006, RF-016, RF-033, RF-041, RF-042, RF-052 | RS-006, RF-017, RF-041, RF-052 |

## 2. Identificación de las relaciones (DDL-07)

| # | Tabla origen | Tabla destino | Cardinalidad | Restricción | ON DELETE / ON UPDATE (MBD Tabla 27) |
|---|---|---|---|---|---|
| R1 | dominios_rol | instituciones | N : 1 | `fk_dominios_rol_instituciones` | RESTRICT / CASCADE |
| R2 | matriz_sanciones | instituciones | N : 1 | `fk_matriz_sanciones_instituciones` | RESTRICT / CASCADE |
| R3 | penalizaciones | instituciones | N : 1 | `fk_penalizaciones_instituciones` | RESTRICT / CASCADE |
| R4 | penalizaciones | usuarios | N : 1 | `fk_penalizaciones_usuarios` | RESTRICT / CASCADE |
| R5 | penalizaciones | prestamos | N : 1 | `fk_penalizaciones_prestamos` | SET NULL / CASCADE |
| R6 | conversaciones | instituciones | N : 1 | `fk_conversaciones_instituciones` | RESTRICT / CASCADE |
| R7 | conversaciones | prestamos | N : 1 | `fk_conversaciones_prestamos` | CASCADE / CASCADE |
| R8 | conversaciones | usuarios | N : 1 | `fk_conversaciones_usuarios` | RESTRICT / CASCADE |
| R9 | historial_comprimido | instituciones | N : 1 | `fk_historial_comprimido_instituciones` | RESTRICT / CASCADE |

R3, R6 (y el `id_institucion` de R1, R2, R9) aplican DT-008. R5 conserva la sanción cuando el préstamo se elimina
por la compresión del historial (RN-012); R7 elimina los mensajes con el préstamo.

## 3. Identificación de las llaves (DDL-08)

| Tabla | PK | UK | Índices de soporte de FK (DT-026) |
|---|---|---|---|
| dominios_rol | `id_dominio_rol` | `uk_dominios_rol_institucion_dominio` (id_institucion, dominio) | — (la UK cubre R1) |
| matriz_sanciones | `id_regla` | `uk_matriz_sanciones_institucion_tipo_incidente` (id_institucion, tipo_incidente) | — (la UK cubre R2) |
| penalizaciones | `id_penalizacion` | — | `idx_penalizaciones_usuario`, `idx_penalizaciones_prestamo` |
| conversaciones | `id_mensaje` | — | `idx_conversaciones_prestamo`, `idx_conversaciones_usuario_emisor` |
| historial_comprimido | `id_historial` | `uk_historial_comprimido_institucion_periodo` (id_institucion, periodo_inicio, periodo_fin) | — (la UK cubre R9) |

Índices de consulta (empiezan por `id_institucion`): `idx_penalizaciones_institucion_usuario_fecha`,
`idx_penalizaciones_institucion_tipo_fecha`, `idx_conversaciones_institucion_prestamo_fecha`. Índice operativo de
la tarea de depuración: `idx_historial_comprimido_id_institucion_eliminacion`
(`id_institucion`, eliminado, fecha_eliminacion_programada).

## 4. Justificación

Llaves y relaciones de MBD 4.9, 4.10, 4.13 y 4.14 con la nomenclatura de MBD 6.3.3 y las cascadas de MBD
Tabla 27 (DT-009). La UK de `matriz_sanciones` es la `UNIQUE(id_institucion, tipo_incidente)` de MBD Tabla 7
(RF-041); la de `historial_comprimido` impide comprimir dos veces el mismo mes.

## 5. Script de rollback

Las restricciones se eliminan con sus tablas (`BBB_DDL03_TablasUsuariosSanciones_v1.0.md`, sección 5). Para retirar
solo una relación:

```sql
ALTER TABLE penalizaciones DROP FOREIGN KEY fk_penalizaciones_prestamos;
```

En MySQL un `ALTER TABLE` confirma implícitamente; el rollback es el script inverso, no `ROLLBACK`.

## 6. Validaciones previas

```sql
SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('dominios_rol', 'matriz_sanciones', 'penalizaciones', 'conversaciones',
        'historial_comprimido');
```

Resultado esperado: 0 (las tablas no existen).

## 7. Validaciones posteriores

```sql
SELECT TABLE_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, UPDATE_RULE, DELETE_RULE
FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('dominios_rol', 'matriz_sanciones', 'penalizaciones', 'conversaciones',
        'historial_comprimido')
ORDER BY TABLE_NAME, CONSTRAINT_NAME;
```

Evidencia del 27/10/2026 (desarrollo): las 9 FK existen con las reglas de la sección 2; CP-053 verifica en vivo
R5 (`id_prestamo` queda nulo al comprimir) y R7 (los mensajes se eliminan con el préstamo).

## 8. Observaciones

- Toda FK tiene índice asociado (UK que la cubre o índice de soporte).
- Se actualiza el diccionario en `BBB_DDL06_DiccionarioUsuariosSanciones_v1.0.md`.

## 9. Firmas

| Rol | Nombre | Firma | Fecha |
|---|---|---|---|
| DBA responsable | Raúl Treviño Licona | Pendiente | |
| Líder técnico | Raúl Treviño Licona | Pendiente | |
