<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         PlanService.php
 * @path         app/Services/PlanService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Modificación de planes por el superadmin: guarda el plan,
 *               propaga los límites a las instituciones suscritas y audita.
 *
 * @requisitos   RF-007, RN-010, RNF-005
 *
 * @see          docs/requisitos/BBB_CU003_ModificarPlanes_v1.0.md; DT-024
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Institucion;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

/**
 * Administración de los parámetros de los planes.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class PlanService
{
    public function __construct(
        private AuditoriaService $auditoriaService,
    ) {}

    /**
     * Actualiza el plan y copia sus límites a las instituciones que lo tienen contratado.
     *
     * @param  Plan  $plan  Plan a modificar.
     * @param  array<string, mixed>  $datos  Datos validados.
     * @return Plan
     */
    public function actualizar(Plan $plan, array $datos): Plan
    {
        return DB::transaction(function () use ($plan, $datos): Plan {
            $anterior = $this->auditoriaService->capturar($plan);
            $plan->update($datos);

            Institucion::query()
                ->where('plan_suscripcion', $plan->clave)
                ->update([
                    'limite_areas' => $plan->limite_areas,
                    'limite_recursos' => $plan->limite_recursos,
                ]);

            // NOTE: el plan es global; el log queda con id_institucion nulo (DT-024).
            $this->auditoriaService->registrar(
                'ACTUALIZAR',
                $plan,
                $anterior,
                $this->auditoriaService->capturar($plan),
            );

            return $plan;
        });
    }
}
