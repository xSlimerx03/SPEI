{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Autenticación
 * @file         create.blade.php
 * @path         resources/views/sesion/create.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Pantalla de inicio de sesión con correo institucional.
 *
 * @requisitos   RNF-001, RN-001
 *
 * @see          docs/requisitos/BBB_CU005_IniciarSesion_v1.0.md; MP Figura 2
 * ============================================================================
--}}
@extends('layouts.publico')

@section('titulo', 'Iniciar sesión')

@section('contenido')
    <div class="tarjeta">
        <div class="publico__encabezado">
            <h1 class="titulo-pantalla">Iniciar sesión</h1>
            <p class="texto-secundario">Use su correo institucional.</p>
        </div>

        <form method="POST" action="{{ route('sesion.store') }}" novalidate>
            @csrf
            @include('partials.campo', [
                'nombre' => 'correo_institucional',
                'etiqueta' => 'Correo institucional',
                'tipo' => 'email',
                'atributos' => 'autocomplete="username" maxlength="150" autofocus',
            ])
            @include('partials.campo', [
                'nombre' => 'contrasena',
                'etiqueta' => 'Contraseña',
                'tipo' => 'password',
                'atributos' => 'autocomplete="current-password" maxlength="255"',
            ])
            <button type="submit" class="boton boton--bloque">
                <span class="icono icono--boton" aria-hidden="true">login</span>Iniciar sesión
            </button>
        </form>

        <p class="texto-secundario" style="text-align: center; margin-top: 16px;">
            ¿Su institución aún no usa el SPEI? <a href="{{ route('contratacion.create') }}">Contratar servicio</a>
        </p>
    </div>
@endsection
