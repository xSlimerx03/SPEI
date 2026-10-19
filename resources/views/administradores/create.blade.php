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
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Invitación de un administrador del sistema con área opcional.
 *
 * @requisitos   RF-008
 *
 * @see          docs/requisitos/BBB_CU004_InvitarAdministrador_v1.0.md (paso 1)
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Invitar administrador')

@section('contenido')
    <div class="tarjeta contenedor-formulario">
        <form method="POST" action="{{ route('administradores.store') }}" novalidate>
            @csrf
            @include('partials.campo', ['nombre' => 'nombre_completo', 'etiqueta' => 'Nombre completo'])
            @include('partials.campo', [
                'nombre' => 'correo_institucional',
                'etiqueta' => 'Correo institucional',
                'tipo' => 'email',
            ])
            <div class="campo">
                <label for="id_area_asignada" class="campo__etiqueta">Área a cargo (opcional)</label>
                <select id="id_area_asignada" name="id_area_asignada" class="control" aria-describedby="ayuda-area">
                    <option value="">Todas las áreas</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->id_area }}" @selected((int) old('id_area_asignada') === $area->id_area)>
                            {{ $area->nombre_area }}
                        </option>
                    @endforeach
                </select>
                <span id="ayuda-area" class="campo__ayuda">
                    Sin área, administra todo el inventario y las áreas; con área, solo los recursos de esa área.
                </span>
            </div>
            <p class="texto-secundario">El invitado recibirá un enlace válido por 72 horas para definir su contraseña.</p>
            <div class="acciones">
                <button type="submit" class="boton">
                    <span class="icono icono--boton" aria-hidden="true">send</span>Enviar
                </button>
                <a href="{{ route('administradores.index') }}" class="boton boton--secundario">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
