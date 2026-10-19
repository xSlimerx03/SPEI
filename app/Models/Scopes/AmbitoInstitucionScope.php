<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Núcleo multi-tenant
 * @file         AmbitoInstitucionScope.php
 * @path         app/Models/Scopes/AmbitoInstitucionScope.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Global Scope que limita toda consulta Eloquent a la institución
 *               de la petición en curso (MD 5.3).
 *
 * @requisitos   RS-006
 *
 * @see          Manual de Desarrollo v1.2, sección 5.3; DT-008
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Support\ContextoInstitucional;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Filtra por id_institucion cuando existe un tenant activo.
 *
 * @package App\Models\Scopes
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AmbitoInstitucionScope implements Scope
{
    /**
     * Aplica el filtro del tenant a la consulta.
     *
     * @param  Builder<Model>  $builder  Consulta en construcción.
     * @param  Model  $model  Modelo consultado.
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        $contexto = app(ContextoInstitucional::class);

        // NOTE: sin tenant (superadmin o tareas de consola) no se filtra; las
        // rutas de tenant siempre pasan por TenantMiddleware y por las Policies.
        if (! $contexto->existe()) {
            return;
        }

        $builder->where($model->qualifyColumn('id_institucion'), $contexto->obtenerIdInstitucion());
    }
}
