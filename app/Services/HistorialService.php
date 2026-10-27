<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.8: Historias, Reportes e Inteligencia Operativa
 * @file         HistorialService.php
 * @path         app/Services/HistorialService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Ciclo de vida del historial (RN-012): los préstamos cerrados
 *               de más de un año se comprimen por institución y mes en un
 *               archivo JSON gzip, se registra el archivo en
 *               historial_comprimido y se eliminan sus filas detalladas; el
 *               archivo se borra tres años después (cuatro en total). La
 *               bitácora logs_auditoria se conserva (log mínimo, DT-010).
 *
 * @requisitos   RF-052, RN-012, RS-006
 *
 * @see          CU-023; MBD 4.14
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\HistorialComprimido;
use App\Models\Prestamo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Compresión y eliminación programada del historial.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class HistorialService
{
    /**
     * Estados de un préstamo ya cerrado.
     */
    private const CERRADOS = ['devuelto', 'cancelado', 'vencido'];

    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  AuditoriaService  $auditoriaService  Dependencia inyectada.
     */
    public function __construct(
        private AuditoriaService $auditoriaService,
    ) {}

    /**
     * Comprime los meses completos anteriores al corte (sin sesión: recorre todas las instituciones).
     *
     * @return int Archivos creados.
     */
    public function comprimir(): int
    {
        $corte = now()->subMonths((int) config('spei.historial.meses_compresion'))->startOfMonth();
        $grupos = Prestamo::withoutGlobalScopes()
            ->whereIn('estado', self::CERRADOS)
            ->where('fecha_hora_fin', '<', $corte)
            ->selectRaw("id_institucion, DATE_FORMAT(fecha_hora_fin, '%Y-%m') AS mes")
            ->groupBy('id_institucion', 'mes')
            ->toBase()
            ->get();
        $total = 0;

        foreach ($grupos as $grupo) {
            $total += (int) $this->comprimirMes((int) $grupo->id_institucion, (string) $grupo->mes);
        }

        return $total;
    }

    /**
     * Borra los archivos cuya fecha de eliminación ya llegó; el registro queda con eliminado = 1.
     *
     * @return int Archivos eliminados.
     */
    public function eliminarVencidos(): int
    {
        $vencidos = HistorialComprimido::withoutGlobalScopes()
            ->where('eliminado', false)
            ->where('fecha_eliminacion_programada', '<=', today())
            ->get();

        foreach ($vencidos as $historial) {
            Storage::disk($this->obtenerDisco())->delete($historial->archivo_comprimido_url);
            $historial->update(['eliminado' => true]);
            $this->auditoriaService->registrarMasivo(
                'ELIMINAR_HISTORIAL',
                'historial_comprimido',
                $historial->id_historial,
                $historial->id_institucion,
                ['archivo' => $historial->archivo_comprimido_url],
            );
        }

        return $vencidos->count();
    }

    /**
     * Comprime un mes de una institución; si la transacción falla se borra el archivo escrito.
     *
     * @param  int  $idInstitucion  Tenant.
     * @param  string  $mes  Mes en formato AAAA-MM.
     * @return bool Verdadero si creó un archivo.
     *
     * @throws QueryException Si falla la transacción.
     */
    private function comprimirMes(int $idInstitucion, string $mes): bool
    {
        $inicio = Carbon::createFromFormat('Y-m-d', $mes . '-01')?->startOfDay() ?? now()->startOfMonth();
        $fin = $inicio->copy()->endOfMonth();
        $registrado = HistorialComprimido::withoutGlobalScopes()->where('id_institucion', $idInstitucion)
            ->whereDate('periodo_inicio', $inicio)->exists();

        if ($registrado) {
            return false;
        }

        $prestamos = Prestamo::withoutGlobalScopes()->with(['usuario', 'detalles'])
            ->where('id_institucion', $idInstitucion)
            ->whereIn('estado', self::CERRADOS)
            ->whereBetween('fecha_hora_fin', [$inicio, $fin])
            ->get();
        $ruta = 'historial/' . $idInstitucion . '/' . $mes . '.json.gz';
        $contenido = json_encode($prestamos->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        Storage::disk($this->obtenerDisco())->put($ruta, (string) gzencode($contenido, 9));

        try {
            DB::transaction(function () use ($idInstitucion, $inicio, $fin, $ruta, $prestamos): void {
                $this->registrarCompresion($idInstitucion, $inicio, $fin, $ruta, $prestamos->modelKeys());
            });
        } catch (QueryException $excepcion) {
            Storage::disk($this->obtenerDisco())->delete($ruta);

            throw $excepcion;
        }

        return true;
    }

    /**
     * Registra el archivo y elimina las filas detalladas (las cascadas de MBD Tabla 27 eliminan detalles, tokens
     * y mensajes; las penalizaciones conservan la sanción con id_prestamo nulo).
     *
     * @param  int  $idInstitucion  Tenant.
     * @param  Carbon  $inicio  Inicio del mes.
     * @param  Carbon  $fin  Fin del mes.
     * @param  string  $ruta  Archivo escrito.
     * @param  list<int>  $ids  Préstamos comprimidos.
     * @return void
     */
    private function registrarCompresion(
        int $idInstitucion,
        Carbon $inicio,
        Carbon $fin,
        string $ruta,
        array $ids,
    ): void {
        $historial = HistorialComprimido::withoutGlobalScopes()->create([
            'id_institucion' => $idInstitucion,
            'archivo_comprimido_url' => $ruta,
            'periodo_inicio' => $inicio->toDateString(),
            'periodo_fin' => $fin->toDateString(),
            'fecha_eliminacion_programada' => $fin->copy()
                ->addYears((int) config('spei.historial.anios_conservacion_archivo'))->toDateString(),
            'eliminado' => false,
        ]);
        Prestamo::withoutGlobalScopes()->whereKey($ids)->delete();
        $this->auditoriaService->registrarMasivo('COMPRIMIR_HISTORIAL', 'historial_comprimido',
            $historial->id_historial, $idInstitucion, ['prestamos' => count($ids), 'archivo' => $ruta]);
    }

    /**
     * Disco de almacenamiento del historial (S3/MinIO en producción, local en desarrollo).
     *
     * @return string
     */
    private function obtenerDisco(): string
    {
        return (string) config('spei.historial.disco');
    }
}
