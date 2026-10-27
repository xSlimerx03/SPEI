<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         PrestamoController.php
 * @path         app/Http/Controllers/Web/PrestamoController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-27
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Préstamos del lado operativo (prototipo hi-fi): listado con
 *               pestañas por estado, filtros y panel de detalle; comprobante;
 *               confirmación de entrega o devolución desde el mostrador;
 *               cancelación justificada y exportación CSV del ámbito visible.
 *
 * @requisitos   RF-028, RF-029, RF-033, RF-034, RF-036, RF-042, RF-051, RF-058, RS-006
 *
 * @see          docs/plan/BBB_MVP_v1.0.md; app/Policies/PrestamoPolicy.php; DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\EstadoPrestamo;
use App\Exceptions\ReglaNegocioException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmarMovimientoRequest;
use App\Http\Requests\FiltrarPrestamosRequest;
use App\Models\Area;
use App\Models\DetallePrestamo;
use App\Models\Penalizacion;
use App\Models\Prestamo;
use App\Models\Usuario;
use App\Services\ConsultaPrestamoService;
use App\Services\ConversacionService;
use App\Services\PrestamoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Préstamos para operadores y comprobantes.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class PrestamoController extends Controller
{
    /**
     * Relaciones necesarias para pintar filas y panel de detalle.
     */
    private const RELACIONES = ['usuario', 'detalles.recurso.area'];

    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  PrestamoService  $prestamoService  Dependencia inyectada.
     * @param  ConsultaPrestamoService  $consultaPrestamoService  Dependencia inyectada.
     * @param  ConversacionService  $conversacionService  Dependencia inyectada.
     */
    public function __construct(
        private PrestamoService $prestamoService,
        private ConsultaPrestamoService $consultaPrestamoService,
        private ConversacionService $conversacionService,
    ) {}

    /**
     * Listado con pestañas por estado, filtros y panel del préstamo seleccionado.
     *
     * @param  FiltrarPrestamosRequest  $request  Filtros validados.
     * @return View
     */
    public function index(FiltrarPrestamosRequest $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $filtros = $request->validated();
        $sinEstado = $this->consultaPrestamoService->filtrar(
            $this->consultaPrestamoService->paraUsuario($usuario),
            array_diff_key($filtros, ['estado' => true]),
        );
        $prestamos = $this->consultaPrestamoService->filtrar(clone $sinEstado, $filtros)
            ->with(self::RELACIONES)
            ->orderByRaw('FIELD(estado, ?, ?, ?, ?, ?)', [
                EstadoPrestamo::Pendiente->value,
                EstadoPrestamo::Activo->value,
                EstadoPrestamo::Vencido->value,
                EstadoPrestamo::Devuelto->value,
                EstadoPrestamo::Cancelado->value,
            ])
            ->orderBy('fecha_hora_inicio')
            ->paginate(10)
            ->withQueryString();
        $idSeleccionado = isset($filtros['p']) ? (int) $filtros['p'] : null;
        $seleccionado = $this->obtenerSeleccionado($usuario, $idSeleccionado) ?? $prestamos->first();

        return view('prestamos.index', [
            'prestamos' => $prestamos,
            'conteo' => $this->consultaPrestamoService->contarPorEstado($sinEstado),
            'seleccionado' => $seleccionado,
            'areas' => Area::query()
                ->when(
                    $usuario->id_area_asignada !== null,
                    fn (Builder $q): Builder => $q->whereKey($usuario->id_area_asignada),
                )
                ->orderBy('nombre_area')
                ->get(),
            'filtros' => $filtros,
            'usuario' => $usuario,
        ]);
    }

    /**
     * Detalle y comprobante imprimible del movimiento.
     *
     * @param  Prestamo  $prestamo  Préstamo del tenant.
     * @return View
     */
    public function show(Request $request, Prestamo $prestamo): View
    {
        Gate::authorize('view', $prestamo);
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->conversacionService->marcarLeidos($usuario, $prestamo);

        return view('prestamos.show', [
            'prestamo' => $prestamo->load(self::RELACIONES),
            'mensajes' => $this->conversacionService->listar($prestamo),
            'puedeConversar' => $usuario->can('conversar', $prestamo),
            'penalizaciones' => Penalizacion::query()->where('id_prestamo', $prestamo->id_prestamo)->get(),
        ]);
    }

    /**
     * Envía un mensaje de la conversación previa a la entrega (RF-033).
     *
     * @param  Request  $request  Texto del mensaje.
     * @param  Prestamo  $prestamo  Préstamo pendiente.
     * @return RedirectResponse
     */
    public function enviarMensaje(Request $request, Prestamo $prestamo): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $datos = $request->validateWithBag('mensaje', [
            'mensaje' => ['required', 'string', 'min:2', 'max:1000'],
        ]);
        $this->conversacionService->enviar($usuario, $prestamo, trim($datos['mensaje']));

        return redirect()->route('prestamos.show', $prestamo)->with('success', 'Mensaje enviado.');
    }

    /**
     * Cierra un préstamo atrasado como no devuelto, con justificación y sanción automática (RF-042).
     *
     * @param  Request  $request  Justificación.
     * @param  Prestamo  $prestamo  Préstamo activo con devolución vencida.
     * @return RedirectResponse
     */
    public function cerrarNoDevuelto(Request $request, Prestamo $prestamo): RedirectResponse
    {
        Gate::authorize('cerrarNoDevuelto', $prestamo);
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $datos = $request->validateWithBag('noDevuelto', [
            'motivo' => ['required', 'string', 'min:20', 'max:1000'],
        ], [], ['motivo' => 'justificación']);
        $this->prestamoService->cerrarNoDevuelto($usuario, $prestamo, trim($datos['motivo']));

        return back()->with('success', 'Préstamo cerrado como no devuelto; se aplicó la sanción correspondiente.');
    }

    /**
     * Consume el código QR y confirma la entrega o la devolución.
     *
     * @param  ConfirmarMovimientoRequest  $request  Código y condición validados.
     * @param  Prestamo  $prestamo  Préstamo del tenant.
     * @return RedirectResponse
     */
    public function confirmar(ConfirmarMovimientoRequest $request, Prestamo $prestamo): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        try {
            $resultado = $this->prestamoService->confirmar($usuario, $prestamo, $request->validated());
        } catch (ReglaNegocioException $excepcion) {
            // La validación del mostrador es POST; el error vuelve a la pantalla de lectura.
            return redirect()->route('mostrador.index')->with('error', $excepcion->getMessage());
        }

        $mensaje = $resultado->estado === EstadoPrestamo::Activo->value
            ? 'Entrega confirmada correctamente.'
            : 'Devolución confirmada correctamente.';

        return redirect()->route('mostrador.index')->with('success', $mensaje);
    }

    /**
     * Cancela antes de la entrega con justificación (20 caracteres si la cancela un operador).
     *
     * @param  Request  $request  Motivo de cancelación.
     * @param  Prestamo  $prestamo  Préstamo del tenant.
     * @return RedirectResponse
     */
    public function cancelar(Request $request, Prestamo $prestamo): RedirectResponse
    {
        Gate::authorize('cancelar', $prestamo);
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $minimo = $usuario->id_usuario === $prestamo->id_usuario ? 5 : 20;
        $datos = $request->validateWithBag('cancelacion', [
            'motivo_cancelacion' => ['required', 'string', 'min:' . $minimo, 'max:1000'],
        ], [], ['motivo_cancelacion' => 'justificación']);
        $this->prestamoService->cancelar($usuario, $prestamo, trim($datos['motivo_cancelacion']));

        return back()->with('success', 'Préstamo cancelado correctamente.');
    }

    /**
     * Exporta el mismo ámbito visible y neutraliza fórmulas de hojas de cálculo.
     *
     * @param  FiltrarPrestamosRequest  $request  Filtros validados.
     * @return StreamedResponse
     */
    public function exportar(FiltrarPrestamosRequest $request): StreamedResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $consulta = $this->consultaPrestamoService->filtrar(
            $this->consultaPrestamoService->paraUsuario($usuario)->with(self::RELACIONES),
            $request->validated(),
        );

        return response()->streamDownload(function () use ($consulta): void {
            $salida = fopen('php://output', 'w');

            if ($salida === false) {
                return;
            }

            fwrite($salida, "\xEF\xBB\xBF");
            $encabezado = ['Folio', 'Solicitante', 'Estado', 'Inicio', 'Devolución', 'Recursos', 'Finalidad'];
            fputcsv($salida, $encabezado, ',', '"', '');

            foreach ($consulta->orderBy('id_prestamo')->lazy(100) as $prestamo) {
                fputcsv($salida, $this->armarFilaCsv($prestamo), ',', '"', '');
            }

            fclose($salida);
        }, 'prestamos-' . today()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Préstamo elegido en el panel de detalle, solo si está en el ámbito del usuario.
     *
     * @param  Usuario  $usuario  Operador.
     * @param  int|null  $idPrestamo  Identificador solicitado.
     * @return Prestamo|null
     */
    private function obtenerSeleccionado(Usuario $usuario, ?int $idPrestamo): ?Prestamo
    {
        if ($idPrestamo === null) {
            return null;
        }

        return $this->consultaPrestamoService->paraUsuario($usuario)
            ->with(self::RELACIONES)
            ->find($idPrestamo);
    }

    /**
     * Fila CSV; antepone apóstrofo a valores que una hoja de cálculo interpretaría como fórmula.
     *
     * @param  Prestamo  $prestamo  Préstamo exportado.
     * @return list<string>
     */
    private function armarFilaCsv(Prestamo $prestamo): array
    {
        $fila = [
            $prestamo->obtenerFolio(),
            $prestamo->usuario->nombre_completo,
            $prestamo->obtenerEstado()->obtenerEtiqueta(),
            $prestamo->fecha_hora_inicio->format('Y-m-d H:i'),
            $prestamo->fecha_hora_fin->format('Y-m-d H:i'),
            $prestamo->detalles
                ->map(fn (DetallePrestamo $detalle): string => $detalle->recurso->nombre_recurso)
                ->implode(' | '),
            (string) $prestamo->motivo,
        ];

        return array_map(
            fn (string $valor): string => preg_match('/^[=+@\-\t\r\n]/u', $valor) === 1 ? "'" . $valor : $valor,
            $fila,
        );
    }
}
