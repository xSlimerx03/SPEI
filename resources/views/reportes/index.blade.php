{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.8: Historias, Reportes e Inteligencia Operativa
 * @file         index.blade.php
 * @path         resources/views/reportes/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-25
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Reportes (prototipo hi-fi): selector de periodo, área,
 *               exportación CSV e impresión; tarjetas de recursos más
 *               prestados, tiempo promedio de uso, condición al devolver y
 *               cancelaciones y vencimientos.
 *
 * @requisitos   RF-054, RF-055, RF-056, RF-057, RF-058
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Reportes); DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Reportes')

@php
    $maximoTop = max([1, ...array_column($reporte['masPrestados'], 'total')]);
    $maximoUso = max([1, ...array_column($reporte['usoPorArea'], 'minutos')]);
    $cierres = $reporte['cierres'];
    $totalCierres = max(1, array_sum($cierres));
    $segmentos = [
        ['Cancelada por el solicitante', $cierres['solicitante'], 'color-azul-1'],
        ['Cancelada por un operador', $cierres['operador'], 'color-azul-2'],
        ['No se recogió a tiempo', $cierres['vencidos'], 'color-azul-3'],
        ['Desplazada por prioridad (RN-008)', $cierres['prioridad'], 'color-azul-4'],
    ];
    $maximoHoras = max([1, ...array_column($detalle['horas'], 'minutos')]);
    $exportar = fn (string $seccion): string => route('reportes.exportar', array_filter([
        'seccion' => $seccion,
        'periodo' => $dias,
        'id_area' => $idArea,
    ]));
    $condiciones = [
        ['bueno', 'Buen estado', 'task_alt'],
        ['regular', 'Estado regular', 'build_circle'],
        ['malo', 'Mal estado', 'report'],
        ['danado', 'Dañados', 'heart_broken'],
        ['no_entregado', 'No devueltos', 'report_off'],
    ];
    $duracion = fn (int $minutos): string => $minutos >= 60
        ? intdiv($minutos, 60) . ' h ' . sprintf('%02d', $minutos % 60) . ' min'
        : $minutos . ' min';
@endphp

@section('contenido')
    <div class="pila">
        <form method="GET" action="{{ route('reportes.index') }}" class="encabezado-pagina encabezado-pagina--centro">
            <div class="barra-filtros">
                <div class="segmentado segmentado--azul" role="group" aria-label="Periodo">
                    @foreach (\App\Services\ReporteService::PERIODOS as $periodo)
                        <a href="{{ route('reportes.index', array_filter([
                            'periodo' => $periodo,
                            'id_area' => $idArea,
                        ])) }}"
                           class="segmentado__opcion {{ $periodo === $dias ? 'segmentado__opcion--activa' : '' }}"
                           @if ($periodo === $dias) aria-current="true" @endif>Últimos {{ $periodo }} días</a>
                    @endforeach
                </div>
                <input type="hidden" name="periodo" value="{{ $dias }}">
                <div class="selector selector--170">
                    <label for="filtro-area" class="visualmente-oculto">Área</label>
                    <select id="filtro-area" name="id_area" class="control control--compacto" data-autoenvio>
                        <option value="">Todas las áreas</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id_area }}"
                                    @selected($idArea === $area->id_area)>{{ $area->nombre_area }}</option>
                        @endforeach
                    </select>
                    <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
                </div>
                <button type="submit" class="visualmente-oculto">Aplicar</button>
            </div>
            <div class="acciones no-imprimir">
                <a href="{{ route('prestamos.exportar', array_filter(['id_area' => $idArea])) }}"
                   class="boton boton--secundario">
                    <span class="icono" aria-hidden="true">table_view</span>Exportar CSV
                </a>
                <button type="button" class="boton boton--primario" data-imprimir>
                    <span class="icono" aria-hidden="true">print</span>Imprimir
                </button>
            </div>
        </form>

        <div class="cuadricula-paneles cuadricula-paneles--estirar">
            <section class="tarjeta tarjeta--relleno pila--16 titulos" aria-labelledby="titulo-top">
                <div class="titulos">
                    <h2 id="titulo-top" class="tarjeta__titulo">Equipos más prestados</h2>
                    <span class="tarjeta__subtitulo">
                        Número de préstamos entregados en el periodo · {{ $reporte['total'] }} solicitudes
                    </span>
                </div>
                @forelse ($reporte['masPrestados'] as $indice => $fila)
                    <div class="barras-horizontales__fila">
                        <span class="texto-13 texto-sec negrita">{{ $indice + 1 }}</span>
                        <span class="truncar">{{ $fila['nombre'] }}</span>
                        <div class="progreso progreso--12" aria-hidden="true">
                            <div class="progreso__valor"
                                 style="width: {{ round($fila['total'] * 100 / $maximoTop, 1) }}%"></div>
                        </div>
                        <span class="negrita texto-derecha">{{ $fila['total'] }}</span>
                    </div>
                @empty
                    <div class="vacio vacio--compacto">
                        <span class="icono" aria-hidden="true">bar_chart</span>
                        <span class="vacio__texto">Sin préstamos entregados en el periodo.</span>
                    </div>
                @endforelse
            </section>

            <section class="tarjeta tarjeta--relleno pila--16 titulos" aria-labelledby="titulo-uso">
                <div class="encabezado-pagina">
                    <div class="titulos">
                        <h2 id="titulo-uso" class="tarjeta__titulo">Tiempo promedio de uso</h2>
                        <span class="tarjeta__subtitulo">Por área, de la entrega a la devolución</span>
                    </div>
                    <div class="titulos alinear-fin">
                        <span class="subtitulo-pagina texto-24">{{ $duracion($reporte['retraso']['minutos']) }}</span>
                        <span class="texto-12 texto-sec">
                            retraso promedio · {{ $reporte['retraso']['tardias'] }} de
                            {{ $reporte['retraso']['devueltos'] }} tardías
                        </span>
                    </div>
                </div>
                @forelse ($reporte['usoPorArea'] as $fila)
                    <div class="pila--4 titulos">
                        <div class="encabezado-pagina">
                            <span>{{ $fila['area'] }}</span>
                            <span class="negrita">{{ $duracion($fila['minutos']) }}</span>
                        </div>
                        <div class="progreso" aria-hidden="true">
                            <div class="progreso__valor"
                                 style="width: {{ round($fila['minutos'] * 100 / $maximoUso, 1) }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="vacio vacio--compacto">
                        <span class="icono" aria-hidden="true">schedule</span>
                        <span class="vacio__texto">Aún no hay devoluciones en el periodo.</span>
                    </div>
                @endforelse
            </section>

            <section class="tarjeta tarjeta--relleno pila--16 titulos" aria-labelledby="titulo-condicion">
                <div class="titulos">
                    <h2 id="titulo-condicion" class="tarjeta__titulo">Condición al devolver</h2>
                    <span class="tarjeta__subtitulo">Recursos recibidos en mostrador durante el periodo</span>
                </div>
                <div class="dos-columnas">
                    @foreach ($condiciones as [$clave, $etiqueta, $icono])
                        <div class="metrica-mini">
                            <span class="icono icono--azul" aria-hidden="true">{{ $icono }}</span>
                            <div class="titulos">
                                <span class="metrica-mini__valor">{{ $reporte['condiciones'][$clave] }}</span>
                                <span class="texto-13 texto-apoyo">{{ $etiqueta }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="tarjeta tarjeta--relleno pila--16 titulos" aria-labelledby="titulo-cierres">
                <div class="titulos">
                    <h2 id="titulo-cierres" class="tarjeta__titulo">Cancelaciones y vencimientos</h2>
                    <span class="tarjeta__subtitulo">
                        {{ array_sum($cierres) }} solicitudes cerradas sin entrega en el periodo
                    </span>
                </div>
                <div class="barra-apilada barra-apilada--16" role="img"
                     aria-label="{{ collect($segmentos)->map(fn (array $s): string => $s[0] . ' ' . $s[1])
                         ->implode(', ') }}">
                    @foreach ($segmentos as [$etiqueta, $valor, $color])
                        @if ($valor > 0)
                            <div class="{{ $color }}"
                                 style="width: {{ round($valor * 100 / $totalCierres, 2) }}%"></div>
                        @endif
                    @endforeach
                </div>
                <div class="titulos">
                    @foreach ($segmentos as [$etiqueta, $valor, $color])
                        <div class="leyenda-lista__fila">
                            <span class="leyenda-lista__punto leyenda-lista__punto--cuadro {{ $color }}"
                                  aria-hidden="true"></span>
                            <span class="crece">{{ $etiqueta }}</span>
                            <span class="negrita">{{ $valor }}</span>
                            <span class="leyenda-lista__porcentaje">{{ round($valor * 100 / $totalCierres) }} %</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="tarjeta tarjeta--relleno pila--16 titulos" aria-labelledby="titulo-horas">
                <div class="encabezado-pagina">
                    <div class="titulos">
                        <h2 id="titulo-horas" class="tarjeta__titulo">Horas de uso por recurso</h2>
                        <span class="tarjeta__subtitulo">Tiempo real entre entrega y devolución (RF-055)</span>
                    </div>
                    <a href="{{ $exportar('horas') }}" class="boton boton--fantasma boton--pequeno no-imprimir">
                        <span class="icono" aria-hidden="true">download</span>Exportar
                    </a>
                </div>
                @forelse ($detalle['horas'] as $fila)
                    <div class="pila--4 titulos">
                        <div class="encabezado-pagina">
                            <span class="truncar">{{ $fila['nombre'] }}</span>
                            <span class="negrita">{{ $duracion($fila['minutos']) }}</span>
                        </div>
                        <div class="progreso" aria-hidden="true">
                            <div class="progreso__valor"
                                 style="width: {{ round($fila['minutos'] * 100 / $maximoHoras, 1) }}%"></div>
                        </div>
                        <span class="texto-12 texto-sec">{{ $fila['prestamos'] }} préstamo(s) devuelto(s)</span>
                    </div>
                @empty
                    <div class="vacio vacio--compacto">
                        <span class="icono" aria-hidden="true">timer</span>
                        <span class="vacio__texto">Aún no hay devoluciones en el periodo.</span>
                    </div>
                @endforelse
            </section>

            <section class="tarjeta tarjeta--relleno pila--16 titulos" aria-labelledby="titulo-sanciones">
                <div class="encabezado-pagina">
                    <div class="titulos">
                        <h2 id="titulo-sanciones" class="tarjeta__titulo">Incidentes y sanciones</h2>
                        <span class="tarjeta__subtitulo">Marcas aplicadas a préstamos del periodo (RF-056)</span>
                    </div>
                    <a href="{{ $exportar('sanciones') }}" class="boton boton--fantasma boton--pequeno no-imprimir">
                        <span class="icono" aria-hidden="true">download</span>Exportar
                    </a>
                </div>
                @foreach ($detalle['sanciones'] as $fila)
                    <div class="leyenda-lista__fila">
                        <span class="icono icono--18 icono--rojo" aria-hidden="true">gavel</span>
                        <span class="crece">{{ $fila['tipo'] }}</span>
                        <span class="negrita">{{ $fila['total'] }}</span>
                        <span class="texto-12 texto-sec">
                            {{ $fila['marcas'] }} marcas · {{ $fila['exentadas'] }} exentadas
                        </span>
                    </div>
                @endforeach
                @if ($detalle['incidencias'] !== [])
                    <span class="dato__etiqueta">Últimas incidencias</span>
                    @foreach ($detalle['incidencias'] as $incidencia)
                        <div class="titulos texto-13">
                            <span>
                                <span class="folio">{{ $incidencia['folio'] }}</span> · {{ $incidencia['recurso'] }} ·
                                <span class="negrita">{{ $incidencia['condicion'] }}</span>
                            </span>
                            @if ($incidencia['texto'] !== '')
                                <span class="texto-sec">{{ $incidencia['texto'] }}</span>
                            @endif
                        </div>
                    @endforeach
                @endif
            </section>

            <section class="tarjeta tarjeta--relleno pila--16 titulos ocupa-todo"
                     aria-labelledby="titulo-justificaciones">
                <div class="encabezado-pagina">
                    <div class="titulos">
                        <h2 id="titulo-justificaciones" class="tarjeta__titulo">Cancelaciones y justificaciones</h2>
                        <span class="tarjeta__subtitulo">Últimas solicitudes cerradas sin entrega (RF-057)</span>
                    </div>
                    <a href="{{ $exportar('cancelaciones') }}" class="boton boton--fantasma boton--pequeno no-imprimir">
                        <span class="icono" aria-hidden="true">download</span>Exportar
                    </a>
                </div>
                @forelse ($detalle['cancelaciones'] as $fila)
                    <div class="fila-lista">
                        <span class="folio texto-13 sin-salto">{{ $fila['folio'] }}</span>
                        <div class="titulos crece-220">
                            <span class="negrita">{{ $fila['solicitante'] }} · {{ $fila['fecha'] }}</span>
                            <span class="texto-12 texto-sec">{{ $fila['justificacion'] ?: 'Sin justificación' }}</span>
                        </div>
                        <span class="insignia insignia--suave">{{ $fila['origen'] }}</span>
                    </div>
                @empty
                    <div class="vacio vacio--compacto">
                        <span class="icono" aria-hidden="true">event_busy</span>
                        <span class="vacio__texto">Sin cancelaciones ni vencimientos en el periodo.</span>
                    </div>
                @endforelse
            </section>
        </div>
    </div>
@endsection
