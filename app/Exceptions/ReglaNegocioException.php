<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Núcleo de la aplicación
 * @file         ReglaNegocioException.php
 * @path         app/Exceptions/ReglaNegocioException.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Excepción que lanzan los servicios cuando una operación viola
 *               una regla de negocio. La API responde 422 con el mensaje y la
 *               web regresa al formulario con el error.
 *
 * @requisitos   RN-002, RN-010, RN-011
 *
 * @see          Manual de Programación v1.2, sección 1.6 (manejo de excepciones)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Violación de una regla de negocio con mensaje apto para el usuario.
 *
 * @package App\Exceptions
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ReglaNegocioException extends RuntimeException
{
    /**
     * Convierte la excepción en la respuesta adecuada al tipo de cliente.
     *
     * @param  Request  $request  Petición en curso.
     * @return JsonResponse|RedirectResponse
     */
    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return back()->withInput($request->except([
            'contrasena', 'contrasena_confirmation', 'password', 'password_confirmation',
        ]))->with('error', $this->getMessage());
    }
}
