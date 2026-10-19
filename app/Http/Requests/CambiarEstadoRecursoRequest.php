<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         CambiarEstadoRecursoRequest.php
 * @path         app/Http/Requests/CambiarEstadoRecursoRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación del cambio de estado de un recurso. Acepta los
 *               cuatro estados para que la regla de negocio responda 422 si se
 *               fuerza prestado o bloqueado (CU-009 E2).
 *
 * @requisitos   RF-013, RF-014, RN-002
 *
 * @see          docs/requisitos/BBB_CU009_CambiarEstadoRecurso_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EstadoRecurso;
use App\Models\Recurso;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Estado destino de un recurso.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class CambiarEstadoRecursoRequest extends FormRequest
{
    /**
     * Exige RecursoPolicy::modificar sobre el recurso de la ruta.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $recurso = $this->route('recurso');

        return $recurso instanceof Recurso && ($this->user()?->can('modificar', $recurso) ?? false);
    }

    /**
     * Reglas de validación.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::enum(EstadoRecurso::class)],
        ];
    }
}
