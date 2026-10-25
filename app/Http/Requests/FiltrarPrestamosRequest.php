<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         FiltrarPrestamosRequest.php
 * @path         app/Http/Requests/FiltrarPrestamosRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación de los filtros del listado y de la exportación de
 *               préstamos: estado, texto (folio o solicitante), área, fecha
 *               y préstamo seleccionado.
 *
 * @requisitos   RF-028, RF-051, RF-058
 *
 * @see          app/Services/ConsultaPrestamoService.php
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EstadoPrestamo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Filtros de préstamos.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class FiltrarPrestamosRequest extends FormRequest
{
    /**
     * La autorización por rol se aplica en la ruta y el ámbito en el servicio.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Reglas de validación.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'estado' => ['nullable', Rule::enum(EstadoPrestamo::class)],
            'texto' => ['nullable', 'string', 'max:100'],
            'id_area' => ['nullable', 'integer', 'min:1'],
            'fecha' => ['nullable', 'date_format:Y-m-d'],
            'p' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
