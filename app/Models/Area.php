<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         Area.php
 * @path         app/Models/Area.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Modelo de las áreas que resguardan recursos prestables.
 *
 * @requisitos   RF-009, RF-015, RN-010, RS-006
 *
 * @see          Manual de Base de Datos v1.2, sección 4.3
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucionTrait;
use Database\Factories\AreaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Área o departamento de una institución.
 *
 * @package App\Models
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class Area extends Model
{
    /** @use HasFactory<AreaFactory> */
    use HasFactory;

    use PerteneceAInstitucionTrait;

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = null;

    protected $table = 'areas';

    protected $primaryKey = 'id_area';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_institucion',
        'nombre_area',
        'descripcion',
        'activo',
    ];

    /**
     * Recursos resguardados por el área.
     *
     * @return HasMany<Recurso, $this>
     */
    public function recursos(): HasMany
    {
        return $this->hasMany(Recurso::class, 'id_area', 'id_area');
    }

    /**
     * Limita la consulta a las áreas activas.
     *
     * @param  Builder<Area>  $query  Consulta en construcción.
     * @return void
     */
    public function scopeActivas(Builder $query): void
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
            'id_institucion' => 'integer',
            'activo' => 'boolean',
        ];
    }
}
