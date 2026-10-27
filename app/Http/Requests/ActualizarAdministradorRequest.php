<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         ActualizarAdministradorRequest.php
 * @path         app/Http/Requests/ActualizarAdministradorRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación del cambio de área de un administrador del sistema
 *               (responsable de área, RF-019): área activa de la institución
 *               o ninguna.
 *
 * @requisitos   RF-019, RS-006
 *
 * @see          CU-018
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Datos del cambio de área de un administrador.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ActualizarAdministradorRequest extends FormRequest
{
    /**
     * Solo el administrador institucional gestiona administradores (CU-004, CU-018).
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $usuario = $this->user();

        return $usuario instanceof Usuario && $usuario->rol === Rol::AdminInstitucional;
    }

    /**
     * Reglas de validación.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $idInstitucion = (int) $this->user()?->getAttribute('id_institucion');

        return [
            'id_area_asignada' => [
                'nullable',
                'integer',
                Rule::exists('areas', 'id_area')->where('id_institucion', $idInstitucion)->where('activo', true),
            ],
        ];
    }

    /**
     * Mensajes de error.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['id_area_asignada.exists' => 'Seleccione un área activa.'];
    }
}
