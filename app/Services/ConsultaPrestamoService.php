<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         ConsultaPrestamoService.php
 * @path         app/Services/ConsultaPrestamoService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Ámbito de consulta de préstamos por rol (propietario, área
 *               asignada o institución completa) y filtros de folio,
 *               solicitante, estado, área y fecha. Lo comparten el listado,
 *               el dashboard, el mostrador, los reportes y la exportación.
 *
 * @requisitos   RF-028, RF-051, RF-053, RS-006
 *
 * @see          app/Policies/PrestamoPolicy.php; docs/plan/BBB_MVP_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoPrestamo;
use App\Enums\Rol;
use App\Models\Prestamo;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;

/**
 * Consultas de préstamos limitadas al ámbito del usuario.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ConsultaPrestamoService
{
    /**
     * Préstamos que el usuario puede ver (mismo criterio que PrestamoPolicy).
     *
     * @param  Usuario  $actor  Usuario autenticado.
     * @return Builder<Prestamo>
     */
    public function paraUsuario(Usuario $actor): Builder
    {
        $consulta = Prestamo::query()->where('id_institucion', $actor->id_institucion);

        if ($actor->rol->esUsuarioFinal()) {
            return $consulta->where('id_usuario', $actor->id_usuario);
        }

        if ($actor->id_area_asignada !== null) {
            $area = $actor->id_area_asignada;

            // Solo préstamos cuyos recursos pertenecen todos al área asignada.
            return $consulta
                ->whereHas('detalles.recurso', fn (Builder $q): Builder => $q->where('id_area', $area))
                ->whereDoesntHave('detalles.recurso', fn (Builder $q): Builder => $q->where('id_area', '!=', $area));
        }

        if ($actor->rol === Rol::Asistente) {
            return $consulta->whereRaw('1 = 0');
        }

        return $consulta;
    }

    /**
     * Aplica los filtros validados del listado.
     *
     * @param  Builder<Prestamo>  $consulta  Consulta del ámbito.
     * @param  array<string, mixed>  $filtros  estado, texto, id_area y fecha opcionales.
     * @return Builder<Prestamo>
     */
    public function filtrar(Builder $consulta, array $filtros): Builder
    {
        return $consulta
            ->when($filtros['estado'] ?? null, fn (Builder $q, string $estado): Builder => $q->where('estado', $estado))
            ->when($filtros['texto'] ?? null, fn (Builder $q, string $texto): Builder => $this->filtrarTexto($q, $texto))
            ->when($filtros['id_area'] ?? null, fn (Builder $q, int|string $area): Builder => $q->whereHas(
                'detalles.recurso',
                fn (Builder $r): Builder => $r->where('id_area', (int) $area),
            ))
            ->when(
                $filtros['fecha'] ?? null,
                fn (Builder $q, string $fecha): Builder => $q->whereDate('fecha_hora_inicio', $fecha),
            );
    }

    /**
     * Total de préstamos por estado dentro del ámbito y filtros (sin el filtro de estado).
     *
     * @param  Builder<Prestamo>  $consulta  Consulta filtrada sin estado.
     * @return array<string, int> Clave = valor del estado, más "todos".
     */
    public function contarPorEstado(Builder $consulta): array
    {
        $porEstado = (clone $consulta)
            ->toBase()
            ->selectRaw('estado, COUNT(*) AS total')
            ->groupBy('estado')
            ->pluck('total', 'estado');
        $conteo = ['todos' => (int) $porEstado->sum()];

        foreach (EstadoPrestamo::cases() as $estado) {
            $conteo[$estado->value] = (int) ($porEstado[$estado->value] ?? 0);
        }

        return $conteo;
    }

    /**
     * Busca por folio (PR-AAAA-NNNN o número) o por nombre y correo del solicitante.
     *
     * @param  Builder<Prestamo>  $consulta  Consulta en construcción.
     * @param  string  $texto  Texto capturado.
     * @return Builder<Prestamo>
     */
    private function filtrarTexto(Builder $consulta, string $texto): Builder
    {
        $texto = trim($texto);
        $folio = preg_match('/^(?:PR-\d{4}-)?0*(\d{1,10})$/i', $texto, $coincidencia) === 1
            ? (int) $coincidencia[1]
            : null;
        $patron = '%' . addcslashes($texto, '%_\\') . '%';

        return $consulta->where(function (Builder $q) use ($folio, $patron): void {
            if ($folio !== null) {
                $q->orWhere('id_prestamo', $folio);
            }

            $q->orWhereHas('usuario', fn (Builder $u): Builder => $u->where('nombre_completo', 'like', $patron)
                ->orWhere('correo_institucional', 'like', $patron));
        });
    }
}
