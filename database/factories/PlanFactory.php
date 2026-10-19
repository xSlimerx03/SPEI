<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         PlanFactory.php
 * @path         database/factories/PlanFactory.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Fábrica de planes de suscripción para pruebas.
 *
 * @requisitos   RF-001, RF-003, RF-007
 *
 * @see          database/seeders/PlanesSeeder.php
 * ============================================================================
 */

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ClavePlan;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Genera planes con límites válidos.
 *
 * @extends Factory<Plan>
 *
 * @package Database\Factories
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * Valores por defecto: plan básico activo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'clave' => ClavePlan::Basico,
            'nombre' => 'Básico',
            'monto_mensual' => 1500,
            'limite_areas' => 3,
            'limite_recursos' => 100,
            'duracion_dias' => 30,
            'activo' => true,
        ];
    }

    /**
     * Plan Pro.
     *
     * @return static
     */
    public function pro(): static
    {
        return $this->state([
            'clave' => ClavePlan::Pro,
            'nombre' => 'Pro',
            'monto_mensual' => 4500,
            'limite_areas' => 10,
            'limite_recursos' => 500,
        ]);
    }

    /**
     * Plan Enterprise.
     *
     * @return static
     */
    public function enterprise(): static
    {
        return $this->state([
            'clave' => ClavePlan::Enterprise,
            'nombre' => 'Enterprise',
            'monto_mensual' => 12000,
            'limite_areas' => 50,
            'limite_recursos' => 5000,
        ]);
    }
}
