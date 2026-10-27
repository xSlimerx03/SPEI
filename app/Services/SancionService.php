<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.6: Penalizaciones y Sanciones Automatizadas
 * @file         SancionService.php
 * @path         app/Services/SancionService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Sanciones automatizadas: matriz por institución (RF-041),
 *               marcas al cerrar un préstamo con incidencias (RF-042, RN-005),
 *               bloqueo al alcanzar el umbral (RF-043), exención justificada
 *               (RF-044), aviso al usuario (RF-045) y levantamiento al cumplir
 *               la sanción. Las marcas las suma y resta un trigger de BD.
 *
 * @requisitos   RF-041, RF-042, RF-043, RF-044, RF-045, RN-003, RN-005, RS-006
 *
 * @see          CU-019, CU-020; DT-036
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\TipoIncidente;
use App\Exceptions\ReglaNegocioException;
use App\Models\MatrizSancion;
use App\Models\Penalizacion;
use App\Models\Prestamo;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Matriz, marcas, bloqueos y exenciones.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class SancionService
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  AuditoriaService  $auditoriaService  Dependencia inyectada.
     * @param  NotificadorService  $notificador  Dependencia inyectada.
     */
    public function __construct(
        private AuditoriaService $auditoriaService,
        private NotificadorService $notificador,
    ) {}

    /**
     * Reglas de la institución por tipo; crea las faltantes con los valores de config/spei.php.
     *
     * @param  int  $idInstitucion  Tenant.
     * @return Collection<string, MatrizSancion> Clave = valor de TipoIncidente.
     */
    public function obtenerMatriz(int $idInstitucion): Collection
    {
        foreach (TipoIncidente::cases() as $tipo) {
            MatrizSancion::withoutGlobalScopes()->firstOrCreate(
                ['id_institucion' => $idInstitucion, 'tipo_incidente' => $tipo->value],
                [
                    'marcas_por_incidente' => (int) config('spei.sanciones.marcas_por_incidente.' . $tipo->value),
                    'umbral_bloqueo' => (int) config('spei.sanciones.umbral_bloqueo'),
                    'dias_sancion' => (int) config('spei.sanciones.dias_sancion'),
                    'activo' => true,
                ],
            );
        }

        return MatrizSancion::withoutGlobalScopes()
            ->where('id_institucion', $idInstitucion)
            ->get()
            ->keyBy(fn (MatrizSancion $regla): string => $regla->tipo_incidente->value);
    }

    /**
     * Actualiza una regla de la matriz (RF-041).
     *
     * @param  MatrizSancion  $regla  Regla de la institución.
     * @param  array{marcas_por_incidente: int, umbral_bloqueo: int, dias_sancion: int, activo: bool}  $datos
     * @return MatrizSancion
     */
    public function actualizarRegla(MatrizSancion $regla, array $datos): MatrizSancion
    {
        return DB::transaction(function () use ($regla, $datos): MatrizSancion {
            $anterior = $this->auditoriaService->capturar($regla);
            $regla->update($datos);
            $this->auditoriaService->registrar(
                'ACTUALIZAR',
                $regla,
                $anterior,
                $this->auditoriaService->capturar($regla),
            );

            return $regla;
        });
    }

    /**
     * Registra las marcas de un préstamo cerrado (RF-042): una penalización por tipo de incidente. Un recurso no
     * devuelto absorbe el retraso. Se ejecuta dentro de la transacción del cierre.
     *
     * @param  Prestamo  $prestamo  Préstamo devuelto o cerrado como no devuelto.
     * @return list<Penalizacion>
     */
    public function evaluarCierre(Prestamo $prestamo): array
    {
        $incidentes = $this->detectarIncidentes($prestamo);

        if ($incidentes === []) {
            return [];
        }

        $matriz = $this->obtenerMatriz($prestamo->id_institucion);
        $penalizaciones = [];

        foreach ($incidentes as $tipo => $motivo) {
            $regla = $matriz->get($tipo);

            if ($regla === null || ! $regla->activo || $regla->marcas_por_incidente === 0) {
                continue;
            }

            $penalizaciones[] = $this->aplicar($prestamo, $regla, $motivo);
        }

        return $penalizaciones;
    }

    /**
     * Exenta una penalización con justificación (RF-044); el trigger resta sus marcas y, si el usuario queda
     * por debajo del umbral, se levanta el bloqueo.
     *
     * @param  Penalizacion  $penalizacion  Penalización vigente.
     * @param  string  $motivo  Justificación (≥ 20 caracteres, validada en el request).
     * @param  Usuario  $actor  Administrador del sistema.
     * @return void
     *
     * @throws ReglaNegocioException Si ya estaba exentada.
     */
    public function exentar(Penalizacion $penalizacion, string $motivo, Usuario $actor): void
    {
        if ($penalizacion->exentada) {
            throw new ReglaNegocioException('La penalización ya fue exentada.');
        }

        DB::transaction(function () use ($penalizacion, $motivo, $actor): void {
            $anterior = $this->auditoriaService->capturar($penalizacion);
            $penalizacion->update(['exentada' => true, 'motivo_exencion' => $motivo]);
            $this->auditoriaService->registrar(
                'EXENTAR',
                $penalizacion,
                $anterior,
                $this->auditoriaService->capturar($penalizacion),
                $actor,
            );
            $usuario = Usuario::query()->lockForUpdate()->findOrFail($penalizacion->id_usuario);
            $umbral = $this->obtenerMatriz($usuario->id_institucion)->get($penalizacion->tipo_penalizacion->value);

            if ($usuario->bloqueado && $usuario->marcas_acumuladas < (int) $umbral?->umbral_bloqueo) {
                $this->desbloquear($usuario, 'Se levantó tu bloqueo por la exención de una sanción.');
            }

            $this->notificador->notificar(
                $usuario,
                'Sanción exentada',
                'Se exentó la sanción "' . $penalizacion->tipo_penalizacion->obtenerEtiqueta() . '": ' . $motivo,
                route('prestamos.mis'),
            );
        });
    }

    /**
     * Levanta los bloqueos cuyo plazo ya terminó; el contador de marcas se reinicia (DT-036). Idempotente.
     *
     * @return int Usuarios desbloqueados.
     */
    public function levantarVencidas(): int
    {
        $total = 0;
        $usuarios = Usuario::withoutGlobalScopes()
            ->where('bloqueado', true)
            ->whereNotNull('fecha_fin_bloqueo')
            ->where('fecha_fin_bloqueo', '<=', now())
            ->lazyById(100, 'id_usuario');

        foreach ($usuarios as $usuario) {
            DB::transaction(function () use ($usuario): void {
                $this->desbloquear($usuario, 'Terminó tu sanción; ya puedes solicitar préstamos.');
            });
            $total++;
        }

        return $total;
    }

    /**
     * Incidentes del préstamo con su motivo, por tipo (RN-003: solo hay custodia si hubo entrega).
     *
     * @param  Prestamo  $prestamo  Préstamo cerrado.
     * @return array<string, string>
     */
    private function detectarIncidentes(Prestamo $prestamo): array
    {
        if ($prestamo->fecha_hora_entrega_real === null) {
            return [];
        }

        $incidentes = [];

        foreach ($prestamo->detalles()->with('recurso')->get() as $detalle) {
            $tipo = TipoIncidente::desdeCondicion($detalle->condicion_devolucion);

            if ($tipo !== null && ! isset($incidentes[$tipo->value])) {
                $incidentes[$tipo->value] = $tipo->obtenerEtiqueta() . ': ' . $detalle->recurso->nombre_recurso
                    . ($detalle->incidencia ? ' (' . $detalle->incidencia . ')' : '') . '.';
            }
        }

        $devolucion = $prestamo->fecha_hora_devolucion_real;
        $tarde = $devolucion !== null && $devolucion->gt($prestamo->fecha_hora_fin);

        if ($tarde && ! isset($incidentes[TipoIncidente::NoEntregado->value])) {
            $minutos = (int) $prestamo->fecha_hora_fin->diffInMinutes($devolucion);
            $incidentes[TipoIncidente::Retraso->value] = 'Devolución tardía de ' . $minutos . ' minutos.';
        }

        return $incidentes;
    }

    /**
     * Inserta la penalización (el trigger suma las marcas), bloquea si se alcanza el umbral y avisa (RF-045).
     *
     * @param  Prestamo  $prestamo  Préstamo con el incidente.
     * @param  MatrizSancion  $regla  Regla activa del tipo.
     * @param  string  $motivo  Descripción del incidente.
     * @return Penalizacion
     */
    private function aplicar(Prestamo $prestamo, MatrizSancion $regla, string $motivo): Penalizacion
    {
        $penalizacion = Penalizacion::query()->create([
            'id_institucion' => $prestamo->id_institucion,
            'id_usuario' => $prestamo->id_usuario,
            'id_prestamo' => $prestamo->id_prestamo,
            'tipo_penalizacion' => $regla->tipo_incidente,
            'motivo' => $motivo,
            'marcas_aplicadas' => $regla->marcas_por_incidente,
        ]);
        $this->auditoriaService->registrar(
            'SANCIONAR',
            $penalizacion,
            null,
            $this->auditoriaService->capturar($penalizacion),
        );
        $usuario = Usuario::query()->lockForUpdate()->findOrFail($prestamo->id_usuario);
        $mensaje = $motivo . ' Se aplicaron ' . $regla->marcas_por_incidente . ' marca(s); llevas '
            . $usuario->marcas_acumuladas . ' de ' . $regla->umbral_bloqueo . '.';

        if (! $usuario->bloqueado && $usuario->marcas_acumuladas >= $regla->umbral_bloqueo) {
            $this->bloquear($usuario, $penalizacion, $regla);
            $mensaje .= ' Tu cuenta queda bloqueada para solicitar préstamos hasta el '
                . $usuario->fecha_fin_bloqueo?->format('d/m/Y H:i') . '.';
        }

        $this->notificador->notificar(
            $usuario,
            'Sanción por ' . mb_strtolower($regla->tipo_incidente->obtenerEtiqueta()),
            $mensaje,
            route('prestamos.mis'),
        );

        return $penalizacion;
    }

    /**
     * Bloquea al usuario por los días de la regla (RF-043) y fecha la sanción en la penalización.
     *
     * @param  Usuario  $usuario  Usuario bloqueado para escritura.
     * @param  Penalizacion  $penalizacion  Penalización que alcanzó el umbral.
     * @param  MatrizSancion  $regla  Regla aplicada.
     * @return void
     */
    private function bloquear(Usuario $usuario, Penalizacion $penalizacion, MatrizSancion $regla): void
    {
        $anterior = $this->auditoriaService->capturar($usuario);
        $fin = now()->addDays($regla->dias_sancion);
        $usuario->forceFill(['bloqueado' => true, 'fecha_bloqueo' => now(), 'fecha_fin_bloqueo' => $fin])->save();
        $penalizacion->update(['fecha_inicio_sancion' => today(), 'fecha_fin_sancion' => $fin->toDateString()]);
        $this->auditoriaService->registrar(
            'BLOQUEAR',
            $usuario,
            $anterior,
            $this->auditoriaService->capturar($usuario),
        );
    }

    /**
     * Quita el bloqueo, reinicia el contador de marcas y avisa.
     *
     * @param  Usuario  $usuario  Usuario bloqueado.
     * @param  string  $mensaje  Texto del aviso.
     * @return void
     */
    private function desbloquear(Usuario $usuario, string $mensaje): void
    {
        $anterior = $this->auditoriaService->capturar($usuario);
        $usuario->forceFill([
            'bloqueado' => false,
            'fecha_fin_bloqueo' => null,
            'marcas_acumuladas' => 0,
        ])->save();
        $this->auditoriaService->registrar(
            'DESBLOQUEAR',
            $usuario,
            $anterior,
            $this->auditoriaService->capturar($usuario),
        );
        $this->notificador->notificar($usuario, 'Cuenta desbloqueada', $mensaje, route('prestamos.mis'));
    }
}
