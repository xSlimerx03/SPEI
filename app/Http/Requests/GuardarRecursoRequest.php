<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         GuardarRecursoRequest.php
 * @path         app/Http/Requests/GuardarRecursoRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación del alta y la edición de recursos: área activa de
 *               la institución, inventario y serie únicos por tenant y año de
 *               adquisición válido.
 *
 * @requisitos   RF-010, RF-011, RN-010, RS-006
 *
 * @see          docs/requisitos/BBB_CU007_RegistrarRecurso_v1.0.md (E2–E4, E7)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EstadoRecurso;
use App\Enums\TipoRecurso;
use App\Models\Recurso;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

/**
 * Datos de un recurso.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class GuardarRecursoRequest extends FormRequest
{
    /**
     * Verifica el rol, la propiedad del recurso y, si es responsable, que el área sea la suya.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $usuario = $this->user();
        $recurso = $this->route('recurso');

        if ($usuario === null || ($recurso instanceof Recurso && ! $usuario->can('modificar', $recurso))) {
            return false;
        }

        if (! $this->filled('id_area')) {
            return $usuario->can('gestionar', Recurso::class);
        }

        return $usuario->can('registrarEnArea', [Recurso::class, $this->integer('id_area')]);
    }

    /**
     * Reglas de validación.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $idInstitucion = (int) $this->user()?->getAttribute('id_institucion');

        return [
            'id_area' => [
                'required',
                'integer',
                Rule::exists('areas', 'id_area')->where('id_institucion', $idInstitucion)->where('activo', true),
            ],
            'nombre_recurso' => ['required', 'string', 'max:150'],
            'tipo_recurso' => ['required', Rule::enum(TipoRecurso::class)],
            'numero_inventario' => ['required', 'string', 'max:100', $this->unicoEnInstitucion('numero_inventario')],
            'numero_serie' => ['nullable', 'string', 'max:100', $this->unicoEnInstitucion('numero_serie')],
            'marca' => ['nullable', 'string', 'max:100'],
            'anio_adquisicion' => ['nullable', 'integer', 'between:1990,' . now()->year],
            'estado' => [
                'sometimes',
                Rule::in([EstadoRecurso::Disponible->value, EstadoRecurso::Mantenimiento->value]),
            ],
        ];
    }

    /**
     * Mensajes de los casos de excepción del CU-007.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id_area.exists' => 'Seleccione un área válida.',
            'numero_inventario.unique' => 'El número de inventario ya está registrado.',
            'numero_serie.unique' => 'El número de serie ya está registrado.',
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
            'id_area' => 'área',
            'nombre_recurso' => 'nombre del recurso',
            'tipo_recurso' => 'tipo de recurso',
            'numero_inventario' => 'número de inventario',
            'numero_serie' => 'número de serie',
            'anio_adquisicion' => 'año de adquisición',
        ];
    }

    /**
     * Regla de unicidad de la columna dentro de la institución, ignorando el recurso editado.
     *
     * @param  string  $columna  Columna a verificar.
     * @return Unique
     */
    private function unicoEnInstitucion(string $columna): Unique
    {
        $recurso = $this->route('recurso');

        return Rule::unique('recursos', $columna)
            ->where('id_institucion', (int) $this->user()?->getAttribute('id_institucion'))
            ->ignore($recurso instanceof Recurso ? $recurso->id_recurso : null, 'id_recurso');
    }
}
