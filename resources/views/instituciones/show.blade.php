{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         show.blade.php
 * @path         resources/views/instituciones/show.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Detalle de una institución para el superadmin: plan actual,
 *               uso, renovación, cambio de estado (MBD Tabla 28) y cambio de
 *               plan.
 *
 * @requisitos   RF-003, RF-004, RF-006
 *
 * @see          docs/requisitos/BBB_CU002_AdministrarSuscripcion_v1.0.md; DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', $institucion->nombre_institucion)
@section('ruta', 'Instituciones')

@section('contenido')
    <div class="pila">
        <a href="{{ route('instituciones.index') }}" class="boton boton--enlace boton--pequeno alinear-inicio">
            <span class="icono" aria-hidden="true">arrow_back</span>Ver instituciones
        </a>

        @include('suscripcion.plan-actual',
            ['institucion' => $institucion, 'uso' => $uso, 'administradores' => $administradores])

        <div class="cuadricula-360">
            <section class="tarjeta tarjeta--relleno-24 pila--16 titulos" aria-labelledby="titulo-renovar">
                <div class="en-linea">
                    <span class="icono-tile icono-tile--40 icono-tile--azul"
                          aria-hidden="true"><span class="icono">autorenew</span></span>
                    <div class="titulos crece">
                        <h2 id="titulo-renovar" class="tarjeta__titulo">Renovar periodo</h2>
                        <span class="tarjeta__subtitulo">
                            Extiende la fecha de corte por la duración del plan y reactiva una suscripción en solo
                            lectura.
                        </span>
                    </div>
                </div>
                <form method="POST" action="{{ route('instituciones.renovar', $institucion) }}"
                      data-confirmar="¿Registrar la renovación de {{ $institucion->nombre_institucion }}?">
                    @csrf
                    <button type="submit" class="boton boton--primario"
                            @disabled($institucion->estado_suscripcion === \App\Enums\EstadoSuscripcion::Cancelada)>
                        <span class="icono" aria-hidden="true">check</span>Renovar
                    </button>
                </form>
            </section>

            <section class="tarjeta tarjeta--relleno-24 pila--16 titulos" aria-labelledby="titulo-estado">
                <div class="en-linea">
                    <span class="icono-tile icono-tile--40 icono-tile--ambar"
                          aria-hidden="true"><span class="icono">swap_horiz</span></span>
                    <div class="titulos crece">
                        <h2 id="titulo-estado" class="tarjeta__titulo">Cambiar estado</h2>
                        <span class="tarjeta__subtitulo">
                            Suspendida o cancelada cierra las sesiones de la institución.
                        </span>
                    </div>
                </div>
                @if (count($estados) === 0)
                    <p class="texto-sec">La suscripción cancelada no admite cambios.</p>
                @else
                    <form method="POST" action="{{ route('instituciones.estado', $institucion) }}" class="en-linea">
                        @csrf
                        @method('PUT')
                        <div class="selector crece">
                            <label for="estado_suscripcion" class="visualmente-oculto">Nuevo estado</label>
                            <select id="estado_suscripcion" name="estado_suscripcion" class="control control--compacto">
                                @foreach ($estados as $estado)
                                    <option value="{{ $estado->value }}">{{ $estado->obtenerEtiqueta() }}</option>
                                @endforeach
                            </select>
                            <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
                        </div>
                        <button type="submit" class="boton boton--secundario">Confirmar</button>
                    </form>
                @endif
            </section>
        </div>

        <section class="pila--12 titulos" aria-labelledby="titulo-planes">
            <h2 id="titulo-planes" class="subtitulo-pagina">Cambiar plan</h2>
            <div class="cuadricula-220">
                @foreach ($planes as $plan)
                    @php
                        $esActual = $plan->clave === $institucion->plan_suscripcion;
                        $cabe = $uso['areas'] <= $plan->limite_areas && $uso['recursos'] <= $plan->limite_recursos;
                    @endphp
                    <div class="tarjeta plan-opcion {{ $esActual ? 'plan-opcion--actual' : '' }}">
                        <div class="encabezado-pagina encabezado-pagina--centro">
                            <span class="plan-opcion__nombre">{{ $plan->nombre }}</span>
                            @if ($esActual)
                                <span class="insignia insignia--activo">Plan actual</span>
                            @endif
                        </div>
                        <span class="texto-13 texto-sec">
                            {{ $plan->limite_areas }} áreas · {{ number_format($plan->limite_recursos) }} recursos
                        </span>
                        @unless ($esActual)
                            <form method="POST" action="{{ route('instituciones.plan', $institucion) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="plan" value="{{ $plan->clave->value }}">
                                <button type="submit" class="boton boton--secundario boton--bloque" @disabled(! $cabe)
                                        aria-label="Cambiar al plan {{ $plan->nombre }}">Cambiar plan</button>
                            </form>
                        @endunless
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
