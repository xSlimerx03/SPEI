<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         ClavePlan.php
 * @path         app/Enums/ClavePlan.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Claves de los planes de suscripción; enlazan planes.clave con
 *               instituciones.plan_suscripcion.
 *
 * @requisitos   RF-001, RF-003, RF-007
 *
 * @see          DT-024
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Enums;

/**
 * Clave de un plan de suscripción.
 *
 * @package App\Enums
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
enum ClavePlan: string
{
    case Basico = 'basico';
    case Pro = 'pro';
    case Enterprise = 'enterprise';

    /**
     * Nombre del plan para mostrarlo en pantalla.
     *
     * @return string
     */
    public function obtenerEtiqueta(): string
    {
        return match ($this) {
            self::Basico => 'Básico',
            self::Pro => 'Pro',
            self::Enterprise => 'Enterprise',
        };
    }
}
