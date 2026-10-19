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
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Formulario de alta y edición de un área.
 *
 * @requisitos   RF-009
 *
 * @see          docs/requisitos/BBB_CU006_GestionarAreas_v1.0.md (pasos 2–5)
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', $area->exists ? 'Editar área' : 'Crear área')

@section('contenido')
    <div class="tarjeta contenedor-formulario">
        <form method="POST" novalidate
              action="{{ $area->exists ? route('areas.update', $area) : route('areas.store') }}">
            @csrf
            @if ($area->exists)
                @method('PUT')
            @endif
            @include('partials.campo', [
                'nombre' => 'nombre_area',
                'etiqueta' => 'Nombre del área',
                'valor' => $area->nombre_area,
            ])
            <div class="campo">
                <label for="descripcion" class="campo__etiqueta">Descripción (opcional)</label>
                <textarea id="descripcion" name="descripcion" class="control">{{ old('descripcion', $area->descripcion) }}</textarea>
            </div>
            <div class="acciones">
                <button type="submit" class="boton">
                    <span class="icono icono--boton" aria-hidden="true">save</span>Guardar
                </button>
                <a href="{{ route('areas.index') }}" class="boton boton--secundario">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
