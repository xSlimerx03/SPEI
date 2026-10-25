<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         FormatoFecha.php
 * @path         app/Support/FormatoFecha.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Formatos cortos de fecha del prototipo ("Hoy", "Mañana",
 *               "Lun 26 oct") independientes de la configuración regional del
 *               servidor.
 *
 * @requisitos   RNF-008
 *
 * @see          DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Support;

use DateTimeInterface;
use Illuminate\Support\Carbon;

/**
 * Fechas cortas en español.
 *
 * @package App\Support
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class FormatoFecha
{
    private const DIAS = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

    private const MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    /**
     * Día relativo: "Hoy", "Mañana", "Ayer" o "Lun 26 oct".
     *
     * @param  DateTimeInterface  $fecha  Fecha a describir.
     * @return string
     */
    public static function dia(DateTimeInterface $fecha): string
    {
        $fecha = Carbon::instance($fecha);

        return match (true) {
            $fecha->isToday() => 'Hoy',
            $fecha->isTomorrow() => 'Mañana',
            $fecha->isYesterday() => 'Ayer',
            default => self::corta($fecha),
        };
    }

    /**
     * Fecha corta con día de la semana: "Lun 26 oct".
     *
     * @param  DateTimeInterface  $fecha  Fecha a describir.
     * @return string
     */
    public static function corta(DateTimeInterface $fecha): string
    {
        $fecha = Carbon::instance($fecha);

        return self::DIAS[$fecha->dayOfWeek] . ' ' . $fecha->day . ' ' . self::MESES[$fecha->month - 1];
    }

    /**
     * Fecha con año: "26 oct 2026".
     *
     * @param  DateTimeInterface  $fecha  Fecha a describir.
     * @return string
     */
    public static function conAnio(DateTimeInterface $fecha): string
    {
        $fecha = Carbon::instance($fecha);

        return $fecha->day . ' ' . self::MESES[$fecha->month - 1] . ' ' . $fecha->year;
    }

    /**
     * Periodo de un préstamo: "Lun 26 oct · 10:00 – 12:00" o con dos fechas si cambia el día.
     *
     * @param  DateTimeInterface  $inicio  Inicio.
     * @param  DateTimeInterface  $fin  Fin.
     * @return string
     */
    public static function periodo(DateTimeInterface $inicio, DateTimeInterface $fin): string
    {
        $inicio = Carbon::instance($inicio);
        $fin = Carbon::instance($fin);

        if ($inicio->isSameDay($fin)) {
            return self::dia($inicio) . ' · ' . $inicio->format('H:i') . ' – ' . $fin->format('H:i');
        }

        return self::dia($inicio) . ' ' . $inicio->format('H:i') . ' – ' . self::dia($fin) . ' ' . $fin->format('H:i');
    }
}
