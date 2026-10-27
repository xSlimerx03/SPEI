{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         dominios.blade.php
 * @path         resources/views/usuarios/dominios.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Reglas dominio → rol (RF-017): lista con baja y formulario de
 *               alta. Variables: reglas, dominioInstitucion.
 *
 * @requisitos   RF-017, RN-001
 *
 * @see          CU-016; DT-034
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Reglas de dominio')
@section('ruta', 'Usuarios')

@section('contenido')
    <div class="cuadricula-380">
        <section class="tarjeta" aria-labelledby="titulo-reglas">
            <div class="tarjeta__cabecera">
                <div class="titulos">
                    <h2 id="titulo-reglas" class="tarjeta__titulo">Rol por dominio del correo</h2>
                    <span class="tarjeta__subtitulo">
                        Gana el dominio más específico. Cambiar una regla no modifica las cuentas ya creadas.
                    </span>
                </div>
            </div>
            @forelse ($reglas as $regla)
                <div class="fila-lista">
                    <span class="icono-tile" aria-hidden="true"><span class="icono">alternate_email</span></span>
                    <span class="mono crece">{{ '@' . $regla->dominio }}</span>
                    <span class="insignia insignia--suave">{{ $regla->rol->obtenerEtiqueta() }}</span>
                    <form method="POST" action="{{ route('dominios.destroy', $regla) }}" class="formulario-en-linea"
                          data-confirmar="¿Eliminar la regla del dominio {{ $regla->dominio }}?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="boton boton--peligro-texto boton--pequeno">
                            <span class="icono" aria-hidden="true">delete</span>Eliminar
                        </button>
                    </form>
                </div>
            @empty
                <div class="vacio">
                    <span class="icono" aria-hidden="true">alternate_email</span>
                    <span class="vacio__titulo">Sin reglas de dominio</span>
                    <span class="vacio__texto">Mientras no existan, el rol de cada cuenta se indica al invitarla.</span>
                </div>
            @endforelse
        </section>

        <form method="POST" action="{{ route('dominios.store') }}"
              class="tarjeta tarjeta--relleno pila--16 titulos" novalidate>
            @csrf
            <div class="titulos">
                <h2 class="tarjeta__titulo">Nueva regla</h2>
                <span class="tarjeta__subtitulo">Usa {{ $dominioInstitucion }} o un subdominio de él.</span>
            </div>
            @include('partials.campo', [
                'nombre' => 'dominio',
                'etiqueta' => 'Dominio',
                'icono' => 'alternate_email',
                'atributos' => 'maxlength="150" autocomplete="off" placeholder="alumnos.' . $dominioInstitucion . '"',
            ])
            <div class="campo">
                <label for="rol" class="campo__etiqueta">Rol que asigna</label>
                <div class="selector selector--icono">
                    <span class="icono icono-inicio" aria-hidden="true">badge</span>
                    <select id="rol" name="rol" class="control" @error('rol') aria-invalid="true" @enderror>
                        <option value="alumno" @selected(old('rol') === 'alumno')>Alumno</option>
                        <option value="docente" @selected(old('rol') === 'docente')>Docente</option>
                    </select>
                    <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
                </div>
                @error('rol')
                    <span class="campo__error"><span class="icono" aria-hidden="true">error</span>{{ $message }}</span>
                @enderror
            </div>
            <div class="pie-pasos">
                <a href="{{ route('usuarios.index') }}" class="boton boton--fantasma boton--grande">Volver</a>
                <button type="submit" class="boton boton--primario boton--grande">
                    <span class="icono" aria-hidden="true">add</span>Agregar
                </button>
            </div>
        </form>
    </div>
@endsection
