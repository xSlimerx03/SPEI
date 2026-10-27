<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.8: Historias, Reportes e Inteligencia Operativa
 * @file         ReporteDetalleService.php
 * @path         app/Services/ReporteDetalleService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Reportes detallados del periodo: horas de uso por recurso
 *               (RF-055), sanciones e incidencias (RF-056), cancelaciones con su
 *               justificación y origen (RF-057) y filas para exportar a CSV
 *               (RF-058). Mismo ámbito que ReporteService (institución y área);
 *               las consultas DB::table filtran id_institucion de forma
 *               explícita.
 *
 * @requisitos   RF-055, RF-056, RF-057, RF-058, RS-006
 *
 * @see          CU-024
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoPrestamo;
use App\Enums\TipoIncidente;
use App\Models\DetallePrestamo;
use App\Models\LogAuditoria;
use App\Models\Penalizacion;
use App\Models\Prestamo;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Reportes de uso, incidentes y cancelaciones.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ReporteDetalleService
{
    /**
     * Secciones exportables a CSV.
     */
    public const SECCIONES = ['mas_prestados', 'horas', 'sanciones', 'cancelaciones'];

    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  ReporteService  $reporteService  Dependencia inyectada.
     */
    public function __construct(
        private ReporteService $reporteService,
    ) {}

    /**
     * Métricas detalladas del periodo.
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @param  int  $dias  Periodo en días.
     * @param  int|null  $idArea  Área opcional.
     * @return array{horas: list<array{nombre: string, prestamos: int, minutos: int}>,
     *     sanciones: list<array{tipo: string, total: int, marcas: int, exentadas: int}>,
     *     incidencias: list<array{folio: string, recurso: string, condicion: string, texto: string}>,
     *     cancelaciones: list<array{folio: string, solicitante: string, fecha: string, origen: string,
     *     justificacion: string}>}
     */
    public function generar(Usuario $actor, int $dias, ?int $idArea): array
    {
        $base = $this->reporteService->consultarBase($actor, $dias, $idArea);

        return [
            'horas' => $this->obtenerHoras(clone $base, (int) $actor->id_institucion, 10),
            'sanciones' => $this->obtenerSanciones(clone $base),
            'incidencias' => $this->obtenerIncidencias(clone $base),
            'cancelaciones' => $this->obtenerCancelaciones(clone $base, 15),
        ];
    }

    /**
     * Encabezado y filas de una sección para exportar (sin límite de filas).
     *
     * @param  string  $seccion  Una de SECCIONES.
     * @param  Usuario  $actor  Administrador del sistema.
     * @param  int  $dias  Periodo en días.
     * @param  int|null  $idArea  Área opcional.
     * @return array{0: list<string>, 1: list<list<string>>}
     */
    public function obtenerFilas(string $seccion, Usuario $actor, int $dias, ?int $idArea): array
    {
        $base = $this->reporteService->consultarBase($actor, $dias, $idArea);
        $idInstitucion = (int) $actor->id_institucion;

        return match ($seccion) {
            'horas' => [['Recurso', 'Préstamos devueltos', 'Minutos de uso'], array_map(
                fn (array $f): array => [$f['nombre'], (string) $f['prestamos'], (string) $f['minutos']],
                $this->obtenerHoras($base, $idInstitucion, null),
            )],
            'sanciones' => [['Tipo', 'Sanciones', 'Marcas', 'Exentadas'], array_map(
                fn (array $f): array => [
                    $f['tipo'],
                    (string) $f['total'],
                    (string) $f['marcas'],
                    (string) $f['exentadas'],
                ],
                $this->obtenerSanciones($base),
            )],
            'cancelaciones' => [['Folio', 'Solicitante', 'Fecha', 'Origen', 'Justificación'], array_map(
                fn (array $f): array => array_values($f),
                $this->obtenerCancelaciones($base, null),
            )],
            default => [['Recurso', 'Préstamos entregados'], array_map(
                fn (array $f): array => [$f['nombre'], (string) $f['total']],
                $this->reporteService->generar($actor, $dias, $idArea)['masPrestados'],
            )],
        };
    }

    /**
     * Minutos reales de uso por recurso (entrega → devolución) de los préstamos devueltos (RF-055).
     *
     * @param  Builder<Prestamo>  $base  Préstamos del periodo.
     * @param  int  $idInstitucion  Institución del actor.
     * @param  int|null  $limite  Máximo de filas; null sin límite.
     * @return list<array{nombre: string, prestamos: int, minutos: int}>
     */
    private function obtenerHoras(Builder $base, int $idInstitucion, ?int $limite): array
    {
        $ids = $base->where('estado', EstadoPrestamo::Devuelto->value)->whereNotNull('fecha_hora_entrega_real')
            ->pluck('id_prestamo');

        return DB::table('detalle_prestamo')
            ->join('prestamos', 'prestamos.id_prestamo', '=', 'detalle_prestamo.id_prestamo')
            ->join('recursos', 'recursos.id_recurso', '=', 'detalle_prestamo.id_recurso')
            ->where('detalle_prestamo.id_institucion', $idInstitucion)
            ->where('prestamos.id_institucion', $idInstitucion)
            ->whereIn('detalle_prestamo.id_prestamo', $ids)
            ->selectRaw('recursos.nombre_recurso AS nombre, COUNT(*) AS prestamos, SUM(TIMESTAMPDIFF(MINUTE, '
                . 'prestamos.fecha_hora_entrega_real, prestamos.fecha_hora_devolucion_real)) AS minutos')
            ->groupBy('recursos.id_recurso', 'recursos.nombre_recurso')
            ->orderByDesc('minutos')
            ->when($limite !== null, fn (QueryBuilder $q): QueryBuilder => $q->limit((int) $limite))
            ->get()
            ->map(fn (object $f): array => [
                'nombre' => (string) $f->nombre,
                'prestamos' => (int) $f->prestamos,
                'minutos' => (int) $f->minutos,
            ])
            ->all();
    }

    /**
     * Sanciones de los préstamos del periodo por tipo (RF-056).
     *
     * @param  Builder<Prestamo>  $base  Préstamos del periodo.
     * @return list<array{tipo: string, total: int, marcas: int, exentadas: int}>
     */
    private function obtenerSanciones(Builder $base): array
    {
        $penalizaciones = Penalizacion::query()->whereIn('id_prestamo', $base->pluck('id_prestamo'))->get();
        $filas = [];

        foreach (TipoIncidente::cases() as $tipo) {
            $delTipo = $penalizaciones->filter(fn (Penalizacion $p): bool => $p->tipo_penalizacion === $tipo);
            $filas[] = [
                'tipo' => $tipo->obtenerEtiqueta(),
                'total' => $delTipo->count(),
                'marcas' => (int) $delTipo->where('exentada', false)->sum('marcas_aplicadas'),
                'exentadas' => $delTipo->where('exentada', true)->count(),
            ];
        }

        return $filas;
    }

    /**
     * Últimas incidencias registradas al devolver (RF-056).
     *
     * @param  Builder<Prestamo>  $base  Préstamos del periodo.
     * @return list<array{folio: string, recurso: string, condicion: string, texto: string}>
     */
    private function obtenerIncidencias(Builder $base): array
    {
        return DetallePrestamo::query()
            ->with(['recurso', 'prestamo'])
            ->whereIn('id_prestamo', $base->pluck('id_prestamo'))
            ->whereIn('condicion_devolucion', ['malo', 'danado', 'no_entregado'])
            ->orderByDesc('id_detalle')
            ->limit(10)
            ->get()
            ->map(fn (DetallePrestamo $d): array => [
                'folio' => $d->prestamo->obtenerFolio(),
                'recurso' => $d->recurso->nombre_recurso,
                'condicion' => TipoIncidente::desdeCondicion($d->condicion_devolucion)?->obtenerEtiqueta() ?? '',
                'texto' => (string) $d->incidencia,
            ])
            ->values()
            ->all();
    }

    /**
     * Cancelaciones y vencimientos con su origen y justificación (RF-057).
     *
     * @param  Builder<Prestamo>  $base  Préstamos del periodo.
     * @param  int|null  $limite  Máximo de filas; null sin límite.
     * @return list<array{folio: string, solicitante: string, fecha: string, origen: string, justificacion: string}>
     */
    private function obtenerCancelaciones(Builder $base, ?int $limite): array
    {
        $prestamos = $base->with('usuario')
            ->whereIn('estado', [EstadoPrestamo::Cancelado->value, EstadoPrestamo::Vencido->value])
            ->orderByDesc('fecha_hora_inicio')
            ->when($limite !== null, fn (Builder $q): Builder => $q->limit((int) $limite))
            ->get();
        $acciones = LogAuditoria::query()
            ->where('tabla_afectada', 'prestamos')
            ->whereIn('accion', ['CANCELAR_PRESTAMO', 'DESPLAZAR_PRESTAMO'])
            ->whereIn('registro_id', $prestamos->modelKeys())
            ->get(['registro_id', 'accion', 'id_usuario'])
            ->keyBy('registro_id');

        return $prestamos->map(fn (Prestamo $p): array => [
            'folio' => $p->obtenerFolio(),
            'solicitante' => $p->usuario->nombre_completo,
            'fecha' => $p->fecha_hora_inicio->format('d/m/Y H:i'),
            'origen' => $this->describirOrigen($p, $acciones->get($p->id_prestamo)),
            'justificacion' => (string) $p->motivo_cancelacion,
        ])->values()->all();
    }

    /**
     * Quién cerró la solicitud sin entrega.
     *
     * @param  Prestamo  $prestamo  Préstamo cancelado o vencido.
     * @param  LogAuditoria|null  $accion  Evento de cancelación, si existe.
     * @return string
     */
    private function describirOrigen(Prestamo $prestamo, ?LogAuditoria $accion): string
    {
        return match (true) {
            $prestamo->estado === EstadoPrestamo::Vencido->value => 'No se recogió a tiempo',
            $accion?->accion === 'DESPLAZAR_PRESTAMO' => 'Prioridad RN-008',
            $accion !== null && $accion->id_usuario === $prestamo->id_usuario => 'Solicitante',
            default => 'Operador',
        };
    }
}
