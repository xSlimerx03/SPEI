<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         InstitucionFactory.php
 * @path         database/factories/InstitucionFactory.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Fábrica de instituciones (tenants) para pruebas.
 *
 * @requisitos   RF-001, RF-004, RF-006, RS-006
 *
 * @see          app/Models/Institucion.php
 * ============================================================================
 */

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ClavePlan;
use App\Enums\EstadoSuscripcion;
use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Genera instituciones activas con dominio único.
 *
 * @extends Factory<Institucion>
 *
 * @package Database\Factories
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class InstitucionFactory extends Factory
{
    protected $model = Institucion::class;

    /**
     * Valores por defecto: institución activa con plan básico y 30 días de vigencia.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre_institucion' => 'Universidad ' . fake()->unique()->lastName(),
            'dominio_correo' => fake()->unique()->lexify('????????') . '.edu.mx',
            'plan_suscripcion' => ClavePlan::Basico,
            'fecha_inicio' => today(),
            'fecha_corte' => today()->addDays(30),
            'estado_suscripcion' => EstadoSuscripcion::Activa,
            'limite_areas' => 3,
            'limite_recursos' => 100,
        ];
    }

    /**
     * Institución con el estado de suscripción indicado.
     *
     * @param  EstadoSuscripcion  $estado  Estado deseado.
     * @return static
     */
    public function conEstado(EstadoSuscripcion $estado): static
    {
        return $this->state(['estado_suscripcion' => $estado]);
    }

    /**
     * Institución cuya fecha de corte cae dentro de N días (negativo: vencida).
     *
     * @param  int  $dias  Días hasta el corte.
     * @return static
     */
    public function conCorteEn(int $dias): static
    {
        return $this->state([
            'fecha_inicio' => today()->subDays(60),
            'fecha_corte' => today()->addDays($dias),
        ]);
    }
}
