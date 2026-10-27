<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         OnboardingService.php
 * @path         app/Services/OnboardingService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Contratación del servicio: crea en una sola transacción la
 *               institución con los límites y la vigencia del plan y su
 *               administrador institucional.
 *
 * @requisitos   RF-001, RF-002, RN-001, RN-010
 *
 * @see          docs/requisitos/BBB_CU001_ContratarSuscripcion_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoSuscripcion;
use App\Enums\Rol;
use App\Exceptions\ReglaNegocioException;
use App\Models\Institucion;
use App\Models\Plan;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

/**
 * Alta de una institución nueva y de su administrador.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class OnboardingService
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
     * Contrata el servicio y devuelve el administrador institucional creado.
     *
     * @param array{nombre_institucion: string, dominio_correo: string, plan: string,
     *     nombre_completo: string, correo_institucional: string, contrasena: string} $datos Datos validados.
     * @return Usuario
     * @throws ReglaNegocioException Si el plan no existe o está inactivo.
     */
    public function contratar(array $datos): Usuario
    {
        return DB::transaction(function () use ($datos): Usuario {
            $plan = Plan::query()->activos()->where('clave', $datos['plan'])->first();

            if (! $plan instanceof Plan) {
                throw new ReglaNegocioException('Seleccione un plan válido.');
            }

            $institucion = Institucion::query()->create([
                'nombre_institucion' => $datos['nombre_institucion'],
                'dominio_correo' => $datos['dominio_correo'],
                'plan_suscripcion' => $plan->clave,
                'fecha_inicio' => today(),
                'fecha_corte' => today()->addDays($plan->duracion_dias),
                'estado_suscripcion' => EstadoSuscripcion::Activa,
                'limite_areas' => $plan->limite_areas,
                'limite_recursos' => $plan->limite_recursos,
            ]);

            $administrador = Usuario::query()->create([
                'id_institucion' => $institucion->id_institucion,
                'nombre_completo' => $datos['nombre_completo'],
                'correo_institucional' => $datos['correo_institucional'],
                'contrasena_hash' => $datos['contrasena'],
                'rol' => Rol::AdminInstitucional,
                'activo' => true,
                'fecha_registro' => now(),
            ]);

            $institucion->refresh();
            $administrador->refresh();
            $this->auditoriaService->registrar(
                'CREAR',
                $institucion,
                null,
                $this->auditoriaService->capturar($institucion),
                $administrador,
            );
            $this->auditoriaService->registrar(
                'CREAR',
                $administrador,
                null,
                $this->auditoriaService->capturar($administrador),
                $administrador,
            );

            return $administrador;
        });
    }
}
