<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         ActualizarPlanRequest.php
 * @path         app/Http/Requests/ActualizarPlanRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación de los parámetros de un plan editados por el
 *               superadmin: montos no negativos y límites mayores que cero.
 *
 * @requisitos   RF-007, RN-010
 *
 * @see          docs/requisitos/BBB_CU003_ModificarPlanes_v1.0.md (E1)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Parámetros de un plan.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ActualizarPlanRequest extends FormRequest
{
    /**
     * Solo el superadmin modifica planes.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()?->esSuperadmin() ?? false;
    }

    /**
     * Reglas de validación.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'monto_mensual' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'limite_areas' => ['required', 'integer', 'min:1', 'max:100000'],
            'limite_recursos' => ['required', 'integer', 'min:1', 'max:1000000'],
            'duracion_dias' => ['required', 'integer', 'min:1', 'max:366'],
            'activo' => ['required', 'boolean'],
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
            'monto_mensual' => 'monto mensual',
            'limite_areas' => 'límite de áreas',
            'limite_recursos' => 'límite de recursos',
            'duracion_dias' => 'duración en días',
        ];
    }

    /**
     * Un checkbox desmarcado no se envía; se interpreta como plan inactivo.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['activo' => $this->boolean('activo')]);
    }
}
