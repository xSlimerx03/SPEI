<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Autenticación
 * @file         AutenticacionService.php
 * @path         app/Services/AutenticacionService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Verifica credenciales, cuenta activa, dominio institucional
 *               (RN-001) y estado de la suscripción antes de abrir una sesión
 *               web o emitir un token de API.
 *
 * @requisitos   RNF-001, RN-001, RN-013, RNF-005, RS-006
 *
 * @see          docs/requisitos/BBB_CU005_IniciarSesion_v1.0.md; DT-007, DT-025
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoSuscripcion;
use App\Models\Institucion;
use App\Models\Usuario;
use App\Support\DominioCorreo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Validación de acceso al SPEI.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AutenticacionService
{
    public const MENSAJE_CREDENCIALES = 'Las credenciales no son válidas.';

    public const MENSAJE_SUSCRIPCION = 'La suscripción de la institución no está disponible.';

    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  AuditoriaService  $auditoriaService  Dependencia inyectada.
     */
    public function __construct(
        private AuditoriaService $auditoriaService,
    ) {}

    /**
     * Devuelve el usuario si puede acceder; lanza un error genérico en caso contrario.
     *
     * @param  string  $correo  Correo institucional capturado.
     * @param  string  $contrasena  Contraseña en claro.
     * @return Usuario
     * @throws ValidationException Si las credenciales, la cuenta o la suscripción no son válidas.
     */
    public function verificarCredenciales(string $correo, string $contrasena): Usuario
    {
        $usuario = Usuario::withoutGlobalScopes()
            ->where('correo_institucional', mb_strtolower(trim($correo)))
            ->first();

        // NOTE: el mismo mensaje para todos los casos evita revelar qué cuentas existen (CU-005 E1).
        if (! $usuario instanceof Usuario || ! $usuario->activo
            || ! Hash::check($contrasena, $usuario->contrasena_hash)) {
            throw ValidationException::withMessages(['correo_institucional' => self::MENSAJE_CREDENCIALES]);
        }

        if ($usuario->esSuperadmin()) {
            return $usuario;
        }

        $institucion = Institucion::query()->find($usuario->id_institucion);

        if (! $institucion instanceof Institucion || ! $this->perteneceAlDominio($usuario, $institucion)) {
            throw ValidationException::withMessages(['correo_institucional' => self::MENSAJE_CREDENCIALES]);
        }

        if (! $institucion->estado_suscripcion->permiteAcceso()) {
            throw ValidationException::withMessages(['correo_institucional' => self::MENSAJE_SUSCRIPCION]);
        }

        return $usuario;
    }

    /**
     * Indica si la institución del usuario permite el acceso en este momento.
     *
     * @param  Usuario  $usuario  Usuario autenticado.
     * @return bool
     */
    public function puedeContinuar(Usuario $usuario): bool
    {
        if (! $usuario->activo) {
            return false;
        }

        if ($usuario->esSuperadmin()) {
            return true;
        }

        return $this->obtenerEstado($usuario)?->permiteAcceso() ?? false;
    }

    /**
     * Devuelve el estado de la suscripción de la institución del usuario.
     *
     * @param  Usuario  $usuario  Usuario del tenant.
     * @return EstadoSuscripcion|null
     */
    public function obtenerEstado(Usuario $usuario): ?EstadoSuscripcion
    {
        return Institucion::query()->find($usuario->id_institucion)?->estado_suscripcion;
    }

    /**
     * Audita un inicio o cierre de sesión.
     *
     * @param  string  $accion  INICIAR_SESION o CERRAR_SESION.
     * @param  Usuario  $usuario  Usuario que accede.
     * @return void
     */
    public function auditarAcceso(string $accion, Usuario $usuario): void
    {
        $this->auditoriaService->registrar($accion, $usuario, null, null, $usuario);
    }

    /**
     * Verifica que el correo pertenezca al dominio registrado por la institución (RN-001).
     *
     * @param  Usuario  $usuario  Usuario del tenant.
     * @param  Institucion  $institucion  Institución del usuario.
     * @return bool
     */
    private function perteneceAlDominio(Usuario $usuario, Institucion $institucion): bool
    {
        // RF-017: se aceptan el dominio contratado y sus subdominios (p. ej. alumnos.escuela.mx).
        return DominioCorreo::perteneceA($usuario->correo_institucional, $institucion->dominio_correo);
    }
}
