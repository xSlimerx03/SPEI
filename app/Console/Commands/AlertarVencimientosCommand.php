<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         AlertarVencimientosCommand.php
 * @path         app/Console/Commands/AlertarVencimientosCommand.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Tarea programada diaria (07:00) que emite las alertas de
 *               vencimiento a 7, 3 y 1 días del corte.
 *
 * @requisitos   RF-005
 *
 * @see          routes/console.php; app/Services/AlertaVencimientoService.php
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AlertaVencimientoService;
use Illuminate\Console\Command;

/**
 * Comando spei:alertar-vencimientos.
 *
 * @package App\Console\Commands
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AlertarVencimientosCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'spei:alertar-vencimientos';

    /**
     * @var string
     */
    protected $description = 'Emite las alertas de vencimiento de suscripción (7, 3 y 1 días antes del corte).';

    /**
     * Ejecuta el comando.
     *
     * @param  AlertaVencimientoService  $alertaService  Servicio de alertas.
     * @return int
     */
    public function handle(AlertaVencimientoService $alertaService): int
    {
        $total = $alertaService->emitirAlertas();
        $this->info(sprintf('Alertas emitidas: %d.', $total));

        return self::SUCCESS;
    }
}
