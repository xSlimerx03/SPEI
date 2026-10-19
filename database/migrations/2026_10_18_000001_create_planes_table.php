<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         2026_10_18_000001_create_planes_table.php
 * @path         database/migrations/2026_10_18_000001_create_planes_table.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Crea la tabla maestra global de planes de suscripción, que el
 *               superadmin administra (RF-007) y de la que cada institución
 *               copia sus límites al contratar o cambiar de plan (RN-010).
 *
 * @requisitos   RF-001, RF-003, RF-007, RN-010
 *
 * @see          docs/bd/DDL-03_20261018_creacion_tablas_bloque_a.md; DT-024
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
     * Crea la tabla planes y su restricción de límites positivos.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->comment('Planes de suscripción del SaaS con precio, límites y duración (RF-007).');

            $table->bigIncrements('id_plan')->comment('Identificador único del plan.');
            $table->enum('clave', ['basico', 'pro', 'enterprise'])
                ->comment('Clave del plan; coincide con instituciones.plan_suscripcion.');
            $table->string('nombre', 100)->comment('Nombre comercial del plan.');
            $table->decimal('monto_mensual', 10, 2)->comment('Precio mensual del plan en pesos mexicanos.');
            $table->unsignedInteger('limite_areas')->comment('Número máximo de áreas permitidas (RN-010).');
            $table->unsignedInteger('limite_recursos')->comment('Número máximo de recursos permitidos (RN-010).');
            $table->unsignedInteger('duracion_dias')->default(30)->comment('Días de vigencia por periodo contratado.');
            $table->boolean('activo')->default(true)->comment('Si el plan se ofrece en la contratación.');
            $table->timestamp('fecha_creacion')->useCurrent()->comment('Fecha de alta del plan.');
            $table->timestamp('fecha_actualizacion')->useCurrent()->useCurrentOnUpdate()
                ->comment('Última modificación del plan.');

            $table->unique('clave', 'uk_planes_clave');
        });

        DB::statement(
            'ALTER TABLE planes ADD CONSTRAINT chk_planes_limites '
            . 'CHECK (limite_areas > 0 AND limite_recursos > 0 AND duracion_dias > 0)',
        );
    }

    /**
     * Elimina la tabla planes.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
