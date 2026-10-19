<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         PlanController.php
 * @path         app/Http/Controllers/Web/PlanController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Listado y edición de planes por el superadmin.
 *
 * @requisitos   RF-007
 *
 * @see          docs/requisitos/BBB_CU003_ModificarPlanes_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarPlanRequest;
use App\Models\Plan;
use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Planes de suscripción.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class PlanController extends Controller
{
    public function __construct(
        private PlanService $planService,
    ) {}

    /**
     * Lista los planes con el número de instituciones suscritas.
     *
     * @return View
     */
    public function index(): View
    {
        return view('planes.index', [
            'planes' => Plan::query()->withCount('instituciones')->orderBy('monto_mensual')->get(),
        ]);
    }

    /**
     * Muestra el formulario de edición.
     *
     * @param  Plan  $plan  Plan a editar.
     * @return View
     */
    public function edit(Plan $plan): View
    {
        return view('planes.edit', ['plan' => $plan]);
    }

    /**
     * Guarda y propaga los parámetros del plan.
     *
     * @param  ActualizarPlanRequest  $request  Datos validados.
     * @param  Plan  $plan  Plan a modificar.
     * @return RedirectResponse
     */
    public function update(ActualizarPlanRequest $request, Plan $plan): RedirectResponse
    {
        $this->planService->actualizar($plan, $request->validated());

        return redirect()->route('planes.index')->with('success', 'Plan actualizado correctamente.');
    }
}
