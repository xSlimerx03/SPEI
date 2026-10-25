{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Panel principal
 * @file         superadmin.blade.php
 * @path         resources/views/panel/superadmin.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Panel del superadmin con el lenguaje del prototipo:
 *               instituciones por estado y próximas a vencer.
 *
 * @requisitos   RF-004, RF-006
 *
 * @see          DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Panel de la plataforma')
@section('ruta', 'Plataforma')

@php
    $tarjetas = [
        'activa' => ['Activas', 'verified', 'icono-tile--verde'],
        'solo_lectura' => ['Solo lectura', 'lock_clock', 'icono-tile--ambar'],
        'suspendida' => ['Suspendidas', 'pause_circle', 'icono-tile--rojo'],
        'cancelada' => ['Canceladas', 'cancel', 'icono-tile--azul'],
    ];
@endphp

@section('contenido')
    <div class="pila">
        <div class="encabezado-pagina">
            <div class="pila--2 titulos">
                <span class="texto-13 texto-sec">{{ ucfirst(now()->translatedFormat('l j \d\e F \d\e Y')) }}</span>
                <span class="subtitulo-pagina">{{ array_sum($metricas) }} instituciones en la plataforma.</span>
            </div>
            <div class="acciones">
                <a href="{{ route('planes.index') }}" class="boton boton--secundario">
                    <span class="icono" aria-hidden="true">sell</span>Ver planes
                </a>
                <a href="{{ route('instituciones.index') }}" class="boton boton--primario">
                    <span class="icono" aria-hidden="true">domain</span>Ver instituciones
                </a>
            </div>
        </div>

        <div class="cuadricula-kpi">
            @foreach ($tarjetas as $estado => [$etiqueta, $icono, $tono])
                @include('panel.indicador', [
                    'etiqueta' => $etiqueta,
                    'valor' => $metricas[$estado],
                    'icono' => $icono,
                    'tono' => $tono,
                    'detalle' => 'Suscripciones ' . mb_strtolower($etiqueta),
                    'claseDetalle' => '',
                ])
            @endforeach
        </div>

        <section class="tarjeta" aria-labelledby="titulo-vencer">
            <div class="tarjeta__cabecera">
                <div class="titulos">
                    <h2 id="titulo-vencer" class="tarjeta__titulo">Próximas a vencer</h2>
                    <span class="tarjeta__subtitulo">Suscripciones activas o en solo lectura, por fecha de corte</span>
                </div>
                <a href="{{ route('instituciones.index') }}" class="texto-13 negrita">Ver todas</a>
            </div>
            @forelse ($porVencer as $institucion)
                @php
                    $dias = $institucion->obtenerDiasParaCorte();
                    $claseDias = match ($institucion->estado_suscripcion) {
                        \App\Enums\EstadoSuscripcion::Activa => 'insignia--exito',
                        \App\Enums\EstadoSuscripcion::SoloLectura => 'insignia--pendiente',
                        default => 'insignia--peligro',
                    };
                @endphp
                <a href="{{ route('instituciones.show', $institucion) }}" class="fila-lista enlace-lista">
                    <span class="icono-tile" aria-hidden="true"><span class="icono">account_balance</span></span>
                    <div class="titulos crece-220">
                        <span class="negrita">{{ $institucion->nombre_institucion }}</span>
                        <span class="texto-12 texto-sec">Plan {{ $institucion->plan_suscripcion->obtenerEtiqueta() }} · {{ $institucion->dominio_correo }}</span>
                    </div>
                    <div class="titulos texto-13 alinear-fin">
                        <span class="negrita">{{ \App\Support\FormatoFecha::conAnio($institucion->fecha_corte) }}</span>
                        <span class="{{ $dias <= 7 ? 'texto-error negrita' : 'texto-sec' }}">{{ $dias >= 0 ? 'En ' . $dias . ' días' : 'Vencida' }}</span>
                    </div>
                    <span class="insignia {{ $claseDias }}">
                        {{ $institucion->estado_suscripcion->obtenerEtiqueta() }}
                    </span>
                </a>
            @empty
                <div class="vacio vacio--compacto">
                    <span class="icono" aria-hidden="true">domain</span>
                    <span class="vacio__texto">Aún no hay instituciones registradas.</span>
                </div>
            @endforelse
        </section>
    </div>
@endsection
