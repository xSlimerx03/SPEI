<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         EstadoRecurso.php
 * @path         app/Enums/EstadoRecurso.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Estados de un recurso y el color de su etiqueta en pantalla.
 *
 * @requisitos   RF-013, RF-014, RF-015, RN-002
 *
 * @see          Manual de Base de Datos v1.2, Tabla 30; CU-009
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Enums;

/**
 * Estado operativo de un recurso prestable.
 *
 * @package App\Enums
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
enum EstadoRecurso: string
{
    case Disponible = 'disponible';
    case Prestado = 'prestado';
    case Bloqueado = 'bloqueado';
    case Mantenimiento = 'mantenimiento';

    /**
     * Indica si el recurso está comprometido por una reserva o un préstamo.
     *
     * @return bool
     */
    public function estaEnUso(): bool
    {
        return $this === self::Prestado || $this === self::Bloqueado;
    }

    /**
     * Devuelve el nombre del estado para mostrarlo en pantalla.
     *
     * @return string
     */
    public function obtenerEtiqueta(): string
    {
        return match ($this) {
            self::Disponible => 'Disponible',
            self::Prestado => 'Prestado',
            self::Bloqueado => 'Bloqueado',
            self::Mantenimiento => 'Mantenimiento',
        };
    }

    /**
     * Devuelve la clase CSS del badge de estado (colores del prototipo, DT-028).
     *
     * @return string
     */
    public function obtenerClaseInsignia(): string
    {
        return match ($this) {
            self::Disponible => 'insignia--exito',
            self::Prestado => 'insignia--activo',
            self::Bloqueado => 'insignia--pendiente',
            self::Mantenimiento => 'insignia--neutra',
        };
    }
}
