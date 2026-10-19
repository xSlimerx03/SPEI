{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         index.blade.php
 * @path         resources/views/catalogo/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Catálogo del usuario final en grid de tarjetas (4, 2 o 1
 *               columnas según el dispositivo) con búsqueda y filtros.
 *
 * @requisitos   RF-015, RN-002, RNF-009
 *
 * @see          docs/requisitos/BBB_CU010_ConsultarCatalogo_v1.0.md; MP sección 2.4.5 (layout 2)
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Catálogo de recursos')

@section('contenido')
    <form method="GET" action="{{ route('catalogo.index') }}" class="filtros tarjeta">
        <div class="campo">
            <label for="texto" class="campo__etiqueta">Buscar por nombre o marca</label>
            <input type="search" id="texto" name="texto" class="control" value="{{ request('texto') }}" maxlength="100">
        </div>
        <div class="campo">
            <label for="tipo_recurso" class="campo__etiqueta">Tipo</label>
            <select id="tipo_recurso" name="tipo_recurso" class="control">
                <option value="">Todos</option>
                @foreach ($tipos as $tipo)
                    <option value="{{ $tipo->value }}" @selected(request('tipo_recurso') === $tipo->value)>
                        {{ $tipo->obtenerEtiqueta() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="campo">
            <label for="id_area" class="campo__etiqueta">Área</label>
            <select id="id_area" name="id_area" class="control">
                <option value="">Todas</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id_area }}" @selected((int) request('id_area') === $area->id_area)>
                        {{ $area->nombre_area }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="boton">
            <span class="icono icono--boton" aria-hidden="true">search</span>Buscar
        </button>
    </form>

    @if ($recursos->isEmpty())
        <div class="tarjeta estado-vacio">
            <span class="icono icono--vacio" aria-hidden="true">inbox</span>
            <p>No hay recursos disponibles</p>
        </div>
    @else
        <div class="cuadricula">
            @foreach ($recursos as $recurso)
                <article class="tarjeta recurso-tarjeta">
                    <div class="recurso-tarjeta__cabecera">
                        <span class="icono icono--tarjeta" aria-hidden="true">
                            {{ $recurso->tipo_recurso === \App\Enums\TipoRecurso::Espacio ? 'meeting_room' : 'devices' }}
                        </span>
                        <h2 class="tarjeta__titulo">{{ $recurso->nombre_recurso }}</h2>
                    </div>
                    <span class="etiqueta {{ $recurso->estado->obtenerClaseEtiqueta() }}">
                        {{ $recurso->estado->obtenerEtiqueta() }}
                    </span>
                    <span class="texto-secundario">{{ $recurso->area->nombre_area }}</span>
                    @if ($recurso->marca)
                        <span class="texto-secundario">{{ $recurso->marca }}</span>
                    @endif
                </article>
            @endforeach
        </div>
        {{ $recursos->links('partials.paginacion') }}
    @endif
@endsection
