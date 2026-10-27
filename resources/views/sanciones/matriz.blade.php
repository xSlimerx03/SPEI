{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.6: Penalizaciones y Sanciones Automatizadas
 * @file         matriz.blade.php
 * @path         resources/views/sanciones/matriz.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Matriz de sanciones de la institución (RF-041): marcas por
 *               incidente, umbral de bloqueo, días de sanción y estado de cada
 *               tipo. Variables: reglas (Collection por tipo).
 *
 * @requisitos   RF-041, RN-005
 *
 * @see          CU-019
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Matriz de sanciones')
@section('ruta', 'Sanciones')

@php
    $campos = [
        'marcas_por_incidente' => ['Marcas', 0, 10],
        'umbral_bloqueo' => ['Umbral de bloqueo', 1, 20],
        'dias_sancion' => ['Días de sanción', 1, 90],
    ];
@endphp

@section('contenido')
    <form method="POST" action="{{ route('sanciones.matriz.update') }}" class="tarjeta pila--16" novalidate>
        @csrf
        @method('PUT')
        <div class="tarjeta__cabecera">
            <div class="titulos">
                <h2 class="tarjeta__titulo">Reglas por tipo de incidente</h2>
                <span class="tarjeta__subtitulo">
                    Al alcanzar el umbral, la cuenta se bloquea los días indicados y después el contador se reinicia.
                    Una entrega no recogida no genera marca (RN-003).
                </span>
            </div>
        </div>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th scope="col">Incidente</th>
                        <th scope="col">Marcas</th>
                        <th scope="col">Umbral de bloqueo</th>
                        <th scope="col">Días de sanción</th>
                        <th scope="col">Activa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (\App\Enums\TipoIncidente::cases() as $tipo)
                        @php
                            $regla = $reglas->get($tipo->value);
                            $base = 'reglas.' . $tipo->value . '.';
                        @endphp
                        <tr>
                            <th scope="row" class="negrita">{{ $tipo->obtenerEtiqueta() }}</th>
                            @foreach ($campos as $campo => [$etiqueta, $min, $max])
                                <td>
                                    <label for="{{ $tipo->value }}-{{ $campo }}" class="visualmente-oculto">
                                        {{ $etiqueta }} de {{ mb_strtolower($tipo->obtenerEtiqueta()) }}
                                    </label>
                                    <input type="number" id="{{ $tipo->value }}-{{ $campo }}"
                                           name="reglas[{{ $tipo->value }}][{{ $campo }}]"
                                           class="control control--compacto"
                                           min="{{ $min }}" max="{{ $max }}" required
                                           value="{{ old($base . $campo, $regla?->{$campo}) }}"
                                           @error($base . $campo) aria-invalid="true" @enderror>
                                    @error($base . $campo)
                                        <span class="campo__error">{{ $message }}</span>
                                    @enderror
                                </td>
                            @endforeach
                            <td>
                                <input type="hidden" name="reglas[{{ $tipo->value }}][activo]" value="0">
                                <label class="casilla">
                                    <input type="checkbox" name="reglas[{{ $tipo->value }}][activo]" value="1"
                                           @checked(old($base . 'activo', $regla?->activo))>
                                    <span class="visualmente-oculto">
                                        Regla activa de {{ $tipo->obtenerEtiqueta() }}
                                    </span>
                                </label>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pie-pasos relleno-20">
            <a href="{{ route('sanciones.index') }}" class="boton boton--fantasma boton--grande">Volver</a>
            <button type="submit" class="boton boton--primario boton--grande">
                <span class="icono" aria-hidden="true">save</span>Guardar
            </button>
        </div>
    </form>
@endsection
