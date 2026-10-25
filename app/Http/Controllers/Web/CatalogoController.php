<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         CatalogoController.php
 * @path         app/Http/Controllers/Web/CatalogoController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Catálogo de alumnos y docentes (prototipo hi-fi): cuadrícula
 *               o lista de recursos disponibles con búsqueda y filtros, y
 *               detalle con disponibilidad semanal y botón "Solicitar".
 *
 * @requisitos   RF-015, RF-022, RN-002
 *
 * @see          docs/requisitos/BBB_CU010_ConsultarCatalogo_v1.0.md; DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\TipoRecurso;
use App\Http\Controllers\Controller;
use App\Http\Requests\FiltrarRecursosRequest;
use App\Models\Area;
use App\Models\Recurso;
use App\Services\CatalogoService;
use App\Services\DisponibilidadRecursoService;
use Illuminate\View\View;

/**
 * Catálogo del usuario final.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class CatalogoController extends Controller
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  CatalogoService  $catalogoService  Dependencia inyectada.
     * @param  DisponibilidadRecursoService  $disponibilidadService  Dependencia inyectada.
     */
    public function __construct(
        private CatalogoService $catalogoService,
        private DisponibilidadRecursoService $disponibilidadService,
    ) {}

    /**
     * Muestra los recursos disponibles con búsqueda y filtros.
     *
     * @param  FiltrarRecursosRequest  $request  Filtros validados.
     * @return View
     */
    public function index(FiltrarRecursosRequest $request): View
    {
        $filtros = $request->safe()->except('estado');
        $recursos = $this->catalogoService->listarCatalogo($filtros);

        return view('catalogo.index', [
            'recursos' => $recursos,
            'disponibilidad' => $this->disponibilidadService->describir($recursos->items()),
            'areas' => Area::query()->activas()->orderBy('nombre_area')->get(),
            'tipos' => TipoRecurso::cases(),
            'filtros' => $filtros,
            'vistaLista' => $request->query('vista') === 'lista',
        ]);
    }

    /**
     * Detalle de un recurso visible en el catálogo; los demás responden 404 (RN-002).
     *
     * @param  int  $recurso  Identificador del recurso.
     * @return View
     */
    public function show(int $recurso): View
    {
        $modelo = Recurso::query()->visiblesEnCatalogo()->with('area')->findOrFail($recurso);

        return view('recursos.show', [
            'recurso' => $modelo,
            'disponibilidad' => $this->disponibilidadService->describir([$modelo])[$modelo->id_recurso],
            'semana' => $this->disponibilidadService->obtenerSemana($modelo),
            'historial' => null,
            'esAdministrador' => false,
        ]);
    }
}
