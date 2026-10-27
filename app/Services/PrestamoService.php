<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas / 4.5: Entrega y Devolución
 * @file         PrestamoService.php
 * @path         app/Services/PrestamoService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-27
 * @version      v1.2.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Reserva de recursos por periodo con prioridad RN-008, emisión de
 *               códigos QR de un solo uso, confirmación de entrega y
 *               devolución, cancelación y vencimiento de reservas. Cada
 *               movimiento se serializa por institución con un lock de Redis y
 *               se ejecuta en una transacción con lockForUpdate (MD 5.6).
 *
 * @requisitos   RF-022, RF-025, RF-026, RF-027, RF-028, RF-032, RF-034, RF-035, RF-036, RF-037, RF-047,
 *               RF-042, RF-048, RF-049, RN-004, RN-008, RS-006
 *
 * @see          docs/plan/BBB_MVP_v1.0.md; DT-033; RED-01 del 25/10/2026 (H-10, H-11)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoPrestamo;
use App\Enums\EstadoRecurso;
use App\Enums\Rol;
use App\Exceptions\ReglaNegocioException;
use App\Models\Prestamo;
use App\Models\Recurso;
use App\Models\TokenQr;
use App\Models\Usuario;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Transiciones del préstamo y del inventario en una misma transacción.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class PrestamoService
{
    private const MAXIMO_RECURSOS = 10;

    /**
     * @param  AuditoriaService  $auditoria  Registro de la bitácora inalterable.
     * @param  ReservaService  $reservaService  Disponibilidad por periodo y RN-008.
     * @param  NotificadorService  $notificador  Avisos en el sistema y por correo.
     * @param  SancionService  $sancionService  Marcas automáticas al cerrar con incidencias.
     */
    public function __construct(
        private AuditoriaService $auditoria,
        private ReservaService $reservaService,
        private NotificadorService $notificador,
        private SancionService $sancionService,
    ) {}

    /**
     * Registra una solicitud y reserva los recursos para el periodo elegido (MD 5.6). Si el periodo choca con
     * reservas concurrentes de menor prioridad (RN-008), estas se desplazan; si no, se rechaza.
     *
     * @param  Usuario  $actor  Alumno o docente que solicita.
     * @param  array{recursos: list<int|string>, fecha_hora_inicio: string, fecha_hora_fin: string,
     *     motivo: string}  $datos  Datos validados de la solicitud.
     * @return Prestamo
     *
     * @throws ReglaNegocioException Si el solicitante, el horario o los recursos no cumplen las reglas.
     */
    public function solicitar(Usuario $actor, array $datos): Prestamo
    {
        abort_unless($actor->rol->esUsuarioFinal() && $actor->activo, 403);

        return $this->ejecutar((int) $actor->id_institucion, function () use ($actor, $datos): Prestamo {
            $usuario = Usuario::query()->lockForUpdate()->findOrFail($actor->id_usuario);
            $this->validarSolicitante($usuario, $datos);
            $ids = array_unique(array_map('intval', $datos['recursos']));
            $recursos = Recurso::query()
                ->where('id_institucion', $actor->id_institucion)
                ->whereIn('id_recurso', $ids)
                ->with('area')
                ->orderBy('id_recurso')
                ->lockForUpdate()
                ->get();
            $this->validarRecursos($recursos, count($ids));
            $conflictos = $this->reservaService->obtenerConflictos(
                $ids,
                Carbon::parse($datos['fecha_hora_inicio']),
                Carbon::parse($datos['fecha_hora_fin']),
            );

            foreach ($this->reservaService->arbitrar($usuario, $conflictos) as $desplazado) {
                $this->desplazar($desplazado);
            }

            $prestamo = $this->crearPrestamo($usuario, $datos, $recursos);
            $this->reservaService->activarVentana($prestamo);
            $this->registrar($prestamo, 'SOLICITAR_PRESTAMO', 'Su solicitud quedó reservada.', $actor);
            $this->avisarOperadores($prestamo, (int) $recursos->firstOrFail()->id_area);

            return $prestamo;
        });
    }

    /**
     * Emite un nuevo código y revoca todos los anteriores de ese préstamo.
     *
     * @param  Usuario  $actor  Propietario del préstamo.
     * @param  Prestamo  $prestamo  Préstamo pendiente o activo.
     * @return string Código en claro; solo se guarda su hash SHA-256.
     *
     * @throws AuthorizationException Si el actor no es el propietario.
     * @throws ReglaNegocioException Si el préstamo ya no admite movimientos o está fuera de horario.
     */
    public function generarToken(Usuario $actor, Prestamo $prestamo): string
    {
        Gate::forUser($actor)->authorize('generarToken', $prestamo);

        return $this->ejecutar($prestamo->id_institucion, function () use ($prestamo): string {
            $actual = Prestamo::query()->lockForUpdate()->findOrFail($prestamo->id_prestamo);
            $tipo = $this->obtenerTipoMovimiento($actual, 'Este préstamo ya no admite movimientos.');

            if ($tipo === 'entrega') {
                $this->validarHorarioEntrega($actual);
            }

            TokenQr::query()->where('id_prestamo', $actual->id_prestamo)->update(['usado' => true]);
            $codigo = bin2hex(random_bytes(16));
            TokenQr::query()->create([
                'id_institucion' => $actual->id_institucion,
                'id_prestamo' => $actual->id_prestamo,
                'tipo_token' => $tipo,
                'token' => hash('sha256', $codigo),
                'fecha_expiracion' => now()->addSeconds(max(30, min(60, (int) config('spei.qr.ttl')))),
            ]);

            return $codigo;
        });
    }

    /**
     * Confirma la entrega o la devolución con el código vigente del solicitante.
     *
     * @param  Usuario  $actor  Operador del área.
     * @param  Prestamo  $prestamo  Préstamo pendiente (entrega) o activo (devolución).
     * @param  array{codigo: string, condicion: string, incidencia?: string|null}  $datos  Datos validados.
     * @return Prestamo
     *
     * @throws AuthorizationException Si el actor no opera el área del préstamo.
     * @throws ReglaNegocioException Si el código, el horario o el inventario no son válidos.
     */
    public function confirmar(Usuario $actor, Prestamo $prestamo, array $datos): Prestamo
    {
        Gate::forUser($actor)->authorize('operar', $prestamo);

        return $this->ejecutar($prestamo->id_institucion, function () use ($actor, $prestamo, $datos): Prestamo {
            $actual = Prestamo::query()->lockForUpdate()->findOrFail($prestamo->id_prestamo);
            Gate::forUser($actor)->authorize('operar', $actual);
            $tipo = $this->obtenerTipoMovimiento($actual, 'El préstamo ya fue atendido o cancelado.');
            $token = $this->obtenerTokenVigente($actual, $datos['codigo'], $tipo);

            if ($tipo === 'entrega') {
                $this->validarEntrega($actual, $datos['condicion']);
            }

            $this->moverRecursos($actual, $tipo, $datos);
            $actual->update(match ($tipo) {
                'entrega' => ['estado' => EstadoPrestamo::Activo->value, 'fecha_hora_entrega_real' => now()],
                default => ['estado' => EstadoPrestamo::Devuelto->value, 'fecha_hora_devolucion_real' => now()],
            });
            $token->update(['usado' => true]);
            $mensaje = $tipo === 'entrega' ? 'Se confirmó la entrega.' : 'Se confirmó la devolución.';
            $this->registrar($actual, strtoupper($tipo), $mensaje, $actor);

            if ($tipo === 'devolucion') {
                $this->sancionService->evaluarCierre($actual);
            }

            return $actual;
        });
    }

    /**
     * Cierra un préstamo atrasado cuyo recurso no se devolvió (RF-042): condición no_entregado, recursos fuera
     * de servicio y sanción automática. Solo el administrador del sistema del área.
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @param  Prestamo  $prestamo  Préstamo activo con devolución vencida.
     * @param  string  $motivo  Justificación (≥ 20 caracteres, validada en el request).
     * @return Prestamo
     *
     * @throws AuthorizationException Si el actor no puede cerrar el préstamo.
     * @throws ReglaNegocioException Si el préstamo no está activo o aún no vence su devolución.
     */
    public function cerrarNoDevuelto(Usuario $actor, Prestamo $prestamo, string $motivo): Prestamo
    {
        Gate::forUser($actor)->authorize('cerrarNoDevuelto', $prestamo);

        return $this->ejecutar($prestamo->id_institucion, function () use ($actor, $prestamo, $motivo): Prestamo {
            $actual = Prestamo::query()->lockForUpdate()->findOrFail($prestamo->id_prestamo);

            if ($actual->estado !== EstadoPrestamo::Activo->value || $actual->fecha_hora_fin->isFuture()) {
                throw new ReglaNegocioException(
                    'Solo se cierra como no devuelto un préstamo activo con devolución vencida.',
                );
            }

            foreach ($actual->detalles()->orderBy('id_recurso')->get() as $detalle) {
                Recurso::query()->lockForUpdate()->whereKey($detalle->id_recurso)
                    ->update(['estado' => EstadoRecurso::Mantenimiento]);
                $detalle->update(['condicion_devolucion' => 'no_entregado', 'incidencia' => $motivo]);
            }

            $actual->update(['estado' => EstadoPrestamo::Devuelto->value, 'fecha_hora_devolucion_real' => now()]);
            TokenQr::query()->where('id_prestamo', $actual->id_prestamo)->update(['usado' => true]);
            $this->registrar($actual, 'CERRAR_NO_DEVUELTO', 'El préstamo se cerró sin devolución: ' . $motivo, $actor);
            $this->sancionService->evaluarCierre($actual);

            return $actual;
        });
    }

    /**
     * Cancela la solicitud antes de transferir la custodia y libera los recursos.
     *
     * @param  Usuario  $actor  Propietario o administrador del sistema del área.
     * @param  Prestamo  $prestamo  Préstamo pendiente.
     * @param  string  $motivo  Justificación registrada en el préstamo y la auditoría.
     * @return void
     *
     * @throws AuthorizationException Si el actor no puede cancelar el préstamo.
     * @throws ReglaNegocioException Si el préstamo ya no está pendiente.
     */
    public function cancelar(Usuario $actor, Prestamo $prestamo, string $motivo): void
    {
        Gate::forUser($actor)->authorize('cancelar', $prestamo);
        $this->ejecutar($prestamo->id_institucion, function () use ($actor, $prestamo, $motivo): void {
            $actual = Prestamo::query()->lockForUpdate()->findOrFail($prestamo->id_prestamo);

            if ($actual->estado !== EstadoPrestamo::Pendiente->value) {
                throw new ReglaNegocioException('Solo puede cancelar un préstamo pendiente de entrega.');
            }

            $this->liberar($actual, EstadoPrestamo::Cancelado->value, $motivo);
            $this->registrar($actual, 'CANCELAR_PRESTAMO', 'Se canceló la solicitud: ' . $motivo, $actor);
        });
    }

    /**
     * Libera una reserva no recogida dentro de la tolerancia; idempotente para la tarea programada.
     *
     * @param  Prestamo  $prestamo  Préstamo candidato a vencer.
     * @return bool Verdadero si el préstamo se venció en esta ejecución.
     *
     * @throws ReglaNegocioException Si el candado de la institución no se obtiene a tiempo.
     */
    public function vencer(Prestamo $prestamo): bool
    {
        return $this->ejecutar($prestamo->id_institucion, function () use ($prestamo): bool {
            $actual = Prestamo::query()->lockForUpdate()->findOrFail($prestamo->id_prestamo);
            $limite = $actual->fecha_hora_inicio->copy()->addMinutes($this->obtenerTolerancia());

            if ($actual->estado !== EstadoPrestamo::Pendiente->value || $limite->gt(now())) {
                return false;
            }

            $this->liberar($actual, EstadoPrestamo::Vencido->value, 'No se recogió dentro del tiempo de tolerancia.');
            $this->registrar($actual, 'VENCER_PRESTAMO', 'La reserva venció porque no se recogió a tiempo.', null);

            return true;
        });
    }

    /**
     * Abre la ventana de recogida de una reserva (tarea programada); idempotente.
     *
     * @param  Prestamo  $prestamo  Reserva pendiente.
     * @return bool Verdadero si la ventana está abierta.
     *
     * @throws ReglaNegocioException Si el candado de la institución no se obtiene a tiempo.
     */
    public function activarReserva(Prestamo $prestamo): bool
    {
        return $this->ejecutar($prestamo->id_institucion, function () use ($prestamo): bool {
            $actual = Prestamo::query()->lockForUpdate()->findOrFail($prestamo->id_prestamo);

            return $this->reservaService->activarVentana($actual);
        });
    }

    /**
     * Crea el préstamo pendiente y sus detalles.
     *
     * @param  Usuario  $usuario  Solicitante.
     * @param  array{recursos: list<int|string>, fecha_hora_inicio: string, fecha_hora_fin: string,
     *     motivo: string}  $datos  Datos validados.
     * @param  Collection<int, Recurso>  $recursos  Recursos bloqueados para escritura.
     * @return Prestamo
     */
    private function crearPrestamo(Usuario $usuario, array $datos, Collection $recursos): Prestamo
    {
        $prestamo = Prestamo::query()->create([
            'id_institucion' => $usuario->id_institucion,
            'id_usuario' => $usuario->id_usuario,
            'fecha_hora_inicio' => $datos['fecha_hora_inicio'],
            'fecha_hora_fin' => $datos['fecha_hora_fin'],
            'motivo' => $datos['motivo'],
            'estado' => EstadoPrestamo::Pendiente->value,
        ]);

        foreach ($recursos as $recurso) {
            $prestamo->detalles()->create([
                'id_institucion' => $usuario->id_institucion,
                'id_recurso' => $recurso->id_recurso,
            ]);
        }

        return $prestamo;
    }

    /**
     * Cancela una reserva desplazada por RN-008 y avisa a su titular con la razón (RN-014 por analogía).
     *
     * @param  Prestamo  $prestamo  Reserva pendiente de menor prioridad, bloqueada para escritura.
     * @return void
     */
    private function desplazar(Prestamo $prestamo): void
    {
        $motivo = 'Reserva desplazada por prioridad (RN-008): otra solicitud concurrente del mismo horario tiene '
            . 'precedencia por rol o historial.';
        $this->liberar($prestamo, EstadoPrestamo::Cancelado->value, $motivo);
        $this->registrar($prestamo, 'DESPLAZAR_PRESTAMO', $motivo . ' Puede solicitar otro horario.', null);
    }

    /**
     * Rechaza cuentas bloqueadas o inactivas y periodos pasados o invertidos.
     *
     * @param  Usuario  $usuario  Solicitante bloqueado para escritura.
     * @param  array<string, mixed>  $datos  Datos de la solicitud.
     * @return void
     *
     * @throws ReglaNegocioException
     */
    private function validarSolicitante(Usuario $usuario, array $datos): void
    {
        if ($usuario->bloqueado || ! $usuario->activo) {
            throw new ReglaNegocioException('Su cuenta no puede solicitar préstamos en este momento.');
        }

        $inicio = Carbon::parse($datos['fecha_hora_inicio']);
        $fin = Carbon::parse($datos['fecha_hora_fin']);

        if ($inicio->lt(now()->startOfMinute()) || $fin->lte($inicio)) {
            throw new ReglaNegocioException('Seleccione un periodo futuro y una devolución posterior al inicio.');
        }
    }

    /**
     * Exige de uno a diez recursos disponibles de una misma área activa.
     *
     * @param  Collection<int, Recurso>  $recursos  Recursos bloqueados para escritura.
     * @param  int  $cantidad  Recursos distintos solicitados.
     * @return void
     *
     * @throws ReglaNegocioException
     */
    private function validarRecursos(Collection $recursos, int $cantidad): void
    {
        $unaArea = $recursos->pluck('id_area')->unique()->count() === 1;

        if ($cantidad === 0 || $cantidad > self::MAXIMO_RECURSOS || $recursos->count() !== $cantidad || ! $unaArea) {
            throw new ReglaNegocioException('Seleccione entre uno y diez recursos de una misma área.');
        }

        foreach ($recursos as $recurso) {
            if (! $recurso->activo || $recurso->estado === EstadoRecurso::Mantenimiento || ! $recurso->area->activo) {
                throw new ReglaNegocioException('Uno de los recursos ya no está disponible. Actualice el catálogo.');
            }
        }
    }

    /**
     * Movimiento que corresponde al estado actual del préstamo.
     *
     * @param  Prestamo  $prestamo  Préstamo bloqueado para escritura.
     * @param  string  $mensaje  Motivo de rechazo si ya no admite movimientos.
     * @return string 'entrega' o 'devolucion'.
     *
     * @throws ReglaNegocioException
     */
    private function obtenerTipoMovimiento(Prestamo $prestamo, string $mensaje): string
    {
        return match ($prestamo->estado) {
            EstadoPrestamo::Pendiente->value => 'entrega',
            EstadoPrestamo::Activo->value => 'devolucion',
            default => throw new ReglaNegocioException($mensaje),
        };
    }

    /**
     * Código vigente, sin usar y del tipo esperado para el préstamo.
     *
     * @param  Prestamo  $prestamo  Préstamo bloqueado para escritura.
     * @param  string  $codigo  Código en claro capturado en mostrador.
     * @param  string  $tipo  Movimiento esperado.
     * @return TokenQr
     *
     * @throws ReglaNegocioException
     */
    private function obtenerTokenVigente(Prestamo $prestamo, string $codigo, string $tipo): TokenQr
    {
        $token = TokenQr::query()
            ->where('id_prestamo', $prestamo->id_prestamo)
            ->where('token', hash('sha256', $codigo))
            ->lockForUpdate()
            ->first();

        if (! $token || $token->usado || $token->fecha_expiracion->lte(now()) || $token->tipo_token !== $tipo) {
            throw new ReglaNegocioException('El código no es válido, ya se utilizó o ha caducado.');
        }

        return $token;
    }

    /**
     * Valida el horario y la condición con la que sale el recurso.
     *
     * @param  Prestamo  $prestamo  Préstamo pendiente.
     * @param  string  $condicion  Condición capturada en mostrador.
     * @return void
     *
     * @throws ReglaNegocioException
     */
    private function validarEntrega(Prestamo $prestamo, string $condicion): void
    {
        $this->validarHorarioEntrega($prestamo);

        if (! in_array($condicion, ['bueno', 'regular', 'malo'], true)) {
            throw new ReglaNegocioException('Seleccione una condición de entrega válida.');
        }
    }

    /**
     * Impide la entrega anticipada o fuera de tolerancia aunque la tarea programada esté detenida.
     *
     * @param  Prestamo  $prestamo  Préstamo pendiente.
     * @return void
     *
     * @throws ReglaNegocioException
     */
    private function validarHorarioEntrega(Prestamo $prestamo): void
    {
        $limite = $prestamo->fecha_hora_inicio->copy()->addMinutes($this->obtenerTolerancia());

        if (now()->lt($prestamo->fecha_hora_inicio) || now()->gte($limite) || now()->gte($prestamo->fecha_hora_fin)) {
            throw new ReglaNegocioException(
                'La entrega solo puede confirmarse desde el inicio y dentro de la tolerancia de recogida.',
            );
        }
    }

    /**
     * Cambia el estado de cada recurso y registra su condición; un recurso dañado pasa a mantenimiento.
     *
     * @param  Prestamo  $prestamo  Préstamo bloqueado para escritura.
     * @param  string  $tipo  'entrega' o 'devolucion'.
     * @param  array{codigo: string, condicion: string, incidencia?: string|null}  $datos  Datos validados.
     * @return void
     *
     * @throws ReglaNegocioException Si el inventario cambió fuera del préstamo.
     */
    private function moverRecursos(Prestamo $prestamo, string $tipo, array $datos): void
    {
        // La ventana pudo abrirse sin que la tarea programada la marcara aún: disponible también es válido.
        $esperados = $tipo === 'entrega'
            ? [EstadoRecurso::Bloqueado, EstadoRecurso::Disponible]
            : [EstadoRecurso::Prestado];
        $destino = match (true) {
            $tipo === 'entrega' => EstadoRecurso::Prestado,
            in_array($datos['condicion'], ['malo', 'danado'], true) => EstadoRecurso::Mantenimiento,
            default => EstadoRecurso::Disponible,
        };

        foreach ($prestamo->detalles()->orderBy('id_recurso')->get() as $detalle) {
            $recurso = Recurso::query()->lockForUpdate()->findOrFail($detalle->id_recurso);

            if (! in_array($recurso->estado, $esperados, true)) {
                throw new ReglaNegocioException(
                    'El estado del inventario cambió. Revise el recurso antes de continuar.',
                );
            }

            $recurso->update(['estado' => $destino]);
            $detalle->update([
                'condicion_' . $tipo => $datos['condicion'],
                'incidencia' => $datos['incidencia'] ?? null,
            ]);
            $this->reservaService->sincronizarRecurso($recurso->id_recurso);
        }
    }

    /**
     * Libera los recursos bloqueados y revoca los códigos dentro de la transacción del préstamo.
     *
     * @param  Prestamo  $prestamo  Préstamo bloqueado para escritura.
     * @param  string  $estado  Estado final (cancelado o vencido).
     * @param  string  $motivo  Justificación registrada.
     * @return void
     */
    private function liberar(Prestamo $prestamo, string $estado, string $motivo): void
    {
        // Una reserva futura no bloqueó nada: liberar sus recursos afectaría a la reserva que sí está en ventana.
        if ($prestamo->fecha_hora_inicio->lte(now())) {
            Recurso::query()->whereIn('id_recurso', $prestamo->detalles()->pluck('id_recurso'))
                ->where('estado', EstadoRecurso::Bloqueado)
                ->update(['estado' => EstadoRecurso::Disponible]);
        }

        TokenQr::query()->where('id_prestamo', $prestamo->id_prestamo)->update(['usado' => true]);
        $prestamo->update(['estado' => $estado, 'motivo_cancelacion' => $motivo]);
    }

    /**
     * Registra el cambio en auditoría (sin códigos de custodia) y avisa al solicitante.
     *
     * @param  Prestamo  $prestamo  Préstamo modificado.
     * @param  string  $accion  Acción de auditoría.
     * @param  string  $mensaje  Texto de la notificación.
     * @param  Usuario|null  $actor  Responsable; null para la tarea programada.
     * @return void
     */
    private function registrar(Prestamo $prestamo, string $accion, string $mensaje, ?Usuario $actor): void
    {
        $this->auditoria->registrar($accion, $prestamo, null, $this->auditoria->capturar($prestamo), $actor);
        $this->notificador->notificar(
            Usuario::query()->findOrFail($prestamo->id_usuario),
            'Préstamo ' . $prestamo->obtenerFolio(),
            $mensaje,
            route('prestamos.show', $prestamo),
        );
    }

    /**
     * Avisa solamente a los administradores del sistema con acceso al área.
     *
     * @param  Prestamo  $prestamo  Solicitud registrada.
     * @param  int  $idArea  Área de los recursos solicitados.
     * @return void
     */
    private function avisarOperadores(Prestamo $prestamo, int $idArea): void
    {
        $usuarios = Usuario::query()
            ->where('id_institucion', $prestamo->id_institucion)
            ->where('activo', true)
            ->where('rol', Rol::AdminSistema)
            ->where(fn (Builder $q): Builder => $q->whereNull('id_area_asignada')->orWhere('id_area_asignada', $idArea))
            ->get();

        foreach ($usuarios as $usuario) {
            // RF-049: alerta en el sistema; el correo se reserva al solicitante para no saturar al operador.
            $this->notificador->notificar(
                $usuario,
                'Solicitud pendiente ' . $prestamo->obtenerFolio(),
                'Hay una nueva solicitud de préstamo en su área.',
                route('prestamos.index', ['p' => $prestamo->id_prestamo]),
                false,
            );
        }
    }

    /**
     * Tolerancia de recogida acotada a 15–20 minutos (MD 5.6).
     *
     * @return int
     */
    private function obtenerTolerancia(): int
    {
        return max(15, min(20, (int) config('spei.prestamos.tolerancia_recogida')));
    }

    /**
     * Serializa los movimientos del tenant y revierte todos los cambios ante errores.
     *
     * @template TResultado
     *
     * @param  int  $institucion  Tenant cuyo candado se toma.
     * @param  Closure(): TResultado  $operacion  Operación transaccional.
     * @return TResultado
     *
     * @throws ReglaNegocioException Si el candado no se obtiene a tiempo.
     */
    private function ejecutar(int $institucion, Closure $operacion): mixed
    {
        try {
            return Cache::lock('prestamos:institucion:' . $institucion, 30)
                ->block(5, fn (): mixed => DB::transaction($operacion, 3));
        } catch (LockTimeoutException) {
            throw new ReglaNegocioException('Hay otra operación en curso. Intente de nuevo en unos segundos.');
        }
    }
}
