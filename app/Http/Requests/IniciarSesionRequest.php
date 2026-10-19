<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Autenticación
 * @file         IniciarSesionRequest.php
 * @path         app/Http/Requests/IniciarSesionRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación del inicio de sesión web y API.
 *
 * @requisitos   RNF-001
 *
 * @see          docs/requisitos/BBB_CU005_IniciarSesion_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Credenciales de acceso.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class IniciarSesionRequest extends FormRequest
{
    /**
     * Cualquier visitante puede intentar iniciar sesión.
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
            'correo_institucional' => ['required', 'string', 'email', 'max:150'],
            'contrasena' => ['required', 'string', 'max:255'],
            'nombre_dispositivo' => ['sometimes', 'string', 'max:100'],
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
            'correo_institucional' => 'correo institucional',
            'contrasena' => 'contraseña',
        ];
    }
}
