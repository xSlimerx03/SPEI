<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         Prestamo.php
 * @path         app/Models/Prestamo.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Solicitud con periodo, estado y trazabilidad de custodia.
 *               Expone el folio legible y el subestado que muestran las
 *               vistas (DT-028).
 *
 * @requisitos   RF-022, RF-028, RF-051, RS-006
 *
 * @see          docs/plan/BBB_MVP_v1.0.md; Manual de Base de Datos v1.2, sección 4.6
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoPrestamo;
use App\Models\Concerns\PerteneceAInstitucionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Préstamo de recursos de una institución.
 *
 * @package App\Models
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class Prestamo extends Model
{
    use PerteneceAInstitucionTrait;

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = 'fecha_actualizacion';

    protected $table = 'prestamos';

    protected $primaryKey = 'id_prestamo';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_institucion',
        'id_usuario',
        'fecha_hora_inicio',
        'fecha_hora_fin',
        'estado',
        'motivo',
        'fecha_hora_entrega_real',
        'fecha_hora_devolucion_real',
        'motivo_cancelacion',
    ];

    /**
     * Usuario solicitante.
     *
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Recursos incluidos en la solicitud.
     *
     * @return HasMany<DetallePrestamo, $this>
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePrestamo::class, 'id_prestamo', 'id_prestamo');
    }

    /**
     * Folio legible PR-AAAA-NNNN a partir del año de alta y el identificador.
     *
     * @return string
     */
    public function obtenerFolio(): string
    {
        $anio = $this->fecha_creacion->format('Y');

        return sprintf('PR-%s-%04d', $anio, $this->id_prestamo);
    }

    /**
     * Estado tipado de la solicitud.
     *
     * @return EstadoPrestamo
     */
    public function obtenerEstado(): EstadoPrestamo
    {
        return EstadoPrestamo::from((string) $this->estado);
    }

    /**
     * Texto breve bajo el badge: contexto operativo del estado.
     *
     * @return string
     */
    public function obtenerSubestado(): string
    {
        return match ($this->obtenerEstado()) {
            EstadoPrestamo::Pendiente => $this->fecha_hora_inicio->isFuture()
                ? 'Reservado · por recoger'
                : 'En tolerancia',
            EstadoPrestamo::Activo => $this->fecha_hora_fin->isPast() ? 'Devolución atrasada' : 'En custodia',
            EstadoPrestamo::Devuelto => $this->tieneIncidencia() ? 'Con incidencia' : 'Sin incidencias',
            EstadoPrestamo::Vencido => 'No se recogió a tiempo',
            EstadoPrestamo::Cancelado => 'Cancelado',
        };
    }

    /**
     * Indica si algún recurso se devolvió con incidencia o en mala condición.
     *
     * @return bool
     */
    public function tieneIncidencia(): bool
    {
        return $this->detalles->contains(
            fn (DetallePrestamo $detalle): bool => $detalle->incidencia !== null
                || in_array($detalle->condicion_devolucion, ['malo', 'danado'], true),
        );
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
            'fecha_hora_inicio' => 'datetime',
            'fecha_hora_fin' => 'datetime',
            'fecha_hora_entrega_real' => 'datetime',
            'fecha_hora_devolucion_real' => 'datetime',
            'fecha_creacion' => 'datetime',
        ];
    }
}
