<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         PrestamoPolicy.php
 * @path         app/Policies/PrestamoPolicy.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Autoriza la consulta, operación en mostrador, emisión de QR y
 *               cancelación de préstamos por institución, propietario y área
 *               asignada (mínimo privilegio, MD 13.1).
 *
 * @requisitos   RF-018, RF-028, RF-029, RF-034, RF-036, RS-006
 *
 * @see          docs/plan/BBB_MVP_v1.0.md; RED-01 del 25/10/2026 (H-08)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Rol;
use App\Models\Prestamo;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;

/**
 * Permisos sobre préstamos.
 *
 * @package App\Policies
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class PrestamoPolicy
{
    /**
     * Consultar el préstamo: propietario, administrador institucional u operador del ámbito.
     *
     * @param  Usuario  $usuario  Usuario autenticado.
     * @param  Prestamo  $prestamo  Préstamo solicitado.
     * @return bool
     */
    public function view(Usuario $usuario, Prestamo $prestamo): bool
    {
        if ($usuario->id_institucion !== $prestamo->id_institucion) {
            return false;
        }

        return $usuario->id_usuario === $prestamo->id_usuario
            || $usuario->rol === Rol::AdminInstitucional
            || $this->operar($usuario, $prestamo);
    }

    /**
     * Confirmar entregas y devoluciones: administrador del sistema o asistente con área, solo de su área.
     *
     * @param  Usuario  $usuario  Usuario autenticado.
     * @param  Prestamo  $prestamo  Préstamo solicitado.
     * @return bool
     */
    public function operar(Usuario $usuario, Prestamo $prestamo): bool
    {
        if ($usuario->id_institucion !== $prestamo->id_institucion
            || ! $usuario->tieneRol(Rol::AdminSistema, Rol::Asistente)) {
            return false;
        }

        if ($usuario->rol === Rol::Asistente && $usuario->id_area_asignada === null) {
            return false;
        }

        $area = $usuario->id_area_asignada;

        return $area === null || ! $prestamo->detalles()
            ->whereHas('recurso', fn (Builder $consulta): Builder => $consulta->where('id_area', '!=', $area))
            ->exists();
    }

    /**
     * Generar el código QR: solo el propietario, que es quien transfiere su custodia.
     *
     * @param  Usuario  $usuario  Usuario autenticado.
     * @param  Prestamo  $prestamo  Préstamo solicitado.
     * @return bool
     */
    public function generarToken(Usuario $usuario, Prestamo $prestamo): bool
    {
        return $usuario->id_institucion === $prestamo->id_institucion
            && $usuario->id_usuario === $prestamo->id_usuario;
    }

    /**
     * Cancelar: el propietario o un administrador del sistema que opera el ámbito del préstamo.
     *
     * @param  Usuario  $usuario  Usuario autenticado.
     * @param  Prestamo  $prestamo  Préstamo solicitado.
     * @return bool
     */
    public function cancelar(Usuario $usuario, Prestamo $prestamo): bool
    {
        if ($usuario->id_institucion !== $prestamo->id_institucion) {
            return false;
        }

        return $usuario->id_usuario === $prestamo->id_usuario
            || ($usuario->rol === Rol::AdminSistema && $this->operar($usuario, $prestamo));
    }
}
