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
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Catálogo de recursos disponibles para alumnos y docentes.
 *
 * @requisitos   RF-015, RN-002
 *
 * @see          docs/requisitos/BBB_CU010_ConsultarCatalogo_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\TipoRecurso;
use App\Http\Controllers\Controller;
use App\Http\Requests\FiltrarRecursosRequest;
use App\Models\Area;
use App\Services\CatalogoService;
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
    public function __construct(
        private CatalogoService $catalogoService,
    ) {}

    /**
     * Muestra las tarjetas de recursos disponibles con búsqueda y filtros.
     *
     * @param  FiltrarRecursosRequest  $request  Filtros validados.
     * @return View
     */
    public function index(FiltrarRecursosRequest $request): View
    {
        $filtros = $request->safe()->except('estado');

        return view('catalogo.index', [
            'recursos' => $this->catalogoService->listarCatalogo($filtros),
            'areas' => Area::query()->activas()->orderBy('nombre_area')->get(),
            'tipos' => TipoRecurso::cases(),
        ]);
    }
}
