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
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Layout autenticado: sidebar de 240 px (fuera de lienzo en
 *               tablet y móvil), topbar de 64 px y contenido de máximo 1280 px.
 *
 * @requisitos   RNF-008
 *
 * @see          Manual de Programación v1.2, Tablas 59–65
 * ============================================================================
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo') · SPEI</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/logo-bigbitbyte.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/spei.css') }}">
</head>
<body>
    <div class="aplicacion">
        <input type="checkbox" id="menu-interruptor" class="menu-interruptor" aria-hidden="true">
        <nav class="sidebar" aria-label="Menú principal">
            <a href="{{ route('panel') }}" class="sidebar__marca">
                <img src="{{ asset('img/logo-bigbitbyte.svg') }}" alt="" width="48" height="48">
                <span>SPEI</span>
            </a>
            @include('partials.menu')
        </nav>
        <label for="menu-interruptor" class="capa-menu" aria-hidden="true"></label>

        <div class="contenido">
            <header class="topbar">
                <label for="menu-interruptor" class="menu-boton" tabindex="0">
                    <span class="icono" aria-hidden="true">menu</span>
                    <span class="visualmente-oculto">Abrir menú</span>
                </label>
                <h1 class="topbar__titulo">@yield('titulo')</h1>
                <div class="topbar__acciones">
                    @if (! auth()->user()->esSuperadmin())
                        <a href="{{ route('notificaciones.index') }}" class="notificaciones-enlace"
                           aria-label="Notificaciones ({{ $notificacionesSinLeer }} sin leer)">
                            <span class="icono" aria-hidden="true">notifications</span>
                            @if ($notificacionesSinLeer > 0)
                                <span class="notificaciones-enlace__contador">{{ $notificacionesSinLeer }}</span>
                            @endif
                        </a>
                    @endif
                    <div class="topbar__perfil">
                        <strong>{{ auth()->user()->nombre_completo }}</strong>
                        <span class="texto-secundario">{{ auth()->user()->rol->obtenerEtiqueta() }}</span>
                    </div>
                </div>
            </header>

            <main class="principal" id="contenido">
                @include('partials.mensajes')
                @yield('contenido')
            </main>

            <footer class="pie">SPEI v1.0.0 · BigBitByte</footer>
        </div>
    </div>
</body>
</html>
