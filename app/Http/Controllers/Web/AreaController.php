<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         AreaController.php
 * @path         app/Http/Controllers/Web/AreaController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Gestión de áreas: listado, alta, edición, activación y
 *               eliminación.
 *
 * @requisitos   RF-009, RS-006
 *
 * @see          docs/requisitos/BBB_CU006_GestionarAreas_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarAreaRequest;
use App\Models\Area;
use App\Models\Institucion;
use App\Services\AreaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Áreas de la institución.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AreaController extends Controller
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  AreaService  $areaService  Dependencia inyectada.
     */
    public function __construct(
        private AreaService $areaService,
    ) {}

    /**
     * Lista las áreas con su número de recursos.
     *
     * @return View
     */
    public function index(): View
    {
        Gate::authorize('gestionar', Area::class);

        return view('areas.index', [
            'areas' => Area::query()->withCount('recursos')->orderBy('nombre_area')->paginate(15),
        ]);
    }

    /**
     * Muestra el formulario de alta.
     *
     * @return View
     */
    public function create(): View
    {
        Gate::authorize('gestionar', Area::class);

        return view('areas.form', ['area' => new Area]);
    }

    /**
     * Crea el área.
     *
     * @param  GuardarAreaRequest  $request  Datos validados.
     * @return RedirectResponse
     */
    public function store(GuardarAreaRequest $request): RedirectResponse
    {
        $institucion = Institucion::query()->findOrFail($request->user()?->getAttribute('id_institucion'));
        $this->areaService->crear($institucion, $request->validated());

        return redirect()->route('areas.index')->with('success', 'Área creada correctamente.');
    }

    /**
     * Muestra el formulario de edición.
     *
     * @param  Area  $area  Área de la propia institución.
     * @return View
     */
    public function edit(Area $area): View
    {
        Gate::authorize('modificar', $area);

        return view('areas.form', ['area' => $area]);
    }

    /**
     * Guarda los cambios del área.
     *
     * @param  GuardarAreaRequest  $request  Datos validados.
     * @param  Area  $area  Área a editar.
     * @return RedirectResponse
     */
    public function update(GuardarAreaRequest $request, Area $area): RedirectResponse
    {
        $this->areaService->actualizar($area, $request->validated());

        return redirect()->route('areas.index')->with('success', 'Área actualizada correctamente.');
    }

    /**
     * Activa o desactiva el área.
     *
     * @param  Request  $request  Valor de "activo".
     * @param  Area  $area  Área a modificar.
     * @return RedirectResponse
     */
    public function cambiarActivo(Request $request, Area $area): RedirectResponse
    {
        Gate::authorize('modificar', $area);
        $this->areaService->cambiarActivo($area, $request->boolean('activo'));

        return back()->with('success', 'Área actualizada correctamente.');
    }

    /**
     * Elimina un área sin recursos.
     *
     * @param  Area  $area  Área a eliminar.
     * @return RedirectResponse
     */
    public function destroy(Area $area): RedirectResponse
    {
        Gate::authorize('modificar', $area);
        $this->areaService->eliminar($area);

        return redirect()->route('areas.index')->with('success', 'Área eliminada correctamente.');
    }
}
