<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         InstitucionPolicy.php
 * @path         app/Policies/InstitucionPolicy.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Autorización sobre la suscripción: el administrador
 *               institucional consulta y cambia el plan de su institución; solo
 *               el superadmin renueva y cambia el estado.
 *
 * @requisitos   RF-003, RF-004, RF-006, RF-008, RS-006
 *
 * @see          docs/requisitos/BBB_CU002_AdministrarSuscripcion_v1.0.md (CA7)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Rol;
use App\Models\Institucion;
use App\Models\Usuario;

/**
 * Reglas de acceso a la institución y su suscripción.
 *
 * @package App\Policies
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class InstitucionPolicy
{
    /**
     * Consultar la suscripción, cambiar el plan e invitar administradores.
     *
     * @param  Usuario  $usuario  Usuario autenticado.
     * @param  Institucion  $institucion  Institución solicitada.
     * @return bool
     */
    public function administrar(Usuario $usuario, Institucion $institucion): bool
    {
        if ($usuario->esSuperadmin()) {
            return true;
        }

        return $usuario->rol === Rol::AdminInstitucional
            && $usuario->id_institucion === $institucion->id_institucion;
    }

    /**
     * Renovar la vigencia o cambiar el estado de la suscripción.
     *
     * @param  Usuario  $usuario  Usuario autenticado.
     * @return bool
     */
    public function controlarServicio(Usuario $usuario): bool
    {
        return $usuario->esSuperadmin();
    }
}
