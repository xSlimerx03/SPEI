<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Autenticación
 * @file         SesionController.php
 * @path         app/Http/Controllers/Web/SesionController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Inicio y cierre de sesión web con sesión regenerada en Redis.
 *
 * @requisitos   RNF-001, RN-001, RN-013, RNF-005
 *
 * @see          docs/requisitos/BBB_CU005_IniciarSesion_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\IniciarSesionRequest;
use App\Models\Usuario;
use App\Services\AutenticacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Sesión web.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class SesionController extends Controller
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  AutenticacionService  $autenticacionService  Dependencia inyectada.
     */
    public function __construct(
        private AutenticacionService $autenticacionService,
    ) {}

    /**
     * Muestra el formulario de inicio de sesión.
     *
     * @return View
     */
    public function create(): View
    {
        return view('sesion.create');
    }

    /**
     * Valida el acceso, regenera la sesión y audita.
     *
     * @param  IniciarSesionRequest  $request  Credenciales.
     * @return RedirectResponse
     */
    public function store(IniciarSesionRequest $request): RedirectResponse
    {
        $usuario = $this->autenticacionService->verificarCredenciales(
            $request->string('correo_institucional')->toString(),
            $request->string('contrasena')->toString(),
        );

        Auth::login($usuario);
        $request->session()->regenerate();
        $this->autenticacionService->auditarAcceso('INICIAR_SESION', $usuario);

        return redirect()->intended(route('panel'));
    }

    /**
     * Audita y cierra la sesión.
     *
     * @param  Request  $request  Petición en curso.
     * @return RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        if ($usuario instanceof Usuario) {
            $this->autenticacionService->auditarAcceso('CERRAR_SESION', $usuario);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }
}
