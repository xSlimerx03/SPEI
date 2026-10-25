{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         index.blade.php
 * @path         resources/views/catalogo/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Catálogo del usuario final (prototipo hi-fi): filtros y
 *               cuadrícula de tarjetas disponibles (RN-002) con "Solicitar".
 *
 * @requisitos   RF-015, RF-022, RN-002, RNF-009
 *
 * @see          docs/requisitos/BBB_CU010_ConsultarCatalogo_v1.0.md; DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Catálogo')

@section('contenido')
    <div class="pila--20 titulos">
        <div class="encabezado-pagina encabezado-pagina--centro">
            <div class="titulos">
                <span class="subtitulo-pagina">Recursos disponibles</span>
                <span class="texto-13 texto-sec">Solo se muestran los recursos que puedes solicitar ahora.</span>
            </div>
            <a href="{{ route('prestamos.create') }}" class="boton boton--primario">
                <span class="icono" aria-hidden="true">add_circle</span>Solicitar préstamo
            </a>
        </div>

        @include('recursos.filtros', ['ruta' => 'catalogo.index'])

        @if ($recursos->isEmpty())
            <div class="tarjeta vacio">
                <span class="icono" aria-hidden="true">inbox</span>
                <span class="vacio__titulo">No hay recursos disponibles</span>
                <span class="vacio__texto">Prueba con otro filtro o vuelve más tarde.</span>
            </div>
        @elseif ($vistaLista)
            @include('recursos.lista', ['rutaDetalle' => 'catalogo.show'])
        @else
            <div class="cuadricula-recursos">
                @foreach ($recursos as $recurso)
                    @include('recursos.tarjeta', [
                        'recurso' => $recurso,
                        'disponibilidad' => $disponibilidad[$recurso->id_recurso] ?? '',
                        'enlace' => route('catalogo.show', $recurso->id_recurso),
                        'solicitable' => true,
                    ])
                @endforeach
            </div>
        @endif
        {{ $recursos->links('partials.paginacion', ['suelta' => true]) }}
    </div>
@endsection
