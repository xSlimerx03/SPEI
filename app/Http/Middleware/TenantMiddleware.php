<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Núcleo multi-tenant
 * @file         TenantMiddleware.php
 * @path         app/Http/Middleware/TenantMiddleware.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Fija el tenant del usuario autenticado antes de resolver los
 *               parámetros de ruta, de modo que un registro de otra
 *               institución responda 404.
 *
 * @requisitos   RS-006
 *
 * @see          Manual de Programación v1.2, Anexo A.3.7; MD 5.3; DT-008
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\ContextoInstitucional;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Establece el contexto institucional de la petición.
 *
 * @package App\Http\Middleware
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class TenantMiddleware
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  ContextoInstitucional  $contexto  Institución activa de la petición.
     */
    public function __construct(
        private ContextoInstitucional $contexto,
    ) {}

    /**
     * Fija el tenant del usuario autenticado; el superadmin no tiene tenant (DT-011).
     *
     * @param  Request  $request  Petición en curso.
     * @param  Closure(Request): Response  $next  Siguiente middleware.
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->contexto->limpiar();
        $idInstitucion = $request->user()?->getAttribute('id_institucion');

        if ($idInstitucion !== null) {
            $this->contexto->establecer((int) $idInstitucion);
        }

        return $next($request);
    }

    /**
     * Limpia el tenant al terminar para que no se herede entre peticiones del mismo proceso.
     *
     * @param  Request  $request  Petición atendida.
     * @param  Response  $response  Respuesta enviada.
     * @return void
     */
    public function terminate(Request $request, Response $response): void
    {
        $this->contexto->limpiar();
    }
}
