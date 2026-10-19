{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         form.blade.php
 * @path         resources/views/recursos/form.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Alta y edición de un recurso; el número de inventario acepta
 *               lector de código de barras.
 *
 * @requisitos   RF-010, RF-011
 *
 * @see          docs/requisitos/BBB_CU007_RegistrarRecurso_v1.0.md
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', $recurso->exists ? 'Editar recurso' : 'Registrar recurso')

@section('contenido')
    <div class="tarjeta contenedor-formulario">
        @if ($areas->isEmpty())
            <div class="estado-vacio">
                <span class="icono icono--vacio" aria-hidden="true">inbox</span>
                <p>Primero cree un área activa para registrar recursos.</p>
            </div>
        @else
            <form method="POST" novalidate
                  action="{{ $recurso->exists ? route('recursos.update', $recurso) : route('recursos.store') }}">
                @csrf
                @if ($recurso->exists)
                    @method('PUT')
                @endif
                <div class="fila-campos">
                    <div class="campo">
                        <label for="id_area" class="campo__etiqueta">Área</label>
                        <select id="id_area" name="id_area" class="control" required>
                            @foreach ($areas as $area)
                                <option value="{{ $area->id_area }}"
                                        @selected((int) old('id_area', $recurso->id_area) === $area->id_area)>
                                    {{ $area->nombre_area }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo">
                        <label for="tipo_recurso" class="campo__etiqueta">Tipo</label>
                        <select id="tipo_recurso" name="tipo_recurso" class="control" required>
                            @foreach ($tipos as $tipo)
                                <option value="{{ $tipo->value }}"
                                        @selected(old('tipo_recurso', $recurso->tipo_recurso?->value) === $tipo->value)>
                                    {{ $tipo->obtenerEtiqueta() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @include('partials.campo', [
                    'nombre' => 'nombre_recurso',
                    'etiqueta' => 'Nombre del recurso',
                    'valor' => $recurso->nombre_recurso,
                ])
                <div class="fila-campos">
                    @include('partials.campo', [
                        'nombre' => 'numero_inventario',
                        'etiqueta' => 'Número de inventario',
                        'valor' => $recurso->numero_inventario,
                        'ayuda' => 'Puede capturarlo con el lector de código de barras.',
                    ])
                    @include('partials.campo', [
                        'nombre' => 'numero_serie',
                        'etiqueta' => 'Número de serie (opcional)',
                        'valor' => $recurso->numero_serie,
                        'requerido' => false,
                    ])
                    @include('partials.campo', [
                        'nombre' => 'marca',
                        'etiqueta' => 'Marca (opcional)',
                        'valor' => $recurso->marca,
                        'requerido' => false,
                    ])
                    @include('partials.campo', [
                        'nombre' => 'anio_adquisicion',
                        'etiqueta' => 'Año de adquisición (opcional)',
                        'tipo' => 'number',
                        'valor' => $recurso->anio_adquisicion,
                        'requerido' => false,
                        'atributos' => 'min="1990" max="' . now()->year . '"',
                    ])
                </div>
                @unless ($recurso->exists)
                    <div class="campo">
                        <label for="estado" class="campo__etiqueta">Estado inicial</label>
                        <select id="estado" name="estado" class="control">
                            <option value="disponible" @selected(old('estado') !== 'mantenimiento')>Disponible</option>
                            <option value="mantenimiento" @selected(old('estado') === 'mantenimiento')>Mantenimiento</option>
                        </select>
                    </div>
                @endunless
                <div class="acciones">
                    <button type="submit" class="boton">
                        <span class="icono icono--boton" aria-hidden="true">save</span>Guardar
                    </button>
                    <a href="{{ route('recursos.index') }}" class="boton boton--secundario">Cancelar</a>
                </div>
            </form>
        @endif
    </div>
@endsection
