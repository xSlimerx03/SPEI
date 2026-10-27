<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.6: Penalizaciones y Sanciones Automatizadas
 * @file         SancionController.php
 * @path         app/Http/Controllers/Web/SancionController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Pantallas de sanciones del administrador del sistema: lista de
 *               penalizaciones con filtros y exención justificada (RF-044), y
 *               matriz de reglas de la institución (RF-041). El responsable de
 *               un área solo ve y exenta sanciones de préstamos de su área.
 *
 * @requisitos   RF-041, RF-042, RF-043, RF-044, RF-056, RS-006
 *
 * @see          CU-019, CU-020
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\TipoIncidente;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarMatrizRequest;
use App\Models\Penalizacion;
use App\Models\Usuario;
use App\Services\SancionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Sanciones y matriz.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class SancionController extends Controller
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  SancionService  $sancionService  Dependencia inyectada.
     */
    public function __construct(
        private SancionService $sancionService,
    ) {}

    /**
     * Lista de penalizaciones con filtros por tipo, estado y usuario.
     *
     * @param  Request  $request  Filtros.
     * @return View
     */
    public function index(Request $request): View
    {
        $actor = $this->obtenerActor($request);
        $filtros = $request->validate([
            'texto' => ['nullable', 'string', 'max:100'],
            'tipo' => ['nullable', Rule::enum(TipoIncidente::class)],
            'estado' => ['nullable', Rule::in(['vigente', 'exentada'])],
        ]);

        $penalizaciones = $this->consultar($actor)
            ->with(['usuario', 'prestamo'])
            ->when(
                $filtros['tipo'] ?? null,
                fn (Builder $q, string $tipo): Builder => $q->where('tipo_penalizacion', $tipo),
            )
            ->when($filtros['estado'] ?? null, fn (Builder $q, string $estado): Builder => $q
                ->where('exentada', $estado === 'exentada'))
            ->when($filtros['texto'] ?? null, fn (Builder $q, string $texto): Builder => $q
                ->whereHas('usuario', fn (Builder $u): Builder => $u
                    ->where('nombre_completo', 'like', '%' . addcslashes($texto, '%_\\') . '%')))
            ->orderByDesc('fecha_creacion')
            ->orderByDesc('id_penalizacion')
            ->paginate(15)
            ->withQueryString();

        return view('sanciones.index', [
            'penalizaciones' => $penalizaciones,
            'filtros' => $filtros,
            'actor' => $actor,
            'bloqueados' => Usuario::query()->where('bloqueado', true)->count(),
        ]);
    }

    /**
     * Matriz de reglas de la institución.
     *
     * @param  Request  $request  Petición.
     * @return View
     */
    public function matriz(Request $request): View
    {
        $actor = $this->obtenerActor($request);
        abort_if($actor->esResponsableDeArea(), 403);

        return view('sanciones.matriz', ['reglas' => $this->sancionService->obtenerMatriz($actor->id_institucion)]);
    }

    /**
     * Guarda la matriz completa.
     *
     * @param  ActualizarMatrizRequest  $request  Datos validados.
     * @return RedirectResponse
     */
    public function actualizarMatriz(ActualizarMatrizRequest $request): RedirectResponse
    {
        $actor = $this->obtenerActor($request);
        $matriz = $this->sancionService->obtenerMatriz($actor->id_institucion);

        foreach ($request->validated('reglas') as $tipo => $datos) {
            $regla = $matriz->get((string) $tipo);

            if ($regla !== null) {
                $this->sancionService->actualizarRegla($regla, [
                    'marcas_por_incidente' => (int) $datos['marcas_por_incidente'],
                    'umbral_bloqueo' => (int) $datos['umbral_bloqueo'],
                    'dias_sancion' => (int) $datos['dias_sancion'],
                    'activo' => (bool) ($datos['activo'] ?? false),
                ]);
            }
        }

        return redirect()->route('sanciones.matriz')->with('success', 'Matriz de sanciones guardada.');
    }

    /**
     * Exenta una penalización con justificación (RF-044).
     *
     * @param  Request  $request  Motivo de la exención.
     * @param  Penalizacion  $penalizacion  Penalización de la institución.
     * @return RedirectResponse
     */
    public function exentar(Request $request, Penalizacion $penalizacion): RedirectResponse
    {
        $actor = $this->obtenerActor($request);
        abort_unless($this->consultar($actor)->whereKey($penalizacion->id_penalizacion)->exists(), 404);
        $datos = $request->validateWithBag('exencion', [
            'motivo_exencion' => ['required', 'string', 'min:20', 'max:1000'],
        ]);
        $this->sancionService->exentar($penalizacion, trim($datos['motivo_exencion']), $actor);

        return back()->with('success', 'Sanción exentada y registrada en auditoría.');
    }

    /**
     * Penalizaciones visibles para el actor (el responsable de un área solo las de préstamos de su área).
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @return Builder<Penalizacion>
     */
    private function consultar(Usuario $actor): Builder
    {
        $consulta = Penalizacion::query()->where('id_institucion', $actor->id_institucion);

        if (! $actor->esResponsableDeArea()) {
            return $consulta;
        }

        $area = $actor->id_area_asignada;

        return $consulta->whereHas(
            'prestamo.detalles.recurso',
            fn (Builder $q): Builder => $q->where('id_area', $area),
        );
    }

    /**
     * Usuario autenticado.
     *
     * @param  Request  $request  Petición.
     * @return Usuario
     */
    private function obtenerActor(Request $request): Usuario
    {
        /** @var Usuario $actor */
        $actor = $request->user();

        return $actor;
    }
}
