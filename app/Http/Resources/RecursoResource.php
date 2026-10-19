<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       API REST v1
 * @file         RecursoResource.php
 * @path         app/Http/Resources/RecursoResource.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Representación JSON de un recurso para el catálogo y el
 *               inventario de la API.
 *
 * @requisitos   RF-011, RF-013, RF-015
 *
 * @see          docs/api/BBB_API_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Area;
use App\Models\Recurso;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso serializado.
 *
 * @mixin Recurso
 *
 * @package App\Http\Resources
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class RecursoResource extends JsonResource
{
    /**
     * Campos públicos del recurso.
     *
     * @param  Request  $request  Petición en curso.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $area = $this->resource->area;

        return [
            'id_recurso' => $this->resource->id_recurso,
            'nombre_recurso' => $this->resource->nombre_recurso,
            'numero_inventario' => $this->resource->numero_inventario,
            'numero_serie' => $this->resource->numero_serie,
            'tipo_recurso' => $this->resource->tipo_recurso->value,
            'marca' => $this->resource->marca,
            'anio_adquisicion' => $this->resource->anio_adquisicion,
            'estado' => $this->resource->estado->value,
            'activo' => $this->resource->activo,
            'area' => $area instanceof Area
                ? ['id_area' => $area->id_area, 'nombre_area' => $area->nombre_area]
                : null,
        ];
    }
}
