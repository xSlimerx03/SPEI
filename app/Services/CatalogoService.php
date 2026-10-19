<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         CatalogoService.php
 * @path         app/Services/CatalogoService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Consultas diferenciadas: catálogo para el usuario final (solo
 *               lo disponible, RN-002) e inventario completo para el
 *               administrador (limitado a su área si es responsable).
 *
 * @requisitos   RF-013, RF-015, RN-002, RNF-009, RS-006
 *
 * @see          docs/requisitos/BBB_CU010_ConsultarCatalogo_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Recurso;
use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Listados paginados del catálogo y del inventario.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class CatalogoService
{
    /**
     * Catálogo del usuario final: recursos disponibles, activos y de áreas activas.
     *
     * @param  array<string, mixed>  $filtros  texto, tipo_recurso e id_area opcionales.
     * @return LengthAwarePaginator<int, Recurso>
     */
    public function listarCatalogo(array $filtros): LengthAwarePaginator
    {
        $consulta = Recurso::query()->visiblesEnCatalogo()->with('area');

        return $this->aplicarFiltros($consulta, $filtros)
            ->orderBy('nombre_recurso')
            ->paginate((int) config('spei.catalogo.por_pagina'))
            ->withQueryString();
    }

    /**
     * Inventario del administrador del sistema con los cuatro estados.
     *
     * @param  Usuario  $actor  Administrador que consulta.
     * @param  array<string, mixed>  $filtros  texto, tipo_recurso, id_area y estado opcionales.
     * @return LengthAwarePaginator<int, Recurso>
     */
    public function listarInventario(Usuario $actor, array $filtros): LengthAwarePaginator
    {
        $consulta = Recurso::query()
            ->with('area')
            ->when($actor->esResponsableDeArea(), fn (Builder $q) => $q->where('id_area', $actor->id_area_asignada))
            ->when($filtros['estado'] ?? null, fn (Builder $q, string $estado) => $q->where('estado', $estado));

        return $this->aplicarFiltros($consulta, $filtros)
            ->orderBy('nombre_recurso')
            ->paginate((int) config('spei.catalogo.por_pagina'))
            ->withQueryString();
    }

    /**
     * Aplica la búsqueda por texto y los filtros comunes.
     *
     * @param  Builder<Recurso>  $consulta  Consulta base.
     * @param  array<string, mixed>  $filtros  Filtros validados.
     * @return Builder<Recurso>
     */
    private function aplicarFiltros(Builder $consulta, array $filtros): Builder
    {
        return $consulta
            ->when($filtros['texto'] ?? null, function (Builder $q, string $texto): void {
                $patron = '%' . addcslashes($texto, '%_\\') . '%';
                $q->where(fn (Builder $sub) => $sub->where('nombre_recurso', 'like', $patron)
                    ->orWhere('marca', 'like', $patron)
                    ->orWhere('numero_inventario', 'like', $patron));
            })
            ->when($filtros['tipo_recurso'] ?? null, fn (Builder $q, string $tipo) => $q->where('tipo_recurso', $tipo))
            ->when($filtros['id_area'] ?? null, fn (Builder $q, int|string $area) => $q->where('id_area', (int) $area));
    }
}
