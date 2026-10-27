<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.8: Historias, Reportes e Inteligencia Operativa
 * @file         ComprimirHistorialCommand.php
 * @path         app/Console/Commands/ComprimirHistorialCommand.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Tarea programada diaria del ciclo de vida del historial
 *               (RN-012): comprime los meses de más de un año y elimina los
 *               archivos que cumplieron cuatro años.
 *
 * @requisitos   RF-052, RN-012
 *
 * @see          CU-023; routes/console.php
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\HistorialService;
use Illuminate\Console\Command;

/**
 * Comprime y depura el historial.
 *
 * @package App\Console\Commands
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ComprimirHistorialCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'spei:comprimir-historial';

    /**
     * @var string
     */
    protected $description = 'Comprime el historial de más de un año y elimina los archivos de más de cuatro';

    /**
     * Ejecuta la tarea.
     *
     * @param  HistorialService  $servicio  Servicio de historial.
     * @return int
     */
    public function handle(HistorialService $servicio): int
    {
        $this->info('Meses comprimidos: ' . $servicio->comprimir());
        $this->info('Archivos eliminados: ' . $servicio->eliminarVencidos());

        return self::SUCCESS;
    }
}
