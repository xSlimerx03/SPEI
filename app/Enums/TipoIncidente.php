<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.6: Penalizaciones y Sanciones Automatizadas
 * @file         TipoIncidente.php
 * @path         app/Enums/TipoIncidente.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Tipos de incidente que generan marcas (MBD Tablas 39 y 40):
 *               matriz_sanciones.tipo_incidente y
 *               penalizaciones.tipo_penalizacion.
 *
 * @requisitos   RF-041, RF-042, RN-005
 *
 * @see          CU-019
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Enums;

/**
 * Tipo de incidente sancionable.
 *
 * @package App\Enums
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
enum TipoIncidente: string
{
    case Retraso = 'retraso';
    case MalEstado = 'mal_estado';
    case Danado = 'danado';
    case NoEntregado = 'no_entregado';

    /**
     * Nombre del incidente para mostrarlo en pantalla.
     *
     * @return string
     */
    public function obtenerEtiqueta(): string
    {
        return match ($this) {
            self::Retraso => 'Devolución tardía',
            self::MalEstado => 'Devuelto en mal estado',
            self::Danado => 'Devuelto dañado',
            self::NoEntregado => 'No devuelto',
        };
    }

    /**
     * Incidente que corresponde a la condición registrada al devolver; null si no hay incidente.
     *
     * @param  string|null  $condicion  Valor de detalle_prestamo.condicion_devolucion.
     * @return self|null
     */
    public static function desdeCondicion(?string $condicion): ?self
    {
        return match ($condicion) {
            'malo' => self::MalEstado,
            'danado' => self::Danado,
            'no_entregado' => self::NoEntregado,
            default => null,
        };
    }
}
