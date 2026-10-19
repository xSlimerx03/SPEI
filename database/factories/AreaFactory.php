<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         AreaFactory.php
 * @path         database/factories/AreaFactory.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Fábrica de áreas para pruebas.
 *
 * @requisitos   RF-009, RS-006
 *
 * @see          app/Models/Area.php
 * ============================================================================
 */

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Area;
use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Genera áreas activas con nombre único.
 *
 * @extends Factory<Area>
 *
 * @package Database\Factories
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AreaFactory extends Factory
{
    protected $model = Area::class;

    /**
     * Valores por defecto: área activa de una institución nueva.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_institucion' => Institucion::factory(),
            'nombre_area' => 'Área ' . fake()->unique()->numerify('####'),
            'descripcion' => fake()->sentence(),
            'activo' => true,
        ];
    }

    /**
     * Área de la institución indicada.
     *
     * @param  Institucion  $institucion  Tenant del área.
     * @return static
     */
    public function deInstitucion(Institucion $institucion): static
    {
        return $this->state(['id_institucion' => $institucion->id_institucion]);
    }

    /**
     * Área desactivada.
     *
     * @return static
     */
    public function inactiva(): static
    {
        return $this->state(['activo' => false]);
    }
}
