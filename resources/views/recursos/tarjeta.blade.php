{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         tarjeta.blade.php
 * @path         resources/views/recursos/tarjeta.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Tarjeta de recurso: imagen 4:3 con badges de estado y tipo,
 *               nombre, área e inventario, y pie de disponibilidad con
 *               "Solicitar" para el usuario final. Variables: recurso,
 *               disponibilidad (texto), enlace, solicitable.
 *
 * @requisitos   RF-013, RF-015
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Recursos); DT-028
 * ============================================================================
--}}
@php
    $esEspacio = $recurso->tipo_recurso === \App\Enums\TipoRecurso::Espacio;
@endphp
<article class="recurso">
    <div class="imagen-pendiente" aria-hidden="true">
        <span class="icono">{{ $esEspacio ? 'meeting_room' : 'devices' }}</span>
        <span class="insignia {{ $recurso->estado->obtenerClaseInsignia() }} recurso__estado">{{ $recurso->estado->obtenerEtiqueta() }}</span>
        <span class="insignia insignia--blanca recurso__tipo">{{ $recurso->tipo_recurso->obtenerEtiqueta() }}</span>
    </div>
    <div class="recurso__cuerpo">
        <a href="{{ $enlace }}" class="recurso__nombre">{{ $recurso->nombre_recurso }}</a>
        <span class="recurso__meta">{{ $recurso->area->nombre_area }} · <span class="mono">{{ $recurso->numero_inventario }}</span></span>
        <span class="visualmente-oculto">Estado: {{ $recurso->estado->obtenerEtiqueta() }}</span>
    </div>
    <div class="recurso__pie">
        <span class="icono texto-estado--{{ $recurso->estado->value }}" aria-hidden="true">schedule</span>
        <span class="recurso__pie-texto">{{ $disponibilidad }}</span>
        @if ($solicitable)
            <a href="{{ route('prestamos.create', ['recurso' => $recurso->id_recurso]) }}"
               class="boton boton--primario boton--pequeno recurso__accion">Solicitar</a>
        @endif
    </div>
</article>
