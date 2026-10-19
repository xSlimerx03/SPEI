<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         Rol.php
 * @path         app/Enums/Rol.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Roles del SPEI tal como los define la columna usuarios.rol.
 *
 * @requisitos   RN-001, RNF-001, RS-006
 *
 * @see          Manual de Base de Datos v1.2, sección 4.4 y Tabla 31
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Enums;

/**
 * Rol de un usuario dentro de la plataforma o de su institución.
 *
 * @package App\Enums
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
enum Rol: string
{
    case Superadmin = 'superadmin';
    case AdminInstitucional = 'admin_institucional';
    case AdminSistema = 'admin_sistema';
    case Asistente = 'asistente';
    case Alumno = 'alumno';
    case Docente = 'docente';

    /**
     * Indica si el rol corresponde a un usuario final que consulta el catálogo.
     *
     * @return bool
     */
    public function esUsuarioFinal(): bool
    {
        return $this === self::Alumno || $this === self::Docente;
    }

    /**
     * Devuelve el nombre del rol para mostrarlo en pantalla.
     *
     * @return string
     */
    public function obtenerEtiqueta(): string
    {
        return match ($this) {
            self::Superadmin => 'Superadministrador',
            self::AdminInstitucional => 'Administrador institucional',
            self::AdminSistema => 'Administrador del sistema',
            self::Asistente => 'Asistente',
            self::Alumno => 'Alumno',
            self::Docente => 'Docente',
        };
    }
}
