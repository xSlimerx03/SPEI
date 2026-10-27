{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         index.blade.php
 * @path         resources/views/instituciones/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Listado de instituciones para el superadmin con búsqueda y
 *               pestañas por estado de suscripción.
 *
 * @requisitos   RF-004, RF-006
 *
 * @see          docs/requisitos/BBB_CU002_AdministrarSuscripcion_v1.0.md; DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Instituciones')
@section('ruta', 'Plataforma')

@php
    $estadoActual = $filtros['estado'] ?? null;
    $texto = $filtros['texto'] ?? null;
@endphp

@section('contenido')
    <div class="pila--20 titulos">
        <nav class="pestanas" aria-label="Estados de suscripción">
            <a href="{{ route('instituciones.index', array_filter(['texto' => $texto])) }}"
               class="pestana {{ $estadoActual === null ? 'pestana--activa' : '' }}">Todas</a>
            @foreach (\App\Enums\EstadoSuscripcion::cases() as $estado)
                <a href="{{ route('instituciones.index', array_filter([
                    'texto' => $texto,
                    'estado' => $estado->value,
                ])) }}"
                   class="pestana {{ $estadoActual === $estado->value ? 'pestana--activa' : '' }}"
                   @if ($estadoActual === $estado->value) aria-current="page"
                   @endif>{{ $estado->obtenerEtiqueta() }}</a>
            @endforeach
        </nav>

        <section class="tarjeta tarjeta--recortada" aria-label="Instituciones">
            <form method="GET" action="{{ route('instituciones.index') }}" class="barra-filtros barra-filtros--tabla"
                  role="search">
                @if ($estadoActual)
                    <input type="hidden" name="estado" value="{{ $estadoActual }}">
                @endif
                <div class="control-icono control-icono--con-icono control-icono--compacto barra-filtros__busqueda">
                    <span class="icono" aria-hidden="true">search</span>
                    <label for="filtro-texto" class="visualmente-oculto">Buscar institución</label>
                    <input type="search" id="filtro-texto" name="texto" class="control control--compacto"
                           maxlength="100"
                           placeholder="Buscar por nombre o dominio" value="{{ $texto }}">
                </div>
                <button type="submit" class="boton boton--secundario">
                    <span class="icono" aria-hidden="true">filter_list</span>Filtrar
                </button>
            </form>

            @if ($instituciones->isEmpty())
                <div class="vacio">
                    <span class="icono" aria-hidden="true">domain</span>
                    <span class="vacio__titulo">Sin instituciones</span>
                    <span class="vacio__texto">No hay instituciones que coincidan con la búsqueda.</span>
                </div>
            @else
                <div class="tabla-contenedor">
                    <table class="tabla tabla--clicable tabla--820">
                        <thead>
                            <tr>
                                <th scope="col">Institución</th>
                                <th scope="col">Plan</th>
                                <th scope="col">Fecha de corte</th>
                                <th scope="col">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($instituciones as $institucion)
                                @php
                                    $claseEstado = match ($institucion->estado_suscripcion) {
                                        \App\Enums\EstadoSuscripcion::Activa => 'insignia--exito',
                                        \App\Enums\EstadoSuscripcion::SoloLectura => 'insignia--pendiente',
                                        default => 'insignia--peligro',
                                    };
                                    $dias = $institucion->obtenerDiasParaCorte();
                                @endphp
                                <tr data-href="{{ route('instituciones.show', $institucion) }}">
                                    <td>
                                        <div class="celda-persona">
                                            <span class="icono-tile"
                                                  aria-hidden="true"><span class="icono">account_balance</span></span>
                                            <div class="celda-doble">
                                                <a href="{{ route('instituciones.show', $institucion) }}"
                                                   class="enlace-fila">{{ $institucion->nombre_institucion }}</a>
                                                <span class="celda-doble__secundaria mono">
                                                    {{ $institucion->dominio_correo }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $institucion->plan_suscripcion->obtenerEtiqueta() }}</td>
                                    <td>
                                        <div class="celda-doble">
                                            <span class="negrita">
                                                {{ \App\Support\FormatoFecha::conAnio($institucion->fecha_corte) }}
                                            </span>
                                            <span class="celda-doble__secundaria">
                                                {{ $dias >= 0 ? 'En ' . $dias . ' días' : 'Vencida hace ' . abs($dias)
                                                    . ' días' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="insignia {{ $claseEstado }}">
                                            {{ $institucion->estado_suscripcion->obtenerEtiqueta() }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $instituciones->links('partials.paginacion') }}
            @endif
        </section>
    </div>
@endsection
