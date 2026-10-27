<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.6: Penalizaciones y Sanciones Automatizadas
 * @file         LevantarSancionesCommand.php
 * @path         app/Console/Commands/LevantarSancionesCommand.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Tarea programada que levanta los bloqueos cuyo plazo terminó
 *               y reinicia el contador de marcas (RF-043, DT-036). Idempotente.
 *
 * @requisitos   RF-043, RN-005
 *
 * @see          CU-019; routes/console.php
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SancionService;
use Illuminate\Console\Command;

/**
 * Levanta las sanciones cumplidas.
 *
 * @package App\Console\Commands
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class LevantarSancionesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'spei:levantar-sanciones';

    /**
     * @var string
     */
    protected $description = 'Desbloquea a los usuarios cuya sanción ya terminó';

    /**
     * Ejecuta la tarea.
     *
     * @param  SancionService  $servicio  Servicio de sanciones.
     * @return int
     */
    public function handle(SancionService $servicio): int
    {
        $this->info('Usuarios desbloqueados: ' . $servicio->levantarVencidas());

        return self::SUCCESS;
    }
}
