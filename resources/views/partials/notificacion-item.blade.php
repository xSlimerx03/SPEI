{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.7: Notificaciones y Recordatorios
 * @file         notificacion-item.blade.php
 * @path         resources/views/partials/notificacion-item.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Elemento del desplegable de notificaciones. Variable:
 *               notificacion.
 *
 * @requisitos   RF-005, RF-046
 *
 * @see          DT-022, DT-028
 * ============================================================================
--}}
@php
    $tituloMinusculas = mb_strtolower($notificacion->titulo);
    [$icono, $tono] = match (true) {
        str_contains($tituloMinusculas, 'venc') => ['event_upcoming', 'icono-tile--ambar'],
        str_contains($tituloMinusculas, 'pendiente') => ['pending_actions', 'icono-tile--ambar'],
        default => ['assignment', 'icono-tile--azul'],
    };
@endphp
<a href="{{ route('notificaciones.index') }}"
   class="notificacion-item {{ $notificacion->leida ? 'notificacion-item--leida' : '' }}">
    <span class="icono-tile {{ $tono }}" aria-hidden="true"><span class="icono">{{ $icono }}</span></span>
    <span class="pila--2 titulos crece">
        <span class="negrita">{{ $notificacion->titulo }}</span>
        <span class="texto-13 texto-apoyo">{{ $notificacion->mensaje }}</span>
        <span class="texto-12 texto-sec">{{ $notificacion->fecha_creacion?->diffForHumans() }}</span>
    </span>
</a>
