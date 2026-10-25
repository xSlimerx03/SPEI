{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         create.blade.php
 * @path         resources/views/administradores/create.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Invitación de un administrador del sistema con área opcional.
 *
 * @requisitos   RF-008
 *
 * @see          docs/requisitos/BBB_CU004_InvitarAdministrador_v1.0.md (paso 1); DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Invitar administrador')
@section('ruta', 'Administradores')

@section('contenido')
    <div class="contenedor-720">
        <form method="POST" action="{{ route('administradores.store') }}" class="tarjeta tarjeta--relleno-24 pila--20 titulos" novalidate>
            @csrf
            <div class="pila--4 titulos">
                <h2 class="subtitulo-pagina">Datos del administrador</h2>
                <p class="texto-sec">Recibirá un enlace válido por 72 horas para definir su contraseña.</p>
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
                <label for="id_area_asignada" class="campo__etiqueta">Área a cargo (opcional)</label>
                <div class="selector selector--icono">
                    <span class="icono icono-inicio" aria-hidden="true">apartment</span>
                    <select id="id_area_asignada" name="id_area_asignada" class="control" aria-describedby="id_area_asignada-ayuda"
                            @error('id_area_asignada') aria-invalid="true" @enderror>
                        <option value="">Todas las áreas</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id_area }}" @selected((int) old('id_area_asignada') === $area->id_area)>{{ $area->nombre_area }}</option>
                        @endforeach
                    </select>
                    <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
                </div>
                <span id="id_area_asignada-ayuda" class="campo__ayuda">Sin área, administra todo el inventario y las áreas; con área, solo los recursos de esa área.</span>
                @error('id_area_asignada')
                    <span class="campo__error"><span class="icono" aria-hidden="true">error</span>{{ $message }}</span>
                @enderror
            </div>
            <div class="pie-pasos">
                <a href="{{ route('administradores.index') }}" class="boton boton--fantasma boton--grande">Cancelar</a>
                <button type="submit" class="boton boton--primario boton--grande">
                    <span class="icono" aria-hidden="true">send</span>Enviar
                </button>
            </div>
        </form>
    </div>
@endsection
