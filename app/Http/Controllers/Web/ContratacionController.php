<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         ContratacionController.php
 * @path         app/Http/Controllers/Web/ContratacionController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Landing pública de contratación y alta de la institución con
 *               su administrador institucional.
 *
 * @requisitos   RF-001, RF-002
 *
 * @see          docs/requisitos/BBB_CU001_ContratarSuscripcion_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContratarSuscripcionRequest;
use App\Models\Plan;
use App\Services\AutenticacionService;
use App\Services\OnboardingService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Contratación del servicio.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ContratacionController extends Controller
{
    public function __construct(
        private OnboardingService $onboardingService,
        private AutenticacionService $autenticacionService,
    ) {}

    /**
     * Muestra los planes activos y el formulario de contratación.
     *
     * @return View
     */
    public function create(): View
    {
        return view('contratacion.create', [
            'planes' => Plan::query()->activos()->orderBy('monto_mensual')->get(),
        ]);
    }

    /**
     * Crea la institución y su administrador e inicia la sesión.
     *
     * @param  ContratarSuscripcionRequest  $request  Datos validados.
     * @return RedirectResponse
     */
    public function store(ContratarSuscripcionRequest $request): RedirectResponse
    {
        try {
            $administrador = $this->onboardingService->contratar($request->validated());
        } catch (QueryException $excepcion) {
            report($excepcion);

            return back()->withInput($request->except(['contrasena', 'contrasena_confirmation']))
                ->with('error', 'No se pudo registrar la institución. Intente de nuevo.');
        }

        Auth::login($administrador);
        $request->session()->regenerate();
        $this->autenticacionService->auditarAcceso('INICIAR_SESION', $administrador);

        return redirect()->route('suscripcion.show')->with('success', 'Institución registrada correctamente.');
    }
}
