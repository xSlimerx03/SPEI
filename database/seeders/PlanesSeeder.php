<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         PlanesSeeder.php
 * @path         database/seeders/PlanesSeeder.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Carga los tres planes iniciales. Es idempotente: si el plan
 *               ya existe no modifica los valores que el superadmin cambió.
 *
 * @requisitos   RF-001, RF-007, RN-010
 *
 * @see          DT-024; MBD sección 4.2 (límites por defecto)
 * ============================================================================
 */

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ClavePlan;
use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Siembra los planes Básico, Pro y Enterprise.
 *
 * @package Database\Seeders
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class PlanesSeeder extends Seeder
{
    /**
     * Planes iniciales: [clave, nombre, monto mensual, límite de áreas, límite de recursos].
     *
     * @var list<array{0: ClavePlan, 1: string, 2: int, 3: int, 4: int}>
     */
    private const PLANES = [
        [ClavePlan::Basico, 'Básico', 1500, 3, 100],
        [ClavePlan::Pro, 'Pro', 4500, 10, 500],
        [ClavePlan::Enterprise, 'Enterprise', 12000, 50, 5000],
    ];

    /**
     * Crea los planes que aún no existen.
     *
     * @return void
     */
    public function run(): void
    {
        foreach (self::PLANES as [$clave, $nombre, $monto, $limiteAreas, $limiteRecursos]) {
            Plan::query()->firstOrCreate(
                ['clave' => $clave],
                [
                    'nombre' => $nombre,
                    'monto_mensual' => $monto,
                    'limite_areas' => $limiteAreas,
                    'limite_recursos' => $limiteRecursos,
                    'duracion_dias' => 30,
                    'activo' => true,
                ],
            );
        }
    }
}
