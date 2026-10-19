<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         ImportacionController.php
 * @path         app/Http/Controllers/Web/ImportacionController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Pantalla y procesamiento de la carga masiva de recursos CSV.
 *
 * @requisitos   RF-012
 *
 * @see          docs/requisitos/BBB_CU008_ImportarCatalogo_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportarCatalogoRequest;
use App\Models\Institucion;
use App\Models\Recurso;
use App\Models\Usuario;
use App\Services\ImportacionCatalogoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Importación del catálogo.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ImportacionController extends Controller
{
    public function __construct(
        private ImportacionCatalogoService $importacionService,
    ) {}

    /**
     * Muestra el formato admitido y el formulario de carga.
     *
     * @return View
     */
    public function create(): View
    {
        Gate::authorize('gestionar', Recurso::class);

        return view('recursos.importar', [
            'encabezado' => implode(',', ImportacionCatalogoService::ENCABEZADO),
            'maximoFilas' => (int) config('spei.importacion.maximo_filas'),
        ]);
    }

    /**
     * Procesa el archivo; si alguna fila falla no se inserta nada.
     *
     * @param  ImportarCatalogoRequest  $request  Archivo validado.
     * @return RedirectResponse
     */
    public function store(ImportarCatalogoRequest $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        /** @var UploadedFile $archivo */
        $archivo = $request->file('archivo');
        $institucion = Institucion::query()->findOrFail($usuario->id_institucion);
        $total = $this->importacionService->importar($usuario, $institucion, $archivo);

        return redirect()->route('recursos.index')
            ->with('success', sprintf('Se importaron %d recursos correctamente.', $total));
    }
}
