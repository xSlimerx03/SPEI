<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         2026_10_18_000002_create_instituciones_table.php
 * @path         database/migrations/2026_10_18_000002_create_instituciones_table.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Crea la tabla instituciones, raíz del modelo multi-tenant del
 *               SPEI: plan, vigencia, límites, estado de suscripción y dominio
 *               de correo con el que se determina el rol (RN-001).
 *
 * @requisitos   RF-001, RF-002, RF-003, RF-004, RF-005, RF-006, RF-007, RF-008,
 *               RN-001, RN-010, RN-011, RN-013
 *
 * @see          Manual de Base de Datos v1.2, sección 4.2; DT-024
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
     * Crea la tabla instituciones con sus restricciones de vigencia y límites.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('instituciones', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->comment('Tenants del SaaS: plan, vigencia, límites y estado de suscripción.');

            $table->bigIncrements('id_institucion')->comment('Identificador único del tenant.');
            $table->string('nombre_institucion', 150)->comment('Nombre oficial de la institución.');
            $table->string('dominio_correo', 100)
                ->comment('Dominio de correo institucional sin arroba; determina el tenant y el rol (RN-001).');
            $table->enum('plan_suscripcion', ['basico', 'pro', 'enterprise'])->default('basico')
                ->comment('Plan contratado.');
            $table->date('fecha_inicio')->comment('Fecha de inicio de vigencia.');
            $table->date('fecha_corte')->comment('Fecha de corte de la suscripción.');
            $table->enum('estado_suscripcion', ['activa', 'solo_lectura', 'suspendida', 'cancelada'])
                ->default('activa')
                ->comment('Estado actual del servicio (MBD Tabla 28).');
            $table->unsignedInteger('limite_areas')->default(3)->comment('Límite de áreas permitidas por plan.');
            $table->unsignedInteger('limite_recursos')->default(100)
                ->comment('Límite de recursos permitidos por plan.');
            $table->timestamp('fecha_creacion')->useCurrent()->comment('Fecha de alta.');
            $table->timestamp('fecha_actualizacion')->useCurrent()->useCurrentOnUpdate()
                ->comment('Última modificación.');

            $table->unique('dominio_correo', 'uk_instituciones_dominio_correo');
            $table->index(['estado_suscripcion', 'fecha_corte'], 'idx_instituciones_estado_fecha_corte');
        });

        DB::statement(
            'ALTER TABLE instituciones ADD CONSTRAINT chk_instituciones_vigencia '
            . 'CHECK (fecha_corte >= fecha_inicio AND limite_areas > 0 AND limite_recursos > 0)',
        );
    }

    /**
     * Elimina la tabla instituciones.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('instituciones');
    }
};
