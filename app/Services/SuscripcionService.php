<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         SuscripcionService.php
 * @path         app/Services/SuscripcionService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Control del plan, la vigencia y el estado de la suscripción:
 *               cambio de plan con validación de uso, renovación, cambios
 *               manuales de estado y paso automático a solo lectura.
 *
 * @requisitos   RF-003, RF-004, RF-006, RN-010, RN-011, RN-013
 *
 * @see          docs/requisitos/BBB_CU002_AdministrarSuscripcion_v1.0.md; MBD Tabla 28
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoSuscripcion;
use App\Exceptions\ReglaNegocioException;
use App\Models\Area;
use App\Models\Institucion;
use App\Models\Plan;
use App\Models\Recurso;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Reglas de negocio de la suscripción institucional.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class SuscripcionService
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  AuditoriaService  $auditoriaService  Dependencia inyectada.
     */
    public function __construct(
        private AuditoriaService $auditoriaService,
    ) {}

    /**
     * Calcula el uso actual de áreas y recursos de la institución.
     *
     * @param  Institucion  $institucion  Tenant consultado.
     * @return array{areas: int, recursos: int}
     */
    public function obtenerUso(Institucion $institucion): array
    {
        return [
            'areas' => Area::withoutGlobalScopes()->where('id_institucion', $institucion->id_institucion)->count(),
            'recursos' => Recurso::withoutGlobalScopes()
                ->where('id_institucion', $institucion->id_institucion)
                ->count(),
        ];
    }

    /**
     * Cambia el plan y copia sus límites si el uso actual cabe en ellos (RN-010).
     *
     * @param  Institucion  $institucion  Tenant a modificar.
     * @param  string  $clave  Clave del plan destino.
     * @return Institucion
     * @throws ReglaNegocioException Si el plan no es válido o el uso supera sus límites.
     */
    public function cambiarPlan(Institucion $institucion, string $clave): Institucion
    {
        return DB::transaction(function () use ($institucion, $clave): Institucion {
            $plan = Plan::query()->activos()->where('clave', $clave)->first();

            if (! $plan instanceof Plan) {
                throw new ReglaNegocioException('Seleccione un plan válido.');
            }

            $institucion = $this->bloquear($institucion);
            $uso = $this->obtenerUso($institucion);

            if ($uso['areas'] > $plan->limite_areas || $uso['recursos'] > $plan->limite_recursos) {
                throw new ReglaNegocioException('El plan no admite las áreas o recursos ya registrados.');
            }

            $anterior = $this->auditoriaService->capturar($institucion);
            $institucion->update([
                'plan_suscripcion' => $plan->clave,
                'limite_areas' => $plan->limite_areas,
                'limite_recursos' => $plan->limite_recursos,
            ]);
            $this->auditoriaService->registrar(
                'CAMBIAR_PLAN',
                $institucion,
                $anterior,
                $this->auditoriaService->capturar($institucion),
            );

            return $institucion;
        });
    }

    /**
     * Renueva la vigencia: máx(fecha de corte, hoy) + duración del plan; reactiva si estaba en solo lectura.
     *
     * @param  Institucion  $institucion  Tenant a renovar.
     * @return Institucion
     * @throws ReglaNegocioException Si la suscripción está cancelada.
     */
    public function renovar(Institucion $institucion): Institucion
    {
        return DB::transaction(function () use ($institucion): Institucion {
            $institucion = $this->bloquear($institucion);

            if ($institucion->estado_suscripcion === EstadoSuscripcion::Cancelada) {
                throw new ReglaNegocioException('No se permite el cambio de estado solicitado.');
            }

            $anterior = $this->auditoriaService->capturar($institucion);
            $base = $institucion->fecha_corte->greaterThan(today()) ? $institucion->fecha_corte : today();
            $duracion = $institucion->plan instanceof Plan ? $institucion->plan->duracion_dias : 30;
            $institucion->fecha_corte = $base->copy()->addDays($duracion);

            if ($institucion->estado_suscripcion === EstadoSuscripcion::SoloLectura) {
                $institucion->estado_suscripcion = EstadoSuscripcion::Activa;
            }

            $institucion->save();
            $this->auditoriaService->registrar(
                'RENOVAR',
                $institucion,
                $anterior,
                $this->auditoriaService->capturar($institucion),
            );

            return $institucion;
        });
    }

    /**
     * Cambia manualmente el estado según la máquina de estados de MBD Tabla 28.
     *
     * @param  Institucion  $institucion  Tenant a modificar.
     * @param  EstadoSuscripcion  $destino  Estado solicitado.
     * @return Institucion
     * @throws ReglaNegocioException Si la transición no está permitida.
     */
    public function cambiarEstado(Institucion $institucion, EstadoSuscripcion $destino): Institucion
    {
        return DB::transaction(function () use ($institucion, $destino): Institucion {
            $institucion = $this->bloquear($institucion);

            if (! $institucion->estado_suscripcion->puedeCambiarA($destino)) {
                throw new ReglaNegocioException('No se permite el cambio de estado solicitado.');
            }

            $anterior = $this->auditoriaService->capturar($institucion);
            $institucion->update(['estado_suscripcion' => $destino]);

            if (! $destino->permiteAcceso()) {
                $this->revocarTokens($institucion);
            }

            $this->auditoriaService->registrar(
                'CAMBIAR_ESTADO',
                $institucion,
                $anterior,
                $this->auditoriaService->capturar($institucion),
            );

            return $institucion;
        });
    }

    /**
     * Pasa a solo lectura las instituciones activas cuya fecha de corte ya pasó (RN-013).
     *
     * @return int Número de instituciones afectadas.
     */
    public function aplicarVencimientos(): int
    {
        $vencidas = Institucion::query()
            ->where('estado_suscripcion', EstadoSuscripcion::Activa)
            ->whereDate('fecha_corte', '<', today())
            ->get();

        foreach ($vencidas as $institucion) {
            DB::transaction(function () use ($institucion): void {
                $anterior = $this->auditoriaService->capturar($institucion);
                $institucion->update(['estado_suscripcion' => EstadoSuscripcion::SoloLectura]);
                $this->auditoriaService->registrar(
                    'CAMBIAR_ESTADO',
                    $institucion,
                    $anterior,
                    $this->auditoriaService->capturar($institucion),
                );
            });
        }

        return $vencidas->count();
    }

    /**
     * Relee la institución con bloqueo de fila para serializar cambios concurrentes.
     *
     * @param  Institucion  $institucion  Tenant a bloquear.
     * @return Institucion
     */
    private function bloquear(Institucion $institucion): Institucion
    {
        return Institucion::query()->lockForUpdate()->findOrFail($institucion->id_institucion);
    }

    /**
     * Revoca los tokens de API de los usuarios de la institución.
     *
     * @param  Institucion  $institucion  Tenant suspendido o cancelado.
     * @return void
     */
    private function revocarTokens(Institucion $institucion): void
    {
        $idsUsuarios = Usuario::withoutGlobalScopes()
            ->where('id_institucion', $institucion->id_institucion)
            ->pluck('id_usuario');

        PersonalAccessToken::query()
            ->where('tokenable_type', (new Usuario)->getMorphClass())
            ->whereIn('tokenable_id', $idsUsuarios)
            ->delete();
    }
}
