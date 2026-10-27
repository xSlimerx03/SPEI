{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         publico.blade.php
 * @path         resources/views/layouts/publico.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Layout público del prototipo: fondo con retícula de 32 px y
 *               columna centrada (máximo 450 px; ancha para la contratación).
 *               Secciones: titulo, columna_ancha, contenido.
 *
 * @requisitos   RNF-008
 *
 * @see          Manual de Programación v1.2, sección 2.4.5 (layout de login); DT-028
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
    <link rel="preload" href="{{ asset('fonts/material-symbols-outlined.woff2') }}" as="font" type="font/woff2"
          crossorigin>
    <link rel="stylesheet" href="{{ asset('css/spei.css') }}?v={{ filemtime(public_path('css/spei.css')) }}">
    <script src="{{ asset('js/spei.js') }}?v={{ filemtime(public_path('js/spei.js')) }}" defer></script>
</head>
<body>
    <main class="acceso" id="contenido">
        <div class="acceso__columna @hasSection('columna_ancha') acceso__columna--ancha @endif">
            @yield('contenido')
            <p class="acceso__legal">© {{ now()->year }} Bigbitbyte · SPEI v1.0</p>
        </div>
    </main>
    @include('partials.toasts')
</body>
</html>
