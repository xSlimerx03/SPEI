# DDL-07 · Relaciones de las tablas de préstamo

## 1. Encabezado

| Campo | Valor |
|---|---|
| Formato | DDL-07 |
| Nombre | Gestión de relaciones entre tablas |
| Versión | v1.0 (documento BBB_DDL07_RelacionesPrestamos_v1.0) |
| Fecha | 25/10/2026 |
| Autor | Raúl Treviño Licona (DBA del SPEI, DT-018) |
| Aprobador | Raúl Treviño Licona (líder técnico) — pendiente de firma |
| Requisito asociado | RS-006, RF-022, RF-024, RF-034, RF-036 |
| Migraciones | `2026_10_18_200001_create_prestamos_mvp_tables.php` (creación) y `2026_10_25_000001_normalizar_nombres_prestamos.php` (nombres oficiales) |

## 2. Identificación del cambio

| # | Tipo | Tabla origen | Tabla destino | Cardinalidad | Restricción | ON DELETE / ON UPDATE |
|---|---|---|---|---|---|---|
| R1 | Creación | prestamos | instituciones | N : 1 | `fk_prestamos_instituciones` | RESTRICT / CASCADE |
| R2 | Creación | prestamos | usuarios | N : 1 | `fk_prestamos_usuarios` | RESTRICT / CASCADE |
| R3 | Creación | detalle_prestamo | instituciones | N : 1 | `fk_detalle_prestamo_instituciones` | RESTRICT / CASCADE |
| R4 | Creación | detalle_prestamo | prestamos | N : 1 | `fk_detalle_prestamo_prestamos` | CASCADE / CASCADE |
| R5 | Creación | detalle_prestamo | recursos | N : 1 | `fk_detalle_prestamo_recursos` | RESTRICT / CASCADE |
| R6 | Creación | tokens_qr | instituciones | N : 1 | `fk_tokens_qr_instituciones` | RESTRICT / CASCADE |
| R7 | Creación | tokens_qr | prestamos | N : 1 | `fk_tokens_qr_prestamos` | CASCADE / CASCADE |

Ambiente: Desarrollo (aplicadas el 18/10/2026; renombradas el 25/10/2026). Pendiente Staging y
Producción. La relación N : M entre `prestamos` y `recursos` se resuelve con `detalle_prestamo`.

## 3. Justificación

Cada préstamo pertenece a una institución y a un solicitante; puede incluir de uno a diez recursos
de una misma área (RF-024) y generar varios códigos QR, de los que solo el último está vigente
(RF-034, RF-036). R3 y R6 llevan `id_institucion` a las tablas hijas (DT-008) para que el Global
Scope y las consultas directas filtren por tenant sin unir con `prestamos`.

## 4. Script DDL

Las restricciones se crean dentro de `CREATE TABLE` (ver `BBB_DDL03_TablasPrestamos_v1.0.md`,
sección 4). Forma equivalente con `ALTER TABLE`:

```sql
ALTER TABLE prestamos
    ADD CONSTRAINT fk_prestamos_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_prestamos_usuarios FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE detalle_prestamo
    ADD CONSTRAINT fk_detalle_prestamo_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_detalle_prestamo_prestamos FOREIGN KEY (id_prestamo)
        REFERENCES prestamos (id_prestamo) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_detalle_prestamo_recursos FOREIGN KEY (id_recurso)
        REFERENCES recursos (id_recurso) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE tokens_qr
    ADD CONSTRAINT fk_tokens_qr_instituciones FOREIGN KEY (id_institucion)
        REFERENCES instituciones (id_institucion) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_tokens_qr_prestamos FOREIGN KEY (id_prestamo)
        REFERENCES prestamos (id_prestamo) ON DELETE CASCADE ON UPDATE CASCADE;
```

Índices asociados: `idx_prestamos_institucion_estado_inicio` (R1), `idx_prestamos_usuario` (R2),
`uk_detalle_prestamo_institucion_prestamo_recurso` (R3), `idx_detalle_prestamo_prestamo` (R4),
`idx_detalle_prestamo_recurso` (R5), `idx_tokens_qr_institucion_prestamo` (R6) e
`idx_tokens_qr_prestamo` (R7).

## 5. Script de rollback

```sql
ALTER TABLE tokens_qr
    DROP FOREIGN KEY fk_tokens_qr_prestamos,
    DROP FOREIGN KEY fk_tokens_qr_instituciones;

ALTER TABLE detalle_prestamo
    DROP FOREIGN KEY fk_detalle_prestamo_recursos,
    DROP FOREIGN KEY fk_detalle_prestamo_prestamos,
    DROP FOREIGN KEY fk_detalle_prestamo_instituciones;

ALTER TABLE prestamos
    DROP FOREIGN KEY fk_prestamos_usuarios,
    DROP FOREIGN KEY fk_prestamos_instituciones;
```

En la práctica el rollback completo es el de DDL-03 (eliminar las tablas). Para volver solo a los
nombres anteriores se ejecuta `php artisan migrate:rollback --step=1` (ver DDL-08).

## 6. Observaciones

- Nombres oficiales `fk_<origen>_<destino>` desde la migración del 25/10/2026 (RED-01 H-21).
- ON UPDATE CASCADE en todas las FK; ON DELETE CASCADE solo en R4 y R7, como define MBD Tabla 27
  (DT-009). Los préstamos no se eliminan desde la aplicación: cambian de estado.
- Toda FK tiene índice asociado; los que no empiezan por `id_institucion` son índices de soporte
  exceptuados por DT-026.
- La coincidencia de `id_institucion` entre hijo y padre se valida en `PrestamoService` (DT-008).
- El diccionario se actualiza en `BBB_DDL06_DiccionarioPrestamos_v1.0.md`.

## 7. Firmas

| Rol | Nombre | Firma | Fecha |
|---|---|---|---|
| DBA responsable | Raúl Treviño Licona | Pendiente | |
| Líder técnico | Raúl Treviño Licona | Pendiente | |
