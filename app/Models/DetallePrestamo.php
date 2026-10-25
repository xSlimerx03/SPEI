<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         DetallePrestamo.php
 * @path         app/Models/DetallePrestamo.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Recurso incluido en un préstamo con su condición de entrega,
 *               de devolución e incidencia registrada en mostrador.
 *
 * @requisitos   RF-024, RF-039, RS-006
 *
 * @see          docs/plan/BBB_MVP_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Detalle de una solicitud de préstamo.
 *
 * @package App\Models
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class DetallePrestamo extends Model
{
    use PerteneceAInstitucionTrait;

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = 'fecha_actualizacion';

    /**
     * @var string
     */
    protected $table = 'detalle_prestamo';

    /**
     * @var string
     */
    protected $primaryKey = 'id_detalle';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_institucion',
        'id_prestamo',
        'id_recurso',
        'condicion_entrega',
        'condicion_devolucion',
        'incidencia',
    ];

    /**
     * Recurso prestado.
     *
     * @return BelongsTo<Recurso, $this>
     */
    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recurso::class, 'id_recurso', 'id_recurso');
    }
}
