<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         Usuario.php
 * @path         app/Models/Usuario.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Modelo autenticable del SPEI. La contraseña vive en
 *               contrasena_hash y no hay "recordar sesión" (DT-025).
 *
 * @requisitos   RF-002, RF-008, RN-001, RNF-001, RS-006
 *
 * @see          Manual de Base de Datos v1.2, sección 4.4; DT-011, DT-012, DT-025
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models;

use App\Enums\Rol;
use App\Models\Concerns\PerteneceAInstitucionTrait;
use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Usuario del sistema (superadmin, administradores y usuarios finales).
 *
 * @package App\Models
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class Usuario extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UsuarioFactory> */
    use HasFactory;

    use PerteneceAInstitucionTrait;

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = null;

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    protected $authPasswordName = 'contrasena_hash';

    // NOTE: sin columna remember_token en MBD 4.4; un nombre vacío desactiva "recordar sesión".
    protected $rememberTokenName = '';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_institucion',
        'nombre_completo',
        'correo_institucional',
        'contrasena_hash',
        'rol',
        'id_area_asignada',
        'activo',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'contrasena_hash',
    ];

    /**
     * Área que el usuario tiene a su cargo, si la hay.
     *
     * @return BelongsTo<Area, $this>
     */
    public function areaAsignada(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'id_area_asignada', 'id_area');
    }

    /**
     * Notificaciones recibidas por el usuario.
     *
     * @return HasMany<Notificacion, $this>
     */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Indica si el usuario tiene alguno de los roles indicados.
     *
     * @param  Rol  ...$roles  Roles aceptados.
     * @return bool
     */
    public function tieneRol(Rol ...$roles): bool
    {
        return in_array($this->rol, $roles, true);
    }

    /**
     * Indica si es el superadmin de la plataforma (sin institución, DT-011).
     *
     * @return bool
     */
    public function esSuperadmin(): bool
    {
        return $this->rol === Rol::Superadmin;
    }

    /**
     * Indica si es un administrador del sistema responsable de una sola área.
     *
     * @return bool
     */
    public function esResponsableDeArea(): bool
    {
        return $this->rol === Rol::AdminSistema && $this->id_area_asignada !== null;
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
            'id_area_asignada' => 'integer',
            'contrasena_hash' => 'hashed',
            'rol' => Rol::class,
            'marcas_acumuladas' => 'integer',
            'bloqueado' => 'boolean',
            'fecha_bloqueo' => 'datetime',
            'fecha_fin_bloqueo' => 'datetime',
            'activo' => 'boolean',
        ];
    }
}
