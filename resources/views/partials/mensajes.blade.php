{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         mensajes.blade.php
 * @path         resources/views/partials/mensajes.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Mensajes del sistema: toast de éxito (3 s) y alertas
 *               persistentes de error, advertencia e información con icono,
 *               título, mensaje y cierre (Tablas 70 y 71).
 *
 * @requisitos   RNF-008
 *
 * @see          Manual de Programación v1.2, sección 2.7
 * ============================================================================
--}}
@if (session('success'))
    <div class="alerta alerta--exito alerta--toast" role="status">
        <span class="icono alerta__icono" aria-hidden="true">check_circle</span>
        <div>
            <p class="alerta__titulo">Éxito</p>
            <p class="alerta__mensaje">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if (session('info'))
    <div class="alerta alerta--informacion" role="status">
        <span class="icono alerta__icono" aria-hidden="true">info</span>
        <div>
            <p class="alerta__titulo">Información</p>
            <p class="alerta__mensaje">{{ session('info') }}</p>
        </div>
    </div>
@endif

@php($mensajeError = session('error') ?? ($errorLimite ?? null))
@if ($mensajeError || $errors->any())
    <div class="alerta alerta--error" role="alert">
        <span class="icono alerta__icono" aria-hidden="true">error</span>
        <div>
            <p class="alerta__titulo">Error</p>
            @if ($mensajeError)
                <p class="alerta__mensaje">{{ $mensajeError }}</p>
            @endif
            @if ($errors->any())
                <ul class="alerta__lista">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif
