<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         Institucion.php
 * @path         app/Models/Institucion.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Modelo del tenant: plan, vigencia, límites y estado de la
 *               suscripción de una institución.
 *
 * @requisitos   RF-001, RF-003, RF-004, RF-006, RN-010, RN-011, RN-013, RS-006
 *
 * @see          Manual de Base de Datos v1.2, sección 4.2; DT-024
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models;

use App\Enums\ClavePlan;
use App\Enums\EstadoSuscripcion;
use Database\Factories\InstitucionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Institución educativa suscrita al SPEI (tenant).
 *
 * @package App\Models
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class Institucion extends Model
{
    /** @use HasFactory<InstitucionFactory> */
    use HasFactory;

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = 'fecha_actualizacion';

    protected $table = 'instituciones';

    protected $primaryKey = 'id_institucion';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre_institucion',
        'dominio_correo',
        'plan_suscripcion',
        'fecha_inicio',
        'fecha_corte',
        'estado_suscripcion',
        'limite_areas',
        'limite_recursos',
    ];

    /**
     * Plan contratado.
     *
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_suscripcion', 'clave');
    }

    /**
     * Áreas de la institución.
     *
     * @return HasMany<Area, $this>
     */
    public function areas(): HasMany
    {
        return $this->hasMany(Area::class, 'id_institucion', 'id_institucion');
    }

    /**
     * Recursos de la institución.
     *
     * @return HasMany<Recurso, $this>
     */
    public function recursos(): HasMany
    {
        return $this->hasMany(Recurso::class, 'id_institucion', 'id_institucion');
    }

    /**
     * Usuarios de la institución.
     *
     * @return HasMany<Usuario, $this>
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'id_institucion', 'id_institucion');
    }

    /**
     * Calcula los días que faltan para la fecha de corte; negativo si ya venció.
     *
     * @return int
     */
    public function obtenerDiasParaCorte(): int
    {
        return (int) today()->diffInDays($this->fecha_corte, false);
    }

    /**
     * Conversión de tipos de las columnas.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'plan_suscripcion' => ClavePlan::class,
            'estado_suscripcion' => EstadoSuscripcion::class,
            'fecha_inicio' => 'date',
            'fecha_corte' => 'date',
            'limite_areas' => 'integer',
            'limite_recursos' => 'integer',
        ];
    }
}
