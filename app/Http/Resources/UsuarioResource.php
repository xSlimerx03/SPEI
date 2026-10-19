<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       API REST v1
 * @file         UsuarioResource.php
 * @path         app/Http/Resources/UsuarioResource.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Representación JSON del usuario autenticado (sin contraseña).
 *
 * @requisitos   RNF-001
 *
 * @see          docs/api/BBB_API_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Usuario serializado.
 *
 * @mixin Usuario
 *
 * @package App\Http\Resources
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class UsuarioResource extends JsonResource
{
    /**
     * Campos públicos del usuario.
     *
     * @param  Request  $request  Petición en curso.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_usuario' => $this->resource->id_usuario,
            'id_institucion' => $this->resource->id_institucion,
            'nombre_completo' => $this->resource->nombre_completo,
            'correo_institucional' => $this->resource->correo_institucional,
            'rol' => $this->resource->rol->value,
            'id_area_asignada' => $this->resource->id_area_asignada,
        ];
    }
}
