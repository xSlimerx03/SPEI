<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.6: Penalizaciones y Sanciones Automatizadas
 * @file         2026_10_27_000003_create_sanciones_tables.php
 * @path         database/migrations/2026_10_27_000003_create_sanciones_tables.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Crea matriz_sanciones (MBD 4.10) y penalizaciones (MBD 4.9) con
 *               id_institucion (DT-008) y las cascadas de MBD Tabla 27, más los
 *               triggers que mantienen usuarios.marcas_acumuladas de forma
 *               atómica al registrar o exentar una penalización (MBD Tabla 7,
 *               RF-042 y RF-044).
 *
 * @requisitos   RF-041, RF-042, RF-043, RF-044, RN-005, RS-006
 *
 * @see          docs/bd/BBB_DDL03_TablasUsuariosSanciones_v1.0.md; DDL-04; DT-008, DT-009
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
     * Tipos de incidente de MBD Tablas 39 y 40.
     */
    private const TIPOS = ['retraso', 'mal_estado', 'danado', 'no_entregado'];

    /**
     * Crea las tablas y los triggers.
     *
     * @return void
     */
    public function up(): void
    {
        $this->crearMatriz();
        $this->crearPenalizaciones();
        DB::unprepared(
            'CREATE TRIGGER trg_penalizaciones_after_insert AFTER INSERT ON penalizaciones
            FOR EACH ROW BEGIN
                IF NEW.exentada = 0 THEN
                    UPDATE usuarios SET marcas_acumuladas = marcas_acumuladas + NEW.marcas_aplicadas
                    WHERE id_usuario = NEW.id_usuario;
                END IF;
            END',
        );
        DB::unprepared(
            'CREATE TRIGGER trg_penalizaciones_after_update AFTER UPDATE ON penalizaciones
            FOR EACH ROW BEGIN
                IF OLD.exentada = 0 AND NEW.exentada = 1 THEN
                    UPDATE usuarios SET marcas_acumuladas = IF(
                        marcas_acumuladas > NEW.marcas_aplicadas, marcas_acumuladas - NEW.marcas_aplicadas, 0
                    )
                    WHERE id_usuario = NEW.id_usuario;
                END IF;
            END',
        );
    }

    /**
     * Elimina los triggers y las tablas en orden de dependencias.
     *
     * @return void
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_penalizaciones_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_penalizaciones_after_insert');
        Schema::dropIfExists('penalizaciones');
        Schema::dropIfExists('matriz_sanciones');
    }

    /**
     * Reglas configurables de sanción por institución (RF-041).
     *
     * @return void
     */
    private function crearMatriz(): void
    {
        Schema::create('matriz_sanciones', function (Blueprint $table): void {
            $table->engine('InnoDB');
            $table->comment('Reglas configurables de sanción por institución y tipo de incidente.');

            $table->bigIncrements('id_regla')->comment('Identificador único de la regla.');
            $table->unsignedBigInteger('id_institucion')->comment('Tenant al que pertenece.');
            $table->enum('tipo_incidente', self::TIPOS)->comment('Tipo de incidente.');
            $table->unsignedInteger('marcas_por_incidente')->default(1)->comment('Marcas por incidente.');
            $table->unsignedInteger('umbral_bloqueo')->default(3)->comment('Marcas acumuladas que bloquean.');
            $table->unsignedInteger('dias_sancion')->default(7)->comment('Días de bloqueo al alcanzar el umbral.');
            $table->boolean('activo')->default(true)->comment('Si la regla está activa.');
            $table->timestamp('fecha_creacion')->useCurrent()->comment('Fecha de alta.');
            $table->timestamp('fecha_actualizacion')->nullable()->comment('Último cambio.');

            $table->unique(['id_institucion', 'tipo_incidente'], 'uk_matriz_sanciones_institucion_tipo_incidente');
            $table->foreign('id_institucion', 'fk_matriz_sanciones_instituciones')
                ->references('id_institucion')->on('instituciones')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Sanciones aplicadas a usuarios finales (RF-042 a RF-044).
     *
     * @return void
     */
    private function crearPenalizaciones(): void
    {
        Schema::create('penalizaciones', function (Blueprint $table): void {
            $table->engine('InnoDB');
            $table->comment('Sanciones aplicadas a usuarios finales por incidencias en sus préstamos.');

            $table->bigIncrements('id_penalizacion')->comment('Identificador único de la penalización.');
            $table->unsignedBigInteger('id_institucion')->comment('Tenant al que pertenece (DT-008).');
            $table->unsignedBigInteger('id_usuario')->comment('Usuario sancionado.');
            $table->unsignedBigInteger('id_prestamo')->nullable()->comment('Préstamo asociado.');
            $table->enum('tipo_penalizacion', self::TIPOS)->comment('Tipo de penalización.');
            $table->text('motivo')->comment('Motivo de la sanción.');
            $table->unsignedInteger('marcas_aplicadas')->default(1)->comment('Marcas aplicadas.');
            $table->date('fecha_inicio_sancion')->nullable()->comment('Inicio del bloqueo que provocó, si lo hubo.');
            $table->date('fecha_fin_sancion')->nullable()->comment('Fin del bloqueo que provocó, si lo hubo.');
            $table->boolean('exentada')->default(false)->comment('Si fue exentada (RF-044).');
            $table->text('motivo_exencion')->nullable()->comment('Motivo de la exención.');
            $table->timestamp('fecha_creacion')->useCurrent()->comment('Fecha de alta.');
            $table->timestamp('fecha_actualizacion')->nullable()->comment('Último cambio (exención).');

            $table->index(
                ['id_institucion', 'id_usuario', 'fecha_creacion'],
                'idx_penalizaciones_institucion_usuario_fecha',
            );
            $table->index(
                ['id_institucion', 'tipo_penalizacion', 'fecha_creacion'],
                'idx_penalizaciones_institucion_tipo_fecha',
            );
            $table->index('id_usuario', 'idx_penalizaciones_usuario');
            $table->index('id_prestamo', 'idx_penalizaciones_prestamo');
            $table->foreign('id_institucion', 'fk_penalizaciones_instituciones')
                ->references('id_institucion')->on('instituciones')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('id_usuario', 'fk_penalizaciones_usuarios')
                ->references('id_usuario')->on('usuarios')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('id_prestamo', 'fk_penalizaciones_prestamos')
                ->references('id_prestamo')->on('prestamos')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }
};
