{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         form.blade.php
 * @path         resources/views/recursos/form.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Alta y edición de un recurso; el número de inventario acepta
 *               lector de código de barras.
 *
 * @requisitos   RF-010, RF-011
 *
 * @see          docs/requisitos/BBB_CU007_RegistrarRecurso_v1.0.md; DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', $recurso->exists ? 'Editar recurso' : 'Registrar recurso')
@section('ruta', 'Recursos')

@section('contenido')
    <div class="contenedor-720">
        @if ($areas->isEmpty())
            <div class="tarjeta vacio">
                <span class="icono" aria-hidden="true">apartment</span>
                <span class="vacio__titulo">Primero crea un área activa</span>
                <span class="vacio__texto">Cada recurso se resguarda en un área.</span>
            </div>
        @else
            <form method="POST"
                  action="{{ $recurso->exists ? route('recursos.update', $recurso) : route('recursos.store') }}"
                  class="tarjeta tarjeta--relleno-24 pila--20 titulos" novalidate>
                @csrf
                @if ($recurso->exists)
                    @method('PUT')
                @endif
                <div class="cuadricula-campos">
                    @foreach ([
                        'id_area' => ['Área', 'apartment',
                            $areas->map(fn (\App\Models\Area $a): array => [$a->id_area, $a->nombre_area]),
                            (string) old('id_area', $recurso->id_area)],
                        'tipo_recurso' => ['Tipo', 'category', collect($tipos)
                            ->map(fn (\App\Enums\TipoRecurso $t): array => [$t->value, $t->obtenerEtiqueta()]),
                            (string) old('tipo_recurso', $recurso->tipo_recurso?->value)],
                    ] as $campo => [$etiqueta, $icono, $opciones, $elegido])
                        <div class="campo">
                            <label for="{{ $campo }}" class="campo__etiqueta">{{ $etiqueta }}</label>
                            <div class="selector selector--icono">
                                <span class="icono icono-inicio" aria-hidden="true">{{ $icono }}</span>
                                <select id="{{ $campo }}" name="{{ $campo }}" class="control" required
                                        @error($campo) aria-invalid="true" aria-describedby="{{ $campo }}-error"
                                        @enderror>
                                    @foreach ($opciones as [$valor, $texto])
                                        <option value="{{ $valor }}"
                                                @selected($elegido === (string) $valor)>{{ $texto }}</option>
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
                @include('partials.campo', [
                    'nombre' => 'nombre_recurso',
                    'etiqueta' => 'Nombre del recurso',
                    'valor' => $recurso->nombre_recurso,
                    'icono' => 'devices',
                    'marcador' => 'Ej. Proyector Epson PowerLite X49',
                    'atributos' => 'maxlength="150"',
                ])
                <div class="cuadricula-campos">
                    @include('partials.campo', [
                        'nombre' => 'numero_inventario',
                        'etiqueta' => 'Número de inventario',
                        'valor' => $recurso->numero_inventario,
                        'icono' => 'barcode',
                        'ayuda' => 'Puedes capturarlo con el lector de código de barras.',
                        'atributos' => 'maxlength="100" autocomplete="off"',
                    ])
                    @include('partials.campo', [
                        'nombre' => 'numero_serie',
                        'etiqueta' => 'Número de serie (opcional)',
                        'valor' => $recurso->numero_serie,
                        'requerido' => false,
                        'atributos' => 'maxlength="100" autocomplete="off"',
                    ])
                    @include('partials.campo', [
                        'nombre' => 'marca',
                        'etiqueta' => 'Marca (opcional)',
                        'valor' => $recurso->marca,
                        'requerido' => false,
                        'atributos' => 'maxlength="100"',
                    ])
                    @include('partials.campo', [
                        'nombre' => 'anio_adquisicion',
                        'etiqueta' => 'Año de adquisición (opcional)',
                        'tipo' => 'number',
                        'valor' => $recurso->anio_adquisicion,
                        'requerido' => false,
                        'atributos' => 'min="1990" max="' . now()->year . '"',
                    ])
                </div>
                @unless ($recurso->exists)
                    <fieldset class="grupo-campos">
                        <legend class="campo__etiqueta">Estado inicial</legend>
                        <div class="condicion alinear-inicio">
                            @foreach (['disponible' => 'Disponible',
                                'mantenimiento' => 'Mantenimiento'] as $valor => $texto)
                                <label>
                                    <input type="radio" name="estado" value="{{ $valor }}"
                                           @checked(old('estado', 'disponible') === $valor)>
                                    <span>{{ $texto }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endunless
                <div class="pie-pasos">
                    <a href="{{ $recurso->exists ? route('recursos.show', $recurso) : route('recursos.index') }}"
                       class="boton boton--fantasma boton--grande">Cancelar</a>
                    <button type="submit" class="boton boton--primario boton--grande">
                        <span class="icono" aria-hidden="true">save</span>Guardar
                    </button>
                </div>
            </form>
        @endif
    </div>
@endsection
