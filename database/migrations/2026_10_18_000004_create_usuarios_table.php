<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         2026_10_18_000004_create_usuarios_table.php
 * @path         database/migrations/2026_10_18_000004_create_usuarios_table.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Crea la tabla usuarios (perfiles, roles y sanciones). El
 *               superadmin no pertenece a ningún tenant (DT-011); la regla se
 *               garantiza con triggers porque MySQL no admite CHECK sobre una
 *               columna con acción referencial (DT-026).
 *
 * @requisitos   RF-002, RF-008, RF-016, RF-017, RF-018, RF-019, RF-020, RF-021,
 *               RN-001, RN-005, RS-006
 *
 * @see          Manual de Base de Datos v1.2, sección 4.4 y Tabla 27; DT-011, DT-012, DT-026
 * ============================================================================
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Roles del sistema (MBD 4.4 y Tabla 31).
     */
    private const ROLES = ['superadmin', 'admin_institucional', 'admin_sistema', 'asistente', 'alumno', 'docente'];

    /**
     * Cuerpo común de los triggers que validan la regla del superadmin.
     */
    private const CUERPO_TRIGGER = "
        IF (NEW.rol = 'superadmin' AND NEW.id_institucion IS NOT NULL)
            OR (NEW.rol <> 'superadmin' AND NEW.id_institucion IS NULL) THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Solo el superadmin carece de institucion (DT-011).';
        END IF;";

    /**
     * Crea la tabla usuarios, sus llaves y los triggers de la regla DT-011.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table): void {
            $table->engine('InnoDB');
            $table->comment('Usuarios del sistema con rol, área asignada, marcas y bloqueo.');

            $table->bigIncrements('id_usuario')->comment('Identificador único del usuario.');
            $table->unsignedBigInteger('id_institucion')->nullable()
                ->comment('Tenant al que pertenece; nulo solo para el superadmin (DT-011).');
            $table->string('nombre_completo', 150)->comment('Nombre completo del usuario.');
            $table->string('correo_institucional', 150)->comment('Correo institucional único (DT-012).');
            $table->string('contrasena_hash', 255)->comment('Contraseña cifrada con bcrypt.');
            $table->enum('rol', self::ROLES)
                ->default('alumno')
                ->comment('Rol del usuario, derivado del dominio y de la asignación (RN-001).');
            $table->unsignedBigInteger('id_area_asignada')->nullable()
                ->comment('Área asignada a responsables y asistentes.');
            $table->unsignedInteger('marcas_acumuladas')->default(0)->comment('Marcas por incidencias.');
            $table->boolean('bloqueado')->default(false)->comment('Si el usuario está bloqueado por sanción.');
            $table->dateTime('fecha_bloqueo')->nullable()->comment('Inicio del bloqueo.');
            $table->dateTime('fecha_fin_bloqueo')->nullable()->comment('Fin del bloqueo.');
            $table->boolean('activo')->default(true)
                ->comment('Si el usuario está activo; falso hasta completar el registro (DT-025).');
            $table->timestamp('fecha_creacion')->useCurrent()->comment('Fecha de alta.');

            $table->unique('correo_institucional', 'uk_usuarios_correo_institucional');
            $table->index(['id_institucion', 'rol'], 'idx_usuarios_institucion_rol');
            $table->index('id_area_asignada', 'idx_usuarios_area_asignada');

            $table->foreign('id_institucion', 'fk_usuarios_instituciones')
                ->references('id_institucion')->on('instituciones')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('id_area_asignada', 'fk_usuarios_areas')
                ->references('id_area')->on('areas')
                ->nullOnDelete()->cascadeOnUpdate();
        });

        DB::unprepared(
            'CREATE TRIGGER trg_usuarios_before_insert BEFORE INSERT ON usuarios '
            . 'FOR EACH ROW BEGIN' . self::CUERPO_TRIGGER . ' END',
        );
        DB::unprepared(
            'CREATE TRIGGER trg_usuarios_before_update BEFORE UPDATE ON usuarios '
            . 'FOR EACH ROW BEGIN' . self::CUERPO_TRIGGER . ' END',
        );
    }

    /**
     * Elimina los triggers y la tabla usuarios.
     *
     * @return void
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_usuarios_before_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_usuarios_before_insert');
        Schema::dropIfExists('usuarios');
    }
};
