<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         RegistroController.php
 * @path         app/Http/Controllers/Web/RegistroController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Completar el registro desde el enlace firmado de invitación.
 *
 * @requisitos   RF-008, RNF-001
 *
 * @see          docs/requisitos/BBB_CU004_InvitarAdministrador_v1.0.md (pasos 4–5, E3, E4)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompletarRegistroRequest;
use App\Models\Usuario;
use App\Services\AdministradorService;
use App\Services\InvitacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Registro de administradores invitados.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class RegistroController extends Controller
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  AdministradorService  $administradorService  Dependencia inyectada.
     */
    public function __construct(
        private AdministradorService $administradorService,
        private InvitacionService $invitacionService,
    ) {}

    /**
     * Muestra el formulario para definir la contraseña.
     *
     * @param  Request  $request  Petición firmada.
     * @param  Usuario  $usuario  Invitado.
     * @return View|RedirectResponse
     */
    public function edit(Request $request, Usuario $usuario): View|RedirectResponse
    {
        $this->validarHuella($request, $usuario);

        if ($usuario->activo || $usuario->completoRegistro()) {
            return redirect()->route('login')->with('error', 'El registro ya fue completado.');
        }

        return view('registro.edit', [
            'usuario' => $usuario,
            'accion' => $request->fullUrl(),
        ]);
    }

    /**
     * Guarda la contraseña y activa la cuenta.
     *
     * @param  CompletarRegistroRequest  $request  Contraseña validada.
     * @param  Usuario  $usuario  Invitado.
     * @return RedirectResponse
     */
    public function update(CompletarRegistroRequest $request, Usuario $usuario): RedirectResponse
    {
        $this->validarHuella($request, $usuario);
        $this->administradorService->completarRegistro($usuario, $request->string('contrasena')->toString());

        return redirect()->route('login')->with('success', 'Registro completado. Ya puede iniciar sesión.');
    }

    /**
     * Rechaza enlaces emitidos antes del último cambio de contraseña de una cuenta inactiva (cuenta dada de
     * baja o invitación reemplazada). Las cuentas activas siguen su flujo y reciben "registro completado".
     *
     * @param  Request  $request  Petición firmada.
     * @param  Usuario  $usuario  Invitado.
     * @return void
     */
    private function validarHuella(Request $request, Usuario $usuario): void
    {
        // Las cuentas ya registradas (activas o dadas de baja) reciben "registro completado" más adelante.
        if ($usuario->activo || $usuario->completoRegistro()) {
            return;
        }

        abort_unless(
            $this->invitacionService->validarHuella($usuario, (string) $request->query('huella')),
            403,
            'El enlace no es válido o ha caducado.',
        );
    }
}
