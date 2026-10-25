<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.5: Entrega y Devolución
 * @file         TokenQr.php
 * @path         app/Models/TokenQr.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Código temporal de un solo uso para confirmar la entrega o la
 *               devolución. Solo se guarda el hash SHA-256 del código.
 *
 * @requisitos   RF-034, RF-036, RN-004, RS-006
 *
 * @see          docs/plan/BBB_MVP_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Token de confirmación de custodia.
 *
 * @property int $id_token
 * @property int $id_institucion
 * @property int $id_prestamo
 * @property string $tipo_token
 * @property string $token
 * @property Carbon $fecha_expiracion
 * @property bool $usado
 *
 * @package App\Models
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class TokenQr extends Model
{
    use PerteneceAInstitucionTrait;

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = null;

    /**
     * @var string
     */
    protected $table = 'tokens_qr';

    /**
     * @var string
     */
    protected $primaryKey = 'id_token';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_institucion',
        'id_prestamo',
        'tipo_token',
        'token',
        'fecha_expiracion',
        'usado',
    ];

    /**
     * El hash nunca se serializa hacia la API ni las vistas.
     *
     * @var list<string>
     */
    protected $hidden = ['token'];

    /**
     * Conversión de tipos de las columnas.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_expiracion' => 'datetime',
            'usado' => 'boolean',
            'id_prestamo' => 'integer',
        ];
    }
}
