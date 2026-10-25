<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         Iniciales.php
 * @path         app/Support/Iniciales.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Calcula las iniciales que muestran los avatares (DT-028).
 *
 * @requisitos   RNF-008
 *
 * @see          Manual de Programación v1.2, sección 2.6.2 (avatares)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Support;

/**
 * Iniciales de un nombre para avatares.
 *
 * @package App\Support
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class Iniciales
{
    /**
     * Devuelve la inicial del primer nombre y del primer apellido en mayúsculas.
     *
     * @param  string  $nombre  Nombre completo.
     * @return string
     */
    public static function de(string $nombre): string
    {
        $partes = preg_split('/\s+/u', trim($nombre)) ?: [];
        $partes = array_values(array_filter($partes, fn (string $parte): bool => $parte !== ''));

        if ($partes === []) {
            return '?';
        }

        // Con tres o más palabras se toma el primer apellido (penúltima si hay dos apellidos).
        $segunda = match (count($partes)) {
            1 => '',
            2 => $partes[1],
            default => $partes[count($partes) - 2],
        };

        return mb_strtoupper(mb_substr($partes[0], 0, 1) . mb_substr($segunda, 0, 1));
    }
}
