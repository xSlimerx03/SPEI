<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         CambiarEstadoSuscripcionRequest.php
 * @path         app/Http/Requests/CambiarEstadoSuscripcionRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación del cambio manual de estado de la suscripción.
 *
 * @requisitos   RF-004, RF-006, RN-013
 *
 * @see          Manual de Base de Datos v1.2, Tabla 28; CU-002 (A1)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EstadoSuscripcion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Estado destino de la suscripción.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class CambiarEstadoSuscripcionRequest extends FormRequest
{
    /**
     * Solo el superadmin controla el estado del servicio.
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
            'estado_suscripcion' => ['required', Rule::enum(EstadoSuscripcion::class)],
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
            'estado_suscripcion' => 'estado de la suscripción',
        ];
    }
}
