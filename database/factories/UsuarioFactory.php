<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         UsuarioFactory.php
 * @path         database/factories/UsuarioFactory.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Fábrica de usuarios para pruebas. El correo se genera con el
 *               dominio de la institución para cumplir RN-001.
 *
 * @requisitos   RF-002, RF-008, RN-001, RS-006
 *
 * @see          app/Models/Usuario.php; DT-011
 * ============================================================================
 */

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Rol;
use App\Models\Area;
use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Genera usuarios activos con correo del dominio institucional.
 *
 * @extends Factory<Usuario>
 *
 * @package Database\Factories
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class UsuarioFactory extends Factory
{
    /**
     * Contraseña en claro de todos los usuarios de prueba.
     */
    public const CONTRASENA_PRUEBA = 'Contrasena2026';

    protected $model = Usuario::class;

    /**
     * Valores por defecto: alumno activo de una institución nueva.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_institucion' => Institucion::factory(),
            'nombre_completo' => fake()->name(),
            'correo_institucional' => fn (array $atributos): string => $this->generarCorreo($atributos),
            'contrasena_hash' => self::CONTRASENA_PRUEBA,
            'rol' => Rol::Alumno,
            'id_area_asignada' => null,
            'activo' => true,
        ];
    }

    /**
     * Usuario de la institución indicada.
     *
     * @param  Institucion  $institucion  Tenant del usuario.
     * @return static
     */
    public function deInstitucion(Institucion $institucion): static
    {
        return $this->state(['id_institucion' => $institucion->id_institucion]);
    }

    /**
     * Superadmin de la plataforma, sin institución (DT-011).
     *
     * @return static
     */
    public function superadmin(): static
    {
        return $this->state([
            'id_institucion' => null,
            'rol' => Rol::Superadmin,
            'correo_institucional' => fake()->unique()->userName() . '@bigbitbyte.mx',
        ]);
    }

    /**
     * Usuario con el rol indicado.
     *
     * @param  Rol  $rol  Rol deseado.
     * @return static
     */
    public function conRol(Rol $rol): static
    {
        return $this->state(['rol' => $rol]);
    }

    /**
     * Administrador del sistema responsable de un área.
     *
     * @param  Area  $area  Área asignada.
     * @return static
     */
    public function responsableDe(Area $area): static
    {
        return $this->state([
            'id_institucion' => $area->id_institucion,
            'rol' => Rol::AdminSistema,
            'id_area_asignada' => $area->id_area,
        ]);
    }

    /**
     * Usuario que aún no completa su registro.
     *
     * @return static
     */
    public function inactivo(): static
    {
        return $this->state(['activo' => false]);
    }

    /**
     * Arma un correo único con el dominio de la institución del usuario.
     *
     * @param  array<string, mixed>  $atributos  Atributos ya resueltos.
     * @return string
     */
    private function generarCorreo(array $atributos): string
    {
        $institucion = Institucion::query()->find($atributos['id_institucion']);
        $dominio = $institucion instanceof Institucion ? $institucion->dominio_correo : 'bigbitbyte.mx';

        return fake()->unique()->userName() . '@' . $dominio;
    }
}
