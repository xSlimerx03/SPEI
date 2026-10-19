{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         edit.blade.php
 * @path         resources/views/planes/edit.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Edición de los parámetros de un plan por el superadmin.
 *
 * @requisitos   RF-007
 *
 * @see          docs/requisitos/BBB_CU003_ModificarPlanes_v1.0.md (pasos 2–5)
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Editar plan ' . $plan->nombre)

@section('contenido')
    <div class="tarjeta contenedor-formulario">
        <form method="POST" action="{{ route('planes.update', $plan) }}" novalidate>
            @csrf
            @method('PUT')
            @include('partials.campo', ['nombre' => 'nombre', 'etiqueta' => 'Nombre', 'valor' => $plan->nombre])
            <div class="fila-campos">
                @include('partials.campo', [
                    'nombre' => 'monto_mensual',
                    'etiqueta' => 'Monto mensual (MXN)',
                    'tipo' => 'number',
                    'valor' => $plan->monto_mensual,
                    'atributos' => 'min="0" step="0.01"',
                ])
                @include('partials.campo', [
                    'nombre' => 'duracion_dias',
                    'etiqueta' => 'Duración en días',
                    'tipo' => 'number',
                    'valor' => $plan->duracion_dias,
                    'atributos' => 'min="1"',
                ])
                @include('partials.campo', [
                    'nombre' => 'limite_areas',
                    'etiqueta' => 'Límite de áreas',
                    'tipo' => 'number',
                    'valor' => $plan->limite_areas,
                    'atributos' => 'min="1"',
                ])
                @include('partials.campo', [
                    'nombre' => 'limite_recursos',
                    'etiqueta' => 'Límite de recursos',
                    'tipo' => 'number',
                    'valor' => $plan->limite_recursos,
                    'atributos' => 'min="1"',
                ])
            </div>
            <label class="casilla">
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $plan->activo))>
                Ofrecer en la contratación
            </label>
            <p class="texto-secundario">Los límites nuevos se aplican a todas las instituciones con este plan.</p>
            <div class="acciones">
                <button type="submit" class="boton">
                    <span class="icono icono--boton" aria-hidden="true">save</span>Guardar
                </button>
                <a href="{{ route('planes.index') }}" class="boton boton--secundario">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
