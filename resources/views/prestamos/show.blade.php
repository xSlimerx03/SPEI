{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         show.blade.php
 * @path         resources/views/prestamos/show.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Detalle y comprobante imprimible de un préstamo: datos del
 *               solicitante, periodo, recursos con condición de entrega y
 *               devolución, incidencias y bitácora de custodia. El PDF se
 *               obtiene con "Imprimir" del navegador (MVP).
 *
 * @requisitos   RF-040, RF-051
 *
 * @see          docs/plan/BBB_MVP_v1.0.md; DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Préstamo ' . $prestamo->obtenerFolio())
@section('ruta', 'Préstamos')

@php
    $estado = $prestamo->obtenerEstado();
    $condiciones = ['bueno' => 'Bueno', 'regular' => 'Regular', 'malo' => 'Malo', 'danado' => 'Dañado'];
    $volver = match (auth()->user()->rol) {
        \App\Enums\Rol::Alumno, \App\Enums\Rol::Docente => route('prestamos.mis'),
        \App\Enums\Rol::AdminSistema, \App\Enums\Rol::Asistente => route('prestamos.index', ['p' => $prestamo->id_prestamo]),
        default => route('panel'),
    };
@endphp

@section('contenido')
    <div class="contenedor-720 pila--20 titulos">
        <div class="encabezado-pagina encabezado-pagina--centro no-imprimir">
            <a href="{{ $volver }}" class="boton boton--enlace boton--pequeno">
                <span class="icono" aria-hidden="true">arrow_back</span>Volver
            </a>
            <button type="button" class="boton boton--secundario" data-imprimir>
                <span class="icono" aria-hidden="true">print</span>Imprimir
            </button>
        </div>

        <article class="tarjeta">
            <div class="panel-detalle__cabecera">
                <div class="encabezado-pagina encabezado-pagina--centro">
                    <div class="en-linea">
                        <img src="{{ asset('img/logo-bigbitbyte.png') }}" alt="" width="40" height="40">
                        <div class="titulos">
                            <span class="subtitulo-pagina">Comprobante de préstamo</span>
                            <span class="folio texto-13">{{ $prestamo->obtenerFolio() }}</span>
                        </div>
                    </div>
                    <span class="insignia {{ $estado->obtenerClaseInsignia() }}">{{ $estado->obtenerEtiqueta() }} · {{ $prestamo->obtenerSubestado() }}</span>
                </div>
            </div>

            <div class="panel-detalle__bloque dos-columnas">
                <div class="dato">
                    <span class="dato__etiqueta">Solicitante</span>
                    <span class="dato__valor">{{ $prestamo->usuario->nombre_completo }}</span>
                    <span class="texto-12 texto-sec">{{ $prestamo->usuario->correo_institucional }} · {{ $prestamo->usuario->rol->obtenerEtiqueta() }}</span>
                </div>
                <div class="dato">
                    <span class="dato__etiqueta">Periodo programado</span>
                    <span class="dato__valor">{{ \App\Support\FormatoFecha::periodo($prestamo->fecha_hora_inicio, $prestamo->fecha_hora_fin) }}</span>
                    <span class="texto-12 texto-sec">{{ \App\Support\FormatoFecha::conAnio($prestamo->fecha_hora_inicio) }}</span>
                </div>
                <div class="dato ocupa-todo">
                    <span class="dato__etiqueta">Motivo</span>
                    <span class="dato__valor">{{ $prestamo->motivo }}</span>
                </div>
                @if ($prestamo->motivo_cancelacion)
                    <div class="dato ocupa-todo">
                        <span class="dato__etiqueta">{{ $estado === \App\Enums\EstadoPrestamo::Vencido ? 'Vencimiento' : 'Justificación de cancelación' }}</span>
                        <span class="dato__valor">{{ $prestamo->motivo_cancelacion }}</span>
                    </div>
                @endif
            </div>

            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th scope="col">Recurso</th>
                            <th scope="col">Inventario</th>
                            <th scope="col">Entrega</th>
                            <th scope="col">Devolución</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prestamo->detalles as $detalle)
                            <tr>
                                <td>
                                    <div class="celda-doble">
                                        <span class="negrita">{{ $detalle->recurso->nombre_recurso }}</span>
                                        <span class="celda-doble__secundaria">{{ $detalle->recurso->area->nombre_area }}</span>
                                    </div>
                                </td>
                                <td class="mono">{{ $detalle->recurso->numero_inventario }}</td>
                                <td>{{ $prestamo->fecha_hora_entrega_real ? ($condiciones[$detalle->condicion_entrega] ?? '—') : '—' }}</td>
                                <td>
                                    {{ $condiciones[$detalle->condicion_devolucion] ?? '—' }}
                                    @if ($detalle->incidencia)
                                        <div class="texto-12 texto-error">{{ $detalle->incidencia }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="panel-detalle__bloque dos-columnas">
                <div class="dato">
                    <span class="dato__etiqueta">Entrega confirmada</span>
                    <span class="dato__valor">{{ $prestamo->fecha_hora_entrega_real?->format('d/m/Y H:i') ?? 'Pendiente' }}</span>
                </div>
                <div class="dato">
                    <span class="dato__etiqueta">Devolución confirmada</span>
                    <span class="dato__valor">{{ $prestamo->fecha_hora_devolucion_real?->format('d/m/Y H:i') ?? 'Pendiente' }}</span>
                </div>
            </div>
            <div class="panel-detalle__bloque texto-12 texto-sec">
                Solicitud registrada el {{ $prestamo->fecha_creacion?->format('d/m/Y H:i') }} · Emitido el {{ now()->format('d/m/Y H:i') }} · SPEI v1.0
            </div>
        </article>
    </div>
@endsection
