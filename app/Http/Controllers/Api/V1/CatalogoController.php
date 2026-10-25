<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       API REST v1
 * @file         CatalogoController.php
 * @path         app/Http/Controllers/Api/V1/CatalogoController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Catálogo paginado de recursos disponibles para alumnos y
 *               docentes.
 *
 * @requisitos   RF-015, RN-002, RNF-009
 *
 * @see          docs/requisitos/BBB_CU010_ConsultarCatalogo_v1.0.md; docs/api/BBB_API_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FiltrarRecursosRequest;
use App\Http\Resources\RecursoResource;
use App\Services\CatalogoService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Catálogo de la API.
 *
 * @package App\Http\Controllers\Api\V1
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class CatalogoController extends Controller
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  CatalogoService  $catalogoService  Dependencia inyectada.
     */
    public function __construct(
        private CatalogoService $catalogoService,
    ) {}

    /**
     * Lista los recursos visibles para el usuario final.
     *
     * @param  FiltrarRecursosRequest  $request  Filtros validados.
     * @return AnonymousResourceCollection
     */
    public function index(FiltrarRecursosRequest $request): AnonymousResourceCollection
    {
        return RecursoResource::collection(
            $this->catalogoService->listarCatalogo($request->safe()->except('estado')),
        );
    }
}
