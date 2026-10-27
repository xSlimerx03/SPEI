<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         SuperadminSeeder.php
 * @path         database/seeders/SuperadminSeeder.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Crea la cuenta del superadmin de la plataforma con las
 *               credenciales de .env; nunca hay credenciales en el código
 *               (MD 7.1).
 *
 * @requisitos   RF-004, RF-006, RF-007, RNF-001
 *
 * @see          DT-003, DT-011; .env.example (SPEI_SUPERADMIN_*)
 * ============================================================================
 */

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;

/**
 * Siembra el superadmin si sus credenciales están configuradas.
 *
 * @package Database\Seeders
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class SuperadminSeeder extends Seeder
{
    /**
     * Crea el superadmin; no hace nada si falta el correo o la contraseña.
     *
     * @return void
     */
    public function run(): void
    {
        $correo = (string) config('spei.superadmin.correo');
        $contrasena = (string) config('spei.superadmin.contrasena');

        if ($correo === '' || $contrasena === '') {
            /** @var Command|null $comando */
            $comando = $this->command;
            $comando?->warn('SPEI_SUPERADMIN_CORREO o SPEI_SUPERADMIN_CONTRASENA vacíos: no se creó el superadmin.');

            return;
        }

        Usuario::query()->firstOrCreate(
            ['correo_institucional' => mb_strtolower($correo)],
            [
                'id_institucion' => null,
                'nombre_completo' => (string) config('spei.superadmin.nombre'),
                'contrasena_hash' => $contrasena,
                'rol' => Rol::Superadmin,
                'activo' => true,
                'fecha_registro' => now(),
            ],
        );
    }
}
