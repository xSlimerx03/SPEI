{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         show.blade.php
 * @path         resources/views/suscripcion/show.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Suscripción del administrador institucional (prototipo
 *               hi-fi): alerta de vencimiento, plan actual con vigencia, uso
 *               del plan, planes disponibles con cambio de plan y
 *               administradores del sistema. La renovación la registra el
 *               superadmin (CU-002 CA7), por eso no se ofrece aquí.
 *
 * @requisitos   RF-003, RF-004, RF-005, RF-008, RN-010
 *
 * @see          docs/requisitos/BBB_CU002_AdministrarSuscripcion_v1.0.md; DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Suscripción')

@php
    $dias = $institucion->obtenerDiasParaCorte();
    $esSoloLectura = $institucion->estado_suscripcion === \App\Enums\EstadoSuscripcion::SoloLectura;
@endphp

@section('contenido')
    <div class="pila">
        @if ($esSoloLectura)
            <div class="alerta alerta--advertencia alerta--grande" role="status">
                <span class="icono" aria-hidden="true">lock_clock</span>
                <span class="crece"><strong>Tu suscripción venció el {{ \App\Support\FormatoFecha::conAnio($institucion->fecha_corte) }}.</strong>
                    La institución está en modo solo lectura hasta que BigBitByte registre la renovación.</span>
            </div>
        @elseif ($dias <= 30)
            <div class="alerta alerta--info alerta--grande" role="status">
                <span class="icono" aria-hidden="true">notifications_active</span>
                <span class="crece"><strong>Tu plan vence en {{ $dias }} {{ $dias === 1 ? 'día' : 'días' }}.</strong>
                    Solicita la renovación antes del {{ \App\Support\FormatoFecha::conAnio($institucion->fecha_corte) }} para evitar el modo solo lectura.</span>
            </div>
        @endif

        @include('suscripcion.plan-actual', [
            'institucion' => $institucion,
            'uso' => $uso,
            'administradores' => $administradores->count(),
        ])

        <section class="pila--12 titulos" aria-labelledby="titulo-planes">
            <h2 id="titulo-planes" class="subtitulo-pagina">Planes disponibles</h2>
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
                        <span><span class="plan-opcion__precio">${{ number_format((float) $plan->monto_mensual, 2) }}</span>
                            <span class="texto-13 texto-sec">MXN / mes</span></span>
                        <div class="pila--8 titulos">
                            <span class="plan-opcion__caracteristica"><span class="icono" aria-hidden="true">check</span>{{ $plan->limite_areas }} áreas</span>
                            <span class="plan-opcion__caracteristica"><span class="icono" aria-hidden="true">check</span>{{ number_format($plan->limite_recursos) }} recursos</span>
                            <span class="plan-opcion__caracteristica"><span class="icono" aria-hidden="true">check</span>{{ $plan->duracion_dias }} días por periodo</span>
                        </div>
                        @unless ($esActual)
                            <form method="POST" action="{{ route('suscripcion.plan') }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="plan" value="{{ $plan->clave->value }}">
                                <button type="submit" class="boton boton--secundario boton--bloque" @disabled(! $cabe)
                                        aria-label="Cambiar al plan {{ $plan->nombre }}"
                                        @unless ($cabe) aria-describedby="plan-{{ $plan->clave->value }}-ayuda" @endunless>
                                    Cambiar plan
                                </button>
                            </form>
                            @unless ($cabe)
                                <span id="plan-{{ $plan->clave->value }}-ayuda" class="campo__ayuda">No admite las áreas o recursos ya registrados.</span>
                            @endunless
                        @endunless
                    </div>
                @endforeach
            </div>
        </section>

        <section class="tarjeta" aria-labelledby="titulo-administradores">
            <div class="tarjeta__cabecera">
                <div class="titulos">
                    <h2 id="titulo-administradores" class="tarjeta__titulo">Administradores del sistema</h2>
                    <span class="tarjeta__subtitulo">Gestionan áreas, recursos y préstamos</span>
                </div>
                <a href="{{ route('administradores.create') }}" class="boton boton--primario">
                    <span class="icono" aria-hidden="true">person_add</span>Crear
                </a>
            </div>
            @forelse ($administradores->take(5) as $administrador)
                @include('administradores.fila', ['administrador' => $administrador])
            @empty
                <div class="vacio vacio--compacto">
                    <span class="icono" aria-hidden="true">manage_accounts</span>
                    <span class="vacio__texto">Aún no hay administradores del sistema.</span>
                </div>
            @endforelse
            @if ($administradores->count() > 5)
                <div class="desplegable__pie">
                    <a href="{{ route('administradores.index') }}" class="texto-13 negrita">Ver todos ({{ $administradores->count() }})</a>
                </div>
            @endif
        </section>
    </div>
@endsection
