{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         app.blade.php
 * @path         resources/views/layouts/app.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Layout autenticado del prototipo hi-fi: sidebar de 240/64 px
 *               con tarjeta del tenant y menú por rol, topbar de 64 px con
 *               ruta, título, buscador, notificaciones y perfil, banner de solo
 *               lectura (RF-006) y toasts. Secciones: titulo, ruta, contenido.
 *
 * @requisitos   RF-006, RF-046, RNF-008, RS-006
 *
 * @see          Manual de Programación v1.2, Tablas 61–65; DT-028
 * ============================================================================
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo') · SPEI</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo-bigbitbyte.png') }}">
    <link rel="preload" href="{{ asset('fonts/material-symbols-outlined.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/spei.css') }}?v={{ filemtime(public_path('css/spei.css')) }}">
    <script>
        // Aplica el sidebar contraído antes de pintar para evitar parpadeo.
        try {
            if (window.localStorage.getItem('spei.lateralContraido') === '1') {
                document.documentElement.classList.add('lateral-contraido');
            }
        } catch (error) {}
    </script>
    <script src="{{ asset('js/spei.js') }}?v={{ filemtime(public_path('js/spei.js')) }}" defer></script>
</head>
<body>
    <a href="#contenido" class="salto-contenido">Saltar al contenido</a>
    <div class="app">
        <aside class="lateral" aria-label="Menú principal">
            <a href="{{ route('panel') }}" class="lateral__marca">
                <img src="{{ asset('img/logo-bigbitbyte.png') }}" alt="" class="lateral__logo" width="32" height="32">
                <span class="lateral__textos">
                    <span class="lateral__producto">SPEI</span>
                    <span class="lateral__por">por Bigbitbyte</span>
                </span>
            </a>

            @if ($institucionActual)
                <div class="lateral__tenant">
                    <span class="icono" aria-hidden="true">account_balance</span>
                    <span class="lateral__tenant-textos">
                        <span class="lateral__tenant-nombre">{{ $institucionActual->nombre_institucion }}</span>
                        <span class="lateral__tenant-detalle">
                            Plan {{ $institucionActual->plan_suscripcion->obtenerEtiqueta() }} · {{ $institucionActual->dominio_correo }}
                        </span>
                    </span>
                </div>
            @endif

            <nav class="lateral__nav" aria-label="Secciones">
                @foreach ($menu as $seccion)
                    <div class="lateral__seccion">{{ $seccion['titulo'] }}</div>
                    @foreach ($seccion['enlaces'] as $enlace)
                        @php($activo = request()->routeIs(...$enlace['patron']))
                        <a href="{{ route($enlace['ruta']) }}" title="{{ $enlace['texto'] }}"
                           class="lateral__enlace {{ $activo ? 'lateral__enlace--activo' : '' }}"
                           @if ($activo) aria-current="page" @endif>
                            <span class="icono" aria-hidden="true">{{ $enlace['icono'] }}</span>
                            <span class="lateral__texto">{{ $enlace['texto'] }}</span>
                            @if ($enlace['contador'])
                                <span class="lateral__contador" aria-label="{{ $enlace['contador'] }} pendientes">{{ $enlace['contador'] }}</span>
                            @endif
                        </a>
                    @endforeach
                @endforeach
            </nav>

            <div class="lateral__pie">
                <button type="button" class="lateral__contraer" data-contraer-lateral aria-expanded="true">
                    <span class="icono icono--contraer" aria-hidden="true">left_panel_close</span>
                    <span class="icono icono--expandir" aria-hidden="true">left_panel_open</span>
                    <span class="lateral__contraer-texto">Contraer menú</span>
                </button>
            </div>
        </aside>

        <div class="columna-principal">
            <header class="topbar">
                <div class="topbar__titulos">
                    <span class="topbar__ruta">
                        {{ $institucionActual?->nombre_institucion ?? 'BigBitByte' }} /
                        @hasSection('ruta') @yield('ruta') @else @yield('titulo') @endif
                    </span>
                    <h1 class="topbar__titulo">@yield('titulo')</h1>
                </div>

                <form method="GET" action="{{ route($buscador['ruta']) }}" class="topbar__buscador" role="search">
                    <span class="icono" aria-hidden="true">search</span>
                    <label for="buscador-global" class="visualmente-oculto">{{ $buscador['marcador'] }}</label>
                    <input type="search" id="buscador-global" name="texto" maxlength="100"
                           placeholder="{{ $buscador['marcador'] }}" value="{{ request('texto') }}">
                </form>

                @unless ($usuarioActual->esSuperadmin())
                    <div class="desplegable" data-desplegable>
                        <button type="button" class="boton-campana" data-desplegable-boton aria-expanded="false"
                                aria-controls="panel-notificaciones"
                                aria-label="Notificaciones, {{ $notificacionesSinLeer }} sin leer">
                            <span class="icono" aria-hidden="true">notifications</span>
                            @if ($notificacionesSinLeer > 0)
                                <span class="boton-campana__contador">{{ $notificacionesSinLeer > 99 ? '99+' : $notificacionesSinLeer }}</span>
                            @endif
                        </button>
                        <div id="panel-notificaciones" class="desplegable__panel desplegable__panel--notificaciones"
                             data-desplegable-panel hidden>
                            <div class="desplegable__cabecera">
                                <span class="desplegable__titulo">Notificaciones</span>
                                @if ($notificacionesSinLeer > 0)
                                    <form method="POST" action="{{ route('notificaciones.leidas') }}" class="formulario-en-linea">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="boton boton--enlace boton--pequeno">Marcar leídas</button>
                                    </form>
                                @endif
                            </div>
                            @forelse ($notificacionesRecientes as $notificacion)
                                @include('partials.notificacion-item', ['notificacion' => $notificacion])
                            @empty
                                <div class="vacio vacio--compacto">
                                    <span class="icono" aria-hidden="true">notifications_off</span>
                                    <span class="vacio__texto">No tienes notificaciones.</span>
                                </div>
                            @endforelse
                            <div class="desplegable__pie">
                                <a href="{{ route('notificaciones.index') }}" class="texto-13 negrita">Ver todas</a>
                            </div>
                        </div>
                    </div>
                @endunless

                <div class="topbar__divisor" aria-hidden="true"></div>

                <div class="desplegable" data-desplegable>
                    <button type="button" class="boton-perfil" data-desplegable-boton aria-expanded="false"
                            aria-controls="panel-perfil">
                        <span class="avatar avatar--azul" aria-hidden="true">{{ \App\Support\Iniciales::de($usuarioActual->nombre_completo) }}</span>
                        <span class="boton-perfil__textos">
                            <span class="boton-perfil__nombre">{{ $usuarioActual->nombre_completo }}</span>
                            <span class="boton-perfil__rol">{{ $usuarioActual->rol->obtenerEtiqueta() }}</span>
                        </span>
                        <span class="icono icono--20 icono--gris" aria-hidden="true">expand_more</span>
                    </button>
                    <div id="panel-perfil" class="desplegable__panel desplegable__panel--perfil" data-desplegable-panel hidden>
                        <div class="desplegable__seccion">{{ $usuarioActual->correo_institucional }}</div>
                        @unless ($usuarioActual->esSuperadmin())
                            <a href="{{ route('notificaciones.index') }}" class="desplegable__opcion">
                                <span class="icono" aria-hidden="true">notifications</span>Notificaciones
                            </a>
                        @endunless
                        <div class="desplegable__divisor"></div>
                        <form method="POST" action="{{ route('sesion.destroy') }}">
                            @csrf
                            <button type="submit" class="desplegable__opcion desplegable__opcion--peligro">
                                <span class="icono" aria-hidden="true">logout</span>Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            @if ($soloLectura)
                <div class="aviso-solo-lectura" role="status">
                    <span class="icono" aria-hidden="true">lock_clock</span>
                    <span class="crece">
                        <strong>Suscripción vencida · modo solo lectura.</strong>
                        Puedes consultar la información, pero no crear ni modificar registros hasta renovar el plan.
                    </span>
                    @if ($usuarioActual->rol === \App\Enums\Rol::AdminInstitucional)
                        <a href="{{ route('suscripcion.show') }}" class="boton boton--pequeno boton--ambar">Ver suscripción</a>
                    @endif
                </div>
            @endif

            <main class="contenido" id="contenido" tabindex="-1">
                @include('partials.errores')
                @yield('contenido')
            </main>
        </div>
    </div>

    @include('partials.toasts')
    @stack('modales')
</body>
</html>
