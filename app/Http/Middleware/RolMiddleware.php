<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Autorización
 * @file         RolMiddleware.php
 * @path         app/Http/Middleware/RolMiddleware.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Restringe una ruta a los roles indicados (alias "rol").
 *               Ejemplo: ->middleware('rol:admin_sistema').
 *
 * @requisitos   RNF-001, RS-006
 *
 * @see          Manual de Base de Datos v1.2, Tabla 31 (roles)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Rol;
use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autorización por rol a nivel de ruta.
 *
 * @package App\Http\Middleware
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class RolMiddleware
{
    /**
     * Responde 403 si el usuario no tiene alguno de los roles.
     *
     * @param  Request  $request  Petición en curso.
     * @param  Closure(Request): Response  $next  Siguiente middleware.
     * @param  string  ...$roles  Valores de Rol aceptados.
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();
        $aceptados = array_map(fn (string $rol): Rol => Rol::from($rol), $roles);

        if (! $usuario instanceof Usuario || ! $usuario->tieneRol(...$aceptados)) {
            abort(403, 'No tiene permiso para realizar esta acción.');
        }

        return $next($request);
    }
}
