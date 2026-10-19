<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Núcleo multi-tenant
 * @file         PerteneceAInstitucionTrait.php
 * @path         app/Models/Concerns/PerteneceAInstitucionTrait.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Comportamiento común de las entidades del tenant: registra el
 *               Global Scope, asigna id_institucion al crear y expone la
 *               relación con la institución.
 *
 * @requisitos   RS-006
 *
 * @see          Manual de Desarrollo v1.2, sección 5.3; DT-008
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Institucion;
use App\Models\Scopes\AmbitoInstitucionScope;
use App\Support\ContextoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aísla un modelo Eloquent por institución.
 *
 * @package App\Models\Concerns
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
trait PerteneceAInstitucionTrait
{
    /**
     * Registra el Global Scope y la asignación automática del tenant.
     *
     * @return void
     */
    public static function bootPerteneceAInstitucionTrait(): void
    {
        static::addGlobalScope(new AmbitoInstitucionScope);

        static::creating(function (Model $modelo): void {
            $contexto = app(ContextoInstitucional::class);

            if ($modelo->getAttribute('id_institucion') === null && $contexto->existe()) {
                $modelo->setAttribute('id_institucion', $contexto->obtenerIdInstitucion());
            }
        });
    }

    /**
     * Institución a la que pertenece el registro.
     *
     * @return BelongsTo<Institucion, $this>
     */
    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }
}
