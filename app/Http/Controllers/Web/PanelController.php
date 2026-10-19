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
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Panel de inicio según el rol: métricas de la plataforma para
 *               el superadmin, de la suscripción para el administrador
 *               institucional y del inventario para el administrador del
 *               sistema; el usuario final va al catálogo.
 *
 * @requisitos   RF-004, RF-013, RF-015
 *
 * @see          Manual de Programación v1.2, sección 2.4.5 (layout de dashboard)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\EstadoRecurso;
use App\Enums\EstadoSuscripcion;
use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\Recurso;
use App\Models\Usuario;
use App\Services\SuscripcionService;
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
    public function __construct(
        private SuscripcionService $suscripcionService,
    ) {}

    /**
     * Muestra las métricas del rol o redirige al catálogo.
     *
     * @param  Request  $request  Petición en curso.
     * @return View|RedirectResponse
     */
    public function __invoke(Request $request): View|RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        return match ($usuario->rol) {
            Rol::Superadmin => view('panel.superadmin', ['metricas' => $this->contarInstituciones()]),
            Rol::AdminInstitucional => $this->mostrarSuscripcion($usuario),
            Rol::AdminSistema => view('panel.admin-sistema', ['metricas' => $this->contarRecursos($usuario)]),
            Rol::Asistente => redirect()->route('notificaciones.index'),
            default => redirect()->route('catalogo.index'),
        };
    }

    /**
     * Panel del administrador institucional.
     *
     * @param  Usuario  $usuario  Administrador institucional.
     * @return View
     */
    private function mostrarSuscripcion(Usuario $usuario): View
    {
        $institucion = Institucion::query()->findOrFail($usuario->id_institucion);

        return view('panel.admin-institucional', [
            'institucion' => $institucion,
            'uso' => $this->suscripcionService->obtenerUso($institucion),
        ]);
    }

    /**
     * Total de instituciones por estado de suscripción.
     *
     * @return array<string, int>
     */
    private function contarInstituciones(): array
    {
        $metricas = [];

        foreach (EstadoSuscripcion::cases() as $estado) {
            $metricas[$estado->obtenerEtiqueta()] = Institucion::query()->where('estado_suscripcion', $estado)->count();
        }

        return $metricas;
    }

    /**
     * Total de recursos por estado (del área asignada si es responsable).
     *
     * @param  Usuario  $usuario  Administrador del sistema.
     * @return array<string, int>
     */
    private function contarRecursos(Usuario $usuario): array
    {
        $metricas = [];

        foreach (EstadoRecurso::cases() as $estado) {
            $metricas[$estado->obtenerEtiqueta()] = Recurso::query()
                ->where('estado', $estado)
                ->when($usuario->esResponsableDeArea(), fn ($q) => $q->where('id_area', $usuario->id_area_asignada))
                ->count();
        }

        return $metricas;
    }
}
