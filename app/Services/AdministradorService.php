<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         AdministradorService.php
 * @path         app/Services/AdministradorService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Alta de administradores del sistema por invitación: usuario
 *               inactivo, enlace firmado de 72 h y activación al completar el
 *               registro (DT-025).
 *
 * @requisitos   RF-008, RN-001, RNF-001, RNF-005
 *
 * @see          docs/requisitos/BBB_CU004_InvitarAdministrador_v1.0.md; DT-025
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\Rol;
use App\Exceptions\ReglaNegocioException;
use App\Mail\InvitacionAdministradorMail;
use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Invitación y registro de administradores del sistema.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AdministradorService
{
    public function __construct(
        private AuditoriaService $auditoriaService,
    ) {}

    /**
     * Crea el administrador del sistema inactivo y le envía la invitación.
     *
     * @param  Institucion  $institucion  Tenant del invitado.
     * @param  array{nombre_completo: string, correo_institucional: string, id_area_asignada?: int|null}  $datos
     * @return Usuario
     */
    public function invitar(Institucion $institucion, array $datos): Usuario
    {
        $invitado = DB::transaction(function () use ($institucion, $datos): Usuario {
            $invitado = Usuario::query()->create([
                'id_institucion' => $institucion->id_institucion,
                'nombre_completo' => $datos['nombre_completo'],
                'correo_institucional' => $datos['correo_institucional'],
                // La contraseña aleatoria no se comunica: la cuenta solo se usa tras completar el registro.
                'contrasena_hash' => Str::password(40),
                'rol' => Rol::AdminSistema,
                'id_area_asignada' => $datos['id_area_asignada'] ?? null,
                'activo' => false,
            ]);
            $invitado->refresh();
            $this->auditoriaService->registrar('CREAR', $invitado, null, $this->auditoriaService->capturar($invitado));

            return $invitado;
        });

        $this->enviarInvitacion($invitado, $institucion);

        return $invitado;
    }

    /**
     * Genera un enlace nuevo para un invitado que aún no completa su registro.
     *
     * @param  Usuario  $invitado  Administrador inactivo.
     * @return void
     * @throws ReglaNegocioException Si la cuenta ya está activa.
     */
    public function reenviarInvitacion(Usuario $invitado): void
    {
        if ($invitado->activo) {
            throw new ReglaNegocioException('El registro ya fue completado.');
        }

        $institucion = Institucion::query()->findOrFail($invitado->id_institucion);
        $this->enviarInvitacion($invitado, $institucion);
    }

    /**
     * Define la contraseña del invitado y activa su cuenta.
     *
     * @param  Usuario  $invitado  Administrador inactivo.
     * @param  string  $contrasena  Contraseña elegida.
     * @return Usuario
     * @throws ReglaNegocioException Si la cuenta ya está activa.
     */
    public function completarRegistro(Usuario $invitado, string $contrasena): Usuario
    {
        return DB::transaction(function () use ($invitado, $contrasena): Usuario {
            // Releer bajo bloqueo impide que dos envíos de la misma invitación sobrescriban la contraseña.
            $invitado = Usuario::query()->lockForUpdate()->findOrFail($invitado->id_usuario);

            if ($invitado->activo) {
                throw new ReglaNegocioException('El registro ya fue completado.');
            }

            $anterior = $this->auditoriaService->capturar($invitado);
            $invitado->update([
                'contrasena_hash' => $contrasena,
                'activo' => true,
            ]);
            $this->auditoriaService->registrar(
                'COMPLETAR_REGISTRO',
                $invitado,
                $anterior,
                $this->auditoriaService->capturar($invitado),
                $invitado,
            );

            return $invitado;
        });
    }

    /**
     * Encola el correo con el enlace firmado temporal.
     *
     * @param  Usuario  $invitado  Destinatario.
     * @param  Institucion  $institucion  Tenant que invita.
     * @return void
     */
    private function enviarInvitacion(Usuario $invitado, Institucion $institucion): void
    {
        $enlace = URL::temporarySignedRoute(
            'registro.completar',
            now()->addHours((int) config('spei.invitacion.vigencia_horas')),
            ['usuario' => $invitado->id_usuario],
        );

        Mail::to($invitado->correo_institucional)->queue(new InvitacionAdministradorMail(
            $invitado->nombre_completo,
            $institucion->nombre_institucion,
            $enlace,
        ));
    }
}
