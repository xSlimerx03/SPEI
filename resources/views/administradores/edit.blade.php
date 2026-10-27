{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         edit.blade.php
 * @path         resources/views/administradores/edit.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Asignación del área de un administrador del sistema
 *               (responsable de área, RF-019): un área admite un solo
 *               responsable. Variables: administrador, areas.
 *
 * @requisitos   RF-019
 *
 * @see          CU-018
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Área del administrador')
@section('ruta', 'Administradores')

@php
    $areaActual = (int) old('id_area_asignada', $administrador->id_area_asignada);
@endphp

@section('contenido')
    <div class="contenedor-720">
        <form method="POST" action="{{ route('administradores.update', $administrador) }}"
              class="tarjeta tarjeta--relleno-24 pila--20 titulos" novalidate>
            @csrf
            @method('PUT')
            <div class="en-linea">
                <span class="avatar avatar--48" aria-hidden="true">
                    {{ \App\Support\Iniciales::de($administrador->nombre_completo) }}
                </span>
                <div class="titulos">
                    <span class="subtitulo-pagina">{{ $administrador->nombre_completo }}</span>
                    <span class="texto-13 texto-sec">{{ $administrador->correo_institucional }}</span>
                </div>
            </div>
            <div class="campo">
                <label for="id_area_asignada" class="campo__etiqueta">Área a cargo</label>
                <div class="selector selector--icono">
                    <span class="icono icono-inicio" aria-hidden="true">apartment</span>
                    <select id="id_area_asignada" name="id_area_asignada" class="control"
                            aria-describedby="area-ayuda" @error('id_area_asignada') aria-invalid="true" @enderror>
                        <option value="">Todas las áreas</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id_area }}"
                                    @selected($areaActual === $area->id_area)>
                                {{ $area->nombre_area }}
                            </option>
                        @endforeach
                    </select>
                    <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
                </div>
                <span id="area-ayuda" class="campo__ayuda">
                    Con área es responsable solo de ella; cada área admite un responsable.
                </span>
                @error('id_area_asignada')
                    <span class="campo__error"><span class="icono" aria-hidden="true">error</span>{{ $message }}</span>
                @enderror
            </div>
            <div class="pie-pasos">
                <a href="{{ route('administradores.index') }}" class="boton boton--fantasma boton--grande">Cancelar</a>
                <button type="submit" class="boton boton--primario boton--grande">
                    <span class="icono" aria-hidden="true">save</span>Guardar
                </button>
            </div>
        </form>
    </div>
@endsection
