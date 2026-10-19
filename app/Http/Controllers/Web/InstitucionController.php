<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         InstitucionController.php
 * @path         app/Http/Controllers/Web/InstitucionController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Administración de las instituciones por el superadmin:
 *               listado, detalle, cambio de plan, renovación y cambio de estado.
 *
 * @requisitos   RF-003, RF-004, RF-006
 *
 * @see          docs/requisitos/BBB_CU002_AdministrarSuscripcion_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\EstadoSuscripcion;
use App\Http\Controllers\Controller;
use App\Http\Requests\CambiarEstadoSuscripcionRequest;
use App\Http\Requests\CambiarPlanRequest;
use App\Models\Institucion;
use App\Models\Plan;
use App\Services\SuscripcionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Instituciones de la plataforma.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class InstitucionController extends Controller
{
    public function __construct(
        private SuscripcionService $suscripcionService,
    ) {}

    /**
     * Lista las instituciones ordenadas por fecha de corte.
     *
     * @return View
     */
    public function index(): View
    {
        return view('instituciones.index', [
            'instituciones' => Institucion::query()->orderBy('fecha_corte')->paginate(15),
        ]);
    }

    /**
     * Detalle de la suscripción de una institución.
     *
     * @param  Institucion  $institucion  Institución consultada.
     * @return View
     */
    public function show(Institucion $institucion): View
    {
        return view('instituciones.show', [
            'institucion' => $institucion,
            'uso' => $this->suscripcionService->obtenerUso($institucion),
            'planes' => Plan::query()->activos()->orderBy('monto_mensual')->get(),
            'estados' => array_filter(
                EstadoSuscripcion::cases(),
                fn (EstadoSuscripcion $estado): bool => $institucion->estado_suscripcion->puedeCambiarA($estado),
            ),
        ]);
    }

    /**
     * Cambia el plan de la institución.
     *
     * @param  CambiarPlanRequest  $request  Plan destino.
     * @param  Institucion  $institucion  Institución a modificar.
     * @return RedirectResponse
     */
    public function cambiarPlan(CambiarPlanRequest $request, Institucion $institucion): RedirectResponse
    {
        Gate::authorize('controlarServicio', Institucion::class);
        $this->suscripcionService->cambiarPlan($institucion, $request->string('plan')->toString());

        return back()->with('success', 'Plan actualizado correctamente.');
    }

    /**
     * Registra la renovación del periodo.
     *
     * @param  Institucion  $institucion  Institución a renovar.
     * @return RedirectResponse
     */
    public function renovar(Institucion $institucion): RedirectResponse
    {
        Gate::authorize('controlarServicio', Institucion::class);
        $this->suscripcionService->renovar($institucion);

        return back()->with('success', 'Suscripción renovada correctamente.');
    }

    /**
     * Cambia manualmente el estado de la suscripción.
     *
     * @param  CambiarEstadoSuscripcionRequest  $request  Estado destino.
     * @param  Institucion  $institucion  Institución a modificar.
     * @return RedirectResponse
     */
    public function cambiarEstado(CambiarEstadoSuscripcionRequest $request, Institucion $institucion): RedirectResponse
    {
        $destino = EstadoSuscripcion::from($request->string('estado_suscripcion')->toString());
        $this->suscripcionService->cambiarEstado($institucion, $destino);

        return back()->with('success', 'Estado actualizado correctamente.');
    }
}
