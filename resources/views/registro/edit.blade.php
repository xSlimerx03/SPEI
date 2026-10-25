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
 * @lastModified 2026-10-23
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Formulario para que el administrador invitado defina su
 *               contraseña desde el enlace firmado.
 *
 * @requisitos   RF-008
 *
 * @see          docs/requisitos/BBB_CU004_InvitarAdministrador_v1.0.md; DT-028
 * ============================================================================
--}}
@extends('layouts.publico')

@section('titulo', 'Completar registro')

@section('contenido')
    <div class="acceso__tarjeta">
        @include('partials.marca-acceso')

        <div class="pila--4 titulos">
            <h1 class="acceso__titulo">Completar registro</h1>
            <p class="texto-sec">Define tu contraseña para activar tu cuenta.</p>
        </div>

        <div class="acceso__institucion">
            <span class="icono-tile" aria-hidden="true"><span class="icono">person</span></span>
            <div class="titulos crece">
                <span class="negrita">{{ $usuario->nombre_completo }}</span>
                <span class="texto-12 texto-sec">{{ $usuario->correo_institucional }}</span>
            </div>
        </div>

        <form method="POST" action="{{ $accion }}" class="pila--16 titulos" novalidate>
            @csrf
            @include('partials.campo', [
                'nombre' => 'contrasena',
                'etiqueta' => 'Contraseña',
                'tipo' => 'password',
                'icono' => 'lock',
                'ayuda' => 'Mínimo 10 caracteres con letras y números.',
                'atributos' => 'autocomplete="new-password" minlength="10"',
            ])
            @include('partials.campo', [
                'nombre' => 'contrasena_confirmation',
                'etiqueta' => 'Confirmar contraseña',
                'tipo' => 'password',
                'icono' => 'lock',
                'atributos' => 'autocomplete="new-password" minlength="10"',
            ])
            <button type="submit" class="boton boton--primario boton--grande boton--bloque">
                <span class="icono" aria-hidden="true">save</span>Guardar
            </button>
        </form>
    </div>
@endsection
