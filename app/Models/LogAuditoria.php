<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.8: Historias, Reportes e Inteligencia Operativa
 * @file         LogAuditoria.php
 * @path         app/Models/LogAuditoria.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Modelo de la bitácora de auditoría. Es de solo inserción: los
 *               triggers de la BD rechazan UPDATE y DELETE (DT-010).
 *
 * @requisitos   RF-052, RNF-005, RS-006
 *
 * @see          Manual de Base de Datos v1.2, sección 4.11; DT-010, DT-024
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceAInstitucionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro inalterable de una transacción crítica.
 *
 * @package App\Models
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class LogAuditoria extends Model
{
    use PerteneceAInstitucionTrait;

    public const CREATED_AT = 'fecha_hora';

    public const UPDATED_AT = null;

    protected $table = 'logs_auditoria';

    protected $primaryKey = 'id_log';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_usuario',
        'id_institucion',
        'accion',
        'tabla_afectada',
        'registro_id',
        'detalle_anterior',
        'detalle_nuevo',
        'direccion_ip',
    ];

    /**
     * Usuario que realizó la acción; null si la ejecutó el sistema.
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
            'id_usuario' => 'integer',
            'id_institucion' => 'integer',
            'registro_id' => 'integer',
            'detalle_anterior' => 'array',
            'detalle_nuevo' => 'array',
            'fecha_hora' => 'datetime',
        ];
    }
}
