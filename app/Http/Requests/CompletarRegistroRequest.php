<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         CompletarRegistroRequest.php
 * @path         app/Http/Requests/CompletarRegistroRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación de la contraseña que define el administrador del
 *               sistema invitado al completar su registro.
 *
 * @requisitos   RF-008, RNF-001
 *
 * @see          docs/requisitos/BBB_CU004_InvitarAdministrador_v1.0.md (paso 4)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Contraseña del invitado.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class CompletarRegistroRequest extends FormRequest
{
    /**
     * La firma del enlace ya autoriza la petición (middleware signed).
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'contrasena' => ['required', 'string', 'confirmed', Password::min(10)->letters()->numbers()],
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
            'contrasena' => 'contraseña',
        ];
    }
}
