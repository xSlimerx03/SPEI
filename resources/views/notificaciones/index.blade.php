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
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Bandeja de notificaciones con el estilo del desplegable del
 *               prototipo: leídas y sin leer, marcar una o todas.
 *
 * @requisitos   RF-005, RF-046
 *
 * @see          DT-022, DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Notificaciones')

@section('contenido')
    <section class="tarjeta contenedor-720" aria-label="Notificaciones">
        <div class="tarjeta__cabecera">
            <div class="titulos">
                <h2 class="tarjeta__titulo">Todas las notificaciones</h2>
                <span class="tarjeta__subtitulo">Avisos de tus préstamos y de la institución</span>
            </div>
            @if ($notificaciones->contains('leida', false))
                <form method="POST" action="{{ route('notificaciones.leidas') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="boton boton--secundario boton--pequeno">
                        <span class="icono" aria-hidden="true">done_all</span>Marcar leídas
                    </button>
                </form>
            @endif
        </div>
        @forelse ($notificaciones as $notificacion)
            <div class="fila-lista fila-lista--arriba {{ $notificacion->leida ? 'notificacion-item--leida' : '' }}">
                <span class="icono-tile {{ $notificacion->leida ? '' : 'icono-tile--azul' }}" aria-hidden="true">
                    <span class="icono">{{ $notificacion->leida ? 'drafts' : 'mark_email_unread' }}</span>
                </span>
                <div class="pila--2 titulos crece-220">
                    <span class="negrita">{{ $notificacion->titulo }}</span>
                    <span class="texto-13 texto-apoyo">{{ $notificacion->mensaje }}</span>
                    <span class="texto-12 texto-sec">
                        {{ $notificacion->fecha_creacion?->diffForHumans() }} ·
                        {{ $notificacion->fecha_creacion?->format('d/m/Y H:i') }}
                    </span>
                </div>
                @unless ($notificacion->leida)
                    <form method="POST" action="{{ route('notificaciones.leida', $notificacion) }}"
                          class="formulario-en-linea">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="boton boton--fantasma boton--pequeno">
                            <span class="icono" aria-hidden="true">check</span>Marcar leída
                        </button>
                    </form>
                @endunless
            </div>
        @empty
            <div class="vacio">
                <span class="icono" aria-hidden="true">notifications_off</span>
                <span class="vacio__titulo">No tienes notificaciones</span>
                <span class="vacio__texto">Aquí verás los avisos de tus préstamos y de la institución.</span>
            </div>
        @endforelse
        {{ $notificaciones->links('partials.paginacion') }}
    </section>
@endsection
