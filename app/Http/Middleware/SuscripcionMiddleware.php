<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         SuscripcionMiddleware.php
 * @path         app/Http/Middleware/SuscripcionMiddleware.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Aplica el estado de la suscripción en cada petición: en
 *               solo_lectura rechaza las escrituras con 403 (RN-011); en
 *               suspendida o cancelada cierra la sesión (RN-013).
 *
 * @requisitos   RF-006, RN-011, RN-013
 *
 * @see          docs/requisitos/BBB_CU002_AdministrarSuscripcion_v1.0.md (E3, E4)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\EstadoSuscripcion;
use App\Models\Usuario;
use App\Services\AutenticacionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\TransientToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Control de acceso según el estado de la suscripción.
 *
 * @package App\Http\Middleware
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class SuscripcionMiddleware
{
    /**
     * Rutas de escritura que se permiten aun en solo lectura.
     */
    private const RUTAS_PERMITIDAS = [
        'sesion.destroy',
        'api.sesiones.destroy',
        'notificaciones.leida',
        'notificaciones.leidas',
    ];

    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  AutenticacionService  $autenticacionService  Dependencia inyectada.
     */
    public function __construct(
        private AutenticacionService $autenticacionService,
    ) {}

    /**
     * Evalúa el estado de la cuenta y de la suscripción antes de atender la petición.
     *
     * @param  Request  $request  Petición en curso.
     * @param  Closure(Request): Response  $next  Siguiente middleware.
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario instanceof Usuario || $usuario->esSuperadmin()) {
            return $next($request);
        }

        if (! $this->autenticacionService->puedeContinuar($usuario)) {
            return $this->cerrarAcceso($request, $usuario);
        }

        $esEscritura = ! $request->isMethodSafe() && ! $request->routeIs(...self::RUTAS_PERMITIDAS);

        if ($esEscritura && $this->autenticacionService->obtenerEstado($usuario) === EstadoSuscripcion::SoloLectura) {
            abort(403, 'La suscripción está en modo solo lectura.');
        }

        return $next($request);
    }

    /**
     * Cierra la sesión web o revoca el token de API y niega el acceso.
     *
     * @param  Request  $request  Petición en curso.
     * @param  Usuario  $usuario  Usuario sin acceso.
     * @return Response
     */
    private function cerrarAcceso(Request $request, Usuario $usuario): Response
    {
        /** @var PersonalAccessToken|TransientToken|null $token */
        $token = $usuario->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => AutenticacionService::MENSAJE_SUSCRIPCION], 403);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('error', AutenticacionService::MENSAJE_SUSCRIPCION);
    }
}
