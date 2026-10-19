<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         ContratarSuscripcionRequest.php
 * @path         app/Http/Requests/ContratarSuscripcionRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación del formulario público de contratación: dominio y
 *               correo únicos, correo dentro del dominio, plan activo y
 *               contraseña segura.
 *
 * @requisitos   RF-001, RF-002, RN-001, RNF-001
 *
 * @see          docs/requisitos/BBB_CU001_ContratarSuscripcion_v1.0.md (E1–E5)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Datos para contratar el servicio.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ContratarSuscripcionRequest extends FormRequest
{
    /**
     * Cualquier visitante puede contratar.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre_institucion' => ['required', 'string', 'max:150'],
            'dominio_correo' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9-]+(\.[a-z0-9-]+)+$/',
                Rule::unique('instituciones', 'dominio_correo'),
            ],
            'plan' => ['required', 'string', Rule::exists('planes', 'clave')->where('activo', true)],
            'nombre_completo' => ['required', 'string', 'max:150'],
            'correo_institucional' => [
                'required',
                'string',
                'email',
                'max:150',
                'ends_with:@' . $this->string('dominio_correo'),
                Rule::unique('usuarios', 'correo_institucional'),
            ],
            'contrasena' => ['required', 'string', 'confirmed', Password::min(10)->letters()->numbers()],
        ];
    }

    /**
     * Mensajes de los casos de excepción del CU-001.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'dominio_correo.unique' => 'El dominio ya pertenece a otra institución.',
            'dominio_correo.regex' => 'Capture un dominio válido, por ejemplo universidad.edu.mx.',
            'correo_institucional.ends_with' => 'El correo debe pertenecer al dominio de la institución.',
            'correo_institucional.unique' => 'El correo ya está registrado.',
            'plan.exists' => 'Seleccione un plan válido.',
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
            'nombre_institucion' => 'nombre de la institución',
            'dominio_correo' => 'dominio de correo',
            'nombre_completo' => 'nombre completo',
            'correo_institucional' => 'correo institucional',
            'contrasena' => 'contraseña',
        ];
    }

    /**
     * Normaliza dominio y correo a minúsculas sin espacios ni arroba inicial.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'dominio_correo' => ltrim(mb_strtolower(trim((string) $this->input('dominio_correo'))), '@'),
            'correo_institucional' => mb_strtolower(trim((string) $this->input('correo_institucional'))),
        ]);
    }
}
