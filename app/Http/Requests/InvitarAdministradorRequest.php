<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         InvitarAdministradorRequest.php
 * @path         app/Http/Requests/InvitarAdministradorRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación de la invitación de un administrador del sistema:
 *               correo del dominio institucional, no registrado, y área
 *               opcional de la misma institución.
 *
 * @requisitos   RF-008, RN-001, RS-006
 *
 * @see          docs/requisitos/BBB_CU004_InvitarAdministrador_v1.0.md (E1, E2, E5)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Rol;
use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Datos del administrador invitado.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class InvitarAdministradorRequest extends FormRequest
{
    /**
     * Solo el administrador institucional invita (CU-004 CA4).
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
        $dominio = (string) Institucion::query()->whereKey($idInstitucion)->value('dominio_correo');

        return [
            'nombre_completo' => ['required', 'string', 'max:150'],
            'correo_institucional' => [
                'required',
                'string',
                'email',
                'max:150',
                'ends_with:@' . $dominio,
                Rule::unique('usuarios', 'correo_institucional'),
            ],
            'id_area_asignada' => [
                'nullable',
                'integer',
                Rule::exists('areas', 'id_area')->where('id_institucion', $idInstitucion),
            ],
        ];
    }

    /**
     * Mensajes de los casos de excepción del CU-004.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'correo_institucional.ends_with' => 'El correo debe pertenecer al dominio de la institución.',
            'correo_institucional.unique' => 'El correo ya está registrado.',
            'id_area_asignada.exists' => 'Seleccione un área válida.',
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
            'id_area_asignada' => 'área asignada',
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
