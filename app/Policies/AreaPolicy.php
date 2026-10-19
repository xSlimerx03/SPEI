<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         AreaPolicy.php
 * @path         app/Policies/AreaPolicy.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Autorización sobre áreas: solo el administrador del sistema
 *               sin área asignada las gestiona, y solo las de su institución.
 *
 * @requisitos   RF-009, RS-006
 *
 * @see          docs/requisitos/BBB_CU006_GestionarAreas_v1.0.md (P1, E5)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Rol;
use App\Models\Area;
use App\Models\Usuario;

/**
 * Reglas de acceso a las áreas.
 *
 * @package App\Policies
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AreaPolicy
{
    /**
     * Listar y crear áreas.
     *
     * @param  Usuario  $usuario  Usuario autenticado.
     * @return bool
     */
    public function gestionar(Usuario $usuario): bool
    {
        return $usuario->rol === Rol::AdminSistema && $usuario->id_area_asignada === null;
    }

    /**
     * Editar, activar o eliminar un área concreta.
     *
     * @param  Usuario  $usuario  Usuario autenticado.
     * @param  Area  $area  Área solicitada.
     * @return bool
     */
    public function modificar(Usuario $usuario, Area $area): bool
    {
        return $this->gestionar($usuario) && $usuario->id_institucion === $area->id_institucion;
    }
}
