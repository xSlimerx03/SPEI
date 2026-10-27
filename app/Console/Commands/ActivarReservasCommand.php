<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         ActivarReservasCommand.php
 * @path         app/Console/Commands/ActivarReservasCommand.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Tarea programada que abre la ventana de recogida de las
 *               reservas cuyo inicio ya llegó: sus recursos pasan de
 *               "disponible" a "bloqueado" (RF-026, MD 5.6). Idempotente.
 *
 * @requisitos   RF-026, RN-007
 *
 * @see          DT-033; routes/console.php
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\EstadoPrestamo;
use App\Models\Prestamo;
use App\Services\PrestamoService;
use Illuminate\Console\Command;

/**
 * Abre las ventanas de recogida vigentes.
 *
 * @package App\Console\Commands
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ActivarReservasCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'spei:activar-reservas';

    /**
     * @var string
     */
    protected $description = 'Bloquea los recursos de las reservas cuya ventana de recogida ya empezó';

    /**
     * Recorre las reservas pendientes de todas las instituciones dentro de su ventana de recogida.
     *
     * @param  PrestamoService  $servicio  Servicio de préstamos.
     * @return int
     */
    public function handle(PrestamoService $servicio): int
    {
        $total = 0;
        $minutos = max(15, min(20, (int) config('spei.prestamos.tolerancia_recogida')));
        // Sin sesión no hay contexto de tenant: cada préstamo se procesa con su propia institución (MD 5.3).
        $reservas = Prestamo::withoutGlobalScopes()
            ->where('estado', EstadoPrestamo::Pendiente->value)
            ->where('fecha_hora_inicio', '<=', now())
            ->where('fecha_hora_inicio', '>', now()->subMinutes($minutos))
            ->lazyById(100, 'id_prestamo');

        foreach ($reservas as $prestamo) {
            $total += (int) $servicio->activarReserva($prestamo);
        }

        $this->info('Ventanas de recogida abiertas: ' . $total);

        return self::SUCCESS;
    }
}
