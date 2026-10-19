<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         FiltrarRecursosRequest.php
 * @path         app/Http/Requests/FiltrarRecursosRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación de los filtros del catálogo y del inventario.
 *
 * @requisitos   RF-015
 *
 * @see          docs/requisitos/BBB_CU010_ConsultarCatalogo_v1.0.md (paso 2)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EstadoRecurso;
use App\Enums\TipoRecurso;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Filtros de búsqueda de recursos.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class FiltrarRecursosRequest extends FormRequest
{
    /**
     * La autorización por rol se aplica en la ruta.
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
            'texto' => ['nullable', 'string', 'max:100'],
            'tipo_recurso' => ['nullable', Rule::enum(TipoRecurso::class)],
            'id_area' => ['nullable', 'integer', 'min:1'],
            'estado' => ['nullable', Rule::enum(EstadoRecurso::class)],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
