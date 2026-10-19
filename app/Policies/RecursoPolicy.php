<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         RecursoPolicy.php
 * @path         app/Policies/RecursoPolicy.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Autorización sobre recursos: el administrador del sistema
 *               gestiona los de su institución; el responsable de un área solo
 *               los de su área.
 *
 * @requisitos   RF-010, RF-011, RF-012, RF-013, RF-014, RS-006
 *
 * @see          docs/requisitos/BBB_CU007_RegistrarRecurso_v1.0.md (E4, CA4)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Rol;
use App\Models\Recurso;
use App\Models\Usuario;

/**
 * Reglas de acceso al inventario.
 *
 * @package App\Policies
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class RecursoPolicy
{
    /**
     * Consultar el inventario, registrar e importar recursos.
     *
     * @param  Usuario  $usuario  Usuario autenticado.
     * @return bool
     */
    public function gestionar(Usuario $usuario): bool
    {
        return $usuario->rol === Rol::AdminSistema;
    }

    /**
     * Registrar un recurso en un área concreta.
     *
     * @param  Usuario  $usuario  Usuario autenticado.
     * @param  int  $idArea  Área elegida.
     * @return bool
     */
    public function registrarEnArea(Usuario $usuario, int $idArea): bool
    {
        if (! $this->gestionar($usuario)) {
            return false;
        }

        return ! $usuario->esResponsableDeArea() || $usuario->id_area_asignada === $idArea;
    }

    /**
     * Editar o cambiar el estado de un recurso concreto.
     *
     * @param  Usuario  $usuario  Usuario autenticado.
     * @param  Recurso  $recurso  Recurso solicitado.
     * @return bool
     */
    public function modificar(Usuario $usuario, Recurso $recurso): bool
    {
        if ($usuario->id_institucion !== $recurso->id_institucion) {
            return false;
        }

        return $this->registrarEnArea($usuario, $recurso->id_area);
    }
}
