<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.7: Notificaciones y Recordatorios
 * @file         TipoNotificacion.php
 * @path         app/Enums/TipoNotificacion.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Canales de una notificación. "push" se entrega dentro del
 *               sistema web porque no hay app móvil (DT-022).
 *
 * @requisitos   RF-005, RF-046, RF-047
 *
 * @see          Manual de Base de Datos v1.2, sección 4.12; DT-022, DT-024
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Enums;

/**
 * Canal de entrega de una notificación.
 *
 * @package App\Enums
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
enum TipoNotificacion: string
{
    case Push = 'push';
    case Email = 'email';
    case Ambas = 'ambas';
}
