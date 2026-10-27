{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         create.blade.php
 * @path         resources/views/prestamos/create.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Nueva solicitud (prototipo hi-fi): asistente de tres pasos
 *               con recursos de una misma área, calendario y horario, y
 *               revisión con reglas y aceptación del reglamento. Sin
 *               JavaScript se muestra como un formulario único.
 *
 * @requisitos   RF-022, RF-023, RF-024, RF-026, RN-004
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Nueva solicitud); DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Nueva solicitud')

@php
    $pasoInicial = match (true) {
        $errors->has('recursos') || $errors->has('recursos.*') => 1,
        $errors->hasAny(['fecha', 'hora_inicio', 'hora_fin', 'motivo']) => 2,
        $errors->has('acepta_reglamento') => 3,
        default => 1,
    };
    $pasos = [
        1 => ['Recursos', 'Qué necesitas'],
        2 => ['Fecha y horario', 'Cuándo lo usarás'],
        3 => ['Confirmación', 'Revisa y envía'],
    ];
    $fecha = old('fecha', today()->toDateString());
@endphp

@section('contenido')
    <div class="contenedor-720 pila">
        <ol class="pasos" aria-label="Pasos de la solicitud">
            @foreach ($pasos as $numero => [$titulo, $detalle])
                <li class="paso {{ $numero === 1 ? 'paso--activo' : '' }}" data-indicador-paso="{{ $numero }}">
                    <span class="paso__circulo" aria-hidden="true">
                        <span class="paso__numero">{{ $numero }}</span>
                        <span class="icono">check</span>
                    </span>
                    <span class="paso__textos">
                        <span class="paso__titulo">{{ $titulo }}</span>
                        <span class="paso__detalle">{{ $detalle }}</span>
                    </span>
                    @if ($numero < count($pasos))
                        <span class="paso__linea" aria-hidden="true"></span>
                    @endif
                </li>
            @endforeach
        </ol>

        @if ($recursosPorArea->isEmpty())
            <div class="tarjeta vacio">
                <span class="icono" aria-hidden="true">inventory_2</span>
                <span class="vacio__titulo">No hay recursos disponibles por ahora</span>
                <span class="vacio__texto">Vuelve a intentarlo más tarde o consulta el catálogo.</span>
                <a href="{{ route('catalogo.index') }}" class="boton boton--secundario">Ver catálogo</a>
            </div>
        @else
            <form method="POST" action="{{ route('prestamos.store') }}"
                  class="tarjeta tarjeta--relleno-24 pila--20 titulos"
                  data-solicitud data-paso-inicial="{{ $pasoInicial }}" novalidate>
                @csrf

                {{-- Paso 1: recursos --}}
                <section class="pila--20 titulos" data-paso="1" aria-labelledby="paso-1-titulo">
                    <div class="pila--4 titulos">
                        <h2 id="paso-1-titulo" class="subtitulo-pagina">¿Qué recursos necesitas?</h2>
                        <p class="texto-sec">
                            Puedes incluir hasta diez recursos de una misma área en una sola solicitud.
                        </p>
                    </div>
                    <div class="control-icono control-icono--con-icono" data-filtro-contenedor hidden>
                        <span class="icono" aria-hidden="true">search</span>
                        <label for="filtro-recursos" class="visualmente-oculto">Buscar recurso</label>
                        <input type="search" id="filtro-recursos" class="control"
                               placeholder="Buscar recurso por nombre o inventario"
                               data-filtro-recursos hidden>
                    </div>
                    @error('recursos')
                        <p id="recursos-error"
                           class="campo__error"><span class="icono" aria-hidden="true">error</span>{{ $message }}</p>
                    @enderror
                    @foreach ($recursosPorArea as $nombreArea => $recursos)
                        <fieldset class="grupo-campos" data-grupo-area>
                            <legend class="grupo-area__titulo">{{ $nombreArea }}</legend>
                            @foreach ($recursos as $recurso)
                                <label class="recurso-elegible" data-recurso-elegible
                                       data-buscar="{{ mb_strtolower($recurso->nombre_recurso . ' '
                                           . $recurso->numero_inventario . ' ' . $recurso->marca) }}">
                                    <input type="checkbox" name="recursos[]" value="{{ $recurso->id_recurso }}"
                                           class="visualmente-oculto"
                                           data-area="{{ $recurso->id_area }}"
                                           data-area-nombre="{{ $recurso->area->nombre_area }}"
                                           data-nombre="{{ $recurso->nombre_recurso }}"
                                           @checked(in_array($recurso->id_recurso, $seleccionados, true))>
                                    <span class="imagen-pendiente imagen-pendiente--miniatura imagen-pendiente--56"
                                          aria-hidden="true">
                                        <span class="icono">
                                            {{ $recurso->tipo_recurso === \App\Enums\TipoRecurso::Espacio
                                                ? 'meeting_room' : 'devices' }}
                                        </span>
                                    </span>
                                    <span class="titulos crece">
                                        <span class="recurso-elegible__nombre">{{ $recurso->nombre_recurso }}</span>
                                        <span class="texto-13 texto-sec">
                                            {{ $recurso->area->nombre_area }} ·
                                            <span class="mono">{{ $recurso->numero_inventario }}</span>
                                        </span>
                                    </span>
                                    <span class="insignia insignia--exito">Disponible</span>
                                    <span class="icono icono--azul casilla-visual"
                                          aria-hidden="true">check_circle</span>
                                </label>
                            @endforeach
                        </fieldset>
                    @endforeach
                </section>

                {{-- Paso 2: fecha y horario --}}
                <section class="pila--20 titulos" data-paso="2" aria-labelledby="paso-2-titulo">
                    <div class="pila--4 titulos">
                        <h2 id="paso-2-titulo" class="subtitulo-pagina">¿Cuándo lo usarás?</h2>
                        <p class="texto-sec">Elige el día y el horario dentro del horario operativo (7:00 a 22:00).</p>
                    </div>
                    <div class="cuadricula-campos">
                        <div class="calendario" data-calendario hidden>
                            <div class="calendario__cabecera">
                                <span class="calendario__mes" data-calendario-mes></span>
                                <div class="en-linea en-linea--8">
                                    <button type="button" class="calendario__navegar" data-calendario-previo
                                            aria-label="Mes anterior">
                                        <span class="icono" aria-hidden="true">chevron_left</span>
                                    </button>
                                    <button type="button" class="calendario__navegar" data-calendario-proximo
                                            aria-label="Mes siguiente">
                                        <span class="icono" aria-hidden="true">chevron_right</span>
                                    </button>
                                </div>
                            </div>
                            <div class="calendario__semana" aria-hidden="true">
                                <span>L</span><span>M</span><span>M</span><span>J</span>
                                <span>V</span><span>S</span><span>D</span>
                            </div>
                            <div class="calendario__dias" data-calendario-dias></div>
                        </div>

                        <div class="pila--16 titulos">
                            <div class="campo" data-fecha-nativa>
                                <label for="fecha" class="campo__etiqueta">Día del préstamo</label>
                                <input type="date" id="fecha" name="fecha" class="control" value="{{ $fecha }}"
                                       min="{{ today()->toDateString() }}" max="{{ $fechaMaxima }}" required
                                       @error('fecha') aria-invalid="true" aria-describedby="fecha-error" @enderror>
                            </div>
                            @error('fecha')
                                <p id="fecha-error"
                                   class="campo__error">
                                       <span class="icono" aria-hidden="true">error</span>{{ $message }}
                                   </p>
                            @enderror
                            <div class="dos-columnas">
                                @foreach (['hora_inicio' => ['Hora de inicio', '10:00'],
                                    'hora_fin' => ['Hora de fin', '12:00']] as $campo => [$etiqueta, $omision])
                                    <div class="campo">
                                        <label for="{{ $campo }}" class="campo__etiqueta">{{ $etiqueta }}</label>
                                        <div class="selector">
                                            <select id="{{ $campo }}" name="{{ $campo }}" class="control"
                                                    @error($campo) aria-invalid="true"
                                                    aria-describedby="{{ $campo }}-error" @enderror>
                                                @foreach ($horas as $hora)
                                                    <option value="{{ $hora }}"
                                                            @selected(old($campo, $omision) === $hora)>
                                                        {{ $hora }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
                                        </div>
                                        @error($campo)
                                            <span id="{{ $campo }}-error"
                                                  class="campo__error">
                                                      <span class="icono" aria-hidden="true">error</span>{{ $message }}
                                                  </span>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                            <div class="campo">
                                <label for="motivo" class="campo__etiqueta">Motivo de uso</label>
                                <textarea id="motivo" name="motivo" class="control" maxlength="1000" required
                                          placeholder="Describe brevemente para qué usarás los recursos"
                                          @error('motivo') aria-invalid="true" aria-describedby="motivo-error"
                                          @enderror>{{ old('motivo') }}</textarea>
                                @error('motivo')
                                    <span id="motivo-error"
                                          class="campo__error">
                                              <span class="icono" aria-hidden="true">error</span>{{ $message }}
                                          </span>
                                @enderror
                            </div>
                            <div class="alerta alerta--exito">
                                <span class="icono" aria-hidden="true">check_circle</span>
                                <div class="pila--2 titulos">
                                    <span class="alerta__titulo">Disponible</span>
                                    <span>
                                        <span data-resumen="cantidad">Los recursos elegidos están</span> disponibles;
                                        al enviar quedan reservados para ti en ese horario y nadie más
                                        podrá tomarlos.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Paso 3: confirmación --}}
                <section class="pila--20 titulos" data-paso="3" aria-labelledby="paso-3-titulo">
                    <div class="pila--4 titulos">
                        <h2 id="paso-3-titulo" class="subtitulo-pagina">Revisa tu solicitud</h2>
                        <p class="texto-sec">
                            Al enviarla, los recursos quedan reservados y te notificaremos cada cambio en el sistema.
                        </p>
                    </div>
                    <div class="resumen-solicitud">
                        <div class="resumen-solicitud__fila">
                            <span class="icono" aria-hidden="true">devices</span>
                            <div class="titulos crece">
                                <span class="dato__etiqueta">Recursos</span>
                                <span data-resumen="recursos">Los recursos seleccionados</span>
                            </div>
                        </div>
                        <div class="resumen-solicitud__fila">
                            <span class="icono" aria-hidden="true">event</span>
                            <div class="titulos crece">
                                <span class="dato__etiqueta">Fecha y horario</span>
                                <span data-resumen="fecha">El día y horario elegidos</span>
                            </div>
                        </div>
                        <div class="resumen-solicitud__fila">
                            <span class="icono" aria-hidden="true">location_on</span>
                            <div class="titulos crece">
                                <span class="dato__etiqueta">Punto de entrega</span>
                                <span>Mostrador de <span data-resumen="area">el área del recurso</span></span>
                            </div>
                        </div>
                    </div>
                    <div class="reglas">
                        <span class="negrita">Antes de enviar</span>
                        <div class="reglas__item">
                            <span class="icono" aria-hidden="true">timer</span>Tienes {{ $tolerancia }} minutos de
                            tolerancia para recoger; después la reserva se cancela automáticamente.
                        </div>
                        <div class="reglas__item">
                            <span class="icono" aria-hidden="true">qr_code_2</span>La entrega y la devolución se
                            confirman con un código QR de un solo uso.
                        </div>
                        <div class="reglas__item">
                            <span class="icono" aria-hidden="true">block</span>No se permiten prórrogas; si necesitas
                            más tiempo, crea una nueva solicitud.
                        </div>
                    </div>
                    <div class="campo">
                        <label class="casilla">
                            <input type="checkbox" name="acepta_reglamento" value="1" @checked(old('acepta_reglamento'))
                                   @error('acepta_reglamento') aria-invalid="true"
                                   aria-describedby="acepta_reglamento-error" @enderror>
                            Acepto el reglamento de préstamo de la institución.
                        </label>
                        @error('acepta_reglamento')
                            <span id="acepta_reglamento-error"
                                  class="campo__error">
                                      <span class="icono" aria-hidden="true">error</span>{{ $message }}
                                  </span>
                        @enderror
                    </div>
                </section>

                <div class="alerta alerta--advertencia" data-paso-aviso role="alert" hidden>
                    <span class="icono" aria-hidden="true">warning</span>
                    <span data-paso-aviso-texto></span>
                </div>

                <div class="pie-pasos">
                    <a href="{{ route('catalogo.index') }}" class="boton boton--fantasma boton--grande">Cancelar</a>
                    <div class="acciones">
                        <button type="button" class="boton boton--secundario boton--grande" data-paso-anterior hidden>
                            <span class="icono" aria-hidden="true">arrow_back</span>Regresar
                        </button>
                        <button type="button" class="boton boton--primario boton--grande" data-paso-siguiente hidden>
                            Continuar<span class="icono" aria-hidden="true">arrow_forward</span>
                        </button>
                        <button type="submit" class="boton boton--primario boton--grande" data-paso-enviar>
                            <span class="icono" aria-hidden="true">send</span>Enviar solicitud
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>
@endsection
