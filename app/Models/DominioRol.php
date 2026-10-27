<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         DominioRol.php
 * @path         app/Models/DominioRol.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Regla de una institución que asigna alumno o docente según el
 *               dominio o subdominio del correo institucional.
 *
 * @requisitos   RF-016, RF-017, RN-001, RS-006
 *
 * @see          DT-034
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models;

use App\Enums\Rol;
use App\Models\Concerns\PerteneceAInstitucionTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Regla de mapeo dominio → rol.
 *
 * @property int $id_dominio_rol
 * @property int $id_institucion
 * @property string $dominio
 * @property Rol $rol
 *
 * @package App\Models
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class DominioRol extends Model
{
    use PerteneceAInstitucionTrait;

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = 'fecha_actualizacion';

    /**
     * @var string
     */
    protected $table = 'dominios_rol';

    /**
     * @var string
     */
    protected $primaryKey = 'id_dominio_rol';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_institucion',
        'dominio',
        'rol',
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
            'rol' => Rol::class,
        ];
    }
}
