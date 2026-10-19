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
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Listado de áreas con número de recursos, activación y
 *               eliminación.
 *
 * @requisitos   RF-009
 *
 * @see          docs/requisitos/BBB_CU006_GestionarAreas_v1.0.md
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Áreas')

@section('contenido')
    <div class="acciones" style="margin-bottom: 24px;">
        <a href="{{ route('areas.create') }}" class="boton">
            <span class="icono icono--boton" aria-hidden="true">add</span>Crear
        </a>
    </div>
    <div class="tarjeta">
        @if ($areas->isEmpty())
            <div class="estado-vacio">
                <span class="icono icono--vacio" aria-hidden="true">inbox</span>
                <p>Aún no hay áreas registradas.</p>
            </div>
        @else
            <div class="tabla-contenedor">
                <table class="tabla">
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
                                    <strong>{{ $area->nombre_area }}</strong>
                                    @if ($area->descripcion)
                                        <div class="texto-secundario">{{ $area->descripcion }}</div>
                                    @endif
                                </td>
                                <td>{{ $area->recursos_count }}</td>
                                <td>
                                    <span class="etiqueta {{ $area->activo ? 'etiqueta--exito' : 'etiqueta--neutra' }}">
                                        {{ $area->activo ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="tabla__acciones">
                                    <a href="{{ route('areas.edit', $area) }}" class="boton boton--pequeno">
                                        <span class="icono icono--boton" aria-hidden="true">edit</span>Editar
                                    </a>
                                    <form method="POST" action="{{ route('areas.activo', $area) }}" class="formulario-en-linea">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="activo" value="{{ $area->activo ? 0 : 1 }}">
                                        <button type="submit" class="boton boton--pequeno boton--secundario">
                                            {{ $area->activo ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                    @if ($area->recursos_count === 0)
                                        <form method="POST" action="{{ route('areas.destroy', $area) }}" class="formulario-en-linea"
                                              onsubmit="return confirm('¿Está seguro de eliminar el área?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="boton boton--pequeno boton--secundario boton--peligro">
                                                <span class="icono icono--boton" aria-hidden="true">delete</span>Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $areas->links('partials.paginacion') }}
        @endif
    </div>
@endsection
