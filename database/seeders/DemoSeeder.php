<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Datos de demostración
 * @file         DemoSeeder.php
 * @path         database/seeders/DemoSeeder.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Crea una institución de demostración con administradores,
 *               áreas, recursos y un alumno. Solo se ejecuta en APP_ENV=local
 *               y la contraseña se toma de SPEI_DEMO_CONTRASENA.
 *
 * @requisitos   RF-001, RF-008, RF-009, RF-010, RF-015
 *
 * @see          database/seeders/DatabaseSeeder.php
 * ============================================================================
 */

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ClavePlan;
use App\Enums\EstadoRecurso;
use App\Enums\Rol;
use App\Models\Area;
use App\Models\Institucion;
use App\Models\Recurso;
use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;

/**
 * Siembra la institución "Universidad Demo" (dominio demo.edu.mx).
 *
 * @package Database\Seeders
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class DemoSeeder extends Seeder
{
    private const DOMINIO = 'demo.edu.mx';

    /**
     * Crea los datos de demostración si aún no existen.
     *
     * @return void
     */
    public function run(): void
    {
        $contrasena = (string) config('spei.demo.contrasena');

        if ($contrasena === '' || Institucion::query()->where('dominio_correo', self::DOMINIO)->exists()) {
            /** @var Command|null $comando */
            $comando = $this->command;
            $comando?->warn('Demo omitida: SPEI_DEMO_CONTRASENA vacía o la institución ya existe.');

            return;
        }

        $institucion = Institucion::query()->create([
            'nombre_institucion' => 'Universidad Demo',
            'dominio_correo' => self::DOMINIO,
            'plan_suscripcion' => ClavePlan::Pro,
            'fecha_inicio' => today(),
            'fecha_corte' => today()->addDays(30),
            'limite_areas' => 10,
            'limite_recursos' => 500,
        ]);

        $this->crearUsuarios($institucion, $contrasena);
        $this->crearInventario($institucion);
    }

    /**
     * Crea un usuario por cada rol de tenant.
     *
     * @param  Institucion  $institucion  Institución de demostración.
     * @param  string  $contrasena  Contraseña común de demostración.
     * @return void
     */
    private function crearUsuarios(Institucion $institucion, string $contrasena): void
    {
        $usuarios = [
            ['admin', 'Administración Demo', Rol::AdminInstitucional],
            ['sistemas', 'Sistemas Demo', Rol::AdminSistema],
            ['alumno', 'Alumno Demo', Rol::Alumno],
            ['docente', 'Docente Demo', Rol::Docente],
        ];

        foreach ($usuarios as [$usuario, $nombre, $rol]) {
            Usuario::query()->create([
                'id_institucion' => $institucion->id_institucion,
                'nombre_completo' => $nombre,
                'correo_institucional' => $usuario . '@' . self::DOMINIO,
                'contrasena_hash' => $contrasena,
                'rol' => $rol,
                'activo' => true,
                'fecha_registro' => now(),
            ]);
        }
    }

    /**
     * Crea dos áreas con recursos en distintos estados.
     *
     * @param  Institucion  $institucion  Institución de demostración.
     * @return void
     */
    private function crearInventario(Institucion $institucion): void
    {
        foreach (['Biblioteca', 'Laboratorio de Cómputo'] as $nombreArea) {
            $area = Area::query()->create([
                'id_institucion' => $institucion->id_institucion,
                'nombre_area' => $nombreArea,
                'descripcion' => 'Área de demostración.',
            ]);

            Recurso::factory()->count(6)->deArea($area)->create();
            Recurso::factory()->deArea($area)->conEstado(EstadoRecurso::Mantenimiento)->create();
        }
    }
}
