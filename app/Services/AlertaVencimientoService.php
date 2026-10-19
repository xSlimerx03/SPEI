<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         AlertaVencimientoService.php
 * @path         app/Services/AlertaVencimientoService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Emite las alertas de vencimiento de la suscripción a 7, 3 y
 *               1 días del corte: notificación en el sistema y correo en cola,
 *               sin duplicados en el mismo día.
 *
 * @requisitos   RF-005, RNF-006
 *
 * @see          docs/requisitos/BBB_CU002_AdministrarSuscripcion_v1.0.md (paso 7, A2)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoSuscripcion;
use App\Enums\Rol;
use App\Enums\TipoNotificacion;
use App\Mail\AlertaVencimientoMail;
use App\Models\Institucion;
use App\Models\Notificacion;
use App\Models\Usuario;
use Illuminate\Support\Facades\Mail;

/**
 * Avisos previos al corte de la suscripción.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AlertaVencimientoService
{
    public const TITULO = 'Vencimiento de la suscripción';

    /**
     * Genera las alertas del día y devuelve cuántas se crearon.
     *
     * @return int
     */
    public function emitirAlertas(): int
    {
        $creadas = 0;

        foreach ((array) config('spei.suscripcion.dias_alerta') as $dias) {
            $instituciones = Institucion::query()
                ->where('estado_suscripcion', EstadoSuscripcion::Activa)
                ->whereDate('fecha_corte', today()->addDays((int) $dias))
                ->get();

            foreach ($instituciones as $institucion) {
                $creadas += $this->alertarAdministradores($institucion, (int) $dias);
            }
        }

        return $creadas;
    }

    /**
     * Notifica a cada administrador institucional activo que no tenga ya la alerta del día.
     *
     * @param  Institucion  $institucion  Tenant por vencer.
     * @param  int  $dias  Días que faltan para el corte.
     * @return int
     */
    private function alertarAdministradores(Institucion $institucion, int $dias): int
    {
        $administradores = Usuario::withoutGlobalScopes()
            ->where('id_institucion', $institucion->id_institucion)
            ->where('rol', Rol::AdminInstitucional)
            ->where('activo', true)
            ->get();
        $creadas = 0;

        foreach ($administradores as $administrador) {
            if ($this->existeAlertaDelDia($administrador)) {
                continue;
            }

            $mensaje = sprintf(
                'La suscripción de %s vence en %d %s (%s). Solicite la renovación para evitar el modo solo lectura.',
                $institucion->nombre_institucion,
                $dias,
                $dias === 1 ? 'día' : 'días',
                $institucion->fecha_corte->format('d/m/Y'),
            );

            Notificacion::query()->create([
                'id_institucion' => $institucion->id_institucion,
                'id_usuario' => $administrador->id_usuario,
                'tipo_notificacion' => TipoNotificacion::Ambas,
                'titulo' => self::TITULO,
                'mensaje' => $mensaje,
                'fecha_envio' => now(),
            ]);
            Mail::to($administrador->correo_institucional)
                ->queue(new AlertaVencimientoMail($administrador->nombre_completo, $mensaje));
            $creadas++;
        }

        return $creadas;
    }

    /**
     * Indica si el administrador ya recibió hoy la alerta de vencimiento.
     *
     * @param  Usuario  $administrador  Destinatario.
     * @return bool
     */
    private function existeAlertaDelDia(Usuario $administrador): bool
    {
        return Notificacion::withoutGlobalScopes()
            ->where('id_usuario', $administrador->id_usuario)
            ->where('titulo', self::TITULO)
            ->whereDate('fecha_creacion', today())
            ->exists();
    }
}
