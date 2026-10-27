<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         ImportarUsuariosRequest.php
 * @path         app/Http/Requests/ImportarUsuariosRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación del archivo CSV de usuarios (RF-016): tipo y
 *               tamaño máximo; el contenido lo valida UsuarioService.
 *
 * @requisitos   RF-016
 *
 * @see          CU-016
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Archivo de carga masiva de usuarios.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ImportarUsuariosRequest extends FormRequest
{
    /**
     * Solo el administrador del sistema importa usuarios.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $usuario = $this->user();

        return $usuario instanceof Usuario && $usuario->rol === Rol::AdminSistema;
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
}
