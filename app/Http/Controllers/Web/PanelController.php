<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Panel principal
 * @file         PanelController.php
 * @path         app/Http/Controllers/Web/PanelController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Inicio de cada rol según el prototipo hi-fi: dashboard
 *               operativo del administrador del sistema, panel de plataforma
 *               del superadmin; el asistente va al mostrador, el administrador
 *               institucional a la suscripción y el usuario final al catálogo.
 *
 * @requisitos   RF-004, RF-013, RF-049, RF-054
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Dashboard); DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\EstadoSuscripcion;
use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\Usuario;
use App\Services\TableroService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Panel de inicio.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class PanelController extends Controller
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  TableroService  $tableroService  Dependencia inyectada.
     */
    public function __construct(
        private TableroService $tableroService,
    ) {}

    /**
     * Muestra el inicio del rol o redirige a su pantalla principal.
     *
     * @param  Request  $request  Petición en curso.
     * @return View|RedirectResponse
     */
    public function __invoke(Request $request): View|RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        return match ($usuario->rol) {
            Rol::Superadmin => $this->mostrarPlataforma(),
            Rol::AdminSistema => $this->mostrarTablero($usuario),
            Rol::AdminInstitucional => redirect()->route('suscripcion.show'),
            Rol::Asistente => redirect()->route('mostrador.index'),
            Rol::Alumno, Rol::Docente => redirect()->route('catalogo.index'),
        };
    }

    /**
     * Dashboard operativo del administrador del sistema.
     *
     * @param  Usuario  $usuario  Administrador del sistema.
     * @return View
     */
    private function mostrarTablero(Usuario $usuario): View
    {
        return view('panel.admin-sistema', [
            'usuario' => $usuario,
            'indicadores' => $this->tableroService->obtenerIndicadores($usuario),
            'serie' => $this->tableroService->obtenerSerieDiaria($usuario),
            'inventario' => $this->tableroService->obtenerInventario($usuario),
            'porAtender' => $this->tableroService->obtenerPorAtender($usuario),
            'agenda' => $this->tableroService->obtenerAgendaHoy($usuario),
        ]);
    }

    /**
     * Panel del superadmin: instituciones por estado y próximas a vencer.
     *
     * @return View
     */
    private function mostrarPlataforma(): View
    {
        $metricas = [];

        foreach (EstadoSuscripcion::cases() as $estado) {
            $metricas[$estado->value] = Institucion::query()->where('estado_suscripcion', $estado)->count();
        }

        return view('panel.superadmin', [
            'metricas' => $metricas,
            'porVencer' => Institucion::query()
                ->whereIn('estado_suscripcion', [EstadoSuscripcion::Activa, EstadoSuscripcion::SoloLectura])
                ->orderBy('fecha_corte')
                ->limit(8)
                ->get(),
        ]);
    }
}
