{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         edit.blade.php
 * @path         resources/views/registro/edit.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Formulario para que el administrador invitado defina su
 *               contraseña desde el enlace firmado.
 *
 * @requisitos   RF-008
 *
 * @see          docs/requisitos/BBB_CU004_InvitarAdministrador_v1.0.md
 * ============================================================================
--}}
@extends('layouts.publico')

@section('titulo', 'Completar registro')

@section('contenido')
    <div class="tarjeta">
        <div class="publico__encabezado">
            <h1 class="titulo-pantalla">Completar registro</h1>
            <p class="texto-secundario">{{ $usuario->nombre_completo }} · {{ $usuario->correo_institucional }}</p>
        </div>

        <form method="POST" action="{{ $accion }}" novalidate>
            @csrf
            @include('partials.campo', [
                'nombre' => 'contrasena',
                'etiqueta' => 'Contraseña',
                'tipo' => 'password',
                'ayuda' => 'Mínimo 10 caracteres con letras y números.',
                'atributos' => 'autocomplete="new-password"',
            ])
            @include('partials.campo', [
                'nombre' => 'contrasena_confirmation',
                'etiqueta' => 'Confirmar contraseña',
                'tipo' => 'password',
                'atributos' => 'autocomplete="new-password"',
            ])
            <button type="submit" class="boton boton--bloque">
                <span class="icono icono--boton" aria-hidden="true">save</span>Guardar
            </button>
        </form>
    </div>
@endsection
