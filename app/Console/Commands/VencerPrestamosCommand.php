<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         VencerPrestamosCommand.php
 * @path         app/Console/Commands/VencerPrestamosCommand.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Tarea programada que libera las reservas no recogidas dentro de
 *               la tolerancia de 15 a 20 minutos. Es idempotente: cada préstamo
 *               se revalida bajo lock dentro de PrestamoService::vencer.
 *
 * @requisitos   RF-032, RN-007
 *
 * @see          docs/plan/BBB_MVP_v1.0.md; routes/console.php
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\EstadoPrestamo;
use App\Models\Prestamo;
use App\Services\PrestamoService;
use Illuminate\Console\Command;

/**
 * Vence las reservas pendientes cuya tolerancia de recogida ya pasó.
 *
 * @package App\Console\Commands
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class VencerPrestamosCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'spei:vencer-prestamos';

    /**
     * @var string
     */
    protected $description = 'Libera las reservas que no se recogieron a tiempo';

    /**
     * Recorre las reservas vencidas de todas las instituciones (sin Global Scope, se ejecuta sin sesión).
     *
     * @param  PrestamoService  $servicio  Servicio de préstamos.
     * @return int
     */
    public function handle(PrestamoService $servicio): int
    {
        $total = 0;
        $minutos = max(15, min(20, (int) config('spei.prestamos.tolerancia_recogida')));
        $pendientes = Prestamo::withoutGlobalScopes()
            ->where('estado', EstadoPrestamo::Pendiente->value)
            ->where('fecha_hora_inicio', '<=', now()->subMinutes($minutos))
            ->lazyById(100, 'id_prestamo');

        foreach ($pendientes as $prestamo) {
            $total += (int) $servicio->vencer($prestamo);
        }

        $this->info('Reservas vencidas: ' . $total);

        return self::SUCCESS;
    }
}
