<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         EstadoPrestamo.php
 * @path         app/Enums/EstadoPrestamo.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Estados de la columna prestamos.estado con su etiqueta y el
 *               badge que usan las vistas (DT-028).
 *
 * @requisitos   RF-022, RF-028, RF-034, RF-036, RF-051
 *
 * @see          Manual de Base de Datos v1.2, Tabla 29; DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Enums;

/**
 * Estado de un préstamo.
 *
 * @package App\Enums
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
enum EstadoPrestamo: string
{
    case Pendiente = 'pendiente';
    case Activo = 'activo';
    case Devuelto = 'devuelto';
    case Vencido = 'vencido';
    case Cancelado = 'cancelado';

    /**
     * Devuelve el nombre del estado para mostrarlo en pantalla.
     *
     * @return string
     */
    public function obtenerEtiqueta(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Activo => 'Activo',
            self::Devuelto => 'Devuelto',
            self::Vencido => 'Vencido',
            self::Cancelado => 'Cancelado',
        };
    }

    /**
     * Devuelve la clase CSS del badge del estado.
     *
     * @return string
     */
    public function obtenerClaseInsignia(): string
    {
        return match ($this) {
            self::Pendiente => 'insignia--pendiente',
            self::Activo => 'insignia--activo',
            self::Devuelto => 'insignia--exito',
            self::Vencido => 'insignia--peligro',
            self::Cancelado => 'insignia--neutra',
        };
    }

    /**
     * Indica si el préstamo sigue abierto (reservado o en custodia).
     *
     * @return bool
     */
    public function estaAbierto(): bool
    {
        return $this === self::Pendiente || $this === self::Activo;
    }
}
