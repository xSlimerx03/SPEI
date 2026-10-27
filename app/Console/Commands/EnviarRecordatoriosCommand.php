<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.7: Notificaciones y Recordatorios
 * @file         EnviarRecordatoriosCommand.php
 * @path         app/Console/Commands/EnviarRecordatoriosCommand.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Tarea programada (cada minuto) que envía los recordatorios
 *               previos de entrega y devolución (RF-046). Idempotente.
 *
 * @requisitos   RF-046
 *
 * @see          CU-021; routes/console.php
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\RecordatorioService;
use Illuminate\Console\Command;

/**
 * Envía los recordatorios pendientes.
 *
 * @package App\Console\Commands
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class EnviarRecordatoriosCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'spei:enviar-recordatorios';

    /**
     * @var string
     */
    protected $description = 'Envía los recordatorios previos de entrega y devolución';

    /**
     * Ejecuta la tarea.
     *
     * @param  RecordatorioService  $servicio  Servicio de recordatorios.
     * @return int
     */
    public function handle(RecordatorioService $servicio): int
    {
        $this->info('Recordatorios enviados: ' . $servicio->enviarPendientes());

        return self::SUCCESS;
    }
}
