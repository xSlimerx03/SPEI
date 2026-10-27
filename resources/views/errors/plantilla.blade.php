{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         plantilla.blade.php
 * @path         resources/views/errors/plantilla.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Página de error común. Variables: codigo, icono, titulo,
 *               mensaje (el de la excepción tiene prioridad si es seguro).
 *
 * @requisitos   RNF-008
 *
 * @see          Manual de Programación v1.2, Tabla 72; DT-028
 * ============================================================================
--}}
@extends('layouts.publico')

@section('titulo', $titulo)

@section('contenido')
    <div class="acceso__tarjeta">
        @include('partials.marca-acceso')
        <div class="vacio vacio--compacto">
            <span class="icono" aria-hidden="true">{{ $icono }}</span>
            <span class="mono texto-sec">Error {{ $codigo }}</span>
            <h1 class="vacio__titulo">{{ $titulo }}</h1>
            <p class="vacio__texto">{{ $mensaje }}</p>
        </div>
        <div class="acciones acciones--fin">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}"
               class="boton boton--secundario">
                <span class="icono" aria-hidden="true">arrow_back</span>Regresar
            </a>
            @auth
                <a href="{{ route('panel') }}" class="boton boton--primario">Abrir inicio</a>
            @else
                <a href="{{ route('login') }}" class="boton boton--primario">Iniciar sesión</a>
            @endauth
        </div>
    </div>
@endsection
