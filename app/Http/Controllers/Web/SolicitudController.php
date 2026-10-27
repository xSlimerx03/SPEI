<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         SolicitudController.php
 * @path         app/Http/Controllers/Web/SolicitudController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Préstamos del usuario final (prototipo hi-fi): "Mis
 *               préstamos" con activos, historial e indicador de marcas;
 *               asistente de nueva solicitud en tres pasos y código QR de
 *               custodia con vigencia de 45 s.
 *
 * @requisitos   RF-022, RF-023, RF-028, RF-034, RF-036, RF-051, RN-004
 *
 * @see          docs/plan/BBB_MVP_v1.0.md; Prototipo interactivo SPEI/design_handoff_spei_web/README.md; DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\EstadoPrestamo;
use App\Http\Controllers\Controller;
use App\Http\Requests\SolicitarPrestamoRequest;
use App\Models\Penalizacion;
use App\Models\Prestamo;
use App\Models\Recurso;
use App\Models\Usuario;
use App\Services\CodigoQrService;
use App\Services\PrestamoService;
use App\Services\SancionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Solicitudes y préstamos propios.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class SolicitudController extends Controller
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  PrestamoService  $prestamoService  Dependencia inyectada.
     * @param  CodigoQrService  $codigoQrService  Dependencia inyectada.
     * @param  SancionService  $sancionService  Dependencia inyectada.
     */
    public function __construct(
        private PrestamoService $prestamoService,
        private CodigoQrService $codigoQrService,
        private SancionService $sancionService,
    ) {}

    /**
     * Préstamos propios: activos y próximos, o historial.
     *
     * @param  Request  $request  Pestaña elegida.
     * @return View
     */
    public function mis(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $historial = $request->query('vista') === 'historial';
        $base = Prestamo::query()->where('id_usuario', $usuario->id_usuario);

        return view('prestamos.mis', [
            'historial' => $historial,
            'abiertos' => (clone $base)->with('detalles.recurso.area')
                ->whereIn('estado', [EstadoPrestamo::Pendiente->value, EstadoPrestamo::Activo->value])
                ->orderBy('fecha_hora_inicio')
                ->get(),
            'cerrados' => (clone $base)->with('detalles.recurso.area')
                ->whereNotIn('estado', [EstadoPrestamo::Pendiente->value, EstadoPrestamo::Activo->value])
                ->orderByDesc('fecha_hora_inicio')
                ->paginate(10)
                ->withQueryString(),
            'totalCerrados' => (clone $base)
                ->whereNotIn('estado', [EstadoPrestamo::Pendiente->value, EstadoPrestamo::Activo->value])->count(),
            'estadisticas' => $this->calcularEstadisticas($usuario),
            // RF-051: el historial personal incluye las sanciones (MBD Tabla 7).
            'penalizaciones' => Penalizacion::query()->where('id_usuario', $usuario->id_usuario)
                ->orderByDesc('fecha_creacion')->limit(5)->get(),
            'umbral' => (int) $this->sancionService->obtenerMatriz($usuario->id_institucion)
                ->where('activo', true)->min('umbral_bloqueo'),
            'usuario' => $usuario->fresh() ?? $usuario,
        ]);
    }

    /**
     * Asistente de nueva solicitud con los recursos visibles en el catálogo.
     *
     * @param  Request  $request  Recurso preseleccionado opcional.
     * @return View
     */
    public function create(Request $request): View
    {
        $recursos = Recurso::query()->visiblesEnCatalogo()->with('area')
            ->orderBy('id_area')
            ->orderBy('nombre_recurso')
            ->get();

        return view('prestamos.create', [
            'recursosPorArea' => $recursos->groupBy(fn (Recurso $recurso): string => $recurso->area->nombre_area),
            'seleccionados' => array_map(
                'intval',
                (array) $request->old('recursos', array_filter([$request->integer('recurso')])),
            ),
            'tolerancia' => max(15, min(20, (int) config('spei.prestamos.tolerancia_recogida'))),
            'horas' => $this->armarHoras(),
            'fechaMaxima' => today()->addDays(60)->toDateString(),
        ]);
    }

    /**
     * Reserva los recursos; el servicio vuelve a validar disponibilidad dentro de la transacción.
     *
     * @param  SolicitarPrestamoRequest  $request  Datos validados.
     * @return RedirectResponse
     */
    public function store(SolicitarPrestamoRequest $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $prestamo = $this->prestamoService->solicitar($usuario, $request->datosServicio());

        return redirect()->route('prestamos.mis')
            ->with('success', 'Solicitud ' . $prestamo->obtenerFolio() . ' registrada correctamente.');
    }

    /**
     * Emite un código de un solo uso; JSON para el modal o página completa sin JavaScript.
     *
     * @param  Request  $request  Petición del propietario.
     * @param  Prestamo  $prestamo  Préstamo propio.
     * @return JsonResponse|Response
     */
    public function token(Request $request, Prestamo $prestamo): JsonResponse|Response
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $codigo = $this->prestamoService->generarToken($usuario, $prestamo);
        $ttl = max(30, min(60, (int) config('spei.qr.ttl')));
        $datos = [
            'codigo' => $codigo,
            'svg' => $this->codigoQrService->dibujar($codigo),
            'expira_en' => now()->addSeconds($ttl)->toIso8601String(),
            'ttl' => $ttl,
            'tipo' => $prestamo->estado === EstadoPrestamo::Pendiente->value ? 'entrega' : 'devolucion',
        ];
        $respuesta = $request->expectsJson()
            ? response()->json($datos)
            : response()->view('prestamos.token', ['prestamo' => $prestamo] + $datos);

        // El código es secreto y de un solo uso: no debe quedar en cachés ni en el historial.
        return $respuesta->header('Cache-Control', 'no-store, private')->header('Referrer-Policy', 'no-referrer');
    }

    /**
     * Marcas, préstamos de los últimos seis meses y porcentaje de devoluciones a tiempo.
     *
     * @param  Usuario  $usuario  Usuario final.
     * @return array{prestamos: int, a_tiempo: int|null}
     */
    private function calcularEstadisticas(Usuario $usuario): array
    {
        $base = Prestamo::query()->where('id_usuario', $usuario->id_usuario);
        $devueltos = (clone $base)->where('estado', EstadoPrestamo::Devuelto->value)->count();
        $aTiempo = (clone $base)->where('estado', EstadoPrestamo::Devuelto->value)
            ->whereColumn('fecha_hora_devolucion_real', '<=', 'fecha_hora_fin')
            ->count();

        return [
            'prestamos' => (clone $base)
                ->whereIn('estado', [EstadoPrestamo::Activo->value, EstadoPrestamo::Devuelto->value])
                ->where('fecha_hora_inicio', '>=', now()->subMonths(6))
                ->count(),
            'a_tiempo' => $devueltos === 0 ? null : (int) round($aTiempo * 100 / $devueltos),
        ];
    }

    /**
     * Horas seleccionables cada 30 minutos dentro del horario operativo (7:00 a 22:00).
     *
     * @return list<string>
     */
    private function armarHoras(): array
    {
        $horas = [];

        for ($minuto = 7 * 60; $minuto <= 22 * 60; $minuto += 30) {
            $horas[] = sprintf('%02d:%02d', intdiv($minuto, 60), $minuto % 60);
        }

        return $horas;
    }
}
