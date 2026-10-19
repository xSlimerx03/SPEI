<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       API REST v1
 * @file         SesionController.php
 * @path         app/Http/Controllers/Api/V1/SesionController.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Emisión y revocación de tokens Sanctum con expiración de 24 h
 *               para el cliente de la API.
 *
 * @requisitos   RNF-001, RNF-005
 *
 * @see          docs/requisitos/BBB_CU005_IniciarSesion_v1.0.md; docs/api/BBB_API_v1.0.md; DT-007
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\IniciarSesionRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;
use App\Services\AutenticacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\TransientToken;

/**
 * Sesiones de la API.
 *
 * @package App\Http\Controllers\Api\V1
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class SesionController extends Controller
{
    public function __construct(
        private AutenticacionService $autenticacionService,
    ) {}

    /**
     * Valida el acceso y devuelve un token Bearer.
     *
     * @param  IniciarSesionRequest  $request  Credenciales.
     * @return JsonResponse
     */
    public function store(IniciarSesionRequest $request): JsonResponse
    {
        $usuario = $this->autenticacionService->verificarCredenciales(
            $request->string('correo_institucional')->toString(),
            $request->string('contrasena')->toString(),
        );
        $expiracion = now()->addMinutes((int) config('sanctum.expiration'));
        $token = $usuario->createToken($request->string('nombre_dispositivo', 'api')->toString(), ['*'], $expiracion);
        $this->autenticacionService->auditarAcceso('INICIAR_SESION', $usuario);

        return response()->json([
            'token' => $token->plainTextToken,
            'tipo' => 'Bearer',
            'expira_en' => $expiracion->toIso8601String(),
            'usuario' => new UsuarioResource($usuario),
        ], 201);
    }

    /**
     * Revoca el token con el que se hizo la petición.
     *
     * @param  Request  $request  Petición autenticada.
     * @return Response
     */
    public function destroy(Request $request): Response
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->autenticacionService->auditarAcceso('CERRAR_SESION', $usuario);
        /** @var PersonalAccessToken|TransientToken|null $token */
        $token = $usuario->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->noContent();
    }
}
