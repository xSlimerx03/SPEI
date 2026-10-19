<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.7: Notificaciones y Recordatorios
 * @file         NotificacionController.php
 * @path         app/Http/Controllers/Web/NotificacionController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Bandeja de notificaciones dentro del sistema web (canal
 *               "push" adaptado, DT-022).
 *
 * @requisitos   RF-005, RF-046
 *
 * @see          DT-022, DT-024
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Notificaciones del usuario autenticado.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class NotificacionController extends Controller
{
    /**
     * Lista las notificaciones propias, las más recientes primero.
     *
     * @param  Request  $request  Petición en curso.
     * @return View
     */
    public function index(Request $request): View
    {
        return view('notificaciones.index', [
            'notificaciones' => Notificacion::query()
                ->where('id_usuario', $request->user()?->getAuthIdentifier())
                ->orderByDesc('fecha_creacion')
                ->paginate(15),
        ]);
    }

    /**
     * Marca como leída una notificación propia.
     *
     * @param  Request  $request  Petición en curso.
     * @param  Notificacion  $notificacion  Notificación del tenant.
     * @return RedirectResponse
     */
    public function marcarLeida(Request $request, Notificacion $notificacion): RedirectResponse
    {
        if ($notificacion->id_usuario !== (int) $request->user()?->getAuthIdentifier()) {
            abort(404);
        }

        $notificacion->update(['leida' => true]);

        return back();
    }
}
