<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Soporte
 * @file         DominioCorreo.php
 * @path         app/Support/DominioCorreo.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Reglas de dominio del correo institucional: un correo pertenece
 *               a la institución si su dominio es el contratado o un subdominio
 *               de él (p. ej. alumnos.escuela.mx de escuela.mx).
 *
 * @requisitos   RF-016, RF-017, RN-001, RS-006
 *
 * @see          DT-034
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Support;

/**
 * Operaciones sobre dominios de correo.
 *
 * @package App\Support
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class DominioCorreo
{
    /**
     * Dominio del correo en minúsculas, o cadena vacía si el correo no tiene arroba.
     *
     * @param  string  $correo  Correo institucional.
     * @return string
     */
    public static function extraer(string $correo): string
    {
        $posicion = strrpos($correo, '@');

        return $posicion === false ? '' : mb_strtolower(substr($correo, $posicion + 1));
    }

    /**
     * Indica si un dominio es el contratado o un subdominio de él.
     *
     * @param  string  $dominio  Dominio a evaluar.
     * @param  string  $dominioInstitucion  Dominio contratado por la institución.
     * @return bool
     */
    public static function esDeInstitucion(string $dominio, string $dominioInstitucion): bool
    {
        $dominio = mb_strtolower($dominio);
        $base = mb_strtolower($dominioInstitucion);

        return $dominio === $base || str_ends_with($dominio, '.' . $base);
    }

    /**
     * Indica si el correo pertenece al dominio de la institución o a un subdominio.
     *
     * @param  string  $correo  Correo institucional.
     * @param  string  $dominioInstitucion  Dominio contratado.
     * @return bool
     */
    public static function perteneceA(string $correo, string $dominioInstitucion): bool
    {
        return self::esDeInstitucion(self::extraer($correo), $dominioInstitucion);
    }
}
