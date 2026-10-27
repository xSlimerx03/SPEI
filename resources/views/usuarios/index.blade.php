{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         index.blade.php
 * @path         resources/views/usuarios/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Lista de alumnos, docentes y asistentes con filtros por texto,
 *               rol y estado. El responsable de un área solo ve a sus
 *               asistentes. Variables: usuarios, filtros, actor, reglas.
 *
 * @requisitos   RF-016, RF-017, RF-018, RF-020, RF-021
 *
 * @see          CU-016, CU-017; DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', $actor->esResponsableDeArea() ? 'Asistentes' : 'Usuarios')
@section('ruta', 'Administración')

@section('contenido')
    <section class="tarjeta" aria-labelledby="titulo-usuarios">
        <div class="tarjeta__cabecera">
            <div class="titulos">
                <h2 id="titulo-usuarios" class="tarjeta__titulo">
                    {{ $actor->esResponsableDeArea() ? 'Asistentes de tu área' : 'Alumnos, docentes y asistentes' }}
                </h2>
                <span class="tarjeta__subtitulo">
                    Cada cuenta recibe una invitación para definir su contraseña; el rol no se autodeclara.
                </span>
            </div>
            <div class="acciones">
                @unless ($actor->esResponsableDeArea())
                    <a href="{{ route('dominios.index') }}" class="boton boton--secundario">
                        <span class="icono" aria-hidden="true">alternate_email</span>Ver dominios
                    </a>
                @endunless
                <a href="{{ route('usuarios.importar') }}" class="boton boton--secundario">
                    <span class="icono" aria-hidden="true">upload_file</span>Importar CSV
                </a>
                <a href="{{ route('usuarios.create') }}" class="boton boton--primario">
                    <span class="icono" aria-hidden="true">person_add</span>Invitar
                </a>
            </div>
        </div>

        @if (! $actor->esResponsableDeArea() && $reglas === 0)
            <div class="alerta alerta--info relleno-20">
                <span class="icono" aria-hidden="true">info</span>
                <span>
                    Aún no hay reglas de dominio: indica el rol de cada cuenta o
                    <a href="{{ route('dominios.index') }}">define qué dominio corresponde a alumnos y docentes</a>.
                </span>
            </div>
        @endif

        <form method="GET" action="{{ route('usuarios.index') }}" class="barra-filtros barra-filtros--tabla"
              role="search">
            <div class="control-icono control-icono--con-icono control-icono--compacto barra-filtros__busqueda">
                <span class="icono" aria-hidden="true">search</span>
                <label for="filtro-texto" class="visualmente-oculto">Buscar usuario</label>
                <input type="search" id="filtro-texto" name="texto" class="control control--compacto" maxlength="100"
                       placeholder="Buscar por nombre o correo" value="{{ $filtros['texto'] ?? '' }}">
            </div>
            @unless ($actor->esResponsableDeArea())
                <div class="selector selector--150">
                    <label for="filtro-rol" class="visualmente-oculto">Rol</label>
                    <select id="filtro-rol" name="rol" class="control control--compacto" data-autoenvio>
                        <option value="">Todos los roles</option>
                        @foreach (['alumno' => 'Alumnos', 'docente' => 'Docentes',
                            'asistente' => 'Asistentes'] as $valor => $texto)
                            <option value="{{ $valor }}"
                                    @selected(($filtros['rol'] ?? '') === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                    <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
                </div>
            @endunless
            <div class="selector selector--150">
                <label for="filtro-estado" class="visualmente-oculto">Estado</label>
                <select id="filtro-estado" name="estado" class="control control--compacto" data-autoenvio>
                    <option value="">Todos los estados</option>
                    @foreach (['pendiente' => 'Invitación pendiente', 'activa' => 'Activas',
                        'baja' => 'Dadas de baja'] as $valor => $texto)
                        <option value="{{ $valor }}"
                                @selected(($filtros['estado'] ?? '') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
                <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
            </div>
            <button type="submit" class="boton boton--secundario">
                <span class="icono" aria-hidden="true">filter_list</span>Filtrar
            </button>
        </form>

        @forelse ($usuarios as $usuario)
            @include('usuarios.fila', ['usuario' => $usuario])
        @empty
            <div class="vacio">
                <span class="icono" aria-hidden="true">group</span>
                <span class="vacio__titulo">No hay cuentas con esos filtros</span>
                <span class="vacio__texto">Invita a una persona o importa un archivo CSV.</span>
            </div>
        @endforelse
        {{ $usuarios->links('partials.paginacion') }}
    </section>
@endsection
