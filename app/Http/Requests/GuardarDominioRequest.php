<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         GuardarDominioRequest.php
 * @path         app/Http/Requests/GuardarDominioRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación de una regla dominio → rol (RF-017). Que el dominio
 *               sea el contratado o un subdominio lo valida DominioRolService.
 *
 * @requisitos   RF-017, RN-001
 *
 * @see          CU-016; DT-034
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Datos de una regla de dominio.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class GuardarDominioRequest extends FormRequest
{
    /**
     * Solo el administrador del sistema sin área define las reglas de la institución.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $usuario = $this->user();

        return $usuario instanceof Usuario && $usuario->rol === Rol::AdminSistema && ! $usuario->esResponsableDeArea();
    }

    /**
     * Reglas de validación.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dominio' => ['required', 'string', 'max:150', 'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9-]+)+$/'],
            'rol' => ['required', Rule::in(['alumno', 'docente'])],
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
            'dominio.regex' => 'Escriba un dominio válido, por ejemplo alumnos.escuela.edu.mx.',
            'rol.in' => 'Por dominio solo se asigna alumno o docente.',
        ];
    }

    /**
     * Normaliza el dominio: minúsculas y sin arroba inicial.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['dominio' => ltrim(mb_strtolower(trim((string) $this->input('dominio'))), '@')]);
    }
}
