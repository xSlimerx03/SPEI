{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.7: Notificaciones y Recordatorios
 * @file         index.blade.php
 * @path         resources/views/notificaciones/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Bandeja de notificaciones del usuario.
 *
 * @requisitos   RF-005, RF-046
 *
 * @see          DT-022
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Notificaciones')

@section('contenido')
    @forelse ($notificaciones as $notificacion)
        <article class="tarjeta">
            <div class="recurso-tarjeta__cabecera">
                <span class="icono" aria-hidden="true">{{ $notificacion->leida ? 'drafts' : 'mark_email_unread' }}</span>
                <h2 class="tarjeta__titulo">{{ $notificacion->titulo }}</h2>
            </div>
            <p>{{ $notificacion->mensaje }}</p>
            <div class="acciones">
                <span class="texto-secundario">{{ $notificacion->fecha_creacion?->format('d/m/Y H:i') }}</span>
                @unless ($notificacion->leida)
                    <form method="POST" action="{{ route('notificaciones.leida', $notificacion) }}" class="formulario-en-linea">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="boton boton--pequeno boton--secundario">
                            <span class="icono icono--boton" aria-hidden="true">check</span>Marcar leída
                        </button>
                    </form>
                @endunless
            </div>
        </article>
    @empty
        <div class="tarjeta estado-vacio">
            <span class="icono icono--vacio" aria-hidden="true">inbox</span>
            <p>No tiene notificaciones.</p>
        </div>
    @endforelse
    {{ $notificaciones->links('partials.paginacion') }}
@endsection
