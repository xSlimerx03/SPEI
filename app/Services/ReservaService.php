<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         ReservaService.php
 * @path         app/Services/ReservaService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Reservas por periodo (MD 5.6): un recurso admite varias
 *               reservas que no se traslapen; solo pasa a "bloqueado" cuando
 *               empieza la ventana de recogida. Ante solicitudes concurrentes
 *               aplica RN-008 (docente > alumno → menos marcas → más antigua).
 *               Se ejecuta dentro de la transacción y el lock de PrestamoService.
 *
 * @requisitos   RF-025, RF-026, RF-027, RN-008
 *
 * @see          CU-011; DT-033
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoPrestamo;
use App\Enums\EstadoRecurso;
use App\Enums\Rol;
use App\Exceptions\ReglaNegocioException;
use App\Models\Prestamo;
use App\Models\Recurso;
use App\Models\Usuario;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Disponibilidad por periodo y arbitraje de prioridad.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ReservaService
{
    /**
     * Precedencia de RN-008 por rol.
     */
    private const RANGO_ROL = [
        'docente' => 2,
        'alumno' => 1,
    ];

    /**
     * Préstamos abiertos que ocupan alguno de los recursos en el periodo, bloqueados para escritura. Un préstamo
     * activo con devolución atrasada ocupa el recurso hasta que se devuelva.
     *
     * @param  list<int>  $idsRecursos  Recursos solicitados.
     * @param  CarbonInterface  $inicio  Inicio solicitado.
     * @param  CarbonInterface  $fin  Fin solicitado.
     * @return Collection<int, Prestamo>
     */
    public function obtenerConflictos(array $idsRecursos, CarbonInterface $inicio, CarbonInterface $fin): Collection
    {
        return Prestamo::query()
            ->with('usuario')
            ->whereIn('estado', [EstadoPrestamo::Pendiente->value, EstadoPrestamo::Activo->value])
            ->whereHas('detalles', fn (Builder $q): Builder => $q->whereIn('id_recurso', $idsRecursos))
            ->where(fn (Builder $q): Builder => $q
                ->where(fn (Builder $p): Builder => $p->where('fecha_hora_inicio', '<', $fin)
                    ->where('fecha_hora_fin', '>', $inicio))
                ->orWhere(fn (Builder $p): Builder => $p->where('estado', EstadoPrestamo::Activo->value)
                    ->where('fecha_hora_fin', '<', now())))
            ->orderBy('id_prestamo')
            ->lockForUpdate()
            ->get();
    }

    /**
     * RN-008 ante solicitudes concurrentes: el solicitante desplaza las reservas en conflicto solo si todas son
     * pendientes, se crearon dentro de la ventana de concurrencia y tienen menor prioridad. En otro caso se
     * respeta la reserva existente.
     *
     * @param  Usuario  $solicitante  Usuario que solicita.
     * @param  Collection<int, Prestamo>  $conflictos  Reservas que ocupan el periodo.
     * @return Collection<int, Prestamo> Reservas que se desplazan (vacía si no hay conflicto).
     *
     * @throws ReglaNegocioException Si algún conflicto se respeta.
     */
    public function arbitrar(Usuario $solicitante, Collection $conflictos): Collection
    {
        $limite = now()->subMinutes($this->obtenerVentanaMinutos());

        foreach ($conflictos as $conflicto) {
            $desplazable = $conflicto->estado === EstadoPrestamo::Pendiente->value
                && $conflicto->fecha_creacion->gte($limite)
                && $this->tienePrioridad($solicitante, $conflicto->usuario);

            if (! $desplazable) {
                throw new ReglaNegocioException('Uno de los recursos ya está reservado en ese horario.');
            }
        }

        return $conflictos;
    }

    /**
     * Indica si el solicitante tiene prioridad estricta sobre el titular de otra reserva. En empate gana la
     * solicitud más antigua, que siempre es la ya registrada.
     *
     * @param  Usuario  $solicitante  Usuario que solicita.
     * @param  Usuario  $titular  Titular de la reserva existente.
     * @return bool
     */
    public function tienePrioridad(Usuario $solicitante, Usuario $titular): bool
    {
        $rangoSolicitante = $this->obtenerRango($solicitante->rol);
        $rangoTitular = $this->obtenerRango($titular->rol);

        if ($rangoSolicitante !== $rangoTitular) {
            return $rangoSolicitante > $rangoTitular;
        }

        return (int) $solicitante->marcas_acumuladas < (int) $titular->marcas_acumuladas;
    }

    /**
     * Bloquea los recursos de una reserva cuya ventana de recogida ya empezó (MD Tabla 25, RF-026).
     *
     * @param  Prestamo  $prestamo  Reserva pendiente.
     * @return bool Verdadero si la ventana está abierta.
     */
    public function activarVentana(Prestamo $prestamo): bool
    {
        if ($prestamo->estado !== EstadoPrestamo::Pendiente->value || $prestamo->fecha_hora_inicio->gt(now())) {
            return false;
        }

        Recurso::query()
            ->whereIn('id_recurso', $prestamo->detalles()->pluck('id_recurso'))
            ->where('estado', EstadoRecurso::Disponible)
            ->update(['estado' => EstadoRecurso::Bloqueado]);

        return true;
    }

    /**
     * Tras devolver o liberar un recurso, lo bloquea si otra reserva ya está en su ventana de recogida.
     *
     * @param  int  $idRecurso  Recurso que quedó disponible.
     * @return void
     */
    public function sincronizarRecurso(int $idRecurso): void
    {
        $enVentana = Prestamo::query()
            ->where('estado', EstadoPrestamo::Pendiente->value)
            ->where('fecha_hora_inicio', '<=', now())
            ->whereHas('detalles', fn (Builder $q): Builder => $q->where('id_recurso', $idRecurso))
            ->exists();

        if ($enVentana) {
            Recurso::query()->whereKey($idRecurso)->where('estado', EstadoRecurso::Disponible)
                ->update(['estado' => EstadoRecurso::Bloqueado]);
        }
    }

    /**
     * Rango de RN-008; los roles que no solicitan préstamos no compiten.
     *
     * @param  Rol  $rol  Rol del usuario.
     * @return int
     */
    private function obtenerRango(Rol $rol): int
    {
        return self::RANGO_ROL[$rol->value] ?? 0;
    }

    /**
     * Minutos en que dos solicitudes se consideran concurrentes (DT-033), acotados a 1–60.
     *
     * @return int
     */
    private function obtenerVentanaMinutos(): int
    {
        return max(1, min(60, (int) config('spei.prestamos.ventana_prioridad_minutos')));
    }
}
