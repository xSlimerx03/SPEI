<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.8: Historias, Reportes e Inteligencia Operativa
 * @file         AuditoriaController.php
 * @path         app/Http/Controllers/Web/AuditoriaController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Consulta y exportación de la bitácora inalterable del tenant
 *               (prototipo hi-fi): filtros por texto, acción, tabla y fecha.
 *               Solo el administrador institucional y el administrador del
 *               sistema sin área asignada la consultan.
 *
 * @requisitos   RF-052, RF-053, RNF-005, RS-006
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Auditoría); DT-010, DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LogAuditoria;
use App\Models\Usuario;
use App\Support\ResumenAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Bitácora de auditoría.
 *
 * @package App\Http\Controllers\Web
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AuditoriaController extends Controller
{
    /**
     * Lista los eventos del tenant con filtros.
     *
     * @param  Request  $request  Filtros.
     * @return View
     */
    public function index(Request $request): View
    {
        $filtros = $this->validarAcceso($request);
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $tabla = LogAuditoria::query()->where('id_institucion', $usuario->id_institucion)->toBase();

        return view('auditoria.index', [
            'eventos' => $this->consultar($usuario, $filtros)->paginate(25)->withQueryString(),
            'acciones' => (clone $tabla)->distinct()->orderBy('accion')->pluck('accion'),
            'tablas' => (clone $tabla)->distinct()->orderBy('tabla_afectada')->pluck('tabla_afectada'),
            'filtros' => $filtros,
        ]);
    }

    /**
     * Exporta a CSV los eventos filtrados.
     *
     * @param  Request  $request  Filtros.
     * @return StreamedResponse
     */
    public function exportar(Request $request): StreamedResponse
    {
        $filtros = $this->validarAcceso($request);
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $consulta = $this->consultar($usuario, $filtros);

        return response()->streamDownload(function () use ($consulta): void {
            $salida = fopen('php://output', 'w');

            if ($salida === false) {
                return;
            }

            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Fecha y hora', 'Usuario', 'Acción', 'Tabla', 'Registro', 'Detalle', 'IP'], ',', '"', '');

            foreach ($consulta->lazy(200) as $evento) {
                $fila = [
                    $evento->fecha_hora->format('Y-m-d H:i:s'),
                    $evento->id_usuario === null ? 'Sistema' : $evento->usuario->nombre_completo,
                    $evento->accion,
                    $evento->tabla_afectada,
                    ResumenAuditoria::registro($evento),
                    ResumenAuditoria::detalle($evento),
                    (string) $evento->direccion_ip,
                ];
                fputcsv($salida, array_map(
                    fn (string $valor): string => preg_match('/^[=+@\-\t\r\n]/u', $valor) === 1 ? "'" . $valor : $valor,
                    $fila,
                ), ',', '"', '');
            }

            fclose($salida);
        }, 'auditoria-' . today()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Valida el rol y los filtros; un responsable de área no ve la bitácora de toda la institución.
     *
     * @param  Request  $request  Petición.
     * @return array<string, mixed>
     */
    private function validarAcceso(Request $request): array
    {
        $usuario = $request->user();
        abort_if(! $usuario instanceof Usuario || $usuario->esResponsableDeArea(), 403);

        return $request->validate([
            'texto' => ['nullable', 'string', 'max:100'],
            'accion' => ['nullable', 'string', 'max:100'],
            'tabla' => ['nullable', 'string', 'max:100'],
            'fecha' => ['nullable', 'date_format:Y-m-d'],
        ]);
    }

    /**
     * Eventos del tenant con los filtros aplicados, más recientes primero.
     *
     * NOTE: el filtro de institución se repite explícito porque la exportación se ejecuta al enviar la
     * respuesta; si el contexto del Global Scope ya se limpió, la consulta no debe abarcar otros tenants.
     *
     * @param  Usuario  $usuario  Usuario autenticado.
     * @param  array<string, mixed>  $filtros  Filtros validados.
     * @return Builder<LogAuditoria>
     */
    private function consultar(Usuario $usuario, array $filtros): Builder
    {
        return LogAuditoria::query()
            ->where('id_institucion', $usuario->id_institucion)
            ->with('usuario')
            ->when($filtros['accion'] ?? null, fn (Builder $q, string $accion): Builder => $q->where('accion', $accion))
            ->when(
                $filtros['tabla'] ?? null,
                fn (Builder $q, string $tabla): Builder => $q->where('tabla_afectada', $tabla),
            )
            ->when(
                $filtros['fecha'] ?? null,
                fn (Builder $q, string $fecha): Builder => $q->whereDate('fecha_hora', $fecha),
            )
            ->when(
                $filtros['texto'] ?? null,
                fn (Builder $q, string $texto): Builder => $this->filtrarTexto($q, $texto),
            )
            ->orderByDesc('fecha_hora')
            ->orderByDesc('id_log');
    }

    /**
     * Busca el texto en acción, tabla, nombre del usuario o número de registro (folio).
     *
     * @param  Builder<LogAuditoria>  $consulta  Consulta en construcción.
     * @param  string  $texto  Texto capturado.
     * @return Builder<LogAuditoria>
     */
    private function filtrarTexto(Builder $consulta, string $texto): Builder
    {
        $patron = '%' . addcslashes($texto, '%_\\') . '%';
        $numero = (int) (preg_replace('/\D/', '', $texto) ?? '');

        return $consulta->where(function (Builder $q) use ($patron, $numero): void {
            $q->where('accion', 'like', $patron)
                ->orWhere('tabla_afectada', 'like', $patron)
                ->orWhereHas('usuario', fn (Builder $u): Builder => $u->where('nombre_completo', 'like', $patron));

            if ($numero > 0) {
                $q->orWhere('registro_id', $numero);
            }
        });
    }
}
