<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         InvitacionService.php
 * @path         app/Services/InvitacionService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Envío de invitaciones con enlace firmado temporal para que
 *               cualquier cuenta creada por un administrador defina su
 *               contraseña (DT-025). Lo usan administradores, usuarios finales
 *               y asistentes.
 *
 * @requisitos   RF-008, RF-016, RF-019, RF-020
 *
 * @see          DT-025; CU-004, CU-016
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\Rol;
use App\Exceptions\ReglaNegocioException;
use App\Mail\InvitacionAdministradorMail;
use App\Models\Institucion;
use App\Models\Usuario;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Invitaciones de registro.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class InvitacionService
{
    /**
     * Encola el correo con el enlace firmado temporal para completar el registro.
     *
     * @param  Usuario  $invitado  Cuenta inactiva.
     * @param  Institucion  $institucion  Tenant que invita.
     * @return void
     */
    public function enviar(Usuario $invitado, Institucion $institucion): void
    {
        Mail::to($invitado->correo_institucional)->queue(new InvitacionAdministradorMail(
            $invitado->nombre_completo,
            $institucion->nombre_institucion,
            $this->generarEnlace($invitado),
            $this->obtenerProposito($invitado->rol),
        ));
    }

    /**
     * Enlace firmado y temporal para completar el registro, atado al estado actual de la contraseña.
     *
     * @param  Usuario  $invitado  Cuenta inactiva.
     * @param  CarbonInterface|null  $expira  Vencimiento; por omisión, la vigencia configurada.
     * @return string
     */
    public function generarEnlace(Usuario $invitado, ?CarbonInterface $expira = null): string
    {
        return URL::temporarySignedRoute(
            'registro.completar',
            $expira ?? now()->addHours((int) config('spei.invitacion.vigencia_horas')),
            ['usuario' => $invitado->id_usuario, 'huella' => $this->calcularHuella($invitado)],
        );
    }

    /**
     * Huella de la contraseña vigente: al registrarse cambia y los enlaces anteriores dejan de servir, así
     * una cuenta dada de baja no puede reactivarse con una invitación vieja.
     *
     * @param  Usuario  $usuario  Cuenta invitada.
     * @return string
     */
    public function calcularHuella(Usuario $usuario): string
    {
        $clave = (string) config('app.key');

        return substr(hash_hmac('sha256', (string) $usuario->getAuthPassword(), $clave), 0, 32);
    }

    /**
     * Indica si la huella del enlace corresponde a la contraseña vigente de la cuenta.
     *
     * @param  Usuario  $usuario  Cuenta invitada.
     * @param  string  $huella  Valor recibido en el enlace.
     * @return bool
     */
    public function validarHuella(Usuario $usuario, string $huella): bool
    {
        return hash_equals($this->calcularHuella($usuario), $huella);
    }

    /**
     * Reenvía la invitación a una cuenta que aún no completa su registro.
     *
     * @param  Usuario  $invitado  Cuenta inactiva.
     * @return void
     *
     * @throws ReglaNegocioException Si la cuenta ya está activa.
     */
    public function reenviar(Usuario $invitado): void
    {
        if ($invitado->activo || $invitado->completoRegistro()) {
            throw new ReglaNegocioException('El registro ya fue completado.');
        }

        $this->enviar($invitado, Institucion::query()->findOrFail($invitado->id_institucion));
    }

    /**
     * Texto que explica para qué se invita a la cuenta.
     *
     * @param  Rol  $rol  Rol de la cuenta.
     * @return string
     */
    private function obtenerProposito(Rol $rol): string
    {
        return match ($rol) {
            Rol::Alumno, Rol::Docente => 'solicitar préstamos de equipo institucional como '
                . mb_strtolower($rol->obtenerEtiqueta()),
            Rol::Asistente => 'atender entregas y devoluciones en el mostrador',
            default => 'administrar su equipo institucional',
        };
    }
}
