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
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Edición de los parámetros de un plan por el superadmin.
 *
 * @requisitos   RF-007
 *
 * @see          docs/requisitos/BBB_CU003_ModificarPlanes_v1.0.md (pasos 2–5); DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Editar plan ' . $plan->nombre)
@section('ruta', 'Planes')

@section('contenido')
    <div class="contenedor-720">
        <form method="POST" action="{{ route('planes.update', $plan) }}" class="tarjeta tarjeta--relleno-24 pila--20 titulos" novalidate>
            @csrf
            @method('PUT')
            @include('partials.campo', [
                'nombre' => 'nombre',
                'etiqueta' => 'Nombre',
                'valor' => $plan->nombre,
                'icono' => 'sell',
                'atributos' => 'maxlength="100"',
            ])
            <div class="cuadricula-campos">
                @include('partials.campo', [
                    'nombre' => 'monto_mensual',
                    'etiqueta' => 'Monto mensual (MXN)',
                    'tipo' => 'number',
                    'valor' => $plan->monto_mensual,
                    'icono' => 'payments',
                    'atributos' => 'min="0" step="0.01"',
                ])
                @include('partials.campo', [
                    'nombre' => 'duracion_dias',
                    'etiqueta' => 'Duración en días',
                    'tipo' => 'number',
                    'valor' => $plan->duracion_dias,
                    'icono' => 'event',
                    'atributos' => 'min="1" max="366"',
                ])
                @include('partials.campo', [
                    'nombre' => 'limite_areas',
                    'etiqueta' => 'Límite de áreas',
                    'tipo' => 'number',
                    'valor' => $plan->limite_areas,
                    'icono' => 'apartment',
                    'atributos' => 'min="1"',
                ])
                @include('partials.campo', [
                    'nombre' => 'limite_recursos',
                    'etiqueta' => 'Límite de recursos',
                    'tipo' => 'number',
                    'valor' => $plan->limite_recursos,
                    'icono' => 'inventory_2',
                    'atributos' => 'min="1"',
                ])
            </div>
            <label class="interruptor">
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $plan->activo))>
                <span class="texto-15">Ofrecer en la contratación</span>
            </label>
            <div class="alerta alerta--info">
                <span class="icono" aria-hidden="true">info</span>
                <span>Los límites nuevos se aplican a todas las instituciones con este plan y el cambio queda en auditoría.</span>
            </div>
            <div class="pie-pasos">
                <a href="{{ route('planes.index') }}" class="boton boton--fantasma boton--grande">Cancelar</a>
                <button type="submit" class="boton boton--primario boton--grande">
                    <span class="icono" aria-hidden="true">save</span>Guardar
                </button>
            </div>
        </form>
    </div>
@endsection
