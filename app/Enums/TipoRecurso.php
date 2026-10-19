<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         TipoRecurso.php
 * @path         app/Enums/TipoRecurso.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Tipos de recurso prestable (equipo físico o espacio).
 *
 * @requisitos   RF-010, RF-012, RF-015
 *
 * @see          Manual de Base de Datos v1.2, sección 4.5
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Enums;

/**
 * Tipo de recurso registrado en el inventario.
 *
 * @package App\Enums
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
enum TipoRecurso: string
{
    case EquipoFisico = 'equipo_fisico';
    case Espacio = 'espacio';

    /**
     * Devuelve el nombre del tipo para mostrarlo en pantalla.
     *
     * @return string
     */
    public function obtenerEtiqueta(): string
    {
        return match ($this) {
            self::EquipoFisico => 'Equipo físico',
            self::Espacio => 'Espacio',
        };
    }
}
