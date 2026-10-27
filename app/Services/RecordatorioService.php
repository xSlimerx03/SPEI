<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.7: Notificaciones y Recordatorios
 * @file         RecordatorioService.php
 * @path         app/Services/RecordatorioService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Recordatorios previos de entrega y devolución (RF-046): avisa
 *               en el sistema y por correo los préstamos que inician o deben
 *               devolverse dentro de los próximos minutos configurados (10 por
 *               MBD Tabla 7). Cada aviso se envía una sola vez gracias a una
 *               clave atómica en Redis.
 *
 * @requisitos   RF-046, RF-047, RF-048, RNF-004
 *
 * @see          CU-021
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoPrestamo;
use App\Models\Prestamo;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\LazyCollection;

/**
 * Recordatorios de préstamos próximos.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class RecordatorioService
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  NotificadorService  $notificador  Dependencia inyectada.
     */
    public function __construct(
        private NotificadorService $notificador,
    ) {}

    /**
     * Envía los recordatorios que correspondan ahora; idempotente.
     *
     * @return int Recordatorios enviados.
     */
    public function enviarPendientes(): int
    {
        $limite = now()->addMinutes(max(1, min(60, (int) config('spei.notificaciones.recordatorio_minutos'))));
        $total = 0;

        foreach ($this->consultar(EstadoPrestamo::Pendiente, 'fecha_hora_inicio', $limite) as $prestamo) {
            $total += $this->recordar($prestamo, 'entrega');
        }

        foreach ($this->consultar(EstadoPrestamo::Activo, 'fecha_hora_fin', $limite) as $prestamo) {
            $total += $this->recordar($prestamo, 'devolucion');
        }

        return $total;
    }

    /**
     * Préstamos de todas las instituciones cuyo momento clave cae entre ahora y el límite (sin sesión no hay
     * contexto de tenant; cada aviso usa la institución del préstamo, MD 5.3).
     *
     * @param  EstadoPrestamo  $estado  Estado del préstamo.
     * @param  string  $columna  fecha_hora_inicio o fecha_hora_fin.
     * @param  CarbonInterface  $limite  Fin de la ventana.
     * @return LazyCollection<int, Prestamo>
     */
    private function consultar(EstadoPrestamo $estado, string $columna, CarbonInterface $limite): LazyCollection
    {
        return Prestamo::withoutGlobalScopes()
            ->with(['usuario', 'detalles.recurso.area'])
            ->where('estado', $estado->value)
            ->where($columna, '>', now())
            ->where($columna, '<=', $limite)
            ->lazyById(100, 'id_prestamo');
    }

    /**
     * Envía un recordatorio si no se envió antes.
     *
     * @param  Prestamo  $prestamo  Préstamo próximo.
     * @param  string  $tipo  entrega o devolucion.
     * @return int 1 si se envió, 0 si ya se había enviado.
     */
    private function recordar(Prestamo $prestamo, string $tipo): int
    {
        if (! Cache::add('spei:recordatorio:' . $tipo . ':' . $prestamo->id_prestamo, true, now()->addDay())) {
            return 0;
        }

        $area = $prestamo->detalles->first()?->recurso->area->nombre_area ?? 'tu área';
        $folio = $prestamo->obtenerFolio();
        [$titulo, $mensaje] = $tipo === 'entrega'
            ? ['Tu préstamo empieza pronto', sprintf(
                'El préstamo %s inicia a las %s. Recoge en el mostrador de %s y muestra tu código QR.',
                $folio,
                $prestamo->fecha_hora_inicio->format('H:i'),
                $area,
            )]
            : ['Devuelve tu préstamo', sprintf(
                'Devuelve %s antes de las %s en el mostrador de %s para evitar una marca por retraso.',
                $folio,
                $prestamo->fecha_hora_fin->format('H:i'),
                $area,
            )];

        $this->notificador->notificar($prestamo->usuario, $titulo, $mensaje, route('prestamos.mis'));

        return 1;
    }
}
