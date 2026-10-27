<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         DominioRolController.php
 * @path         app/Http/Controllers/Web/DominioRolController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Reglas dominio → rol de la institución (RF-017): lista, alta y
 *               baja. Solo el administrador del sistema sin área.
 *
 * @requisitos   RF-017, RN-001, RS-006
 *
 * @see          CU-016; DT-034
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarDominioRequest;
use App\Models\DominioRol;
use App\Models\Institucion;
use App\Models\Usuario;
use App\Services\DominioRolService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Reglas de dominio de la institución.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class DominioRolController extends Controller
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  DominioRolService  $dominioRolService  Dependencia inyectada.
     */
    public function __construct(
        private DominioRolService $dominioRolService,
    ) {}

    /**
     * Lista las reglas y muestra el formulario de alta.
     *
     * @param  Request  $request  Petición.
     * @return View
     */
    public function index(Request $request): View
    {
        $actor = $this->autorizar($request);

        return view('usuarios.dominios', [
            'reglas' => DominioRol::query()->orderBy('dominio')->get(),
            'dominioInstitucion' => Institucion::query()->whereKey($actor->id_institucion)->value('dominio_correo'),
        ]);
    }

    /**
     * Registra una regla.
     *
     * @param  GuardarDominioRequest  $request  Datos validados.
     * @return RedirectResponse
     */
    public function store(GuardarDominioRequest $request): RedirectResponse
    {
        $actor = $this->autorizar($request);
        $institucion = Institucion::query()->findOrFail($actor->id_institucion);
        $this->dominioRolService->registrar(
            $institucion,
            $request->string('dominio')->toString(),
            Rol::from($request->string('rol')->toString()),
        );

        return redirect()->route('dominios.index')->with('success', 'Regla de dominio registrada.');
    }

    /**
     * Elimina una regla de la institución.
     *
     * @param  Request  $request  Petición.
     * @param  DominioRol  $dominio  Regla (el Global Scope impide reglas de otra institución).
     * @return RedirectResponse
     */
    public function destroy(Request $request, DominioRol $dominio): RedirectResponse
    {
        $this->autorizar($request);
        $this->dominioRolService->eliminar($dominio);

        return redirect()->route('dominios.index')->with('success', 'Regla de dominio eliminada.');
    }

    /**
     * Exige un administrador del sistema sin área (el responsable de un área no define reglas).
     *
     * @param  Request  $request  Petición.
     * @return Usuario
     */
    private function autorizar(Request $request): Usuario
    {
        /** @var Usuario $actor */
        $actor = $request->user();
        abort_if($actor->esResponsableDeArea(), 403);

        return $actor;
    }
}
