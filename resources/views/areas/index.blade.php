{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         index.blade.php
 * @path         resources/views/areas/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Listado de áreas con número de recursos, activación y
 *               eliminación, con el lenguaje visual del prototipo.
 *
 * @requisitos   RF-009
 *
 * @see          docs/requisitos/BBB_CU006_GestionarAreas_v1.0.md; DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Áreas')

@section('contenido')
    <section class="tarjeta tarjeta--recortada" aria-labelledby="titulo-areas">
        <div class="tarjeta__cabecera">
            <div class="titulos">
                <h2 id="titulo-areas" class="tarjeta__titulo">Áreas y departamentos</h2>
                <span class="tarjeta__subtitulo">Un área inactiva oculta sus recursos del catálogo.</span>
            </div>
            <a href="{{ route('areas.create') }}" class="boton boton--primario">
                <span class="icono" aria-hidden="true">add</span>Crear
            </a>
        </div>
        @if ($areas->isEmpty())
            <div class="vacio">
                <span class="icono" aria-hidden="true">apartment</span>
                <span class="vacio__titulo">Aún no hay áreas registradas</span>
                <span class="vacio__texto">Crea la primera para empezar a registrar recursos.</span>
            </div>
        @else
            <div class="tabla-contenedor">
                <table class="tabla tabla--720">
                    <thead>
                        <tr>
                            <th scope="col">Área</th>
                            <th scope="col">Recursos</th>
                            <th scope="col">Estado</th>
                            <th scope="col"><span class="visualmente-oculto">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($areas as $area)
                            <tr>
                                <td>
                                    <div class="celda-persona">
                                        <span class="icono-tile" aria-hidden="true"><span class="icono">apartment</span></span>
                                        <div class="celda-doble">
                                            <span class="negrita">{{ $area->nombre_area }}</span>
                                            @if ($area->descripcion)
                                                <span class="celda-doble__secundaria">{{ \Illuminate\Support\Str::limit($area->descripcion, 90) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $area->recursos_count }}</td>
                                <td>
                                    <span class="insignia {{ $area->activo ? 'insignia--exito' : 'insignia--neutra' }}">{{ $area->activo ? 'Activa' : 'Inactiva' }}</span>
                                </td>
                                <td>
                                    <div class="tabla__acciones">
                                        <a href="{{ route('areas.edit', $area) }}" class="boton boton--secundario boton--pequeno">
                                            <span class="icono" aria-hidden="true">edit</span>Editar
                                        </a>
                                        <form method="POST" action="{{ route('areas.activo', $area) }}" class="formulario-en-linea">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="activo" value="{{ $area->activo ? 0 : 1 }}">
                                            <button type="submit" class="boton boton--fantasma boton--pequeno">
                                                <span class="icono" aria-hidden="true">{{ $area->activo ? 'visibility_off' : 'visibility' }}</span>{{ $area->activo ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                        @if ($area->recursos_count === 0)
                                            <form method="POST" action="{{ route('areas.destroy', $area) }}" class="formulario-en-linea"
                                                  data-confirmar="¿Está seguro de eliminar el área {{ $area->nombre_area }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="boton boton--peligro-texto boton--pequeno">
                                                    <span class="icono" aria-hidden="true">delete</span>Eliminar
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $areas->links('partials.paginacion') }}
        @endif
    </section>
@endsection
