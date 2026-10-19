<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         ImportarCatalogoRequest.php
 * @path         app/Http/Requests/ImportarCatalogoRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación del archivo CSV de la carga masiva: tipo de texto
 *               o CSV y tamaño máximo de 2 MB.
 *
 * @requisitos   RF-012
 *
 * @see          docs/requisitos/BBB_CU008_ImportarCatalogo_v1.0.md (E4)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Recurso;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Archivo de importación.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ImportarCatalogoRequest extends FormRequest
{
    /**
     * Exige RecursoPolicy::gestionar.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()?->can('gestionar', Recurso::class) ?? false;
    }

    /**
     * Reglas de validación.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'archivo' => [
                'required',
                'file',
                'extensions:csv,txt',
                'mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel',
                'max:' . (int) config('spei.importacion.maximo_kb'),
            ],
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
            'archivo.extensions' => 'El archivo debe ser .csv.',
            'archivo.mimetypes' => 'El archivo debe ser .csv.',
            'archivo.max' => 'El archivo no debe pesar más de 2 MB.',
        ];
    }
}
