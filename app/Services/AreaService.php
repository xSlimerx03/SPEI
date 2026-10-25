<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         AreaService.php
 * @path         app/Services/AreaService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Alta, edición, activación y eliminación de áreas respetando
 *               el límite del plan (RN-010).
 *
 * @requisitos   RF-009, RN-010, RNF-005, RS-006
 *
 * @see          docs/requisitos/BBB_CU006_GestionarAreas_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Area;
use App\Models\Institucion;
use Illuminate\Support\Facades\DB;

/**
 * Reglas de negocio de las áreas.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AreaService
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
     * Crea un área si el plan lo permite.
     *
     * @param  Institucion  $institucion  Tenant del actor.
     * @param  array{nombre_area: string, descripcion?: string|null}  $datos  Datos validados.
     * @return Area
     * @throws ReglaNegocioException Si se alcanzó el límite de áreas.
     */
    public function crear(Institucion $institucion, array $datos): Area
    {
        return DB::transaction(function () use ($institucion, $datos): Area {
            // El bloqueo de la institución serializa altas simultáneas frente al límite.
            $institucion = Institucion::query()->lockForUpdate()->findOrFail($institucion->id_institucion);
            $total = Area::query()->where('id_institucion', $institucion->id_institucion)->count();

            if ($total >= $institucion->limite_areas) {
                throw new ReglaNegocioException('El plan contratado no permite más áreas.');
            }

            $area = Area::query()->create([
                'id_institucion' => $institucion->id_institucion,
                'nombre_area' => $datos['nombre_area'],
                'descripcion' => $datos['descripcion'] ?? null,
                'activo' => true,
            ]);
            $area->refresh();
            $this->auditoriaService->registrar('CREAR', $area, null, $this->auditoriaService->capturar($area));

            return $area;
        });
    }

    /**
     * Cambia el nombre o la descripción del área.
     *
     * @param  Area  $area  Área a editar.
     * @param  array{nombre_area: string, descripcion?: string|null}  $datos  Datos validados.
     * @return Area
     */
    public function actualizar(Area $area, array $datos): Area
    {
        return DB::transaction(function () use ($area, $datos): Area {
            $anterior = $this->auditoriaService->capturar($area);
            $area->update([
                'nombre_area' => $datos['nombre_area'],
                'descripcion' => $datos['descripcion'] ?? null,
            ]);
            $posterior = $this->auditoriaService->capturar($area);
            $this->auditoriaService->registrar('ACTUALIZAR', $area, $anterior, $posterior);

            return $area;
        });
    }

    /**
     * Activa o desactiva el área; un área inactiva oculta sus recursos del catálogo.
     *
     * @param  Area  $area  Área a modificar.
     * @param  bool  $activo  Nuevo valor.
     * @return Area
     */
    public function cambiarActivo(Area $area, bool $activo): Area
    {
        return DB::transaction(function () use ($area, $activo): Area {
            $anterior = $this->auditoriaService->capturar($area);
            $area->update(['activo' => $activo]);
            $this->auditoriaService->registrar(
                'CAMBIAR_ESTADO',
                $area,
                $anterior,
                $this->auditoriaService->capturar($area),
            );

            return $area;
        });
    }

    /**
     * Elimina un área sin recursos; los usuarios asignados quedan sin área (MBD Tabla 27).
     *
     * @param  Area  $area  Área a eliminar.
     * @return void
     * @throws ReglaNegocioException Si el área tiene recursos.
     */
    public function eliminar(Area $area): void
    {
        if ($area->recursos()->withoutGlobalScopes()->exists()) {
            throw new ReglaNegocioException('No se puede eliminar un área con recursos.');
        }

        DB::transaction(function () use ($area): void {
            $this->auditoriaService->registrar('ELIMINAR', $area, $this->auditoriaService->capturar($area));
            $area->delete();
        });
    }
}
