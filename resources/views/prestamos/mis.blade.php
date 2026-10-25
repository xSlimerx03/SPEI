{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         mis.blade.php
 * @path         resources/views/prestamos/mis.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Mis préstamos (prototipo hi-fi): pestañas "Activos y próximos"
 *               e "Historial", tarjetas con QR de entrega o devolución y
 *               cancelación, comprobante y estado de la cuenta.
 *
 * @requisitos   RF-028, RF-034, RF-036, RF-040, RF-041, RF-051
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Mis préstamos); DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Mis préstamos')

@php
    $tolerancia = max(15, min(20, (int) config('spei.prestamos.tolerancia_recogida')));
@endphp

@section('contenido')
    <div class="prestamos-layout">
        <div class="prestamos-layout__lista pila--20 titulos">
            <nav class="pestanas" aria-label="Mis préstamos">
                <a href="{{ route('prestamos.mis') }}" class="pestana {{ $historial ? '' : 'pestana--activa' }}"
                   @unless ($historial) aria-current="page" @endunless>
                    Activos y próximos<span class="pestana__contador">{{ $abiertos->count() }}</span>
                </a>
                <a href="{{ route('prestamos.mis', ['vista' => 'historial']) }}" class="pestana {{ $historial ? 'pestana--activa' : '' }}"
                   @if ($historial) aria-current="page" @endif>
                    Historial<span class="pestana__contador">{{ $totalCerrados }}</span>
                </a>
            </nav>

            @unless ($historial)
                @forelse ($abiertos as $prestamo)
                    @php
                        $estado = $prestamo->obtenerEstado();
                        $esPendiente = $estado === \App\Enums\EstadoPrestamo::Pendiente;
                        $nombres = $prestamo->detalles->map(fn (\App\Models\DetallePrestamo $d): string => $d->recurso->nombre_recurso)->implode(' · ');
                        $area = $prestamo->detalles->first()?->recurso?->area?->nombre_area;
                    @endphp
                    <article class="tarjeta prestamo-tarjeta">
                        <span class="imagen-pendiente imagen-pendiente--miniatura imagen-pendiente--72" aria-hidden="true">
                            <span class="icono">{{ $prestamo->detalles->first()?->recurso?->tipo_recurso === \App\Enums\TipoRecurso::Espacio ? 'meeting_room' : 'devices' }}</span>
                        </span>
                        <div class="prestamo-tarjeta__info">
                            <div class="en-linea en-linea--8">
                                <a href="{{ route('prestamos.show', $prestamo) }}" class="folio">{{ $prestamo->obtenerFolio() }}</a>
                                <span class="insignia {{ $estado->obtenerClaseInsignia() }}">{{ $estado->obtenerEtiqueta() }}</span>
                            </div>
                            <span class="texto-16 negrita">{{ $nombres }}</span>
                            <span class="dato-icono"><span class="icono" aria-hidden="true">event</span>{{ \App\Support\FormatoFecha::periodo($prestamo->fecha_hora_inicio, $prestamo->fecha_hora_fin) }}</span>
                            <span class="nota-contextual">
                                @if ($esPendiente)
                                    <span class="icono" aria-hidden="true">timer</span>
                                    Recoge en el mostrador de {{ $area }}. Tienes {{ $tolerancia }} minutos de tolerancia desde la hora de inicio.
                                @elseif ($prestamo->fecha_hora_fin->isPast())
                                    <span class="icono texto-error" aria-hidden="true">assignment_late</span>
                                    La devolución está atrasada; regrésalo cuanto antes en el mostrador de {{ $area }}.
                                @else
                                    <span class="icono" aria-hidden="true">assignment_return</span>
                                    Devuélvelo en el mostrador de {{ $area }} antes de las {{ $prestamo->fecha_hora_fin->format('H:i') }}.
                                @endif
                            </span>
                        </div>
                        <div class="prestamo-tarjeta__acciones">
                            <form method="POST" action="{{ route('prestamos.token', $prestamo) }}" target="_blank" class="formulario-en-linea">
                                @csrf
                                <button type="submit" class="boton boton--primario boton--grande boton--bloque"
                                        data-qr-url="{{ route('prestamos.token', $prestamo) }}"
                                        data-qr-titulo="{{ $esPendiente ? 'QR de entrega' : 'QR de devolución' }}"
                                        data-qr-folio="{{ $prestamo->obtenerFolio() }}">
                                    <span class="icono" aria-hidden="true">qr_code_2</span>Mostrar QR
                                </button>
                            </form>
                            @if ($esPendiente)
                                <button type="button" class="boton boton--pequeno boton--peligro-texto"
                                        data-abrir-modal="modal-cancelar-{{ $prestamo->id_prestamo }}">Cancelar solicitud</button>
                                @push('modales')
                                    @include('prestamos.modal-cancelar', ['prestamo' => $prestamo, 'minimo' => 5, 'operador' => false])
                                @endpush
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="tarjeta vacio">
                        <span class="icono" aria-hidden="true">assignment</span>
                        <span class="vacio__titulo">No tienes préstamos activos</span>
                        <span class="vacio__texto">Explora el catálogo y solicita lo que necesites.</span>
                        <a href="{{ route('prestamos.create') }}" class="boton boton--primario">
                            <span class="icono" aria-hidden="true">add</span>Solicitar préstamo
                        </a>
                    </div>
                @endforelse
            @else
                <section class="tarjeta" aria-label="Historial">
                    @forelse ($cerrados as $prestamo)
                        @php($estado = $prestamo->obtenerEstado())
                        <div class="fila-lista">
                            <span class="icono {{ $estado === \App\Enums\EstadoPrestamo::Devuelto ? 'icono--verde' : 'icono--gris' }}" aria-hidden="true">
                                {{ $estado === \App\Enums\EstadoPrestamo::Devuelto ? 'task_alt' : 'event_busy' }}
                            </span>
                            <div class="titulos crece-220">
                                <span class="texto-15 negrita">{{ $prestamo->detalles->map(fn (\App\Models\DetallePrestamo $d): string => $d->recurso->nombre_recurso)->implode(' · ') }}</span>
                                <span class="texto-13 texto-sec">
                                    <span class="mono">{{ $prestamo->obtenerFolio() }}</span> ·
                                    {{ \App\Support\FormatoFecha::conAnio($prestamo->fecha_hora_inicio) }} ·
                                    {{ $estado->obtenerEtiqueta() }} · {{ $prestamo->obtenerSubestado() }}
                                </span>
                            </div>
                            <a href="{{ route('prestamos.show', $prestamo) }}" class="boton boton--secundario boton--pequeno">
                                <span class="icono" aria-hidden="true">receipt_long</span>Ver comprobante
                            </a>
                        </div>
                    @empty
                        <div class="vacio vacio--compacto">
                            <span class="icono" aria-hidden="true">history</span>
                            <span class="vacio__texto">Aún no tienes préstamos concluidos.</span>
                        </div>
                    @endforelse
                    {{ $cerrados->links('partials.paginacion') }}
                </section>
            @endunless
        </div>

        <aside class="columna-lateral" aria-label="Tu historial">
            <section class="tarjeta tarjeta--relleno pila--12 titulos">
                <h2 class="tarjeta__titulo">Tu historial</h2>
                <div class="en-linea">
                    <span class="avatar avatar--48 {{ $usuario->bloqueado ? 'avatar--rojo' : 'avatar--verde' }}" aria-hidden="true">
                        <span class="icono">{{ $usuario->bloqueado ? 'gpp_maybe' : 'verified_user' }}</span>
                    </span>
                    <div class="titulos">
                        <span class="texto-15 negrita">{{ $usuario->bloqueado ? 'Cuenta bloqueada' : 'Cuenta activa' }}</span>
                        <span class="texto-13 texto-sec">
                            {{ $usuario->bloqueado ? 'No puedes crear solicitudes; acude con tu administrador.' : 'Puedes crear solicitudes nuevas.' }}
                        </span>
                    </div>
                </div>
            </section>
            <section class="tarjeta tarjeta--relleno dos-columnas" aria-label="Estadísticas">
                <div class="titulos">
                    <span class="titulo-grande">{{ $estadisticas['prestamos'] }}</span>
                    <span class="texto-13 texto-sec">Préstamos en los últimos 6 meses</span>
                </div>
                <div class="titulos">
                    <span class="titulo-grande">{{ $estadisticas['a_tiempo'] === null ? '—' : $estadisticas['a_tiempo'] . ' %' }}</span>
                    <span class="texto-13 texto-sec">Devoluciones a tiempo</span>
                </div>
            </section>
        </aside>
    </div>

    @push('modales')
        @include('prestamos.modal-qr')
    @endpush
@endsection
