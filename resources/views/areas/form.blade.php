{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         form.blade.php
 * @path         resources/views/areas/form.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Formulario de alta y edición de un área.
 *
 * @requisitos   RF-009
 *
 * @see          docs/requisitos/BBB_CU006_GestionarAreas_v1.0.md (pasos 2–5); DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', $area->exists ? 'Editar área' : 'Crear área')
@section('ruta', 'Áreas')

@section('contenido')
    <div class="contenedor-720">
        <form method="POST" action="{{ $area->exists ? route('areas.update', $area) : route('areas.store') }}"
              class="tarjeta tarjeta--relleno-24 pila--20 titulos" novalidate>
            @csrf
            @if ($area->exists)
                @method('PUT')
            @endif
            @include('partials.campo', [
                'nombre' => 'nombre_area',
                'etiqueta' => 'Nombre del área',
                'valor' => $area->nombre_area,
                'icono' => 'apartment',
                'marcador' => 'Ej. Biblioteca',
                'atributos' => 'maxlength="150"',
            ])
            <div class="campo">
                <label for="descripcion" class="campo__etiqueta">Descripción (opcional)</label>
                <textarea id="descripcion" name="descripcion" class="control" maxlength="1000"
                          @error('descripcion') aria-invalid="true"
                          @enderror>{{ old('descripcion', $area->descripcion) }}</textarea>
                @error('descripcion')
                    <span class="campo__error"><span class="icono" aria-hidden="true">error</span>{{ $message }}</span>
                @enderror
            </div>
            <div class="pie-pasos">
                <a href="{{ route('areas.index') }}" class="boton boton--fantasma boton--grande">Cancelar</a>
                <button type="submit" class="boton boton--primario boton--grande">
                    <span class="icono" aria-hidden="true">save</span>Guardar
                </button>
            </div>
        </form>
    </div>
@endsection
