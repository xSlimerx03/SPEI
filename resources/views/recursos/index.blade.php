{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         index.blade.php
 * @path         resources/views/recursos/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Recursos del administrador (prototipo hi-fi): chips por
 *               estado con conteo, "Escanear código", "Importar CSV" y
 *               "Crear", filtros y cuadrícula o lista con disponibilidad.
 *
 * @requisitos   RF-010, RF-011, RF-012, RF-013, RF-015
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Recursos); DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Recursos')

@php
    $estadoActual = $filtros['estado'] ?? null;
    $consulta = array_filter(\Illuminate\Support\Arr::except($filtros, ['estado', 'page']), fn (mixed $v): bool => $v !== null && $v !== '')
        + ($vistaLista ? ['vista' => 'lista'] : []);
    $chips = [
        '' => ['Todos', 'todos'],
        'disponible' => ['Disponibles', 'disponible'],
        'prestado' => ['Prestados', 'prestado'],
        'bloqueado' => ['Bloqueados', 'bloqueado'],
        'mantenimiento' => ['Mantenimiento', 'mantenimiento'],
    ];
@endphp

@section('contenido')
    <div class="pila--20 titulos">
        <div class="encabezado-pagina encabezado-pagina--centro">
            <nav class="chips" aria-label="Estados de recurso">
                @foreach ($chips as $clave => [$texto, $contador])
                    @php($activo = ($estadoActual ?? '') === $clave)
                    <a href="{{ route('recursos.index', $consulta + ($clave === '' ? [] : ['estado' => $clave])) }}"
                       class="chip {{ $activo ? 'chip--activo' : '' }}" @if ($activo) aria-current="page" @endif>
                        {{ $texto }}<span class="chip__contador">{{ $conteo[$contador] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="acciones">
                <button type="button" class="boton boton--secundario" data-abrir-modal="modal-codigo">
                    <span class="icono" aria-hidden="true">barcode_scanner</span>Escanear código
                </button>
                <a href="{{ route('recursos.importar') }}" class="boton boton--secundario">
                    <span class="icono" aria-hidden="true">upload_file</span>Importar CSV
                </a>
                <a href="{{ route('recursos.create') }}" class="boton boton--primario">
                    <span class="icono" aria-hidden="true">add</span>Crear
                </a>
            </div>
        </div>

        @include('recursos.filtros', ['ruta' => 'recursos.index'])

        @if ($recursos->isEmpty())
            <div class="tarjeta vacio">
                <span class="icono" aria-hidden="true">inventory_2</span>
                <span class="vacio__titulo">Sin recursos en este estado</span>
                <span class="vacio__texto">Prueba con otro filtro o limpia la búsqueda.</span>
            </div>
        @elseif ($vistaLista)
            @include('recursos.lista', ['rutaDetalle' => 'recursos.show'])
        @else
            <div class="cuadricula-recursos">
                @foreach ($recursos as $recurso)
                    @include('recursos.tarjeta', [
                        'recurso' => $recurso,
                        'disponibilidad' => $disponibilidad[$recurso->id_recurso] ?? '',
                        'enlace' => route('recursos.show', $recurso),
                        'solicitable' => false,
                    ])
                @endforeach
            </div>
        @endif
        {{ $recursos->links('partials.paginacion', ['suelta' => true]) }}
    </div>

    @push('modales')
        <dialog id="modal-codigo" class="modal" aria-labelledby="modal-codigo-titulo">
            <form method="GET" action="{{ route('recursos.buscar') }}" class="modal__contenido">
                <div class="modal__cabecera">
                    <span class="icono-tile icono-tile--44 icono-tile--azul" aria-hidden="true"><span class="icono">barcode_scanner</span></span>
                    <div class="pila--4 titulos crece">
                        <h2 id="modal-codigo-titulo" class="modal__titulo">Escanear código</h2>
                        <p class="modal__descripcion">Coloca el cursor en el campo y escanea la etiqueta con el lector. Si el código no existe, abrimos el alta con el número precargado.</p>
                    </div>
                    <button type="button" class="modal__cerrar" data-cerrar-modal aria-label="Cerrar">
                        <span class="icono" aria-hidden="true">close</span>
                    </button>
                </div>
                @include('partials.campo', [
                    'nombre' => 'codigo',
                    'etiqueta' => 'Número de inventario o de serie',
                    'icono' => 'barcode',
                    'atributos' => 'maxlength="100" autocomplete="off" autofocus',
                ])
                <div class="acciones acciones--fin">
                    <button type="button" class="boton boton--secundario boton--grande" data-cerrar-modal>Cancelar</button>
                    <button type="submit" class="boton boton--primario boton--grande">
                        <span class="icono" aria-hidden="true">search</span>Buscar
                    </button>
                </div>
            </form>
        </dialog>
    @endpush
@endsection
