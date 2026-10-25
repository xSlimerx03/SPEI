<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         LayoutAppComposer.php
 * @path         app/View/Composers/LayoutAppComposer.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Datos comunes del layout autenticado: menú por rol, buscador,
 *               tarjeta del tenant, banner de solo lectura y desplegable de
 *               notificaciones.
 *
 * @requisitos   RF-006, RF-046, RNF-008, RS-006
 *
 * @see          resources/views/layouts/app.blade.php; DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\View\Composers;

use App\Enums\EstadoSuscripcion;
use App\Models\Institucion;
use App\Models\Notificacion;
use App\Models\Usuario;
use App\Support\MenuNavegacion;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Compositor del layout layouts.app.
 *
 * @package App\View\Composers
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class LayoutAppComposer
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  MenuNavegacion  $menuNavegacion  Dependencia inyectada.
     */
    public function __construct(
        private MenuNavegacion $menuNavegacion,
    ) {}

    /**
     * Comparte los datos del layout con la vista.
     *
     * @param  View  $vista  Vista en composición.
     * @return void
     */
    public function compose(View $vista): void
    {
        $usuario = Auth::user();

        if (! $usuario instanceof Usuario) {
            return;
        }

        $institucion = $usuario->id_institucion === null
            ? null
            : Institucion::query()->find($usuario->id_institucion);

        $vista->with([
            'usuarioActual' => $usuario,
            'institucionActual' => $institucion,
            'soloLectura' => $institucion?->estado_suscripcion === EstadoSuscripcion::SoloLectura,
            'menu' => $this->menuNavegacion->obtenerSecciones($usuario),
            'buscador' => $this->menuNavegacion->obtenerBuscador($usuario),
            'notificacionesSinLeer' => Notificacion::query()
                ->where('id_usuario', $usuario->id_usuario)
                ->where('leida', false)
                ->count(),
            'notificacionesRecientes' => Notificacion::query()
                ->where('id_usuario', $usuario->id_usuario)
                ->orderByDesc('fecha_creacion')
                ->orderByDesc('id_notificacion')
                ->limit(5)
                ->get(),
        ]);
    }
}
