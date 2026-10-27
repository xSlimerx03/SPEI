{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         create.blade.php
 * @path         resources/views/usuarios/create.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Invitación de un alumno, docente o asistente. Sin rol
 *               indicado, se aplica la regla del dominio del correo (RF-017).
 *               El responsable de un área solo invita asistentes de su área.
 *               Variables: actor, areas, reglas.
 *
 * @requisitos   RF-016, RF-017, RF-020
 *
 * @see          CU-016; DT-034
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Invitar usuario')
@section('ruta', 'Usuarios')

@php
    $esResponsable = $actor->esResponsableDeArea();
    $roles = $esResponsable
        ? ['asistente' => 'Asistente de mostrador']
        : ['' => 'Según el dominio del correo', 'alumno' => 'Alumno', 'docente' => 'Docente',
            'asistente' => 'Asistente de mostrador'];
@endphp

@section('contenido')
    <div class="contenedor-720">
        <form method="POST" action="{{ route('usuarios.store') }}"
              class="tarjeta tarjeta--relleno-24 pila--20 titulos" novalidate>
            @csrf
            <div class="pila--4 titulos">
                <h2 class="subtitulo-pagina">Datos de la persona</h2>
                <p class="texto-sec">
                    Recibirá un enlace válido por {{ config('spei.invitacion.vigencia_horas') }} horas para definir
                    su contraseña.
                </p>
            </div>
            @include('partials.campo', [
                'nombre' => 'nombre_completo',
                'etiqueta' => 'Nombre completo',
                'icono' => 'person',
                'atributos' => 'maxlength="150" autocomplete="off"',
            ])
            @include('partials.campo', [
                'nombre' => 'correo_institucional',
                'etiqueta' => 'Correo institucional',
                'tipo' => 'email',
                'icono' => 'mail',
                'atributos' => 'maxlength="150" autocomplete="off"',
            ])
            <div class="campo">
                <label for="rol" class="campo__etiqueta">Rol</label>
                <div class="selector selector--icono">
                    <span class="icono icono-inicio" aria-hidden="true">badge</span>
                    <select id="rol" name="rol" class="control" aria-describedby="rol-ayuda"
                            @error('rol') aria-invalid="true" @enderror>
                        @foreach ($roles as $valor => $texto)
                            <option value="{{ $valor }}"
                                    @selected(old('rol', $esResponsable ? 'asistente' : '') === $valor)>
                                {{ $texto }}
                            </option>
                        @endforeach
                    </select>
                    <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
                </div>
                <span id="rol-ayuda" class="campo__ayuda">
                    @if ($reglas->isEmpty())
                        No hay reglas de dominio: elige el rol de forma explícita.
                    @else
                        Reglas vigentes:
                        @foreach ($reglas as $regla)
                            {{ '@' . $regla->dominio }} → {{ mb_strtolower($regla->rol->obtenerEtiqueta()) }}@if (!
                            $loop->last), @endif
                        @endforeach
                    @endif
                </span>
                @error('rol')
                    <span class="campo__error"><span class="icono" aria-hidden="true">error</span>{{ $message }}</span>
                @enderror
            </div>
            <div class="campo">
                <label for="id_area_asignada" class="campo__etiqueta">Área (solo asistentes)</label>
                <div class="selector selector--icono">
                    <span class="icono icono-inicio" aria-hidden="true">apartment</span>
                    <select id="id_area_asignada" name="id_area_asignada" class="control"
                            @error('id_area_asignada') aria-invalid="true" @enderror>
                        @unless ($esResponsable)
                            <option value="">Sin área</option>
                        @endunless
                        @foreach ($areas as $area)
                            <option value="{{ $area->id_area }}"
                                    @selected((int) old('id_area_asignada') === $area->id_area)>
                                {{ $area->nombre_area }}
                            </option>
                        @endforeach
                    </select>
                    <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
                </div>
                @error('id_area_asignada')
                    <span class="campo__error"><span class="icono" aria-hidden="true">error</span>{{ $message }}</span>
                @enderror
            </div>
            <div class="pie-pasos">
                <a href="{{ route('usuarios.index') }}" class="boton boton--fantasma boton--grande">Cancelar</a>
                <button type="submit" class="boton boton--primario boton--grande">
                    <span class="icono" aria-hidden="true">send</span>Enviar
                </button>
            </div>
        </form>
    </div>
@endsection
