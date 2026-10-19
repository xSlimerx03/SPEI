{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         index.blade.php
 * @path         resources/views/recursos/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Inventario del administrador con filtros, etiqueta de color
 *               por estado y acciones de mantenimiento y desactivación.
 *
 * @requisitos   RF-011, RF-013, RF-014, RF-015
 *
 * @see          docs/requisitos/BBB_CU009_CambiarEstadoRecurso_v1.0.md; BBB_CU010
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Inventario')

@section('contenido')
    <div class="acciones" style="margin-bottom: 24px;">
        <a href="{{ route('recursos.create') }}" class="boton">
            <span class="icono icono--boton" aria-hidden="true">add</span>Crear
        </a>
        <a href="{{ route('recursos.importar') }}" class="boton boton--secundario">
            <span class="icono icono--boton" aria-hidden="true">upload_file</span>Importar CSV
        </a>
    </div>

    @include('recursos.buscar-codigo')

    <section class="tarjeta" aria-label="Recursos">
        <form method="GET" action="{{ route('recursos.index') }}" class="filtros">
            <div class="campo">
                <label for="texto" class="campo__etiqueta">Buscar</label>
                <input type="search" id="texto" name="texto" class="control" value="{{ request('texto') }}" maxlength="100">
            </div>
            <div class="campo">
                <label for="estado" class="campo__etiqueta">Estado</label>
                <select id="estado" name="estado" class="control">
                    <option value="">Todos</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->value }}" @selected(request('estado') === $estado->value)>
                            {{ $estado->obtenerEtiqueta() }}
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
            <button type="submit" class="boton boton--secundario">
                <span class="icono icono--boton" aria-hidden="true">filter_list</span>Filtrar
            </button>
        </form>

        @if ($recursos->isEmpty())
            <div class="estado-vacio">
                <span class="icono icono--vacio" aria-hidden="true">inbox</span>
                <p>No hay recursos registrados.</p>
            </div>
        @else
            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th scope="col">Recurso</th>
                            <th scope="col">Inventario</th>
                            <th scope="col">Área</th>
                            <th scope="col">Estado</th>
                            <th scope="col"><span class="visualmente-oculto">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recursos as $recurso)
                            <tr>
                                <td>
                                    <strong>{{ $recurso->nombre_recurso }}</strong>
                                    <div class="texto-secundario">
                                        {{ $recurso->tipo_recurso->obtenerEtiqueta() }}{{ $recurso->marca ? ' · ' . $recurso->marca : '' }}
                                        @unless ($recurso->activo) · Inactivo @endunless
                                    </div>
                                </td>
                                <td>{{ $recurso->numero_inventario }}</td>
                                <td>{{ $recurso->area->nombre_area }}</td>
                                <td>
                                    <span class="etiqueta {{ $recurso->estado->obtenerClaseEtiqueta() }}">
                                        {{ $recurso->estado->obtenerEtiqueta() }}
                                    </span>
                                </td>
                                <td class="tabla__acciones">
                                    <a href="{{ route('recursos.edit', $recurso) }}" class="boton boton--pequeno">
                                        <span class="icono icono--boton" aria-hidden="true">edit</span>Editar
                                    </a>
                                    @include('recursos.acciones-estado', ['recurso' => $recurso])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $recursos->links('partials.paginacion') }}
        @endif
    </section>
@endsection
