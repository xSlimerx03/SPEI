<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.7: Notificaciones y Recordatorios
 * @file         Notificacion.php
 * @path         app/Models/Notificacion.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Modelo de las notificaciones dentro del sistema y por correo.
 *
 * @requisitos   RF-005, RF-046, RF-047, RS-006
 *
 * @see          Manual de Base de Datos v1.2, sección 4.12; DT-022, DT-024
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoNotificacion;
use App\Models\Concerns\PerteneceAInstitucionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aviso dirigido a un usuario.
 *
 * @package App\Models
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class Notificacion extends Model
{
    use PerteneceAInstitucionTrait;

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = null;

    protected $table = 'notificaciones';

    protected $primaryKey = 'id_notificacion';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_institucion',
        'id_usuario',
        'tipo_notificacion',
        'titulo',
        'mensaje',
        'leida',
        'fecha_envio',
    ];

    /**
     * Usuario destinatario.
     *
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
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
            'tipo_notificacion' => TipoNotificacion::class,
            'leida' => 'boolean',
            'fecha_envio' => 'datetime',
        ];
    }
}
