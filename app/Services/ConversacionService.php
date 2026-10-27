<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         ConversacionService.php
 * @path         app/Services/ConversacionService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Canal de diálogo previo a la entrega (RF-033): mensajes entre el
 *               solicitante y los operadores del área mientras la solicitud
 *               está pendiente, con aviso en el sistema por cada mensaje
 *               (RNF-004) y marca de leído.
 *
 * @requisitos   RF-033, RNF-004, RS-006
 *
 * @see          CU-022
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\Rol;
use App\Models\MensajeConversacion;
use App\Models\Prestamo;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Mensajes de la conversación de un préstamo.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ConversacionService
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
     * Mensajes del préstamo en orden cronológico.
     *
     * @param  Prestamo  $prestamo  Préstamo visible para el usuario.
     * @return Collection<int, MensajeConversacion>
     */
    public function listar(Prestamo $prestamo): Collection
    {
        return MensajeConversacion::query()
            ->with('emisor')
            ->where('id_prestamo', $prestamo->id_prestamo)
            ->orderBy('fecha_hora')
            ->orderBy('id_mensaje')
            ->get();
    }

    /**
     * Registra el mensaje y avisa a la otra parte (RNF-004).
     *
     * @param  Usuario  $emisor  Propietario u operador del área.
     * @param  Prestamo  $prestamo  Préstamo pendiente.
     * @param  string  $texto  Mensaje validado.
     * @return MensajeConversacion
     *
     * @throws AuthorizationException Si el préstamo no está pendiente o el emisor no participa.
     */
    public function enviar(Usuario $emisor, Prestamo $prestamo, string $texto): MensajeConversacion
    {
        Gate::forUser($emisor)->authorize('conversar', $prestamo);

        return DB::transaction(function () use ($emisor, $prestamo, $texto): MensajeConversacion {
            $mensaje = MensajeConversacion::query()->create([
                'id_institucion' => $prestamo->id_institucion,
                'id_prestamo' => $prestamo->id_prestamo,
                'id_usuario_emisor' => $emisor->id_usuario,
                'mensaje' => $texto,
                'leido' => false,
            ]);

            foreach ($this->obtenerDestinatarios($emisor, $prestamo) as $destinatario) {
                $this->notificador->notificar(
                    $destinatario,
                    'Mensaje sobre ' . $prestamo->obtenerFolio(),
                    $emisor->nombre_completo . ': ' . mb_strimwidth($texto, 0, 180, '…'),
                    route('prestamos.show', $prestamo),
                    false,
                );
            }

            return $mensaje;
        });
    }

    /**
     * Marca como leídos los mensajes que el lector recibió.
     *
     * @param  Usuario  $lector  Usuario que abre la conversación.
     * @param  Prestamo  $prestamo  Préstamo.
     * @return void
     */
    public function marcarLeidos(Usuario $lector, Prestamo $prestamo): void
    {
        MensajeConversacion::query()
            ->where('id_prestamo', $prestamo->id_prestamo)
            ->where('id_usuario_emisor', '!=', $lector->id_usuario)
            ->where('leido', false)
            ->update(['leido' => true]);
    }

    /**
     * La otra parte: si escribe el solicitante, los operadores activos del área; si escribe un operador, el
     * solicitante.
     *
     * @param  Usuario  $emisor  Quien escribe.
     * @param  Prestamo  $prestamo  Préstamo.
     * @return Collection<int, Usuario>
     */
    private function obtenerDestinatarios(Usuario $emisor, Prestamo $prestamo): Collection
    {
        if ($emisor->id_usuario !== $prestamo->id_usuario) {
            return Usuario::query()->whereKey($prestamo->id_usuario)->get();
        }

        $idArea = (int) $prestamo->detalles()->with('recurso')->first()?->recurso?->id_area;

        return Usuario::query()
            ->where('activo', true)
            ->where(fn (Builder $q): Builder => $q
                ->where(fn (Builder $a): Builder => $a->where('rol', Rol::AdminSistema)
                    ->where(fn (Builder $z): Builder => $z->whereNull('id_area_asignada')
                        ->orWhere('id_area_asignada', $idArea)))
                ->orWhere(fn (Builder $a): Builder => $a->where('rol', Rol::Asistente)
                    ->where('id_area_asignada', $idArea)))
            ->get();
    }
}
