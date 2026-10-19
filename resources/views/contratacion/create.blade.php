{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         create.blade.php
 * @path         resources/views/contratacion/create.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Landing de contratación: planes activos con precio y límites
 *               y formulario de alta de la institución y su administrador.
 *
 * @requisitos   RF-001, RF-002
 *
 * @see          docs/requisitos/BBB_CU001_ContratarSuscripcion_v1.0.md
 * ============================================================================
--}}
@extends('layouts.publico')

@section('titulo', 'Contratar servicio')
@section('clase_tarjeta', 'publico__tarjeta--ancha')

@section('contenido')
    <div class="publico__encabezado">
        <h1 class="titulo-pantalla">Contratar el SPEI</h1>
        <p class="texto-secundario">Préstamo de equipo institucional para su escuela, en minutos.</p>
    </div>

    <form method="POST" action="{{ route('contratacion.store') }}" novalidate>
        @csrf
        <fieldset class="tarjeta" style="border: 0;">
            <legend class="subtitulo">1. Elija un plan</legend>
            @if ($planes->isEmpty())
                <div class="estado-vacio">
                    <span class="icono icono--vacio" aria-hidden="true">inbox</span>
                    <p>No hay planes disponibles por el momento.</p>
                </div>
            @endif
            <div class="cuadricula cuadricula--planes">
                @foreach ($planes as $plan)
                    <label class="tarjeta plan-opcion">
                        <input type="radio" name="plan" value="{{ $plan->clave->value }}" class="visualmente-oculto" required
                               @error('plan') aria-invalid="true" aria-describedby="plan-error" @enderror
                               @checked(old('plan', $planes->first()?->clave->value) === $plan->clave->value)>
                        <span class="tarjeta__titulo">{{ $plan->nombre }}</span>
                        <span class="plan-opcion__precio">${{ number_format((float) $plan->monto_mensual, 2) }}</span>
                        <span class="texto-secundario"> MXN / mes</span>
                        <ul class="texto-secundario">
                            <li>{{ $plan->limite_areas }} áreas</li>
                            <li>{{ number_format($plan->limite_recursos) }} recursos</li>
                            <li>{{ $plan->duracion_dias }} días por periodo</li>
                        </ul>
                    </label>
                @endforeach
            </div>
            @error('plan')
                <p id="plan-error" class="campo__error">{{ $message }}</p>
            @enderror
        </fieldset>

        <fieldset class="tarjeta" style="border: 0;">
            <legend class="subtitulo">2. Datos de la institución</legend>
            <div class="fila-campos">
                @include('partials.campo', ['nombre' => 'nombre_institucion', 'etiqueta' => 'Nombre de la institución'])
                @include('partials.campo', [
                    'nombre' => 'dominio_correo',
                    'etiqueta' => 'Dominio de correo',
                    'ayuda' => 'Sin arroba, por ejemplo universidad.edu.mx',
                ])
            </div>
        </fieldset>

        <fieldset class="tarjeta" style="border: 0;">
            <legend class="subtitulo">3. Administrador institucional</legend>
            <div class="fila-campos">
                @include('partials.campo', ['nombre' => 'nombre_completo', 'etiqueta' => 'Nombre completo'])
                @include('partials.campo', [
                    'nombre' => 'correo_institucional',
                    'etiqueta' => 'Correo institucional',
                    'tipo' => 'email',
                    'atributos' => 'autocomplete="username" maxlength="150"',
                ])
                @include('partials.campo', [
                    'nombre' => 'contrasena',
                    'etiqueta' => 'Contraseña',
                    'tipo' => 'password',
                    'ayuda' => 'Mínimo 10 caracteres con letras y números.',
                    'atributos' => 'autocomplete="new-password" minlength="10"',
                ])
                @include('partials.campo', [
                    'nombre' => 'contrasena_confirmation',
                    'etiqueta' => 'Confirmar contraseña',
                    'tipo' => 'password',
                    'atributos' => 'autocomplete="new-password" minlength="10"',
                ])
            </div>
            <div class="acciones">
                <button type="submit" class="boton" @disabled($planes->isEmpty())>
                    <span class="icono icono--boton" aria-hidden="true">check</span>Contratar
                </button>
                <a href="{{ route('login') }}" class="boton boton--secundario">Iniciar sesión</a>
            </div>
        </fieldset>
    </form>
@endsection
