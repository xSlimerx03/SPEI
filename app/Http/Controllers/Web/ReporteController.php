<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.8: Historias, Reportes e Inteligencia Operativa
 * @file         ReporteController.php
 * @path         app/Http/Controllers/Web/ReporteController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-25
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Pantalla de reportes del administrador del sistema
 *               (prototipo hi-fi): periodo, área, tarjetas de métricas,
 *               exportación CSV del historial e impresión.
 *
 * @requisitos   RF-054, RF-055, RF-056, RF-057, RF-058
 *
 * @see          app/Services/ReporteService.php; DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Usuario;
use App\Services\ReporteService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Reportes operativos.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ReporteController extends Controller
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  ReporteService  $reporteService  Dependencia inyectada.
     */
    public function __construct(
        private ReporteService $reporteService,
    ) {}

    /**
     * Muestra las métricas del periodo y el área elegidos.
     *
     * @param  Request  $request  Periodo y área.
     * @return View
     */
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $datos = $request->validate([
            'periodo' => ['nullable', 'integer', Rule::in(ReporteService::PERIODOS)],
            'id_area' => ['nullable', 'integer', 'min:1'],
        ]);
        $dias = (int) ($datos['periodo'] ?? 30);
        $idArea = isset($datos['id_area']) ? (int) $datos['id_area'] : null;

        return view('reportes.index', [
            'dias' => $dias,
            'idArea' => $idArea,
            'reporte' => $this->reporteService->generar($usuario, $dias, $idArea),
            'areas' => Area::query()
                ->when(
                    $usuario->id_area_asignada !== null,
                    fn (Builder $q): Builder => $q->whereKey($usuario->id_area_asignada),
                )
                ->orderBy('nombre_area')
                ->get(),
        ]);
    }
}
