<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Tareas programadas
 * @file         console.php
 * @path         routes/console.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Programación de las tareas diarias de la suscripción. El
 *               contenedor ejecuta "php artisan schedule:work".
 *
 * @requisitos   RF-005, RF-006, RN-013
 *
 * @see          app/Console/Commands
 * ============================================================================
 */

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('spei:aplicar-vencimientos')->dailyAt('00:05')->withoutOverlapping()->onOneServer();
Schedule::command('spei:alertar-vencimientos')->dailyAt('07:00')->withoutOverlapping()->onOneServer();
Schedule::command('spei:vencer-prestamos')->everyMinute()->withoutOverlapping()->onOneServer();
