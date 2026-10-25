<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.8: Historias, Reportes e Inteligencia Operativa
 * @file         TableroService.php
 * @path         app/Services/TableroService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Datos del dashboard operativo y de la agenda de mostrador:
 *               indicadores, préstamos por día, estado del inventario,
 *               solicitudes por atender y movimientos del día, siempre en el
 *               ámbito del usuario (institución y, si aplica, área).
 *
 * @requisitos   RF-013, RF-049, RF-054, RS-006
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Dashboard); DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoPrestamo;
use App\Enums\EstadoRecurso;
use App\Models\Prestamo;
use App\Models\Recurso;
use App\Models\Usuario;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Indicadores operativos del día.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class TableroService
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  ConsultaPrestamoService  $consultaPrestamoService  Dependencia inyectada.
     */
    public function __construct(
        private ConsultaPrestamoService $consultaPrestamoService,
    ) {}

    /**
     * Indicadores principales: activos, por recoger, devoluciones atrasadas y disponibilidad.
     *
     * @param  Usuario  $actor  Operador.
     * @return array{activos: int, activos_hoy: int, pendientes: int, pendientes_hoy: int, atrasados: int,
     *     disponibles: int, total_recursos: int}
     */
    public function obtenerIndicadores(Usuario $actor): array
    {
        $base = $this->consultaPrestamoService->paraUsuario($actor);
        $inventario = $this->obtenerInventario($actor);

        return [
            'activos' => (clone $base)->where('estado', EstadoPrestamo::Activo->value)->count(),
            'activos_hoy' => (clone $base)->where('estado', EstadoPrestamo::Activo->value)
                ->whereDate('fecha_hora_entrega_real', today())->count(),
            'pendientes' => (clone $base)->where('estado', EstadoPrestamo::Pendiente->value)->count(),
            'pendientes_hoy' => (clone $base)->where('estado', EstadoPrestamo::Pendiente->value)
                ->whereDate('fecha_hora_inicio', today())->count(),
            'atrasados' => (clone $base)->where('estado', EstadoPrestamo::Activo->value)
                ->where('fecha_hora_fin', '<', now())->count(),
            'disponibles' => $inventario[EstadoRecurso::Disponible->value],
            'total_recursos' => array_sum($inventario),
        ];
    }

    /**
     * Préstamos programados por día (sin cancelados) en los últimos N días, hoy incluido.
     *
     * @param  Usuario  $actor  Operador.
     * @param  int  $dias  Número de días de la serie.
     * @return list<array{fecha: Carbon, total: int, hoy: bool}>
     */
    public function obtenerSerieDiaria(Usuario $actor, int $dias = 14): array
    {
        $desde = today()->subDays($dias - 1);
        $totales = $this->consultaPrestamoService->paraUsuario($actor)
            ->where('estado', '!=', EstadoPrestamo::Cancelado->value)
            ->where('fecha_hora_inicio', '>=', $desde)
            ->where('fecha_hora_inicio', '<', today()->addDay())
            ->toBase()
            ->selectRaw('DATE(fecha_hora_inicio) AS dia, COUNT(*) AS total')
            ->groupBy('dia')
            ->pluck('total', 'dia');
        $serie = [];

        for ($indice = 0; $indice < $dias; $indice++) {
            $fecha = $desde->copy()->addDays($indice);
            $serie[] = [
                'fecha' => $fecha,
                'total' => (int) ($totales[$fecha->toDateString()] ?? 0),
                'hoy' => $fecha->isToday(),
            ];
        }

        return $serie;
    }

    /**
     * Recursos activos por estado (del área asignada si es responsable).
     *
     * @param  Usuario  $actor  Operador.
     * @return array<string, int>
     */
    public function obtenerInventario(Usuario $actor): array
    {
        $porEstado = Recurso::query()
            ->where('activo', true)
            ->when(
                $actor->id_area_asignada !== null,
                fn (Builder $q): Builder => $q->where('id_area', $actor->id_area_asignada),
            )
            ->toBase()
            ->selectRaw('estado, COUNT(*) AS total')
            ->groupBy('estado')
            ->pluck('total', 'estado');
        $inventario = [];

        foreach (EstadoRecurso::cases() as $estado) {
            $inventario[$estado->value] = (int) ($porEstado[$estado->value] ?? 0);
        }

        return $inventario;
    }

    /**
     * Reservas por recoger más próximas.
     *
     * @param  Usuario  $actor  Operador.
     * @param  int  $limite  Máximo de filas.
     * @return Collection<int, Prestamo>
     */
    public function obtenerPorAtender(Usuario $actor, int $limite = 5): Collection
    {
        return $this->consultaPrestamoService->paraUsuario($actor)
            ->with(['usuario', 'detalles.recurso.area'])
            ->where('estado', EstadoPrestamo::Pendiente->value)
            ->orderBy('fecha_hora_inicio')
            ->limit($limite)
            ->get();
    }

    /**
     * Entregas y devoluciones programadas para hoy, ordenadas por hora.
     *
     * @param  Usuario  $actor  Operador.
     * @return list<array{hora: CarbonInterface, tipo: string, icono: string, prestamo: Prestamo, estado: string,
     *     clase: string}>
     */
    public function obtenerAgendaHoy(Usuario $actor): array
    {
        $prestamos = $this->consultaPrestamoService->paraUsuario($actor)
            ->with(['usuario', 'detalles.recurso'])
            ->where(fn (Builder $q): Builder => $q
                ->where(fn (Builder $e): Builder => $e->where('estado', EstadoPrestamo::Pendiente->value)
                    ->whereDate('fecha_hora_inicio', today()))
                ->orWhere(fn (Builder $d): Builder => $d->where('estado', EstadoPrestamo::Activo->value)
                    ->where('fecha_hora_fin', '<', today()->addDay())))
            ->get();
        $agenda = [];

        foreach ($prestamos as $prestamo) {
            $agenda[] = $prestamo->estado === EstadoPrestamo::Pendiente->value
                ? $this->armarEntrega($prestamo)
                : $this->armarDevolucion($prestamo);
        }

        usort($agenda, fn (array $a, array $b): int => $a['hora'] <=> $b['hora']);

        return $agenda;
    }

    /**
     * Fila de agenda para una entrega pendiente.
     *
     * @param  Prestamo  $prestamo  Reserva por recoger.
     * @return array{hora: CarbonInterface, tipo: string, icono: string, prestamo: Prestamo, estado: string,
     *     clase: string}
     */
    private function armarEntrega(Prestamo $prestamo): array
    {
        $inicio = $prestamo->fecha_hora_inicio;
        [$estado, $clase] = $inicio->isFuture()
            ? ['Programada', 'insignia--neutra']
            : [
                'En tolerancia · ' . sprintf('%02d', (int) $inicio->diffInMinutes(now())) . ' min',
                'insignia--pendiente',
            ];

        return [
            'hora' => $inicio,
            'tipo' => 'Entrega',
            'icono' => 'outbox',
            'prestamo' => $prestamo,
            'estado' => $estado,
            'clase' => $clase,
        ];
    }

    /**
     * Fila de agenda para una devolución de hoy o atrasada.
     *
     * @param  Prestamo  $prestamo  Préstamo en custodia.
     * @return array{hora: CarbonInterface, tipo: string, icono: string, prestamo: Prestamo, estado: string,
     *     clase: string}
     */
    private function armarDevolucion(Prestamo $prestamo): array
    {
        $fin = $prestamo->fecha_hora_fin;
        [$estado, $clase] = $fin->isFuture()
            ? ['A tiempo', 'insignia--exito']
            : ['Atrasada', 'insignia--peligro'];

        return [
            'hora' => $fin,
            'tipo' => 'Devolución',
            'icono' => 'assignment_return',
            'prestamo' => $prestamo,
            'estado' => $estado,
            'clase' => $clase,
        ];
    }
}
