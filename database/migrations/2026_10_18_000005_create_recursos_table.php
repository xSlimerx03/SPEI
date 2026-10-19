<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         2026_10_18_000005_create_recursos_table.php
 * @path         database/migrations/2026_10_18_000005_create_recursos_table.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Crea la tabla recursos (inventario de equipos y espacios) con
 *               id_institucion (DT-008), identificadores únicos por tenant y el
 *               índice de estado que usa el catálogo (RN-002).
 *
 * @requisitos   RF-010, RF-011, RF-012, RF-013, RF-014, RF-015, RN-002, RN-010, RS-006
 *
 * @see          Manual de Base de Datos v1.2, sección 4.5 y Tabla 27; DT-008
 * ============================================================================
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla recursos con sus llaves únicas y foráneas.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('recursos', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->comment('Inventario de equipos, cables y espacios prestables por institución.');

            $table->bigIncrements('id_recurso')->comment('Identificador único del recurso.');
            $table->unsignedBigInteger('id_institucion')->comment('Tenant al que pertenece (DT-008).');
            $table->unsignedBigInteger('id_area')->comment('Área de resguardo.');
            $table->string('nombre_recurso', 150)->comment('Nombre del recurso.');
            $table->string('numero_serie', 100)->nullable()->comment('Número de serie del fabricante.');
            $table->string('numero_inventario', 100)->comment('Número de inventario institucional.');
            $table->enum('tipo_recurso', ['equipo_fisico', 'espacio'])->default('equipo_fisico')
                ->comment('Tipo de recurso.');
            $table->string('marca', 100)->nullable()->comment('Marca del recurso.');
            $table->year('anio_adquisicion')->nullable()->comment('Año de adquisición.');
            $table->enum('estado', ['disponible', 'prestado', 'bloqueado', 'mantenimiento'])
                ->default('disponible')
                ->comment('Estado actual del recurso (MBD Tabla 30).');
            $table->boolean('activo')->default(true)->comment('Si el recurso está activo.');
            $table->timestamp('fecha_creacion')->useCurrent()->comment('Fecha de alta.');

            $table->unique(['id_institucion', 'numero_inventario'], 'uk_recursos_institucion_numero_inventario');
            $table->unique(['id_institucion', 'numero_serie'], 'uk_recursos_institucion_numero_serie');
            $table->index(['id_institucion', 'estado'], 'idx_recursos_institucion_estado');
            $table->index('id_area', 'idx_recursos_area');

            $table->foreign('id_institucion', 'fk_recursos_instituciones')
                ->references('id_institucion')->on('instituciones')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('id_area', 'fk_recursos_areas')
                ->references('id_area')->on('areas')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Elimina la tabla recursos.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('recursos');
    }
};
