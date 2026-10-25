<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.5: Entrega y Devolución
 * @file         MostradorController.php
 * @path         app/Http/Controllers/Web/MostradorController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Pantalla de mostrador (prototipo hi-fi): lector del código QR
 *               por cámara o captura manual, resultado del escaneo con la
 *               condición de los recursos y agenda del día.
 *
 * @requisitos   RF-034, RF-035, RF-036, RF-037, RS-006
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Mostrador); DT-022, DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\Rol;
use App\Exceptions\ReglaNegocioException;
use App\Http\Controllers\Controller;
use App\Models\Prestamo;
use App\Models\TokenQr;
use App\Models\Usuario;
use App\Services\MostradorService;
use App\Services\TableroService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Entregas y devoluciones en mostrador.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class MostradorController extends Controller
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  MostradorService  $mostradorService  Dependencia inyectada.
     * @param  TableroService  $tableroService  Dependencia inyectada.
     */
    public function __construct(
        private MostradorService $mostradorService,
        private TableroService $tableroService,
    ) {}

    /**
     * Muestra el lector y la agenda del día.
     *
     * @param  Request  $request  Petición en curso.
     * @return View
     */
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        return $this->mostrar($usuario, null, null, null);
    }

    /**
     * Valida el código capturado (por POST para que no quede en la URL ni en el historial).
     *
     * @param  Request  $request  Código capturado o leído con la cámara.
     * @return Response
     */
    public function validar(Request $request): Response
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $datos = $request->validate(['codigo' => ['required', 'string', 'max:64']]);
        $codigo = mb_strtolower(trim($datos['codigo']));

        try {
            $resultado = $this->mostradorService->validarCodigo($usuario, $codigo);
        } catch (ReglaNegocioException $excepcion) {
            return $this->responderSinCache($this->mostrar($usuario, null, null, $excepcion->getMessage()));
        }

        return $this->responderSinCache($this->mostrar($usuario, $codigo, $resultado, null));
    }

    /**
     * Arma la vista del mostrador.
     *
     * @param  Usuario  $usuario  Operador.
     * @param  string|null  $codigo  Código validado.
     * @param  array{prestamo: Prestamo, token: TokenQr}|null  $resultado  Validación.
     * @param  string|null  $errorCodigo  Motivo de rechazo.
     * @return View
     */
    private function mostrar(Usuario $usuario, ?string $codigo, ?array $resultado, ?string $errorCodigo): View
    {
        return view('mostrador.index', [
            'codigo' => $codigo,
            'resultado' => $resultado,
            'errorCodigo' => $errorCodigo,
            'agenda' => $this->tableroService->obtenerAgendaHoy($usuario),
            'sinArea' => $usuario->rol === Rol::Asistente && $usuario->id_area_asignada === null,
        ]);
    }

    /**
     * Evita que el navegador o un proxy guarden la página con el código QR validado (RS-006).
     *
     * @param  View  $vista  Vista del mostrador.
     * @return Response
     */
    private function responderSinCache(View $vista): Response
    {
        return response($vista)->header('Cache-Control', 'no-store, private');
    }
}
