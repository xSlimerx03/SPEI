<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         UsuarioController.php
 * @path         app/Http/Controllers/Web/UsuarioController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Pantallas de usuarios del administrador del sistema: lista con
 *               filtros, alta individual, carga CSV, cambio de rol y área,
 *               baja/reactivación y reenvío de invitación. El responsable de
 *               un área solo ve y gestiona a los asistentes de su área.
 *
 * @requisitos   RF-016, RF-017, RF-018, RF-020, RF-021, RS-006
 *
 * @see          CU-016, CU-017
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Exceptions\ReglaNegocioException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarUsuarioRequest;
use App\Http\Requests\ImportarUsuariosRequest;
use App\Http\Requests\InvitarUsuarioRequest;
use App\Models\Area;
use App\Models\DominioRol;
use App\Models\Institucion;
use App\Models\Usuario;
use App\Services\UsuarioService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Gestión de usuarios finales y asistentes.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class UsuarioController extends Controller
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  UsuarioService  $usuarioService  Dependencia inyectada.
     */
    public function __construct(
        private UsuarioService $usuarioService,
    ) {}

    /**
     * Lista las cuentas gestionables con filtros por texto, rol y estado.
     *
     * @param  Request  $request  Filtros.
     * @return View
     */
    public function index(Request $request): View
    {
        $actor = $this->obtenerActor($request);
        $filtros = $request->validate([
            'texto' => ['nullable', 'string', 'max:100'],
            'rol' => ['nullable', Rule::in(['alumno', 'docente', 'asistente'])],
            'estado' => ['nullable', Rule::in(['pendiente', 'activa', 'baja'])],
        ]);

        $usuarios = $this->usuarioService->consultarGestionables($actor)
            ->with('areaAsignada')
            ->when(
                $filtros['texto'] ?? null,
                fn (Builder $q, string $texto): Builder => $this->filtrarTexto($q, $texto),
            )
            ->when($filtros['rol'] ?? null, fn (Builder $q, string $rol): Builder => $q->where('rol', $rol))
            ->when(
                $filtros['estado'] ?? null,
                fn (Builder $q, string $estado): Builder => $this->filtrarEstado($q, $estado),
            )
            ->orderBy('nombre_completo')
            ->paginate(15)
            ->withQueryString();

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'filtros' => $filtros,
            'actor' => $actor,
            'reglas' => $actor->esResponsableDeArea() ? 0 : DominioRol::query()->count(),
        ]);
    }

    /**
     * Formulario de alta individual.
     *
     * @param  Request  $request  Petición.
     * @return View
     */
    public function create(Request $request): View
    {
        $actor = $this->obtenerActor($request);

        return view('usuarios.create', [
            'actor' => $actor,
            'areas' => $this->obtenerAreas($actor),
            'reglas' => DominioRol::query()->orderBy('dominio')->get(),
        ]);
    }

    /**
     * Crea la cuenta inactiva y envía la invitación.
     *
     * @param  InvitarUsuarioRequest  $request  Datos validados.
     * @return RedirectResponse
     */
    public function store(InvitarUsuarioRequest $request): RedirectResponse
    {
        $actor = $this->obtenerActor($request);
        $institucion = Institucion::query()->findOrFail($actor->id_institucion);

        try {
            $this->usuarioService->invitar($actor, $institucion, $request->validated());
        } catch (ReglaNegocioException $excepcion) {
            return back()->withInput()->withErrors(['correo_institucional' => $excepcion->getMessage()]);
        }

        return redirect()->route('usuarios.index')->with('success', 'Invitación enviada correctamente.');
    }

    /**
     * Formulario de rol y área.
     *
     * @param  Request  $request  Petición.
     * @param  Usuario  $usuario  Cuenta de la institución.
     * @return View
     */
    public function edit(Request $request, Usuario $usuario): View
    {
        $actor = $this->obtenerActor($request);
        $this->autorizar($actor, $usuario);

        return view('usuarios.edit', [
            'actor' => $actor,
            'usuario' => $usuario,
            'areas' => $this->obtenerAreas($actor),
        ]);
    }

    /**
     * Guarda el rol y el área.
     *
     * @param  ActualizarUsuarioRequest  $request  Datos validados.
     * @param  Usuario  $usuario  Cuenta de la institución.
     * @return RedirectResponse
     */
    public function update(ActualizarUsuarioRequest $request, Usuario $usuario): RedirectResponse
    {
        $actor = $this->obtenerActor($request);
        $this->autorizar($actor, $usuario);
        $this->usuarioService->actualizar($actor, $usuario, $request->validated());

        return redirect()->route('usuarios.index')->with('success', 'Cambios guardados correctamente.');
    }

    /**
     * Da de baja o reactiva la cuenta.
     *
     * @param  Request  $request  Valor de "activo".
     * @param  Usuario  $usuario  Cuenta de la institución.
     * @return RedirectResponse
     */
    public function cambiarActivo(Request $request, Usuario $usuario): RedirectResponse
    {
        $actor = $this->obtenerActor($request);
        $this->autorizar($actor, $usuario);
        $activo = $request->boolean('activo');
        $this->usuarioService->cambiarActivo($actor, $usuario, $activo);

        return back()->with('success', $activo ? 'Cuenta reactivada.' : 'Cuenta dada de baja.');
    }

    /**
     * Reenvía la invitación pendiente.
     *
     * @param  Request  $request  Petición.
     * @param  Usuario  $usuario  Cuenta de la institución.
     * @return RedirectResponse
     */
    public function reenviar(Request $request, Usuario $usuario): RedirectResponse
    {
        $actor = $this->obtenerActor($request);
        $this->autorizar($actor, $usuario);
        $this->usuarioService->reenviarInvitacion($actor, $usuario);

        return back()->with('success', 'Invitación enviada correctamente.');
    }

    /**
     * Formulario de carga masiva por CSV.
     *
     * @param  Request  $request  Petición.
     * @return View
     */
    public function importar(Request $request): View
    {
        return view('usuarios.importar', ['actor' => $this->obtenerActor($request)]);
    }

    /**
     * Importa el CSV completo o ninguna fila.
     *
     * @param  ImportarUsuariosRequest  $request  Archivo validado.
     * @return RedirectResponse
     */
    public function importarStore(ImportarUsuariosRequest $request): RedirectResponse
    {
        $actor = $this->obtenerActor($request);
        $institucion = Institucion::query()->findOrFail($actor->id_institucion);
        /** @var UploadedFile $archivo */
        $archivo = $request->file('archivo');
        $total = $this->usuarioService->importar($actor, $institucion, $archivo);

        return redirect()->route('usuarios.index')
            ->with('success', sprintf('Se importaron %d usuarios; cada uno recibirá su invitación.', $total));
    }

    /**
     * Usuario autenticado.
     *
     * @param  Request  $request  Petición.
     * @return Usuario
     */
    private function obtenerActor(Request $request): Usuario
    {
        /** @var Usuario $actor */
        $actor = $request->user();

        return $actor;
    }

    /**
     * Responde 404 si la cuenta no es gestionable por el actor (no revela que existe).
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @param  Usuario  $usuario  Cuenta objetivo.
     * @return void
     */
    private function autorizar(Usuario $actor, Usuario $usuario): void
    {
        $gestionable = $this->usuarioService->consultarGestionables($actor)->whereKey($usuario->id_usuario)->exists();
        abort_unless($gestionable, 404);
    }

    /**
     * Áreas activas que el actor puede asignar.
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @return Collection<int, Area>
     */
    private function obtenerAreas(Usuario $actor): Collection
    {
        return Area::query()
            ->activas()
            ->when($actor->esResponsableDeArea(), fn (Builder $q): Builder => $q->whereKey($actor->id_area_asignada))
            ->orderBy('nombre_area')
            ->get();
    }

    /**
     * Busca por nombre o correo.
     *
     * @param  Builder<Usuario>  $consulta  Consulta.
     * @param  string  $texto  Texto capturado.
     * @return Builder<Usuario>
     */
    private function filtrarTexto(Builder $consulta, string $texto): Builder
    {
        $patron = '%' . addcslashes($texto, '%_\\') . '%';

        return $consulta->where(fn (Builder $q): Builder => $q->where('nombre_completo', 'like', $patron)
            ->orWhere('correo_institucional', 'like', $patron));
    }

    /**
     * Filtra por estado de la cuenta (DT-035).
     *
     * @param  Builder<Usuario>  $consulta  Consulta.
     * @param  string  $estado  pendiente, activa o baja.
     * @return Builder<Usuario>
     */
    private function filtrarEstado(Builder $consulta, string $estado): Builder
    {
        return match ($estado) {
            'pendiente' => $consulta->whereNull('fecha_registro'),
            'activa' => $consulta->whereNotNull('fecha_registro')->where('activo', true),
            default => $consulta->whereNotNull('fecha_registro')->where('activo', false),
        };
    }
}
