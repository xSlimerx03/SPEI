<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         SuscripcionController.php
 * @path         app/Http/Controllers/Web/SuscripcionController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Consulta de la suscripción propia y cambio de plan por el
 *               administrador institucional.
 *
 * @requisitos   RF-003, RF-004, RN-010
 *
 * @see          docs/requisitos/BBB_CU002_AdministrarSuscripcion_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Http\Requests\CambiarPlanRequest;
use App\Models\Institucion;
use App\Models\Plan;
use App\Models\Usuario;
use App\Services\SuscripcionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Suscripción de la institución del usuario.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class SuscripcionController extends Controller
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  SuscripcionService  $suscripcionService  Dependencia inyectada.
     */
    public function __construct(
        private SuscripcionService $suscripcionService,
    ) {}

    /**
     * Muestra plan, límites con uso, vigencia y estado.
     *
     * @param  Request  $request  Petición en curso.
     * @return View
     */
    public function show(Request $request): View
    {
        $institucion = $this->obtenerInstitucion($request);
        Gate::authorize('administrar', $institucion);

        return view('suscripcion.show', [
            'institucion' => $institucion,
            'uso' => $this->suscripcionService->obtenerUso($institucion),
            'planes' => Plan::query()->activos()->orderBy('monto_mensual')->get(),
            'administradores' => Usuario::query()
                ->with('areaAsignada')
                ->where('rol', Rol::AdminSistema)
                ->orderBy('nombre_completo')
                ->get(),
        ]);
    }

    /**
     * Cambia el plan de la institución propia.
     *
     * @param  CambiarPlanRequest  $request  Plan destino.
     * @return RedirectResponse
     */
    public function cambiarPlan(CambiarPlanRequest $request): RedirectResponse
    {
        $institucion = $this->obtenerInstitucion($request);
        Gate::authorize('administrar', $institucion);
        $this->suscripcionService->cambiarPlan($institucion, $request->string('plan')->toString());

        return redirect()->route('suscripcion.show')->with('success', 'Plan actualizado correctamente.');
    }

    /**
     * Institución del usuario autenticado.
     *
     * @param  Request  $request  Petición en curso.
     * @return Institucion
     */
    private function obtenerInstitucion(Request $request): Institucion
    {
        return Institucion::query()->findOrFail($request->user()?->getAttribute('id_institucion'));
    }
}
