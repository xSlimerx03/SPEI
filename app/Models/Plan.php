<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         Plan.php
 * @path         app/Models/Plan.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Modelo de la tabla global planes (precio, límites y duración).
 *
 * @requisitos   RF-001, RF-003, RF-007, RN-010
 *
 * @see          DT-024; database/migrations/2026_10_18_000001_create_planes_table.php
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models;

use App\Enums\ClavePlan;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Plan de suscripción ofrecido por la plataforma.
 *
 * @package App\Models
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = 'fecha_actualizacion';

    protected $table = 'planes';

    protected $primaryKey = 'id_plan';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'clave',
        'nombre',
        'monto_mensual',
        'limite_areas',
        'limite_recursos',
        'duracion_dias',
        'activo',
    ];

    /**
     * Instituciones suscritas a este plan.
     *
     * @return HasMany<Institucion, $this>
     */
    public function instituciones(): HasMany
    {
        return $this->hasMany(Institucion::class, 'plan_suscripcion', 'clave');
    }

    /**
     * Limita la consulta a los planes que se ofrecen en la contratación.
     *
     * @param  Builder<Plan>  $query  Consulta en construcción.
     * @return void
     */
    public function scopeActivos(Builder $query): void
    {
        $query->where('activo', true);
    }

    /**
     * Conversión de tipos de las columnas.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'clave' => ClavePlan::class,
            'monto_mensual' => 'decimal:2',
            'limite_areas' => 'integer',
            'limite_recursos' => 'integer',
            'duracion_dias' => 'integer',
            'activo' => 'boolean',
        ];
    }
}
