<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Infraestructura (colas)
 * @file         0001_01_01_000002_create_failed_jobs_table.php
 * @path         database/migrations/0001_01_01_000002_create_failed_jobs_table.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Tabla interna del framework que conserva los trabajos de cola
 *               fallidos (notificaciones, PDF, cargas CSV) para su reintento.
 *               Las colas viven en Redis; solo los fallos persisten en MySQL.
 *
 * @requisitos   RF-046, RF-047, RF-048, RNF-004
 *
 * @see          docs/decisiones/BBB_DT_Registro_v1.0.md (DT-016)
 * ============================================================================
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de trabajos fallidos.
     */
    public function up(): void
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
            $table->comment('Tabla interna de Laravel: trabajos de cola fallidos (DT-016).');
        });
    }

    /**
     * Elimina la tabla de trabajos fallidos.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
    }
};
