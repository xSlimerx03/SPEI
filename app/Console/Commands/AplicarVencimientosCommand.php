<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         AplicarVencimientosCommand.php
 * @path         app/Console/Commands/AplicarVencimientosCommand.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Tarea programada diaria (00:05) que pasa a solo lectura las
 *               suscripciones vencidas (RN-013).
 *
 * @requisitos   RF-006, RN-013
 *
 * @see          routes/console.php; app/Services/SuscripcionService.php
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SuscripcionService;
use Illuminate\Console\Command;

/**
 * Comando spei:aplicar-vencimientos.
 *
 * @package App\Console\Commands
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AplicarVencimientosCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'spei:aplicar-vencimientos';

    /**
     * @var string
     */
    protected $description = 'Pasa a solo lectura las suscripciones activas cuya fecha de corte ya pasó.';

    /**
     * Ejecuta el comando.
     *
     * @param  SuscripcionService  $suscripcionService  Servicio de suscripciones.
     * @return int
     */
    public function handle(SuscripcionService $suscripcionService): int
    {
        $total = $suscripcionService->aplicarVencimientos();
        $this->info(sprintf('Instituciones en solo lectura: %d.', $total));

        return self::SUCCESS;
    }
}
