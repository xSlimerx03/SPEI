<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         DisponibilidadRecursoService.php
 * @path         app/Services/DisponibilidadRecursoService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Describe la disponibilidad de los recursos a partir de los
 *               préstamos abiertos: texto del pie de cada tarjeta, línea de
 *               tiempo semanal (7:00 a 22:00) e historial reciente.
 *
 * @requisitos   RF-013, RF-015, RF-023, RN-002
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Recursos y Detalle); DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoPrestamo;
use App\Enums\EstadoRecurso;
use App\Models\Prestamo;
use App\Models\Recurso;
use App\Support\FormatoFecha;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Disponibilidad de recursos para el catálogo y el inventario.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class DisponibilidadRecursoService
{
    private const HORA_APERTURA = 7;

    private const HORA_CIERRE = 22;

    /**
     * Texto del pie de cada tarjeta, indexado por id_recurso.
     *
     * @param  iterable<Recurso>  $recursos  Recursos de la página.
     * @return array<int, string>
     */
    public function describir(iterable $recursos): array
    {
        $lista = collect($recursos);
        $abiertos = $this->obtenerPrestamosAbiertos($lista->pluck('id_recurso')->all());
        $textos = [];

        foreach ($lista as $recurso) {
            $textos[$recurso->id_recurso] = $this->describirUno($recurso, $abiertos[$recurso->id_recurso] ?? null);
        }

        return $textos;
    }

    /**
     * Bloques ocupados por día para los próximos seis días (hoy incluido).
     *
     * @param  Recurso  $recurso  Recurso consultado.
     * @return list<array{fecha: Carbon, hoy: bool, bloques: list<array{izquierda: float, ancho: float,
     *     texto: string, pendiente: bool}>}>
     */
    public function obtenerSemana(Recurso $recurso): array
    {
        $desde = today();
        $hasta = today()->addDays(6);
        $prestamos = Prestamo::query()
            ->whereIn('estado', [EstadoPrestamo::Pendiente->value, EstadoPrestamo::Activo->value])
            ->whereHas('detalles', fn (Builder $q): Builder => $q->where('id_recurso', $recurso->id_recurso))
            ->where('fecha_hora_inicio', '<', $hasta)
            ->where('fecha_hora_fin', '>', $desde)
            ->get();
        $semana = [];

        for ($dia = 0; $dia < 6; $dia++) {
            $fecha = $desde->copy()->addDays($dia);
            $semana[] = [
                'fecha' => $fecha,
                'hoy' => $fecha->isToday(),
                'bloques' => $this->calcularBloques($prestamos, $fecha),
            ];
        }

        return $semana;
    }

    /**
     * Últimos préstamos que incluyeron el recurso.
     *
     * @param  Recurso  $recurso  Recurso consultado.
     * @param  int  $limite  Máximo de filas.
     * @return Collection<int, Prestamo>
     */
    public function obtenerHistorial(Recurso $recurso, int $limite = 5): Collection
    {
        return Prestamo::query()
            ->with('usuario')
            ->whereHas('detalles', fn (Builder $q): Builder => $q->where('id_recurso', $recurso->id_recurso))
            ->orderByDesc('fecha_hora_inicio')
            ->limit($limite)
            ->get();
    }

    /**
     * Préstamos pendientes o activos por recurso (uno por recurso: el más próximo).
     *
     * @param  list<int>  $ids  Recursos.
     * @return array<int, Prestamo>
     */
    private function obtenerPrestamosAbiertos(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $prestamos = Prestamo::query()
            ->with('detalles')
            ->whereIn('estado', [EstadoPrestamo::Pendiente->value, EstadoPrestamo::Activo->value])
            ->whereHas('detalles', fn (Builder $q): Builder => $q->whereIn('id_recurso', $ids))
            ->orderBy('fecha_hora_inicio')
            ->get();
        $porRecurso = [];

        foreach ($prestamos as $prestamo) {
            foreach ($prestamo->detalles as $detalle) {
                $porRecurso[$detalle->id_recurso] ??= $prestamo;
            }
        }

        return $porRecurso;
    }

    /**
     * Texto de disponibilidad de un recurso.
     *
     * @param  Recurso  $recurso  Recurso.
     * @param  Prestamo|null  $prestamo  Préstamo abierto que lo ocupa.
     * @return string
     */
    private function describirUno(Recurso $recurso, ?Prestamo $prestamo): string
    {
        if (! $recurso->activo) {
            return 'Inactivo · no aparece en el catálogo';
        }

        return match ($recurso->estado) {
            EstadoRecurso::Disponible => 'Disponible ahora',
            EstadoRecurso::Mantenimiento => 'Fuera de servicio por mantenimiento',
            EstadoRecurso::Prestado => $prestamo
                ? 'Regresa ' . mb_strtolower(FormatoFecha::dia($prestamo->fecha_hora_fin))
                    . ' a las ' . $prestamo->fecha_hora_fin->format('H:i')
                : 'En préstamo',
            EstadoRecurso::Bloqueado => $prestamo
                ? 'Reservado ' . FormatoFecha::periodo($prestamo->fecha_hora_inicio, $prestamo->fecha_hora_fin)
                : 'Reservado',
        };
    }

    /**
     * Posiciones porcentuales de los préstamos dentro del horario operativo de un día.
     *
     * @param  Collection<int, Prestamo>  $prestamos  Préstamos abiertos del recurso.
     * @param  Carbon  $fecha  Día.
     * @return list<array{izquierda: float, ancho: float, texto: string, pendiente: bool}>
     */
    private function calcularBloques(Collection $prestamos, Carbon $fecha): array
    {
        $apertura = $fecha->copy()->setTime(self::HORA_APERTURA, 0);
        $cierre = $fecha->copy()->setTime(self::HORA_CIERRE, 0);
        $minutosDia = (self::HORA_CIERRE - self::HORA_APERTURA) * 60;
        $bloques = [];

        foreach ($prestamos as $prestamo) {
            $inicio = $prestamo->fecha_hora_inicio->max($apertura);
            $fin = $prestamo->fecha_hora_fin->min($cierre);

            if ($fin->lte($inicio)) {
                continue;
            }

            $bloques[] = [
                'izquierda' => round($apertura->diffInMinutes($inicio) * 100 / $minutosDia, 2),
                'ancho' => round($inicio->diffInMinutes($fin) * 100 / $minutosDia, 2),
                'texto' => $prestamo->fecha_hora_inicio->format('H:i') . ' – '
                    . $prestamo->fecha_hora_fin->format('H:i'),
                'pendiente' => $prestamo->estado === EstadoPrestamo::Pendiente->value,
            ];
        }

        return $bloques;
    }
}
