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
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Landing de contratación con el lenguaje visual del prototipo:
 *               planes activos seleccionables, datos de la institución y del
 *               administrador institucional.
 *
 * @requisitos   RF-001, RF-002
 *
 * @see          docs/requisitos/BBB_CU001_ContratarSuscripcion_v1.0.md; DT-028
 * ============================================================================
--}}
@extends('layouts.publico')

@section('titulo', 'Contratar servicio')
@section('columna_ancha', '1')

@section('contenido')
    <div class="acceso__tarjeta">
        <div class="encabezado-pagina encabezado-pagina--centro">
            @include('partials.marca-acceso')
            <a href="{{ route('login') }}" class="boton boton--secundario">
                <span class="icono" aria-hidden="true">login</span>Iniciar sesión
            </a>
        </div>

        <div class="pila--4 titulos">
            <h1 class="acceso__titulo">Contratar el SPEI</h1>
            <p class="texto-sec">
                Préstamo de equipo institucional para tu escuela: elige un plan y crea tu cuenta de administración.
            </p>
        </div>

        @if (session('error'))
            <div class="alerta alerta--error" role="alert">
                <span class="icono" aria-hidden="true">error</span><span>{{ session('error') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('contratacion.store') }}" class="pila" novalidate>
            @csrf
            <fieldset class="grupo-campos">
                <legend class="subtitulo-pagina">1. Elige un plan</legend>
                @if ($planes->isEmpty())
                    <div class="vacio vacio--compacto tarjeta">
                        <span class="icono" aria-hidden="true">inbox</span>
                        <span class="vacio__titulo">No hay planes disponibles por el momento.</span>
                    </div>
                @endif
                <div class="cuadricula-220">
                    @foreach ($planes as $plan)
                        <label class="tarjeta plan-opcion plan-elegible">
                            <input type="radio" name="plan" value="{{ $plan->clave->value }}" required
                                   @error('plan') aria-invalid="true" aria-describedby="plan-error" @enderror
                                   @checked(old('plan', $planes->first()?->clave->value) === $plan->clave->value)>
                            <span class="plan-opcion__nombre">{{ $plan->nombre }}</span>
                            <span>
                                <span class="plan-opcion__precio">
                                    ${{ number_format((float) $plan->monto_mensual, 2) }}
                                </span>
                                <span class="texto-13 texto-sec">MXN / mes</span></span>
                            <span class="pila--8 titulos">
                                <span class="plan-opcion__caracteristica">
                                    <span class="icono" aria-hidden="true">check</span>{{ $plan->limite_areas }} áreas
                                </span>
                                <span class="plan-opcion__caracteristica">
                                    <span class="icono"
                                          aria-hidden="true">check</span>{{ number_format($plan->limite_recursos) }}
                                    recursos
                                </span>
                                <span class="plan-opcion__caracteristica">
                                    <span class="icono" aria-hidden="true">check</span>{{ $plan->duracion_dias }} días
                                    por periodo
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('plan')
                    <p id="plan-error"
                       class="campo__error"><span class="icono" aria-hidden="true">error</span>{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="grupo-campos">
                <legend class="subtitulo-pagina">2. Datos de la institución</legend>
                <div class="cuadricula-campos">
                    @include('partials.campo', [
                        'nombre' => 'nombre_institucion',
                        'etiqueta' => 'Nombre de la institución',
                        'icono' => 'account_balance',
                        'atributos' => 'maxlength="150"',
                    ])
                    @include('partials.campo', [
                        'nombre' => 'dominio_correo',
                        'etiqueta' => 'Dominio de correo',
                        'icono' => 'alternate_email',
                        'marcador' => 'universidad.edu.mx',
                        'ayuda' => 'Sin arroba. Define quién puede entrar a tu institución.',
                        'atributos' => 'maxlength="100"',
                    ])
                </div>
            </fieldset>

            <fieldset class="grupo-campos">
                <legend class="subtitulo-pagina">3. Administrador institucional</legend>
                <div class="cuadricula-campos">
                    @include('partials.campo', [
                        'nombre' => 'nombre_completo',
                        'etiqueta' => 'Nombre completo',
                        'icono' => 'person',
                        'atributos' => 'autocomplete="name" maxlength="150"',
                    ])
                    @include('partials.campo', [
                        'nombre' => 'correo_institucional',
                        'etiqueta' => 'Correo institucional',
                        'tipo' => 'email',
                        'icono' => 'mail',
                        'marcador' => 'nombre@universidad.edu.mx',
                        'atributos' => 'autocomplete="username" maxlength="150"',
                    ])
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
                </div>
            </fieldset>

            <div class="pie-pasos">
                <span class="texto-13 texto-sec centrado-vertical">
                    La vigencia inicia hoy y se calcula con la duración del plan.
                </span>
                <button type="submit" class="boton boton--primario boton--grande" @disabled($planes->isEmpty())>
                    <span class="icono" aria-hidden="true">check</span>Contratar
                </button>
            </div>
        </form>
    </div>
@endsection
