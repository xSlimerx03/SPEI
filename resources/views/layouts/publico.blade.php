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
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Layout público centrado de una columna (inicio de sesión,
 *               contratación, registro y páginas de error).
 *
 * @requisitos   RNF-008
 *
 * @see          Manual de Programación v1.2, sección 2.4.5 (layout de login)
 * ============================================================================
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo') · SPEI</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/logo-bigbitbyte.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/spei.css') }}">
</head>
<body>
    <main class="publico">
        <div class="publico__tarjeta @yield('clase_tarjeta')">
            <img src="{{ asset('img/logo-bigbitbyte.svg') }}" alt="BigBitByte" class="publico__logo" width="64" height="64">
            @include('partials.mensajes')
            @yield('contenido')
        </div>
        <footer class="pie">SPEI v1.0.0 · BigBitByte</footer>
    </main>
</body>
</html>
