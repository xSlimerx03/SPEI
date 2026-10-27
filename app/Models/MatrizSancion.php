<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.6: Penalizaciones y Sanciones Automatizadas
 * @file         MatrizSancion.php
 * @path         app/Models/MatrizSancion.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Regla configurable de sanción de una institución por tipo de
 *               incidente (MBD 4.10).
 *
 * @requisitos   RF-041, RN-005, RS-006
 *
 * @see          CU-019
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoIncidente;
use App\Models\Concerns\PerteneceAInstitucionTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Regla de la matriz de sanciones.
 *
 * @property int $id_regla
 * @property int $id_institucion
 * @property TipoIncidente $tipo_incidente
 * @property int $marcas_por_incidente
 * @property int $umbral_bloqueo
 * @property int $dias_sancion
 * @property bool $activo
 *
 * @package App\Models
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class MatrizSancion extends Model
{
    use PerteneceAInstitucionTrait;

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = 'fecha_actualizacion';

    /**
     * @var string
     */
    protected $table = 'matriz_sanciones';

    /**
     * @var string
     */
    protected $primaryKey = 'id_regla';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_institucion',
        'tipo_incidente',
        'marcas_por_incidente',
        'umbral_bloqueo',
        'dias_sancion',
        'activo',
    ];

    /**
     * Conversión de tipos de las columnas.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id_institucion' => 'integer',
            'tipo_incidente' => TipoIncidente::class,
            'marcas_por_incidente' => 'integer',
            'umbral_bloqueo' => 'integer',
            'dias_sancion' => 'integer',
            'activo' => 'boolean',
        ];
    }
}
