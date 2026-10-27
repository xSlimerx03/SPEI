{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.6: Penalizaciones y Sanciones Automatizadas
 * @file         index.blade.php
 * @path         resources/views/sanciones/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Penalizaciones aplicadas con filtros por usuario, tipo y estado,
 *               y exención justificada en un diálogo (RF-044). Variables:
 *               penalizaciones, filtros, actor, bloqueados.
 *
 * @requisitos   RF-042, RF-043, RF-044, RF-056
 *
 * @see          CU-019, CU-020; DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Sanciones')
@section('ruta', 'Administración')

@section('contenido')
    <section class="tarjeta" aria-labelledby="titulo-sanciones">
        <div class="tarjeta__cabecera">
            <div class="titulos">
                <h2 id="titulo-sanciones" class="tarjeta__titulo">Sanciones aplicadas</h2>
                <span class="tarjeta__subtitulo">
                    Se registran solas al cerrar un préstamo con incidencias. {{ $bloqueados }}
                    {{ $bloqueados === 1 ? 'cuenta bloqueada' : 'cuentas bloqueadas' }} en este momento.
                </span>
            </div>
            @unless ($actor->esResponsableDeArea())
                <a href="{{ route('sanciones.matriz') }}" class="boton boton--secundario">
                    <span class="icono" aria-hidden="true">tune</span>Ver matriz
                </a>
            @endunless
        </div>

        <form method="GET" action="{{ route('sanciones.index') }}" class="barra-filtros barra-filtros--tabla"
              role="search">
            <div class="control-icono control-icono--con-icono control-icono--compacto barra-filtros__busqueda">
                <span class="icono" aria-hidden="true">search</span>
                <label for="filtro-texto" class="visualmente-oculto">Buscar usuario</label>
                <input type="search" id="filtro-texto" name="texto" class="control control--compacto" maxlength="100"
                       placeholder="Buscar por nombre del usuario" value="{{ $filtros['texto'] ?? '' }}">
            </div>
            <div class="selector selector--150">
                <label for="filtro-tipo" class="visualmente-oculto">Tipo</label>
                <select id="filtro-tipo" name="tipo" class="control control--compacto" data-autoenvio>
                    <option value="">Todos los tipos</option>
                    @foreach (\App\Enums\TipoIncidente::cases() as $tipo)
                        <option value="{{ $tipo->value }}" @selected(($filtros['tipo'] ?? '') === $tipo->value)>
                            {{ $tipo->obtenerEtiqueta() }}
                        </option>
                    @endforeach
                </select>
                <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
            </div>
            <div class="selector selector--150">
                <label for="filtro-estado" class="visualmente-oculto">Estado</label>
                <select id="filtro-estado" name="estado" class="control control--compacto" data-autoenvio>
                    <option value="">Todos los estados</option>
                    <option value="vigente" @selected(($filtros['estado'] ?? '') === 'vigente')>Vigentes</option>
                    <option value="exentada" @selected(($filtros['estado'] ?? '') === 'exentada')>Exentadas</option>
                </select>
                <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
            </div>
            <button type="submit" class="boton boton--secundario">
                <span class="icono" aria-hidden="true">filter_list</span>Filtrar
            </button>
        </form>

        @forelse ($penalizaciones as $penalizacion)
            <div class="fila-lista fila-lista--12">
                <span class="avatar {{ $penalizacion->usuario->bloqueado ? 'avatar--rojo' : 'avatar--ambar' }}"
                      aria-hidden="true">
                    {{ \App\Support\Iniciales::de($penalizacion->usuario->nombre_completo) }}
                </span>
                <div class="titulos crece-220">
                    <span class="negrita">{{ $penalizacion->usuario->nombre_completo }}</span>
                    <span class="texto-12 texto-sec">{{ $penalizacion->motivo }}</span>
                </div>
                <span class="insignia insignia--suave">{{ $penalizacion->tipo_penalizacion->obtenerEtiqueta() }}</span>
                <div class="titulos texto-13 celda-200">
                    <span class="negrita">{{ $penalizacion->marcas_aplicadas }} marca(s)</span>
                    <span class="texto-sec">
                        {{ $penalizacion->prestamo?->obtenerFolio() ?? 'Préstamo archivado' }} ·
                        {{ $penalizacion->fecha_creacion->format('d/m/Y') }}
                    </span>
                </div>
                @if ($penalizacion->exentada)
                    <span class="insignia insignia--neutra" title="{{ $penalizacion->motivo_exencion }}">Exentada</span>
                @else
                    <span class="insignia insignia--peligro">Vigente</span>
                    <button type="button" class="boton boton--fantasma boton--pequeno"
                            data-abrir-modal="modal-exentar-{{ $penalizacion->id_penalizacion }}">
                        <span class="icono" aria-hidden="true">gavel</span>Exentar
                    </button>
                    @push('modales')
                        @include('sanciones.modal-exentar', ['penalizacion' => $penalizacion])
                    @endpush
                @endif
            </div>
        @empty
            <div class="vacio">
                <span class="icono" aria-hidden="true">verified_user</span>
                <span class="vacio__titulo">Sin sanciones con esos filtros</span>
                <span class="vacio__texto">Las marcas aparecen cuando un préstamo se devuelve tarde o con daños.</span>
            </div>
        @endforelse
        {{ $penalizaciones->links('partials.paginacion') }}
    </section>
@endsection
