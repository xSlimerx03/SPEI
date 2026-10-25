<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         RecursoService.php
 * @path         app/Services/RecursoService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Alta, edición, búsqueda por código de barras, cambio de
 *               estado (disponible / mantenimiento) y desactivación de
 *               recursos.
 *
 * @requisitos   RF-010, RF-011, RF-013, RF-014, RN-002, RN-010, RNF-005
 *
 * @see          docs/requisitos/BBB_CU007_RegistrarRecurso_v1.0.md; BBB_CU009_CambiarEstadoRecurso_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoRecurso;
use App\Exceptions\ReglaNegocioException;
use App\Models\Institucion;
use App\Models\Recurso;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Reglas de negocio del inventario de recursos.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class RecursoService
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
     * Registra un recurso si el plan lo permite.
     *
     * @param  Institucion  $institucion  Tenant del actor.
     * @param  array<string, mixed>  $datos  Datos validados.
     * @return Recurso
     * @throws ReglaNegocioException Si se alcanzó el límite de recursos.
     */
    public function crear(Institucion $institucion, array $datos): Recurso
    {
        return DB::transaction(function () use ($institucion, $datos): Recurso {
            $institucion = Institucion::query()->lockForUpdate()->findOrFail($institucion->id_institucion);
            $this->validarCupo($institucion, 1);

            $recurso = Recurso::query()->create([
                ...$datos,
                'id_institucion' => $institucion->id_institucion,
                'estado' => $datos['estado'] ?? EstadoRecurso::Disponible->value,
                'activo' => true,
            ]);
            $recurso->refresh();
            $this->auditoriaService->registrar('CREAR', $recurso, null, $this->auditoriaService->capturar($recurso));

            return $recurso;
        });
    }

    /**
     * Actualiza los datos descriptivos del recurso (el estado se cambia con cambiarEstado).
     *
     * @param  Recurso  $recurso  Recurso a editar.
     * @param  array<string, mixed>  $datos  Datos validados.
     * @return Recurso
     */
    public function actualizar(Recurso $recurso, array $datos): Recurso
    {
        return DB::transaction(function () use ($recurso, $datos): Recurso {
            $anterior = $this->auditoriaService->capturar($recurso);
            unset($datos['estado'], $datos['id_institucion']);
            $recurso->update($datos);
            $this->auditoriaService->registrar(
                'ACTUALIZAR',
                $recurso,
                $anterior,
                $this->auditoriaService->capturar($recurso),
            );

            return $recurso;
        });
    }

    /**
     * Cambia entre disponible y mantenimiento; los demás estados los asignan reservas y entregas.
     *
     * @param  Recurso  $recurso  Recurso a modificar.
     * @param  EstadoRecurso  $destino  Estado solicitado.
     * @return Recurso
     * @throws ReglaNegocioException Si la transición no está permitida.
     */
    public function cambiarEstado(Recurso $recurso, EstadoRecurso $destino): Recurso
    {
        return DB::transaction(function () use ($recurso, $destino): Recurso {
            $recurso = Recurso::query()->lockForUpdate()->findOrFail($recurso->id_recurso);

            if ($recurso->estado->estaEnUso()) {
                throw new ReglaNegocioException('No se puede enviar a mantenimiento un recurso en uso.');
            }

            if ($destino->estaEnUso() || $destino === $recurso->estado) {
                throw new ReglaNegocioException('No se permite el cambio de estado solicitado.');
            }

            $anterior = $this->auditoriaService->capturar($recurso);
            $recurso->update(['estado' => $destino]);
            $this->auditoriaService->registrar(
                'CAMBIAR_ESTADO',
                $recurso,
                $anterior,
                $this->auditoriaService->capturar($recurso),
            );

            return $recurso;
        });
    }

    /**
     * Activa o desactiva el recurso; uno inactivo no aparece en el catálogo.
     *
     * @param  Recurso  $recurso  Recurso a modificar.
     * @param  bool  $activo  Nuevo valor.
     * @return Recurso
     * @throws ReglaNegocioException Si se intenta desactivar un recurso en uso.
     */
    public function cambiarActivo(Recurso $recurso, bool $activo): Recurso
    {
        if (! $activo && $recurso->estado->estaEnUso()) {
            throw new ReglaNegocioException('No se puede desactivar un recurso en uso.');
        }

        return DB::transaction(function () use ($recurso, $activo): Recurso {
            $anterior = $this->auditoriaService->capturar($recurso);
            $recurso->update(['activo' => $activo]);
            $this->auditoriaService->registrar(
                'ACTUALIZAR',
                $recurso,
                $anterior,
                $this->auditoriaService->capturar($recurso),
            );

            return $recurso;
        });
    }

    /**
     * Busca un recurso del tenant por número de inventario o de serie (lector de código de barras).
     *
     * @param  string  $codigo  Código leído.
     * @return Recurso|null
     */
    public function buscarPorCodigo(string $codigo): ?Recurso
    {
        $codigo = trim($codigo);

        return Recurso::query()
            ->with('area')
            ->where(function (Builder $consulta) use ($codigo): void {
                $consulta->where('numero_inventario', $codigo)->orWhere('numero_serie', $codigo);
            })
            ->first();
    }

    /**
     * Verifica que la institución tenga cupo para N recursos más (RN-010).
     *
     * @param  Institucion  $institucion  Tenant bloqueado.
     * @param  int  $nuevos  Recursos por agregar.
     * @return void
     * @throws ReglaNegocioException Si se excede el límite.
     */
    public function validarCupo(Institucion $institucion, int $nuevos): void
    {
        $total = Recurso::withoutGlobalScopes()->where('id_institucion', $institucion->id_institucion)->count();

        if ($total + $nuevos > $institucion->limite_recursos) {
            throw new ReglaNegocioException('El plan contratado no permite más recursos.');
        }
    }
}
