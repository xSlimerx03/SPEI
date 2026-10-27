<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         MensajeConversacion.php
 * @path         app/Models/MensajeConversacion.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Mensaje de la conversación previa a la entrega de una
 *               solicitud (MBD 4.13, tabla conversaciones).
 *
 * @requisitos   RF-033, RS-006
 *
 * @see          CU-022
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Mensaje entre solicitante y operadores.
 *
 * @property int $id_mensaje
 * @property int $id_institucion
 * @property int $id_prestamo
 * @property int $id_usuario_emisor
 * @property string $mensaje
 * @property bool $leido
 * @property Carbon $fecha_hora
 *
 * @package App\Models
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class MensajeConversacion extends Model
{
    use PerteneceAInstitucionTrait;

    public const CREATED_AT = 'fecha_hora';

    public const UPDATED_AT = null;

    /**
     * @var string
     */
    protected $table = 'conversaciones';

    /**
     * @var string
     */
    protected $primaryKey = 'id_mensaje';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_institucion',
        'id_prestamo',
        'id_usuario_emisor',
        'mensaje',
        'leido',
    ];

    /**
     * Usuario que escribió el mensaje.
     *
     * @return BelongsTo<Usuario, $this>
     */
    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_emisor', 'id_usuario');
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
            'id_prestamo' => 'integer',
            'id_usuario_emisor' => 'integer',
            'leido' => 'boolean',
            'fecha_hora' => 'datetime',
        ];
    }
}
