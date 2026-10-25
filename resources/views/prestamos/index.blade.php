{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         index.blade.php
 * @path         resources/views/prestamos/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Préstamos (prototipo hi-fi): pestañas por estado con
 *               contadores, filtros, tabla y panel de detalle con datos,
 *               recursos y acciones según el estado.
 *
 * @requisitos   RF-028, RF-029, RF-031, RF-051, RF-058
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Préstamos); DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Préstamos')

@php
    $pestanas = [
        null => ['Todos', 'todos'],
        \App\Enums\EstadoPrestamo::Pendiente->value => ['Pendientes', 'pendiente'],
        \App\Enums\EstadoPrestamo::Activo->value => ['Activos', 'activo'],
        \App\Enums\EstadoPrestamo::Vencido->value => ['Vencidos', 'vencido'],
        \App\Enums\EstadoPrestamo::Devuelto->value => ['Devueltos', 'devuelto'],
        \App\Enums\EstadoPrestamo::Cancelado->value => ['Cancelados', 'cancelado'],
    ];
    $estadoActual = $filtros['estado'] ?? null;
    $consultaBase = array_filter(
        \Illuminate\Support\Arr::except($filtros, ['estado', 'p', 'page']),
        fn (mixed $valor): bool => $valor !== null && $valor !== '',
    );
@endphp

@section('contenido')
    <div class="pila--20 titulos">
        <div class="encabezado-pagina encabezado-pagina--centro fila-pestanas">
        <nav class="pestanas crece" aria-label="Estados de préstamo">
            @foreach ($pestanas as $clave => [$texto, $contador])
                @php
                    $activa = $estadoActual === $clave || ($clave === '' && $estadoActual === null);
                @endphp
                <a href="{{ route('prestamos.index', array_filter($consultaBase + ['estado' => $clave ?: null])) }}"
                   class="pestana {{ $activa ? 'pestana--activa' : '' }}" @if ($activa) aria-current="page" @endif>
                    {{ $texto }}<span class="pestana__contador">{{ $conteo[$contador] }}</span>
                </a>
            @endforeach
        </nav>
            <a href="{{ route('prestamos.exportar', request()->except(['p', 'page'])) }}" class="boton boton--secundario">
                <span class="icono" aria-hidden="true">download</span>Exportar CSV
            </a>
        </div>

        <div class="prestamos-layout">
            <section class="tarjeta tarjeta--recortada prestamos-layout__lista" aria-label="Listado de préstamos">
                <form method="GET" action="{{ route('prestamos.index') }}" class="barra-filtros barra-filtros--tabla">
                    @if ($estadoActual)
                        <input type="hidden" name="estado" value="{{ $estadoActual }}">
                    @endif
                    <div class="control-icono control-icono--con-icono control-icono--compacto barra-filtros__busqueda">
                        <span class="icono" aria-hidden="true">search</span>
                        <label for="filtro-texto" class="visualmente-oculto">Buscar por folio o solicitante</label>
                        <input type="search" id="filtro-texto" name="texto" class="control control--compacto"
                               placeholder="Buscar por folio o solicitante" value="{{ $filtros['texto'] ?? '' }}" maxlength="100">
                    </div>
                    <div class="selector selector--150">
                        <label for="filtro-area" class="visualmente-oculto">Área</label>
                        <select id="filtro-area" name="id_area" class="control control--compacto" data-autoenvio>
                            <option value="">Todas las áreas</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->id_area }}" @selected((int) ($filtros['id_area'] ?? 0) === $area->id_area)>{{ $area->nombre_area }}</option>
                            @endforeach
                        </select>
                        <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
                    </div>
                    <div class="control-icono control-icono--con-icono control-icono--compacto">
                        <span class="icono" aria-hidden="true">calendar_month</span>
                        <label for="filtro-fecha" class="visualmente-oculto">Fecha de inicio</label>
                        <input type="date" id="filtro-fecha" name="fecha" class="control control--compacto"
                               value="{{ $filtros['fecha'] ?? '' }}" data-autoenvio>
                    </div>
                    <button type="submit" class="boton boton--secundario">
                        <span class="icono" aria-hidden="true">filter_list</span>Filtrar
                    </button>
                </form>

                @if ($prestamos->isEmpty())
                    <div class="vacio">
                        <span class="icono" aria-hidden="true">assignment</span>
                        <span class="vacio__titulo">Sin préstamos en este estado</span>
                        <span class="vacio__texto">Prueba con otro filtro o limpia la búsqueda.</span>
                    </div>
                @else
                    <div class="tabla-contenedor">
                        <table class="tabla tabla--clicable tabla--720">
                            <thead>
                                <tr>
                                    <th scope="col">Folio</th>
                                    <th scope="col">Solicitante</th>
                                    <th scope="col">Recursos</th>
                                    <th scope="col">Horario</th>
                                    <th scope="col">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($prestamos as $prestamo)
                                    @php
                                        $estado = $prestamo->obtenerEstado();
                                        $enlace = route('prestamos.index', request()->except('p') + ['p' => $prestamo->id_prestamo]);
                                        $primero = $prestamo->detalles->first()?->recurso;
                                    @endphp
                                    <tr data-href="{{ $enlace }}"
                                        class="{{ $seleccionado?->id_prestamo === $prestamo->id_prestamo ? 'tabla__fila--seleccionada' : '' }}">
                                        <td><a href="{{ $enlace }}" class="folio">{{ $prestamo->obtenerFolio() }}</a></td>
                                        <td>
                                            <div class="celda-persona">
                                                <span class="avatar avatar--32" aria-hidden="true">{{ \App\Support\Iniciales::de($prestamo->usuario->nombre_completo) }}</span>
                                                <div class="celda-doble">
                                                    <span class="negrita truncar">{{ $prestamo->usuario->nombre_completo }}</span>
                                                    <span class="celda-doble__secundaria">{{ $prestamo->usuario->rol->obtenerEtiqueta() }} · {{ $primero?->area?->nombre_area }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="celda-doble">
                                                <span class="truncar">{{ $primero?->nombre_recurso }}</span>
                                                @if ($prestamo->detalles->count() > 1)
                                                    <span class="celda-doble__secundaria">+{{ $prestamo->detalles->count() - 1 }} más</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="celda-doble">
                                                <span class="negrita">{{ \App\Support\FormatoFecha::dia($prestamo->fecha_hora_inicio) }}</span>
                                                <span class="celda-doble__secundaria sin-salto">{{ $prestamo->fecha_hora_inicio->format('H:i') }} – {{ $prestamo->fecha_hora_fin->format('H:i') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="pila--2 titulos">
                                                <span class="insignia {{ $estado->obtenerClaseInsignia() }}">{{ $estado->obtenerEtiqueta() }}</span>
                                                <span class="texto-11 texto-sec sin-salto">{{ $prestamo->obtenerSubestado() }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $prestamos->links('partials.paginacion') }}
                @endif
            </section>

            @if ($seleccionado)
                @include('prestamos.panel', ['prestamo' => $seleccionado, 'usuario' => $usuario])
            @endif
        </div>
    </div>
@endsection
