{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         edit.blade.php
 * @path         resources/views/usuarios/edit.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Cambio de rol y de área de una cuenta (RF-018). Asignar o
 *               mover a un asistente delega la operación del mostrador de esa
 *               área y queda en auditoría (RF-021). Variables: actor, usuario,
 *               areas.
 *
 * @requisitos   RF-018, RF-020, RF-021
 *
 * @see          CU-017
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Editar usuario')
@section('ruta', 'Usuarios')

@php
    $esResponsable = $actor->esResponsableDeArea();
    $roles = $esResponsable
        ? ['asistente' => 'Asistente de mostrador']
        : ['alumno' => 'Alumno', 'docente' => 'Docente', 'asistente' => 'Asistente de mostrador'];
    $rolActual = old('rol', $usuario->rol->value);
    $areaActual = (int) old('id_area_asignada', $usuario->id_area_asignada);
@endphp

@section('contenido')
    <div class="contenedor-720">
        <form method="POST" action="{{ route('usuarios.update', $usuario) }}"
              class="tarjeta tarjeta--relleno-24 pila--20 titulos" novalidate>
            @csrf
            @method('PUT')
            <div class="en-linea">
                <span class="avatar avatar--48" aria-hidden="true">
                    {{ \App\Support\Iniciales::de($usuario->nombre_completo) }}
                </span>
                <div class="titulos">
                    <span class="subtitulo-pagina">{{ $usuario->nombre_completo }}</span>
                    <span class="texto-13 texto-sec">{{ $usuario->correo_institucional }}</span>
                </div>
            </div>
            <div class="campo">
                <label for="rol" class="campo__etiqueta">Rol</label>
                <div class="selector selector--icono">
                    <span class="icono icono-inicio" aria-hidden="true">badge</span>
                    <select id="rol" name="rol" class="control" @error('rol') aria-invalid="true" @enderror>
                        @foreach ($roles as $valor => $texto)
                            <option value="{{ $valor }}" @selected($rolActual === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                    <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
                </div>
                @error('rol')
                    <span class="campo__error"><span class="icono" aria-hidden="true">error</span>{{ $message }}</span>
                @enderror
            </div>
            <div class="campo">
                <label for="id_area_asignada" class="campo__etiqueta">Área del mostrador (solo asistentes)</label>
                <div class="selector selector--icono">
                    <span class="icono icono-inicio" aria-hidden="true">apartment</span>
                    <select id="id_area_asignada" name="id_area_asignada" class="control"
                            aria-describedby="area-ayuda" @error('id_area_asignada') aria-invalid="true" @enderror>
                        @unless ($esResponsable)
                            <option value="">Sin área</option>
                        @endunless
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
                    El asistente solo valida entregas y devoluciones de los recursos de esta área.
                </span>
                @error('id_area_asignada')
                    <span class="campo__error"><span class="icono" aria-hidden="true">error</span>{{ $message }}</span>
                @enderror
            </div>
            <div class="pie-pasos">
                <a href="{{ route('usuarios.index') }}" class="boton boton--fantasma boton--grande">Cancelar</a>
                <button type="submit" class="boton boton--primario boton--grande">
                    <span class="icono" aria-hidden="true">save</span>Guardar
                </button>
            </div>
        </form>
    </div>
@endsection
