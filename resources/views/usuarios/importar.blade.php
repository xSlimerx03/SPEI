{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         importar.blade.php
 * @path         resources/views/usuarios/importar.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Carga masiva de usuarios por CSV (RF-016): todo o nada, con
 *               el formato del archivo y sus reglas. Variables: actor.
 *
 * @requisitos   RF-016, RF-017
 *
 * @see          CU-016
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Importar usuarios')
@section('ruta', 'Usuarios')

@section('contenido')
    <div class="cuadricula-380">
        <form method="POST" action="{{ route('usuarios.importar.store') }}" enctype="multipart/form-data"
              class="tarjeta tarjeta--relleno pila--16 titulos" novalidate>
            @csrf
            <div class="titulos">
                <h2 class="tarjeta__titulo">Carga masiva de usuarios</h2>
                <span class="tarjeta__subtitulo">Si alguna fila tiene errores no se importa ninguna.</span>
            </div>
            <label for="archivo" class="zona-archivo">
                <span class="icono icono--40 icono--azul" aria-hidden="true">upload_file</span>
                <span class="texto-15 negrita">Selecciona tu archivo CSV</span>
                <span class="texto-13 texto-sec">
                    UTF-8 separado por comas · máx. 2 MB y
                    {{ number_format((int) config('spei.importacion.maximo_filas'), 0, '.', ',') }} filas
                </span>
                <input type="file" id="archivo" name="archivo" class="control" accept=".csv,text/csv" required
                       @error('archivo') aria-invalid="true" aria-describedby="archivo-error" @enderror>
            </label>
            @error('archivo')
                <div id="archivo-error" class="alerta alerta--error" role="alert">
                    <span class="icono" aria-hidden="true">error</span>
                    <ul class="alerta__lista">
                        @foreach ($errors->get('archivo') as $mensaje)
                            <li>{{ $mensaje }}</li>
                        @endforeach
                    </ul>
                </div>
            @enderror
            <div class="pie-pasos">
                <a href="{{ route('usuarios.index') }}" class="boton boton--fantasma boton--grande">Cancelar</a>
                <button type="submit" class="boton boton--primario boton--grande">
                    <span class="icono" aria-hidden="true">upload_file</span>Importar
                </button>
            </div>
        </form>

        <section class="tarjeta tarjeta--relleno pila--12 titulos" aria-labelledby="titulo-formato">
            <h2 id="titulo-formato" class="tarjeta__titulo">Formato del archivo</h2>
            <span class="campo__etiqueta">Encabezado (primera fila, en este orden)</span>
            <code class="bloque-codigo">{{ implode(',', \App\Services\UsuarioService::ENCABEZADO) }}</code>
            <div class="reglas">
                <div class="reglas__item">
                    <span class="icono" aria-hidden="true">badge</span><span><code class="mono">rol</code>:
                    <code class="mono">alumno</code>, <code class="mono">docente</code> o
                    <code class="mono">asistente</code>. Si se deja vacío se aplica la regla del dominio del
                    correo.</span>
                </div>
                <div class="reglas__item">
                    <span class="icono" aria-hidden="true">apartment</span><span><code class="mono">nombre_area</code>
                    es obligatorio para asistentes y debe coincidir con un área activa.</span>
                </div>
                <div class="reglas__item">
                    <span class="icono" aria-hidden="true">alternate_email</span><span>El correo debe ser del dominio
                    de la institución o de un subdominio, y no estar registrado.</span>
                </div>
                <div class="reglas__item">
                    <span class="icono" aria-hidden="true">mail</span><span>Cada cuenta creada recibe una invitación
                    para definir su contraseña.</span>
                </div>
            </div>
        </section>
    </div>
@endsection
