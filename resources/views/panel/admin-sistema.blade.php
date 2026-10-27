{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Panel principal
 * @file         admin-sistema.blade.php
 * @path         resources/views/panel/admin-sistema.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Dashboard del administrador del sistema (prototipo hi-fi):
 *               saludo, cuatro indicadores, préstamos por día (14 días),
 *               estado del inventario, solicitudes por atender y agenda de
 *               mostrador. Todos los datos son del tenant y del área asignada.
 *
 * @requisitos   RF-013, RF-049, RF-054
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Dashboard); DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Dashboard')

@php
    $hora = (int) now()->format('G');
    $saludo = match (true) {
        $hora < 12 => 'Buen día',
        $hora < 19 => 'Buenas tardes',
        default => 'Buenas noches',
    };
    $nombre = explode(' ', trim($usuario->nombre_completo))[0];
    $maximo = max(5, (int) (ceil(max(array_column($serie, 'total') ?: [0]) / 5) * 5));
    $totalInventario = max(1, array_sum($inventario));
@endphp

@section('contenido')
    <div class="pila">
        <div class="encabezado-pagina">
            <div class="pila--2 titulos">
                <span class="texto-13 texto-sec">{{ ucfirst(now()->translatedFormat('l j \d\e F \d\e Y')) }}</span>
                <span class="subtitulo-pagina">
                    {{ $saludo }}, {{ $nombre }}.
                    @if ($indicadores['pendientes'] === 1)
                        Hay 1 reserva por recoger.
                    @else
                        Hay {{ $indicadores['pendientes'] }} reservas por recoger.
                    @endif
                </span>
            </div>
            <div class="acciones">
                <a href="{{ route('reportes.index') }}" class="boton boton--secundario">
                    <span class="icono" aria-hidden="true">bar_chart</span>Ver reportes
                </a>
                <a href="{{ route('prestamos.index', ['estado' => \App\Enums\EstadoPrestamo::Pendiente->value]) }}"
                   class="boton boton--primario">
                    <span class="icono" aria-hidden="true">pending_actions</span>Revisar solicitudes
                </a>
            </div>
        </div>

        <div class="cuadricula-kpi">
            @include('panel.indicador', [
                'etiqueta' => 'Préstamos activos',
                'valor' => $indicadores['activos'],
                'icono' => 'assignment_turned_in',
                'tono' => 'icono-tile--azul',
                'detalle' => '+' . $indicadores['activos_hoy'] . ' entregados hoy',
                'claseDetalle' => 'kpi__detalle--verde',
            ])
            @include('panel.indicador', [
                'etiqueta' => 'Reservas por recoger',
                'valor' => $indicadores['pendientes'],
                'icono' => 'pending_actions',
                'tono' => 'icono-tile--ambar',
                'detalle' => $indicadores['pendientes_hoy'] . ' programadas para hoy',
                'claseDetalle' => 'kpi__detalle--ambar',
            ])
            @include('panel.indicador', [
                'etiqueta' => 'Devoluciones atrasadas',
                'valor' => $indicadores['atrasados'],
                'icono' => 'assignment_late',
                'tono' => 'icono-tile--rojo',
                'detalle' => $indicadores['atrasados'] > 0 ? 'Requieren seguimiento en mostrador' : 'Todo al corriente',
                'claseDetalle' => $indicadores['atrasados'] > 0 ? 'kpi__detalle--rojo' : '',
            ])
            @include('panel.indicador', [
                'etiqueta' => 'Recursos disponibles',
                'valor' => $indicadores['disponibles'],
                'icono' => 'inventory_2',
                'tono' => 'icono-tile--verde',
                'detalle' => 'de ' . $indicadores['total_recursos'] . ' en inventario · '
                    . round($indicadores['disponibles'] * 100 / max(1, $indicadores['total_recursos'])) . ' %',
                'claseDetalle' => '',
            ])
        </div>

        <div class="cuadricula-paneles cuadricula-paneles--estirar">
            <section class="tarjeta tarjeta--relleno pila--16 titulos ocupa-2" aria-labelledby="titulo-serie">
                <div class="encabezado-pagina encabezado-pagina--centro">
                    <div class="titulos">
                        <h2 id="titulo-serie" class="tarjeta__titulo">Préstamos por día</h2>
                        <span class="tarjeta__subtitulo">
                            Últimos 14 días · {{ $serie[0]['fecha']->translatedFormat('j M') }} –
                            {{ now()->translatedFormat('j M Y') }}
                        </span>
                    </div>
                    <div class="leyenda" aria-hidden="true">
                        <span class="leyenda__item">
                            <span class="leyenda__muestra color-previo"></span>Días previos
                        </span>
                        <span class="leyenda__item">
                            <span class="leyenda__muestra color-prestado"></span>Hoy (parcial)
                        </span>
                    </div>
                </div>
                <div class="grafica-barras" role="img"
                     aria-label="Préstamos por día: {{ collect($serie)
                         ->map(fn (array $d): string => $d['fecha']->format('d/m') . ' ' . $d['total'])
                         ->implode(', ') }}">
                    <div class="grafica-barras__eje" aria-hidden="true">
                        <span>{{ $maximo }}</span><span>{{ intdiv($maximo, 2) }}</span><span>0</span>
                    </div>
                    <div class="grafica-barras__area" aria-hidden="true">
                        <div class="grafica-barras__guias"><div></div><div></div><div></div></div>
                        <div class="grafica-barras__columnas">
                            @foreach ($serie as $dia)
                                <div class="grafica-barras__columna {{ $dia['hoy']
                                    ? 'grafica-barras__columna--hoy' : '' }}"
                                     title="{{ $dia['total'] }} préstamos">
                                    <span class="grafica-barras__valor">{{ $dia['total'] }}</span>
                                    <div class="grafica-barras__barra"
                                         style="height: {{ round($dia['total'] * 100 / $maximo, 1) }}%"></div>
                                </div>
                            @endforeach
                        </div>
                        <div class="grafica-barras__etiquetas">
                            @foreach ($serie as $dia)
                                <span class="{{ $dia['hoy'] ? 'grafica-barras__etiqueta--hoy' : '' }}">
                                    {{ $dia['fecha']->format('j') }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="tarjeta tarjeta--relleno pila--20 titulos" aria-labelledby="titulo-inventario">
                <div class="titulos">
                    <h2 id="titulo-inventario" class="tarjeta__titulo">Estado del inventario</h2>
                    <span class="tarjeta__subtitulo">Tiempo real · {{ array_sum($inventario) }} recursos</span>
                </div>
                @include('panel.inventario', ['inventario' => $inventario, 'total' => $totalInventario])
            </section>
        </div>

        <div class="cuadricula-paneles cuadricula-paneles--estirar">
            <section class="tarjeta ocupa-2" aria-labelledby="titulo-atender">
                <div class="tarjeta__cabecera">
                    <div class="titulos">
                        <h2 id="titulo-atender" class="tarjeta__titulo">Solicitudes por atender</h2>
                        <span class="tarjeta__subtitulo">Reservas por recoger, de la más próxima a la más lejana</span>
                    </div>
                    <a href="{{ route('prestamos.index', ['estado' => \App\Enums\EstadoPrestamo::Pendiente->value]) }}"
                       class="texto-13 negrita">Ver todas</a>
                </div>
                @forelse ($porAtender as $prestamo)
                    @php($primero = $prestamo->detalles->first()?->recurso)
                    <div class="fila-lista">
                        <span class="avatar"
                              aria-hidden="true">
                                  {{ \App\Support\Iniciales::de($prestamo->usuario->nombre_completo) }}
                              </span>
                        <div class="pila--2 titulos crece-220">
                            <div class="en-linea en-linea--8">
                                <span class="negrita">{{ $prestamo->usuario->nombre_completo }}</span>
                                <span class="insignia insignia--20 insignia--suave">
                                    {{ $prestamo->usuario->rol->obtenerEtiqueta() }}
                                </span>
                            </div>
                            <span class="texto-13 texto-apoyo truncar">
                                {{ $primero?->nombre_recurso }}@if ($prestamo->detalles->count() > 1) ·
                                +{{ $prestamo->detalles->count() - 1 }} más @endif
                            </span>
                        </div>
                        <div class="titulos texto-13 alinear-fin">
                            <span class="negrita">
                                {{ \App\Support\FormatoFecha::dia($prestamo->fecha_hora_inicio) }}
                            </span>
                            <span class="texto-sec">
                                {{ $prestamo->fecha_hora_inicio->format('H:i') }} –
                                {{ $prestamo->fecha_hora_fin->format('H:i') }}
                            </span>
                        </div>
                        <a href="{{ route('prestamos.index', ['p' => $prestamo->id_prestamo,
                            'estado' => \App\Enums\EstadoPrestamo::Pendiente->value]) }}"
                           class="boton boton--secundario boton--pequeno">Revisar</a>
                    </div>
                @empty
                    <div class="vacio vacio--compacto">
                        <span class="icono" aria-hidden="true">task_alt</span>
                        <span class="vacio__texto">No hay reservas por recoger.</span>
                    </div>
                @endforelse
            </section>

            <section class="tarjeta" aria-labelledby="titulo-agenda">
                <div class="tarjeta__cabecera">
                    <div class="titulos">
                        <h2 id="titulo-agenda" class="tarjeta__titulo">Agenda de mostrador</h2>
                        <span class="tarjeta__subtitulo">Entregas y devoluciones de hoy</span>
                    </div>
                </div>
                @include('mostrador.agenda', ['agenda' => $agenda, 'conIcono' => false])
            </section>
        </div>
    </div>
@endsection
