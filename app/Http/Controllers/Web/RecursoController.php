<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         RecursoController.php
 * @path         app/Http/Controllers/Web/RecursoController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Inventario del administrador del sistema: listado con
 *               filtros, alta, edición, búsqueda por código de barras, cambio
 *               de estado y desactivación.
 *
 * @requisitos   RF-010, RF-011, RF-013, RF-014, RF-015, RS-006
 *
 * @see          docs/requisitos/BBB_CU007_RegistrarRecurso_v1.0.md; BBB_CU009; BBB_CU010
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\EstadoRecurso;
use App\Enums\TipoRecurso;
use App\Http\Controllers\Controller;
use App\Http\Requests\CambiarEstadoRecursoRequest;
use App\Http\Requests\FiltrarRecursosRequest;
use App\Http\Requests\GuardarRecursoRequest;
use App\Models\Area;
use App\Models\Institucion;
use App\Models\Recurso;
use App\Models\Usuario;
use App\Services\CatalogoService;
use App\Services\RecursoService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Inventario de recursos.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class RecursoController extends Controller
{
    public function __construct(
        private RecursoService $recursoService,
        private CatalogoService $catalogoService,
    ) {}

    /**
     * Lista el inventario con los cuatro estados y filtros.
     *
     * @param  FiltrarRecursosRequest  $request  Filtros validados.
     * @return View
     */
    public function index(FiltrarRecursosRequest $request): View
    {
        Gate::authorize('gestionar', Recurso::class);
        /** @var Usuario $usuario */
        $usuario = $request->user();

        return view('recursos.index', [
            'recursos' => $this->catalogoService->listarInventario($usuario, $request->validated()),
            'areas' => $this->obtenerAreas($usuario),
            'estados' => EstadoRecurso::cases(),
            'tipos' => TipoRecurso::cases(),
        ]);
    }

    /**
     * Muestra el formulario de alta; precarga el inventario si viene de una búsqueda por código.
     *
     * @param  Request  $request  Petición con "codigo" opcional.
     * @return View
     */
    public function create(Request $request): View
    {
        Gate::authorize('gestionar', Recurso::class);
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $recurso = new Recurso(['numero_inventario' => $request->string('codigo')->limit(100, '')->toString()]);

        return view('recursos.form', $this->armarFormulario($usuario, $recurso));
    }

    /**
     * Registra el recurso.
     *
     * @param  GuardarRecursoRequest  $request  Datos validados.
     * @return RedirectResponse
     */
    public function store(GuardarRecursoRequest $request): RedirectResponse
    {
        $institucion = Institucion::query()->findOrFail($request->user()?->getAttribute('id_institucion'));
        $this->recursoService->crear($institucion, $request->validated());

        return redirect()->route('recursos.index')->with('success', 'Recurso registrado correctamente.');
    }

    /**
     * Muestra el formulario de edición.
     *
     * @param  Request  $request  Petición en curso.
     * @param  Recurso  $recurso  Recurso de la propia institución.
     * @return View
     */
    public function edit(Request $request, Recurso $recurso): View
    {
        Gate::authorize('modificar', $recurso);
        /** @var Usuario $usuario */
        $usuario = $request->user();

        return view('recursos.form', $this->armarFormulario($usuario, $recurso));
    }

    /**
     * Guarda los cambios del recurso.
     *
     * @param  GuardarRecursoRequest  $request  Datos validados.
     * @param  Recurso  $recurso  Recurso a editar.
     * @return RedirectResponse
     */
    public function update(GuardarRecursoRequest $request, Recurso $recurso): RedirectResponse
    {
        $this->recursoService->actualizar($recurso, $request->validated());

        return redirect()->route('recursos.index')->with('success', 'Recurso actualizado correctamente.');
    }

    /**
     * Cambia el estado entre disponible y mantenimiento.
     *
     * @param  CambiarEstadoRecursoRequest  $request  Estado destino.
     * @param  Recurso  $recurso  Recurso a modificar.
     * @return RedirectResponse
     */
    public function cambiarEstado(CambiarEstadoRecursoRequest $request, Recurso $recurso): RedirectResponse
    {
        $destino = EstadoRecurso::from($request->string('estado')->toString());
        $this->recursoService->cambiarEstado($recurso, $destino);

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    /**
     * Activa o desactiva el recurso.
     *
     * @param  Request  $request  Valor de "activo".
     * @param  Recurso  $recurso  Recurso a modificar.
     * @return RedirectResponse
     */
    public function cambiarActivo(Request $request, Recurso $recurso): RedirectResponse
    {
        Gate::authorize('modificar', $recurso);
        $this->recursoService->cambiarActivo($recurso, $request->boolean('activo'));

        return back()->with('success', 'Recurso actualizado correctamente.');
    }

    /**
     * Abre el recurso leído con el lector de código de barras o el alta precargada.
     *
     * @param  Request  $request  Petición con "codigo".
     * @return RedirectResponse
     */
    public function buscarCodigo(Request $request): RedirectResponse
    {
        Gate::authorize('gestionar', Recurso::class);
        $request->validate(['codigo' => ['required', 'string', 'max:100']]);
        $codigo = $request->string('codigo')->toString();
        $recurso = $this->recursoService->buscarPorCodigo($codigo);

        if ($recurso instanceof Recurso) {
            return redirect()->route('recursos.edit', $recurso);
        }

        return redirect()->route('recursos.create', ['codigo' => $codigo])
            ->with('info', 'El código no está registrado. Complete el alta del recurso.');
    }

    /**
     * Datos comunes del formulario de alta y edición.
     *
     * @param  Usuario  $usuario  Administrador del sistema.
     * @param  Recurso  $recurso  Recurso nuevo o existente.
     * @return array<string, mixed>
     */
    private function armarFormulario(Usuario $usuario, Recurso $recurso): array
    {
        return [
            'recurso' => $recurso,
            'areas' => $this->obtenerAreas($usuario)->where('activo', true),
            'tipos' => TipoRecurso::cases(),
        ];
    }

    /**
     * Áreas visibles para el administrador (solo la suya si es responsable).
     *
     * @param  Usuario  $usuario  Administrador del sistema.
     * @return Collection<int, Area>
     */
    private function obtenerAreas(Usuario $usuario): Collection
    {
        return Area::query()
            ->when($usuario->esResponsableDeArea(), fn ($q) => $q->whereKey($usuario->id_area_asignada))
            ->orderBy('nombre_area')
            ->get();
    }
}
