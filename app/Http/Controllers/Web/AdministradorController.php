<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         AdministradorController.php
 * @path         app/Http/Controllers/Web/AdministradorController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Listado e invitación de administradores del sistema por el
 *               administrador institucional.
 *
 * @requisitos   RF-008, RS-006
 *
 * @see          docs/requisitos/BBB_CU004_InvitarAdministrador_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Http\Requests\InvitarAdministradorRequest;
use App\Models\Area;
use App\Models\Institucion;
use App\Models\Usuario;
use App\Services\AdministradorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Administradores del sistema de la institución.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AdministradorController extends Controller
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  AdministradorService  $administradorService  Dependencia inyectada.
     */
    public function __construct(
        private AdministradorService $administradorService,
    ) {}

    /**
     * Lista los administradores del sistema de la institución.
     *
     * @return View
     */
    public function index(): View
    {
        return view('administradores.index', [
            'administradores' => Usuario::query()
                ->with('areaAsignada')
                ->where('rol', Rol::AdminSistema)
                ->orderBy('nombre_completo')
                ->paginate(15),
        ]);
    }

    /**
     * Muestra el formulario de invitación.
     *
     * @return View
     */
    public function create(): View
    {
        return view('administradores.create', [
            'areas' => Area::query()->activas()->orderBy('nombre_area')->get(),
        ]);
    }

    /**
     * Crea el usuario inactivo y envía la invitación.
     *
     * @param  InvitarAdministradorRequest  $request  Datos validados.
     * @return RedirectResponse
     */
    public function store(InvitarAdministradorRequest $request): RedirectResponse
    {
        $institucion = Institucion::query()->findOrFail($request->user()?->getAttribute('id_institucion'));
        $this->administradorService->invitar($institucion, $request->validated());

        return redirect()->route('administradores.index')->with('success', 'Invitación enviada correctamente.');
    }

    /**
     * Reenvía la invitación a un administrador que no ha completado su registro.
     *
     * @param  Usuario  $usuario  Administrador invitado (solo de la propia institución).
     * @return RedirectResponse
     */
    public function reenviar(Usuario $usuario): RedirectResponse
    {
        if ($usuario->rol !== Rol::AdminSistema) {
            abort(404);
        }

        $this->administradorService->reenviarInvitacion($usuario);

        return back()->with('success', 'Invitación enviada correctamente.');
    }
}
