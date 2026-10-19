<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         RecursoFactory.php
 * @path         database/factories/RecursoFactory.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Fábrica de recursos para pruebas.
 *
 * @requisitos   RF-010, RF-013, RF-015, RS-006
 *
 * @see          app/Models/Recurso.php
 * ============================================================================
 */

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EstadoRecurso;
use App\Enums\TipoRecurso;
use App\Models\Area;
use App\Models\Recurso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Genera recursos disponibles con inventario y serie únicos.
 *
 * @extends Factory<Recurso>
 *
 * @package Database\Factories
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class RecursoFactory extends Factory
{
    protected $model = Recurso::class;

    /**
     * Valores por defecto: equipo disponible en un área nueva.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_area' => Area::factory(),
            'id_institucion' => fn (array $atributos): int => $this->obtenerInstitucionDelArea($atributos),
            'nombre_recurso' => fake()->randomElement(['Proyector', 'Laptop', 'Cable HDMI', 'Bocina', 'Cámara']),
            'numero_serie' => fake()->unique()->bothify('SN-########'),
            'numero_inventario' => fake()->unique()->numerify('INV-######'),
            'tipo_recurso' => TipoRecurso::EquipoFisico,
            'marca' => fake()->randomElement(['Epson', 'Dell', 'HP', 'Lenovo', 'Sony']),
            'anio_adquisicion' => fake()->numberBetween(2015, 2026),
            'estado' => EstadoRecurso::Disponible,
            'activo' => true,
        ];
    }

    /**
     * Recurso del área indicada (y de su institución).
     *
     * @param  Area  $area  Área de resguardo.
     * @return static
     */
    public function deArea(Area $area): static
    {
        return $this->state([
            'id_area' => $area->id_area,
            'id_institucion' => $area->id_institucion,
        ]);
    }

    /**
     * Recurso con el estado indicado.
     *
     * @param  EstadoRecurso  $estado  Estado deseado.
     * @return static
     */
    public function conEstado(EstadoRecurso $estado): static
    {
        return $this->state(['estado' => $estado]);
    }

    /**
     * Obtiene la institución del área ya resuelta.
     *
     * @param  array<string, mixed>  $atributos  Atributos ya resueltos.
     * @return int
     */
    private function obtenerInstitucionDelArea(array $atributos): int
    {
        return (int) Area::withoutGlobalScopes()->findOrFail($atributos['id_area'])->id_institucion;
    }
}
