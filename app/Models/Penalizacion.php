<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.6: Penalizaciones y Sanciones Automatizadas
 * @file         Penalizacion.php
 * @path         app/Models/Penalizacion.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Sanción aplicada a un usuario final (MBD 4.9). Los triggers de
 *               la tabla actualizan usuarios.marcas_acumuladas al registrarla o
 *               exentarla.
 *
 * @requisitos   RF-042, RF-043, RF-044, RF-056, RS-006
 *
 * @see          CU-019, CU-020
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoIncidente;
use App\Models\Concerns\PerteneceAInstitucionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Penalización aplicada.
 *
 * @property int $id_penalizacion
 * @property int $id_institucion
 * @property int $id_usuario
 * @property int|null $id_prestamo
 * @property TipoIncidente $tipo_penalizacion
 * @property string $motivo
 * @property int $marcas_aplicadas
 * @property Carbon|null $fecha_inicio_sancion
 * @property Carbon|null $fecha_fin_sancion
 * @property bool $exentada
 * @property string|null $motivo_exencion
 * @property Carbon $fecha_creacion
 *
 * @package App\Models
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class Penalizacion extends Model
{
    use PerteneceAInstitucionTrait;

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = 'fecha_actualizacion';

    /**
     * @var string
     */
    protected $table = 'penalizaciones';

    /**
     * @var string
     */
    protected $primaryKey = 'id_penalizacion';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_institucion',
        'id_usuario',
        'id_prestamo',
        'tipo_penalizacion',
        'motivo',
        'marcas_aplicadas',
        'fecha_inicio_sancion',
        'fecha_fin_sancion',
        'exentada',
        'motivo_exencion',
    ];

    /**
     * Usuario sancionado.
     *
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Préstamo que originó la sanción.
     *
     * @return BelongsTo<Prestamo, $this>
     */
    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class, 'id_prestamo', 'id_prestamo');
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
            'id_usuario' => 'integer',
            'id_prestamo' => 'integer',
            'tipo_penalizacion' => TipoIncidente::class,
            'marcas_aplicadas' => 'integer',
            'fecha_inicio_sancion' => 'date',
            'fecha_fin_sancion' => 'date',
            'exentada' => 'boolean',
            'fecha_creacion' => 'datetime',
        ];
    }
}
