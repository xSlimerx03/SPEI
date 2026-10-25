{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.5: Entrega y Devolución
 * @file         agenda.blade.php
 * @path         resources/views/mostrador/agenda.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Filas de la agenda de entregas y devoluciones del día.
 *               Variables: agenda (TableroService::obtenerAgendaHoy), conIcono.
 *
 * @requisitos   RF-034, RF-036, RF-049
 *
 * @see          app/Services/TableroService.php; DT-028
 * ============================================================================
--}}
@forelse ($agenda as $movimiento)
    @php
        $detalles = $movimiento['prestamo']->detalles;
        $que = $detalles->first()?->recurso?->nombre_recurso . ($detalles->count() > 1 ? ' + ' . ($detalles->count() - 1) : '');
    @endphp
    <div class="fila-lista fila-lista--12 {{ $conIcono ? '' : 'fila-lista--arriba' }}">
        <span class="hora">
            {{ $movimiento['hora']->format('H:i') }}
            @unless ($movimiento['hora']->isToday())
                <span class="texto-11 texto-sec">{{ \App\Support\FormatoFecha::dia($movimiento['hora']) }}</span>
            @endunless
        </span>
        @if ($conIcono)
            <span class="icono-tile icono-tile--32" aria-hidden="true"><span class="icono">{{ $movimiento['icono'] }}</span></span>
        @endif
        <div class="pila--2 titulos crece">
            <span class="negrita">{{ $movimiento['prestamo']->usuario->nombre_completo }}</span>
            <span class="texto-13 texto-apoyo en-linea en-linea--8 sin-envolver">
                @unless ($conIcono)
                    <span class="icono icono--16 icono--gris" aria-hidden="true">{{ $movimiento['icono'] }}</span>
                @endunless
                <span class="truncar">{{ $movimiento['tipo'] }} · {{ $que }}</span>
            </span>
        </div>
        <span class="insignia {{ $movimiento['clase'] }}">{{ $movimiento['estado'] }}</span>
    </div>
@empty
    <div class="vacio vacio--compacto">
        <span class="icono" aria-hidden="true">event_available</span>
        <span class="vacio__texto">No hay entregas ni devoluciones programadas para hoy.</span>
    </div>
@endforelse
