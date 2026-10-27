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
 * @lastModified 2026-10-27
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
use App\Services\AuditoriaService;
use App\Services\ReporteDetalleService;
use App\Services\ReporteService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * @param  ReporteDetalleService  $reporteDetalleService  Dependencia inyectada.
     * @param  AuditoriaService  $auditoriaService  Dependencia inyectada.
     */
    public function __construct(
        private ReporteService $reporteService,
        private ReporteDetalleService $reporteDetalleService,
        private AuditoriaService $auditoriaService,
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
        [$dias, $idArea] = $this->validarFiltros($request);

        return view('reportes.index', [
            'dias' => $dias,
            'idArea' => $idArea,
            'reporte' => $this->reporteService->generar($usuario, $dias, $idArea),
            'detalle' => $this->reporteDetalleService->generar($usuario, $dias, $idArea),
            'areas' => Area::query()
                ->when(
                    $usuario->id_area_asignada !== null,
                    fn (Builder $q): Builder => $q->whereKey($usuario->id_area_asignada),
                )
                ->orderBy('nombre_area')
                ->get(),
        ]);
    }

    /**
     * Exporta una sección del reporte a CSV UTF-8 con BOM; neutraliza fórmulas y deja registro (RF-058).
     *
     * @param  Request  $request  Sección, periodo y área.
     * @return StreamedResponse
     */
    public function exportar(Request $request): StreamedResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        [$dias, $idArea] = $this->validarFiltros($request);
        $seccion = (string) $request->validate([
            'seccion' => ['required', Rule::in(ReporteDetalleService::SECCIONES)],
        ])['seccion'];
        [$encabezado, $filas] = $this->reporteDetalleService->obtenerFilas($seccion, $usuario, $dias, $idArea);
        $this->auditoriaService->registrarMasivo('EXPORTAR_REPORTE', 'prestamos', 0, (int) $usuario->id_institucion, [
            'seccion' => $seccion,
            'periodo_dias' => $dias,
            'id_area' => $idArea,
            'filas' => count($filas),
        ]);

        return response()->streamDownload(function () use ($encabezado, $filas): void {
            $salida = fopen('php://output', 'w');

            if ($salida === false) {
                return;
            }

            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, $encabezado, ',', '"', '');

            foreach ($filas as $fila) {
                fputcsv($salida, array_map(
                    fn (string $valor): string => preg_match('/^[=+@\-\t\r\n]/u', $valor) === 1 ? "'" . $valor : $valor,
                    $fila,
                ), ',', '"', '');
            }

            fclose($salida);
        }, 'reporte-' . $seccion . '-' . today()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Periodo (7, 30 o 90 días) y área opcional validados.
     *
     * @param  Request  $request  Petición.
     * @return array{0: int, 1: int|null}
     */
    private function validarFiltros(Request $request): array
    {
        $datos = $request->validate([
            'periodo' => ['nullable', 'integer', Rule::in(ReporteService::PERIODOS)],
            'id_area' => ['nullable', 'integer', 'min:1'],
        ]);

        return [(int) ($datos['periodo'] ?? 30), isset($datos['id_area']) ? (int) $datos['id_area'] : null];
    }
}
