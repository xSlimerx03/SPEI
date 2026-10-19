<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         Recurso.php
 * @path         app/Models/Recurso.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Modelo del inventario de recursos prestables.
 *
 * @requisitos   RF-010, RF-011, RF-012, RF-013, RF-014, RF-015, RN-002, RS-006
 *
 * @see          Manual de Base de Datos v1.2, sección 4.5; DT-008
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoRecurso;
use App\Enums\TipoRecurso;
use App\Models\Concerns\PerteneceAInstitucionTrait;
use Database\Factories\RecursoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Equipo o espacio que una institución presta.
 *
 * @package App\Models
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class Recurso extends Model
{
    /** @use HasFactory<RecursoFactory> */
    use HasFactory;

    use PerteneceAInstitucionTrait;

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = null;

    protected $table = 'recursos';

    protected $primaryKey = 'id_recurso';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_institucion',
        'id_area',
        'nombre_recurso',
        'numero_serie',
        'numero_inventario',
        'tipo_recurso',
        'marca',
        'anio_adquisicion',
        'estado',
        'activo',
    ];

    /**
     * Área que resguarda el recurso.
     *
     * @return BelongsTo<Area, $this>
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }

    /**
     * Limita la consulta a lo que ve un usuario final: disponible, activo y de área activa (RN-002).
     *
     * @param  Builder<Recurso>  $query  Consulta en construcción.
     * @return void
     */
    public function scopeVisiblesEnCatalogo(Builder $query): void
    {
        $query->where('estado', EstadoRecurso::Disponible)
            ->where('activo', true)
            ->whereHas('area', fn (Builder $area) => $area->where('activo', true));
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
            'id_area' => 'integer',
            'tipo_recurso' => TipoRecurso::class,
            'estado' => EstadoRecurso::class,
            'anio_adquisicion' => 'integer',
            'activo' => 'boolean',
        ];
    }
}
