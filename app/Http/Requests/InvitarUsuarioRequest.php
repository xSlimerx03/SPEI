<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         InvitarUsuarioRequest.php
 * @path         app/Http/Requests/InvitarUsuarioRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación del alta individual de un alumno, docente o
 *               asistente. El dominio, la regla dominio → rol y el área según
 *               el actor los valida UsuarioService.
 *
 * @requisitos   RF-016, RF-017, RF-020, RS-006
 *
 * @see          CU-016
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Datos de la invitación de un usuario.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class InvitarUsuarioRequest extends FormRequest
{
    /**
     * Solo el administrador del sistema (con o sin área) da de alta usuarios.
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
            'nombre_completo' => ['required', 'string', 'max:150'],
            'correo_institucional' => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('usuarios', 'correo_institucional'),
            ],
            'rol' => ['nullable', Rule::in(['alumno', 'docente', 'asistente'])],
            'id_area_asignada' => [
                'nullable',
                'integer',
                Rule::exists('areas', 'id_area')->where('id_institucion', $idInstitucion),
            ],
        ];
    }

    /**
     * Mensajes de los casos de excepción del CU-016.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'correo_institucional.unique' => 'El correo ya está registrado en el SPEI.',
            'id_area_asignada.exists' => 'Seleccione un área válida.',
            'rol.in' => 'El rol debe ser alumno, docente o asistente.',
        ];
    }

    /**
     * Nombres de los campos en los mensajes.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nombre_completo' => 'nombre completo',
            'correo_institucional' => 'correo institucional',
            'id_area_asignada' => 'área',
        ];
    }

    /**
     * Normaliza el correo a minúsculas.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'correo_institucional' => mb_strtolower(trim((string) $this->input('correo_institucional'))),
        ]);
    }
}
