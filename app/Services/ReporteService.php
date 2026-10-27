<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.8: Historias, Reportes e Inteligencia Operativa
 * @file         ReporteService.php
 * @path         app/Services/ReporteService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-25
 * @lastModified 2026-10-27
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Indicadores de los reportes operativos: recursos más
 *               prestados, tiempo promedio de uso por área, retraso en la
 *               devolución, condición al devolver y desglose de cancelaciones
 *               y vencimientos. Todo se calcula en el ámbito del usuario
 *               (institución y área) y en el periodo elegido.
 *
 * @requisitos   RF-054, RF-055, RF-056, RF-057, RS-006
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Reportes); DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoPrestamo;
use App\Models\LogAuditoria;
use App\Models\Prestamo;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Métricas de los reportes.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ReporteService
{
    /**
     * Periodos admitidos en días.
     */
    public const PERIODOS = [7, 30, 90];

    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  ConsultaPrestamoService  $consultaPrestamoService  Dependencia inyectada.
     */
    public function __construct(
        private ConsultaPrestamoService $consultaPrestamoService,
    ) {}

    /**
     * Arma todas las métricas del reporte.
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @param  int  $dias  Periodo en días.
     * @param  int|null  $idArea  Área opcional.
     * @return array<string, mixed>
     */
    public function generar(Usuario $actor, int $dias, ?int $idArea): array
    {
        $base = $this->consultarBase($actor, $dias, $idArea);
        // NOTE: las consultas con DB::table no pasan por el Global Scope; el filtro de tenant se repite explícito.
        $idInstitucion = (int) $actor->id_institucion;

        return [
            'total' => (clone $base)->count(),
            'masPrestados' => $this->obtenerMasPrestados(clone $base, $idInstitucion),
            'usoPorArea' => $this->obtenerUsoPorArea(clone $base, $idInstitucion),
            'retraso' => $this->obtenerRetrasoPromedio(clone $base),
            'condiciones' => $this->contarCondiciones(clone $base, $idInstitucion),
            'cierres' => $this->desglosarCierres(clone $base),
        ];
    }

    /**
     * Préstamos del ámbito del usuario (institución y área) que iniciaron en el periodo.
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @param  int  $dias  Periodo en días.
     * @param  int|null  $idArea  Área opcional.
     * @return Builder<Prestamo>
     */
    public function consultarBase(Usuario $actor, int $dias, ?int $idArea): Builder
    {
        return $this->consultaPrestamoService->filtrar(
            $this->consultaPrestamoService->paraUsuario($actor),
            ['id_area' => $idArea],
        )->where('fecha_hora_inicio', '>=', today()->subDays($dias - 1));
    }

    /**
     * Siete recursos con más préstamos entregados en el periodo.
     *
     * @param  Builder<Prestamo>  $base  Préstamos del periodo.
     * @param  int  $idInstitucion  Institución del actor.
     * @return list<array{nombre: string, total: int}>
     */
    private function obtenerMasPrestados(Builder $base, int $idInstitucion): array
    {
        $ids = $base->whereIn('estado', [EstadoPrestamo::Activo->value, EstadoPrestamo::Devuelto->value])
            ->pluck('id_prestamo');

        return DB::table('detalle_prestamo')
            ->join('recursos', 'recursos.id_recurso', '=', 'detalle_prestamo.id_recurso')
            ->where('detalle_prestamo.id_institucion', $idInstitucion)
            ->where('recursos.id_institucion', $idInstitucion)
            ->whereIn('detalle_prestamo.id_prestamo', $ids)
            ->selectRaw('recursos.nombre_recurso AS nombre, COUNT(*) AS total')
            ->groupBy('recursos.id_recurso', 'recursos.nombre_recurso')
            ->orderByDesc('total')
            ->limit(7)
            ->get()
            ->map(fn (object $fila): array => ['nombre' => (string) $fila->nombre, 'total' => (int) $fila->total])
            ->all();
    }

    /**
     * Minutos promedio entre entrega y devolución reales, por área.
     *
     * @param  Builder<Prestamo>  $base  Préstamos del periodo.
     * @param  int  $idInstitucion  Institución del actor.
     * @return list<array{area: string, minutos: int}>
     */
    private function obtenerUsoPorArea(Builder $base, int $idInstitucion): array
    {
        $ids = $base->where('estado', EstadoPrestamo::Devuelto->value)->pluck('id_prestamo');

        return DB::table('prestamos')
            ->join('detalle_prestamo', 'detalle_prestamo.id_prestamo', '=', 'prestamos.id_prestamo')
            ->join('recursos', 'recursos.id_recurso', '=', 'detalle_prestamo.id_recurso')
            ->join('areas', 'areas.id_area', '=', 'recursos.id_area')
            ->where('prestamos.id_institucion', $idInstitucion)
            ->where('areas.id_institucion', $idInstitucion)
            ->whereIn('prestamos.id_prestamo', $ids)
            ->selectRaw('areas.nombre_area AS area, AVG(TIMESTAMPDIFF(MINUTE, prestamos.fecha_hora_entrega_real, '
                . 'prestamos.fecha_hora_devolucion_real)) AS minutos')
            ->groupBy('areas.id_area', 'areas.nombre_area')
            ->orderByDesc('minutos')
            ->get()
            ->map(fn (object $fila): array => [
                'area' => (string) $fila->area,
                'minutos' => (int) round((float) $fila->minutos),
            ])
            ->all();
    }

    /**
     * Retraso promedio en minutos de las devoluciones tardías y su número.
     *
     * @param  Builder<Prestamo>  $base  Préstamos del periodo.
     * @return array{minutos: int, tardias: int, devueltos: int}
     */
    private function obtenerRetrasoPromedio(Builder $base): array
    {
        $devueltos = (clone $base)->where('estado', EstadoPrestamo::Devuelto->value);
        $tardias = (clone $devueltos)->whereColumn('fecha_hora_devolucion_real', '>', 'fecha_hora_fin');
        $promedio = (clone $tardias)->toBase()
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, fecha_hora_fin, fecha_hora_devolucion_real)) AS minutos')
            ->value('minutos');

        return [
            'minutos' => (int) round((float) $promedio),
            'tardias' => $tardias->count(),
            'devueltos' => $devueltos->count(),
        ];
    }

    /**
     * Recursos devueltos por condición.
     *
     * @param  Builder<Prestamo>  $base  Préstamos del periodo.
     * @param  int  $idInstitucion  Institución del actor.
     * @return array<string, int>
     */
    private function contarCondiciones(Builder $base, int $idInstitucion): array
    {
        $ids = $base->where('estado', EstadoPrestamo::Devuelto->value)->pluck('id_prestamo');
        $porCondicion = DB::table('detalle_prestamo')
            ->where('id_institucion', $idInstitucion)
            ->whereIn('id_prestamo', $ids)
            ->selectRaw('condicion_devolucion AS condicion, COUNT(*) AS total')
            ->groupBy('condicion_devolucion')
            ->pluck('total', 'condicion');
        $conteo = [];

        foreach (['bueno', 'regular', 'malo', 'danado', 'no_entregado'] as $condicion) {
            $conteo[$condicion] = (int) ($porCondicion[$condicion] ?? 0);
        }

        return $conteo;
    }

    /**
     * Préstamos cerrados sin entrega: cancelados por el solicitante, por un operador y vencidos.
     *
     * @param  Builder<Prestamo>  $base  Préstamos del periodo.
     * @return array{solicitante: int, prioridad: int, operador: int, vencidos: int}
     */
    private function desglosarCierres(Builder $base): array
    {
        $cancelados = (clone $base)->where('estado', EstadoPrestamo::Cancelado->value)
            ->pluck('id_usuario', 'id_prestamo');
        $actores = LogAuditoria::query()
            ->where('tabla_afectada', 'prestamos')
            ->where('accion', 'CANCELAR_PRESTAMO')
            ->whereIn('registro_id', $cancelados->keys())
            ->pluck('id_usuario', 'registro_id');
        $porSolicitante = $cancelados->filter(
            fn (int $idUsuario, int $idPrestamo): bool => (int) ($actores[$idPrestamo] ?? 0) === $idUsuario,
        )->count();
        // RN-008: las reservas desplazadas por una solicitud de mayor prioridad se cuentan aparte.
        $porPrioridad = LogAuditoria::query()
            ->where('tabla_afectada', 'prestamos')
            ->where('accion', 'DESPLAZAR_PRESTAMO')
            ->whereIn('registro_id', $cancelados->keys())
            ->distinct()
            ->count('registro_id');

        return [
            'solicitante' => $porSolicitante,
            'prioridad' => $porPrioridad,
            'operador' => $cancelados->count() - $porSolicitante - $porPrioridad,
            'vencidos' => (clone $base)->where('estado', EstadoPrestamo::Vencido->value)->count(),
        ];
    }
}
