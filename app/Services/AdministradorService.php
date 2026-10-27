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
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Alta de administradores del sistema por invitación: usuario
 *               inactivo, enlace firmado de 72 h y activación al completar el
 *               registro (DT-025).
 *
 * @requisitos   RF-008, RF-019, RN-001, RNF-001, RNF-005
 *
 * @see          docs/requisitos/BBB_CU004_InvitarAdministrador_v1.0.md; DT-025
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\Rol;
use App\Exceptions\ReglaNegocioException;
use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
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
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  AuditoriaService  $auditoriaService  Dependencia inyectada.
     */
    public function __construct(
        private AuditoriaService $auditoriaService,
        private InvitacionService $invitacionService,
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
        $this->validarAreaLibre($datos['id_area_asignada'] ?? null, null);

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

        $this->invitacionService->enviar($invitado, $institucion);

        return $invitado;
    }

    /**
     * Asigna, cambia o quita el área de un administrador del sistema (responsable de área, RF-019).
     *
     * @param  Usuario  $administrador  Administrador del sistema de la institución.
     * @param  int|null  $idArea  Área activa o null para administrar toda la institución.
     * @return Usuario
     *
     * @throws ReglaNegocioException Si la cuenta no es administrador del sistema o el área ya tiene responsable.
     */
    public function actualizarArea(Usuario $administrador, ?int $idArea): Usuario
    {
        $this->validarAdministrador($administrador);
        $this->validarAreaLibre($idArea, $administrador->id_usuario);

        return DB::transaction(function () use ($administrador, $idArea): Usuario {
            $anterior = $this->auditoriaService->capturar($administrador);
            $administrador->update(['id_area_asignada' => $idArea]);
            $this->auditoriaService->registrar(
                'ACTUALIZAR',
                $administrador,
                $anterior,
                $this->auditoriaService->capturar($administrador),
            );

            return $administrador;
        });
    }

    /**
     * Da de baja o reactiva a un administrador del sistema que ya completó su registro.
     *
     * @param  Usuario  $administrador  Administrador del sistema de la institución.
     * @param  bool  $activo  Estado deseado.
     * @return void
     *
     * @throws ReglaNegocioException Si no completó su registro, el área quedaría con dos responsables o se
     *                               daría de baja al último administrador sin área.
     */
    public function cambiarActivo(Usuario $administrador, bool $activo): void
    {
        $this->validarAdministrador($administrador);

        if (! $administrador->completoRegistro()) {
            throw new ReglaNegocioException('La cuenta aún no completa su registro; reenvíe la invitación.');
        }

        if ($activo) {
            $this->validarAreaLibre($administrador->id_area_asignada, $administrador->id_usuario);
        }

        if (! $activo && $this->esUltimoAdministradorGeneral($administrador)) {
            throw new ReglaNegocioException('Debe quedar al menos un administrador del sistema sin área activo.');
        }

        DB::transaction(function () use ($administrador, $activo): void {
            $anterior = $this->auditoriaService->capturar($administrador);
            $administrador->update(['activo' => $activo]);
            $this->auditoriaService->registrar(
                $activo ? 'REACTIVAR' : 'DAR_BAJA',
                $administrador,
                $anterior,
                $this->auditoriaService->capturar($administrador),
            );
        });
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
        $this->invitacionService->reenviar($invitado);
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

            // Una cuenta dada de baja ya se registró: no puede reactivarse con una invitación (DT-035).
            if ($invitado->activo || $invitado->completoRegistro()) {
                throw new ReglaNegocioException('El registro ya fue completado.');
            }

            $anterior = $this->auditoriaService->capturar($invitado);
            $invitado->update([
                'contrasena_hash' => $contrasena,
                'activo' => true,
                'fecha_registro' => now(),
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
     * Rechaza cuentas que no son administradores del sistema.
     *
     * @param  Usuario  $usuario  Cuenta objetivo.
     * @return void
     *
     * @throws ReglaNegocioException
     */
    private function validarAdministrador(Usuario $usuario): void
    {
        if ($usuario->rol !== Rol::AdminSistema) {
            throw new ReglaNegocioException('La cuenta no es un administrador del sistema.');
        }
    }

    /**
     * Un área tiene a lo sumo un responsable activo o con invitación pendiente (MBD Tabla 7, RF-019).
     *
     * @param  int|null  $idArea  Área solicitada; null no restringe.
     * @param  int|null  $exceptoUsuario  Cuenta que se está editando.
     * @return void
     *
     * @throws ReglaNegocioException Si el área ya tiene responsable.
     */
    private function validarAreaLibre(?int $idArea, ?int $exceptoUsuario): void
    {
        if ($idArea === null) {
            return;
        }

        $ocupada = Usuario::query()
            ->where('rol', Rol::AdminSistema)
            ->where('id_area_asignada', $idArea)
            ->when($exceptoUsuario !== null, fn (Builder $q): Builder => $q->whereKeyNot($exceptoUsuario))
            ->where(fn (Builder $q): Builder => $q->where('activo', true)->orWhereNull('fecha_registro'))
            ->exists();

        if ($ocupada) {
            throw new ReglaNegocioException('El área ya tiene un responsable asignado.');
        }
    }

    /**
     * Indica si la cuenta es el único administrador del sistema activo que administra toda la institución.
     *
     * @param  Usuario  $administrador  Cuenta que se daría de baja.
     * @return bool
     */
    private function esUltimoAdministradorGeneral(Usuario $administrador): bool
    {
        if ($administrador->id_area_asignada !== null) {
            return false;
        }

        return ! Usuario::query()
            ->where('rol', Rol::AdminSistema)
            ->whereNull('id_area_asignada')
            ->where('activo', true)
            ->whereKeyNot($administrador->id_usuario)
            ->exists();
    }
}
