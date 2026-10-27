<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.8: Historias, Reportes e Inteligencia Operativa
 * @file         HistorialComprimido.php
 * @path         app/Models/HistorialComprimido.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Archivo comprimido del historial de préstamos de un mes y su
 *               eliminación programada (MBD 4.14, RN-012).
 *
 * @requisitos   RF-052, RN-012, RS-006
 *
 * @see          CU-023
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Histórico comprimido.
 *
 * @property int $id_historial
 * @property int $id_institucion
 * @property string $archivo_comprimido_url
 * @property Carbon $periodo_inicio
 * @property Carbon $periodo_fin
 * @property Carbon $fecha_eliminacion_programada
 * @property bool $eliminado
 *
 * @package App\Models
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class HistorialComprimido extends Model
{
    use PerteneceAInstitucionTrait;

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = null;

    /**
     * @var string
     */
    protected $table = 'historial_comprimido';

    /**
     * @var string
     */
    protected $primaryKey = 'id_historial';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_institucion',
        'archivo_comprimido_url',
        'periodo_inicio',
        'periodo_fin',
        'fecha_eliminacion_programada',
        'eliminado',
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
            'periodo_inicio' => 'date',
            'periodo_fin' => 'date',
            'fecha_eliminacion_programada' => 'date',
            'eliminado' => 'boolean',
        ];
    }
}
