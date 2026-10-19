<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         EstadoSuscripcion.php
 * @path         app/Enums/EstadoSuscripcion.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Estados de la suscripción institucional y sus transiciones
 *               permitidas según la máquina de estados del MBD.
 *
 * @requisitos   RF-004, RF-006, RN-011, RN-013
 *
 * @see          Manual de Base de Datos v1.2, Tabla 28; CU-002
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Enums;

/**
 * Estado del servicio contratado por una institución.
 *
 * @package App\Enums
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
enum EstadoSuscripcion: string
{
    case Activa = 'activa';
    case SoloLectura = 'solo_lectura';
    case Suspendida = 'suspendida';
    case Cancelada = 'cancelada';

    /**
     * Indica si los usuarios del tenant pueden iniciar sesión (RN-013).
     *
     * @return bool
     */
    public function permiteAcceso(): bool
    {
        return $this === self::Activa || $this === self::SoloLectura;
    }

    /**
     * Indica si se permiten operaciones de escritura (RN-011).
     *
     * @return bool
     */
    public function permiteEscritura(): bool
    {
        return $this === self::Activa;
    }

    /**
     * Indica si la transición manual hacia el estado destino es válida (MBD Tabla 28).
     *
     * @param  EstadoSuscripcion  $destino  Estado solicitado.
     * @return bool
     */
    public function puedeCambiarA(self $destino): bool
    {
        $permitidos = match ($this) {
            self::Activa => [self::SoloLectura, self::Suspendida, self::Cancelada],
            self::SoloLectura, self::Suspendida => [self::Activa, self::Cancelada],
            self::Cancelada => [],
        };

        return in_array($destino, $permitidos, true);
    }

    /**
     * Devuelve el nombre del estado para mostrarlo en pantalla.
     *
     * @return string
     */
    public function obtenerEtiqueta(): string
    {
        return match ($this) {
            self::Activa => 'Activa',
            self::SoloLectura => 'Solo lectura',
            self::Suspendida => 'Suspendida',
            self::Cancelada => 'Cancelada',
        };
    }
}
