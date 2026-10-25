<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.5: Entrega y Devolución
 * @file         MostradorService.php
 * @path         app/Services/MostradorService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Valida, sin consumirlo, el código QR que presenta el usuario
 *               en el mostrador: lo busca por su hash dentro del tenant y
 *               comprueba vigencia, uso y ámbito del operador. La custodia se
 *               transfiere después con PrestamoService::confirmar().
 *
 * @requisitos   RF-034, RF-035, RF-036, RS-006
 *
 * @see          app/Services/PrestamoService.php; docs/plan/BBB_MVP_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Prestamo;
use App\Models\TokenQr;
use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;

/**
 * Lectura de códigos en el mostrador.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class MostradorService
{
    /**
     * Devuelve el préstamo y el token vigentes del código, o lanza un error de negocio.
     *
     * @param  Usuario  $operador  Administrador del sistema o asistente.
     * @param  string  $codigo  Código capturado o leído con la cámara.
     * @return array{prestamo: Prestamo, token: TokenQr}
     * @throws ReglaNegocioException Si el código no es válido, caducó, ya se usó o es de otra área.
     */
    public function validarCodigo(Usuario $operador, string $codigo): array
    {
        $codigo = mb_strtolower(trim($codigo));
        $token = preg_match('/^[a-f0-9]{32}$/', $codigo) === 1
            ? TokenQr::query()->where('token', hash('sha256', $codigo))->first()
            : null;

        if (! $token instanceof TokenQr || $token->usado || $token->fecha_expiracion->lte(now())) {
            throw new ReglaNegocioException('El código no es válido, ya se utilizó o ha caducado.');
        }

        $prestamo = Prestamo::query()
            ->with(['usuario', 'detalles.recurso.area'])
            ->findOrFail($token->id_prestamo);

        if (! Gate::forUser($operador)->allows('operar', $prestamo)) {
            throw new ReglaNegocioException('El préstamo pertenece a un área que usted no atiende.');
        }

        return ['prestamo' => $prestamo, 'token' => $token];
    }
}
