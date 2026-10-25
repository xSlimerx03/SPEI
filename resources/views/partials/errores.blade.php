{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         errores.blade.php
 * @path         resources/views/partials/errores.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Alerta persistente de error (MP Tabla 70): reglas de negocio
 *               rechazadas y errores de validación sin campo visible.
 *
 * @requisitos   RNF-008
 *
 * @see          Manual de Programación v1.2, sección 2.7; DT-028
 * ============================================================================
--}}
@php
    $mensajeError = session('error') ?? ($errorLimite ?? null);
    $erroresSueltos = $errors->any() && ! ($ocultarListaErrores ?? false) ? $errors->all() : [];
@endphp
@if ($mensajeError || $erroresSueltos)
    <div class="alerta alerta--error alerta--grande alerta--separada" role="alert">
        <span class="icono" aria-hidden="true">error</span>
        <div class="crece">
            @if ($mensajeError)
                <p class="alerta__titulo">{{ $mensajeError }}</p>
            @else
                <p class="alerta__titulo">Revisa los datos marcados.</p>
            @endif
            @if ($erroresSueltos)
                <ul class="alerta__lista">
                    @foreach ($erroresSueltos as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif
