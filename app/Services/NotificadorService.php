<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.7: Notificaciones y Recordatorios
 * @file         NotificadorService.php
 * @path         app/Services/NotificadorService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Punto único de avisos: guarda la notificación en el sistema
 *               (RF-047, push web por DT-022) y, si corresponde, encola el
 *               correo (RF-048) en Redis. La notificación queda con tipo
 *               "ambas" cuando también sale por correo.
 *
 * @requisitos   RF-045, RF-046, RF-047, RF-048, RF-049, RF-050, RNF-004
 *
 * @see          CU-021; DT-022
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\TipoNotificacion;
use App\Mail\AvisoMail;
use App\Models\Notificacion;
use App\Models\Usuario;
use Illuminate\Support\Facades\Mail;

/**
 * Notificaciones en el sistema y por correo.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class NotificadorService
{
    /**
     * Registra el aviso para el usuario y lo envía por correo si se pide y está habilitado.
     *
     * @param  Usuario  $destinatario  Usuario del tenant.
     * @param  string  $titulo  Título breve (máx. 150).
     * @param  string  $mensaje  Texto del aviso.
     * @param  string|null  $enlace  Pantalla relacionada.
     * @param  bool  $correo  Si además se envía por correo.
     * @return Notificacion
     */
    public function notificar(
        Usuario $destinatario,
        string $titulo,
        string $mensaje,
        ?string $enlace = null,
        bool $correo = true,
    ): Notificacion {
        $enviarCorreo = $correo && $destinatario->activo && (bool) config('spei.notificaciones.correo');

        $notificacion = Notificacion::query()->create([
            'id_institucion' => $destinatario->id_institucion,
            'id_usuario' => $destinatario->id_usuario,
            'tipo_notificacion' => $enviarCorreo ? TipoNotificacion::Ambas : TipoNotificacion::Push,
            'titulo' => mb_substr($titulo, 0, 150),
            'mensaje' => $mensaje,
            'leida' => false,
            'fecha_envio' => now(),
        ]);

        if ($enviarCorreo) {
            Mail::to($destinatario->correo_institucional)->queue(
                new AvisoMail($destinatario->nombre_completo, $titulo, $mensaje, $enlace),
            );
        }

        return $notificacion;
    }
}
