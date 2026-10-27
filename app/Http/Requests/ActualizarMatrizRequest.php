<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.6: Penalizaciones y Sanciones Automatizadas
 * @file         ActualizarMatrizRequest.php
 * @path         app/Http/Requests/ActualizarMatrizRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación de la matriz de sanciones (RF-041): una fila por
 *               tipo de incidente con marcas, umbral, días y estado.
 *
 * @requisitos   RF-041, RN-005
 *
 * @see          CU-019
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Datos de la matriz de sanciones.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ActualizarMatrizRequest extends FormRequest
{
    /**
     * Solo el administrador del sistema sin área configura la matriz de la institución.
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
            'reglas' => ['required', 'array'],
            'reglas.*.marcas_por_incidente' => ['required', 'integer', 'between:0,10'],
            'reglas.*.umbral_bloqueo' => ['required', 'integer', 'between:1,20'],
            'reglas.*.dias_sancion' => ['required', 'integer', 'between:1,90'],
            'reglas.*.activo' => ['nullable', 'boolean'],
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
            'reglas.*.marcas_por_incidente' => 'marcas por incidente',
            'reglas.*.umbral_bloqueo' => 'umbral de bloqueo',
            'reglas.*.dias_sancion' => 'días de sanción',
        ];
    }
}
