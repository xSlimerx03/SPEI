<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         GuardarAreaRequest.php
 * @path         app/Http/Requests/GuardarAreaRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación del alta y la edición de áreas: nombre único dentro
 *               de la institución.
 *
 * @requisitos   RF-009, RS-006
 *
 * @see          docs/requisitos/BBB_CU006_GestionarAreas_v1.0.md (E2)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Area;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Datos de un área.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class GuardarAreaRequest extends FormRequest
{
    /**
     * Crear exige AreaPolicy::gestionar; editar, AreaPolicy::modificar.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $area = $this->route('area');

        if ($area instanceof Area) {
            return $this->user()?->can('modificar', $area) ?? false;
        }

        return $this->user()?->can('gestionar', Area::class) ?? false;
    }

    /**
     * Reglas de validación.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $area = $this->route('area');

        return [
            'nombre_area' => [
                'required',
                'string',
                'max:150',
                Rule::unique('areas', 'nombre_area')
                    ->where('id_institucion', (int) $this->user()?->getAttribute('id_institucion'))
                    ->ignore($area instanceof Area ? $area->id_area : null, 'id_area'),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Mensajes personalizados.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre_area.unique' => 'Ya existe un área con ese nombre.',
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
            'nombre_area' => 'nombre del área',
            'descripcion' => 'descripción',
        ];
    }
}
