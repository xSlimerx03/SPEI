<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         ActualizarUsuarioRequest.php
 * @path         app/Http/Requests/ActualizarUsuarioRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación del cambio de rol y área de un usuario gestionado
 *               (RF-018) y de la delegación a asistentes (RF-021).
 *
 * @requisitos   RF-018, RF-021, RS-006
 *
 * @see          CU-017
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Datos del cambio de rol y área.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ActualizarUsuarioRequest extends FormRequest
{
    /**
     * Solo el administrador del sistema gestiona roles; UsuarioService limita al responsable de área.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $usuario = $this->user();

        return $usuario instanceof Usuario && $usuario->rol === Rol::AdminSistema;
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
            'rol' => ['required', Rule::in(['alumno', 'docente', 'asistente'])],
            'id_area_asignada' => [
                'nullable',
                'integer',
                Rule::exists('areas', 'id_area')->where('id_institucion', $idInstitucion),
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
        return [
            'rol.in' => 'El rol debe ser alumno, docente o asistente.',
            'id_area_asignada.exists' => 'Seleccione un área válida.',
        ];
    }
}
