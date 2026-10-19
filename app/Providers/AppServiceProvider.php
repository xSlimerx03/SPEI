<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Arranque de la aplicación
 * @file         AppServiceProvider.php
 * @path         app/Providers/AppServiceProvider.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Registra el contexto institucional por petición, el límite
 *               de intentos de inicio de sesión (5 por minuto por correo e IP),
 *               el contador de notificaciones del layout y la prevención de
 *               carga diferida fuera de producción.
 *
 * @requisitos   RNF-001, RS-006
 *
 * @see          docs/requisitos/BBB_CU005_IniciarSesion_v1.0.md (paso 2, E2)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Providers;

use App\Models\Notificacion;
use App\Support\ContextoInstitucional;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;

/**
 * Proveedor principal del SPEI.
 *
 * @package App\Providers
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra los servicios del contenedor.
     *
     * @return void
     */
    public function register(): void
    {
        // "scoped" crea un contexto limpio por petición y por trabajo de la cola.
        $this->app->scoped(ContextoInstitucional::class);
    }

    /**
     * Configura limitadores y comportamiento de Eloquent.
     *
     * @return void
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->environment('production'));

        View::composer('layouts.app', function (ViewContract $vista): void {
            $vista->with('notificacionesSinLeer', Notificacion::query()
                ->where('id_usuario', Auth::id())
                ->where('leida', false)
                ->count());
        });

        RateLimiter::for('inicio-sesion', function (Request $request): Limit {
            $clave = mb_strtolower(trim((string) $request->input('correo_institucional'))) . '|' . $request->ip();

            return Limit::perMinute(5)->by($clave)->response(
                fn (Request $request, array $headers): Response => $this->responderLimite($request, $headers),
            );
        });
    }

    /**
     * Respuesta al exceder los intentos: JSON en la API y error de formulario en la web.
     *
     * @param  Request  $request  Petición rechazada.
     * @param  array<string, string|int>  $headers  Encabezados Retry-After y X-RateLimit-*.
     * @return Response
     */
    private function responderLimite(Request $request, array $headers): Response
    {
        $mensaje = sprintf('Demasiados intentos. Intente de nuevo en %d segundos.', (int) ($headers['Retry-After'] ?? 60));

        if ($request->expectsJson()) {
            return response()->json(['message' => $mensaje], 429, $headers);
        }

        return response()->view('sesion.create', ['errorLimite' => $mensaje], 429, $headers);
    }
}
