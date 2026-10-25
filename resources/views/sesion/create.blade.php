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
 * @lastModified 2026-10-23
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Inicio de sesión del prototipo hi-fi: tarjeta centrada con
 *               correo institucional y contraseña con mostrar/ocultar. Sin SSO,
 *               recuperación ni "mantener sesión" hasta que existan (DT-016,
 *               DT-025, DT-028).
 *
 * @requisitos   RNF-001, RN-001
 *
 * @see          docs/requisitos/BBB_CU005_IniciarSesion_v1.0.md; MP Figura 2
 * ============================================================================
--}}
@extends('layouts.publico')

@section('titulo', 'Iniciar sesión')

@section('contenido')
    <div class="acceso__tarjeta">
        @include('partials.marca-acceso')

        <div class="pila--4 titulos">
            <h1 class="acceso__titulo">Iniciar sesión</h1>
            <p class="texto-sec">Sistema de Préstamo de Equipo Institucional</p>
        </div>

        @php($mensajeError = session('error') ?? ($errorLimite ?? null))
        @if ($mensajeError)
            <div class="alerta alerta--error" role="alert">
                <span class="icono" aria-hidden="true">error</span>
                <span>{{ $mensajeError }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('sesion.store') }}" class="pila--16 titulos" novalidate>
            @csrf
            @include('partials.campo', [
                'nombre' => 'correo_institucional',
                'etiqueta' => 'Correo institucional',
                'tipo' => 'email',
                'icono' => 'mail',
                'marcador' => 'nombre@institucion.edu.mx',
                'atributos' => 'autocomplete="username" maxlength="150" autofocus',
            ])
            @include('partials.campo', [
                'nombre' => 'contrasena',
                'etiqueta' => 'Contraseña',
                'tipo' => 'password',
                'icono' => 'lock',
                'atributos' => 'autocomplete="current-password" maxlength="255"',
            ])
            <button type="submit" class="boton boton--primario boton--grande boton--bloque">
                Iniciar sesión<span class="icono" aria-hidden="true">arrow_forward</span>
            </button>
        </form>

        <div class="acceso__pie">
            <a href="{{ route('contratacion.create') }}" class="negrita">¿Tu institución aún no usa el SPEI?</a>
            <span class="texto-12 texto-sec">Tu rol se asigna automáticamente según tu dominio institucional.</span>
        </div>
    </div>
@endsection
