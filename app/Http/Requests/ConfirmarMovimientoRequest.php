<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.5: Entrega y Devolución
 * @file         ConfirmarMovimientoRequest.php
 * @path         app/Http/Requests/ConfirmarMovimientoRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-23
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación del movimiento de custodia antes de consumir el
 *               token: código de 32 caracteres hexadecimales, condición y
 *               descripción obligatoria si hay daño. Los errores regresan al
 *               mostrador.
 *
 * @requisitos   RF-034, RF-035, RF-036, RF-037
 *
 * @see          app/Services/PrestamoService.php; DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Prestamo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Datos de una entrega o devolución.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ConfirmarMovimientoRequest extends FormRequest
{
    /**
     * @var string
     */
    protected $redirectRoute = 'mostrador.index';

    /**
     * Exige PrestamoPolicy::operar sobre el préstamo de la ruta.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $prestamo = $this->route('prestamo');

        return $prestamo instanceof Prestamo && ($this->user()?->can('operar', $prestamo) ?? false);
    }

    /**
     * Reglas de validación.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'regex:/^[a-f0-9]{32}$/'],
            'condicion' => ['required', Rule::in(['bueno', 'regular', 'malo', 'danado'])],
            'incidencia' => ['required_if:condicion,malo,danado', 'nullable', 'string', 'max:2000'],
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
            'codigo.regex' => 'El código no es válido, ya se utilizó o ha caducado.',
            'incidencia.required_if' => 'Describa la incidencia cuando la condición es mala o dañada.',
        ];
    }
}
