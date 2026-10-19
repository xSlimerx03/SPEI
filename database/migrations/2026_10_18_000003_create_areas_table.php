<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         2026_10_18_000003_create_areas_table.php
 * @path         database/migrations/2026_10_18_000003_create_areas_table.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Crea la tabla areas (dependencias que resguardan recursos) con
 *               su relación a instituciones y el nombre único por tenant.
 *
 * @requisitos   RF-009, RF-014, RF-019, RN-010, RS-006
 *
 * @see          Manual de Base de Datos v1.2, sección 4.3 y Tabla 27; DT-024
 * ============================================================================
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla areas con su llave foránea al tenant.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->comment('Dependencias institucionales que resguardan recursos prestables.');

            $table->bigIncrements('id_area')->comment('Identificador único del área.');
            $table->unsignedBigInteger('id_institucion')->comment('Tenant al que pertenece.');
            $table->string('nombre_area', 150)->comment('Nombre del área (ej. Biblioteca).');
            $table->text('descripcion')->nullable()->comment('Descripción del área.');
            $table->boolean('activo')->default(true)->comment('Si el área está activa.');
            $table->timestamp('fecha_creacion')->useCurrent()->comment('Fecha de alta.');

            $table->unique(['id_institucion', 'nombre_area'], 'uk_areas_institucion_nombre_area');
            $table->index(['id_institucion', 'activo'], 'idx_areas_institucion_activo');

            $table->foreign('id_institucion', 'fk_areas_instituciones')
                ->references('id_institucion')->on('instituciones')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Elimina la tabla areas.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
