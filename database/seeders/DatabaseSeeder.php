<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Datos iniciales
 * @file         DatabaseSeeder.php
 * @path         database/seeders/DatabaseSeeder.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Orquesta la siembra: planes y superadmin en todo ambiente;
 *               datos de demostración solo en local.
 *
 * @requisitos   RF-001, RF-007
 *
 * @see          PlanesSeeder, SuperadminSeeder, DemoSeeder
 * ============================================================================
 */

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder principal del SPEI.
 *
 * @package Database\Seeders
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Ejecuta los seeders según el ambiente.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            PlanesSeeder::class,
            SuperadminSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call(DemoSeeder::class);
        }
    }
}
