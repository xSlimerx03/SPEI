{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         show.blade.php
 * @path         resources/views/recursos/show.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Detalle de recurso (prototipo hi-fi): badges, nombre, caja de
 *               disponibilidad, acciones por rol, línea de tiempo semanal de
 *               7:00 a 22:00, especificaciones e historial (administrador).
 *               Variables: recurso, disponibilidad, semana, historial,
 *               esAdministrador.
 *
 * @requisitos   RF-013, RF-014, RF-015, RF-022
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Detalle de recurso); DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Detalle de recurso')
@section('ruta', $esAdministrador ? 'Recursos' : 'Catálogo')

@php
    $esEspacio = $recurso->tipo_recurso === \App\Enums\TipoRecurso::Espacio;
    $enMantenimiento = $recurso->estado === \App\Enums\EstadoRecurso::Mantenimiento;
    $horas = ['7:00', '10:00', '13:00', '16:00', '19:00', '22:00'];
@endphp

@section('contenido')
    <div class="pila--20 titulos">
        <a href="{{ $esAdministrador ? route('recursos.index') : route('catalogo.index') }}"
           class="boton boton--enlace boton--pequeno alinear-inicio">
            <span class="icono"
                  aria-hidden="true">arrow_back</span>{{ $esAdministrador ? 'Ver recursos' : 'Ver catálogo' }}
        </a>

        <div class="cuadricula-380">
            <div class="pila">
                <section class="tarjeta tarjeta--relleno-24 pila--16 titulos" aria-labelledby="titulo-recurso">
                    <div class="en-linea en-linea--8">
                        <span class="insignia {{ $recurso->estado->obtenerClaseInsignia() }}">
                            {{ $recurso->estado->obtenerEtiqueta() }}
                        </span>
                        <span class="insignia insignia--suave">{{ $recurso->tipo_recurso->obtenerEtiqueta() }}</span>
                        @unless ($recurso->activo)
                            <span class="insignia insignia--neutra">Inactivo</span>
                        @endunless
                    </div>
                    <div class="pila--4 titulos">
                        <h2 id="titulo-recurso" class="titulo-grande">{{ $recurso->nombre_recurso }}</h2>
                        <span class="texto-sec">
                            {{ $recurso->area->nombre_area }} ·
                            <span class="mono texto-13">{{ $recurso->numero_inventario }}</span>
                        </span>
                    </div>
                    <div class="caja-disponibilidad">
                        <span class="icono" aria-hidden="true">schedule</span>
                        <span class="crece negrita">{{ $disponibilidad }}</span>
                    </div>
                    <div class="acciones">
                        @if (! $esAdministrador && $recurso->estado === \App\Enums\EstadoRecurso::Disponible)
                            <a href="{{ route('prestamos.create', ['recurso' => $recurso->id_recurso]) }}"
                               class="boton boton--primario boton--grande">
                                <span class="icono" aria-hidden="true">add</span>Solicitar préstamo
                            </a>
                        @endif
                        @if ($esAdministrador)
                            <a href="{{ route('recursos.edit', $recurso) }}" class="boton boton--secundario">
                                <span class="icono" aria-hidden="true">edit</span>Editar
                            </a>
                            @unless ($recurso->estado->estaEnUso())
                                <form method="POST" action="{{ route('recursos.estado', $recurso) }}"
                                      class="formulario-en-linea">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="estado"
                                           value="{{ $enMantenimiento ? 'disponible' : 'mantenimiento' }}">
                                    <button type="submit" class="boton boton--secundario">
                                        <span class="icono"
                                              aria-hidden="true">build</span>{{ $enMantenimiento ? 'Reactivar recurso'
                                                  : 'Iniciar mantenimiento' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('recursos.activo', $recurso) }}"
                                      class="formulario-en-linea"
                                      @if ($recurso->activo)
                                      data-confirmar="{{ '¿Está seguro de desactivar el recurso? '
                                          . 'Dejará de mostrarse en el catálogo.' }}"
                                      @endif>
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="activo" value="{{ $recurso->activo ? 0 : 1 }}">
                                    <button type="submit" class="boton boton--fantasma">
                                        <span class="icono" aria-hidden="true">
                                            {{ $recurso->activo ? 'visibility_off' : 'visibility' }}
                                        </span>{{ $recurso->activo ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                            @endunless
                        @endif
                    </div>
                </section>

                <section class="tarjeta tarjeta--relleno-24 pila--16 titulos" aria-labelledby="titulo-disponibilidad">
                    <div class="encabezado-pagina encabezado-pagina--centro">
                        <div class="titulos">
                            <h2 id="titulo-disponibilidad" class="tarjeta__titulo">Disponibilidad</h2>
                            <span class="tarjeta__subtitulo">
                                Del {{ \App\Support\FormatoFecha::conAnio($semana[0]['fecha']) }} al
                                {{ \App\Support\FormatoFecha::conAnio(end($semana)['fecha']) }} · horario operativo 7:00
                                – 22:00
                            </span>
                        </div>
                        <div class="leyenda">
                            <span class="leyenda__item">
                                <span class="leyenda__muestra leyenda__muestra--12 muestra-reservado"></span>En uso
                            </span>
                            <span class="leyenda__item">
                                <span class="leyenda__muestra leyenda__muestra--12 muestra-pendiente"></span>Por recoger
                            </span>
                        </div>
                    </div>
                    <div class="pila--8 titulos">
                        <div class="linea-tiempo__fila" aria-hidden="true">
                            <span class="linea-tiempo__dia"></span>
                            <div class="linea-tiempo__horas">
                                @foreach ($horas as $hora)
                                    <span>{{ $hora }}</span>
                                @endforeach
                            </div>
                        </div>
                        @foreach ($semana as $dia)
                            <div class="linea-tiempo__fila">
                                <span class="linea-tiempo__dia {{ $dia['hoy'] ? 'linea-tiempo__dia--hoy' : '' }}">
                                    {{ $dia['hoy'] ? 'Hoy' : \App\Support\FormatoFecha::corta($dia['fecha']) }}
                                </span>
                                <div class="linea-tiempo__pista" role="img"
                                     aria-label="{{ count($dia['bloques']) === 0 ? 'Libre' : collect($dia['bloques'])
                                         ->pluck('texto')->implode(', ') }}">
                                    @foreach ($dia['bloques'] as $bloque)
                                        <div class="linea-tiempo__bloque {{ $bloque['pendiente']
                                            ? 'linea-tiempo__bloque--pendiente' : '' }}"
                                             style="left: {{ $bloque['izquierda'] }}%; width: {{ $bloque['ancho'] }}%">
                                                 {{ $bloque['texto'] }}
                                             </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <div class="pila">
                <div class="imagen-pendiente imagen-pendiente--detalle" aria-hidden="true">
                    <span class="icono icono--64">{{ $esEspacio ? 'meeting_room' : 'devices' }}</span>
                </div>
                <section class="tarjeta especificaciones" aria-label="Especificaciones">
                    @foreach ([
                        'Número de inventario' => $recurso->numero_inventario,
                        'Número de serie' => $recurso->numero_serie ?: '—',
                        'Marca' => $recurso->marca ?: '—',
                        'Año de adquisición' => $recurso->anio_adquisicion ?: '—',
                        'Tipo' => $recurso->tipo_recurso->obtenerEtiqueta(),
                        'Área' => $recurso->area->nombre_area,
                    ] as $etiqueta => $valor)
                        <div class="especificaciones__fila">
                            <span class="texto-sec">{{ $etiqueta }}</span>
                            <span class="especificaciones__valor {{ str_starts_with($etiqueta, 'Número')
                                ? 'mono' : '' }}">
                                {{ $valor }}
                            </span>
                        </div>
                    @endforeach
                </section>
                @if ($esAdministrador && $historial !== null)
                    <section class="tarjeta" aria-labelledby="titulo-historial">
                        <div class="tarjeta__cabecera">
                            <h2 id="titulo-historial" class="tarjeta__titulo">Historial reciente</h2>
                        </div>
                        @forelse ($historial as $prestamo)
                            @php($estado = $prestamo->obtenerEstado())
                            <a href="{{ route('prestamos.show', $prestamo) }}"
                               class="fila-lista fila-lista--12 enlace-lista">
                                <span class="folio">{{ $prestamo->obtenerFolio() }}</span>
                                <span class="crece">{{ $prestamo->usuario->nombre_completo }}</span>
                                <span class="texto-13 texto-sec">
                                    {{ \App\Support\FormatoFecha::corta($prestamo->fecha_hora_inicio) }}
                                </span>
                                <span class="insignia {{ $estado->obtenerClaseInsignia() }}">
                                    {{ $estado->obtenerEtiqueta() }}
                                </span>
                            </a>
                        @empty
                            <div class="vacio vacio--compacto">
                                <span class="icono" aria-hidden="true">history</span>
                                <span class="vacio__texto">Este recurso aún no se ha prestado.</span>
                            </div>
                        @endforelse
                    </section>
                @endif
            </div>
        </div>
    </div>
@endsection
