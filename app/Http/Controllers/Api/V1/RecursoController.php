<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       API REST v1
 * @file         RecursoController.php
 * @path         app/Http/Controllers/Api/V1/RecursoController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Inventario de la API y búsqueda de un recurso por código de
 *               barras (inventario o serie) dentro del tenant.
 *
 * @requisitos   RF-011, RF-013, RF-015, RS-006
 *
 * @see          docs/requisitos/BBB_CU007_RegistrarRecurso_v1.0.md (paso 5, E5); docs/api/BBB_API_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FiltrarRecursosRequest;
use App\Http\Resources\RecursoResource;
use App\Models\Recurso;
use App\Models\Usuario;
use App\Services\CatalogoService;
use App\Services\RecursoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

/**
 * Inventario de la API.
 *
 * @package App\Http\Controllers\Api\V1
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class RecursoController extends Controller
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  CatalogoService  $catalogoService  Dependencia inyectada.
     * @param  RecursoService  $recursoService  Dependencia inyectada.
     */
    public function __construct(
        private CatalogoService $catalogoService,
        private RecursoService $recursoService,
    ) {}

    /**
     * Lista el inventario con los cuatro estados.
     *
     * @param  FiltrarRecursosRequest  $request  Filtros validados.
     * @return AnonymousResourceCollection
     */
    public function index(FiltrarRecursosRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('gestionar', Recurso::class);
        /** @var Usuario $usuario */
        $usuario = $request->user();

        return RecursoResource::collection($this->catalogoService->listarInventario($usuario, $request->validated()));
    }

    /**
     * Devuelve el recurso del tenant cuyo inventario o serie coincide con el código.
     *
     * @param  string  $codigo  Código leído.
     * @return RecursoResource|JsonResponse
     */
    public function mostrarPorCodigo(string $codigo): RecursoResource|JsonResponse
    {
        Gate::authorize('gestionar', Recurso::class);
        $recurso = $this->recursoService->buscarPorCodigo($codigo);

        if (! $recurso instanceof Recurso) {
            return response()->json(['message' => 'No se encontró un recurso con ese código.'], 404);
        }

        return new RecursoResource($recurso);
    }
}
