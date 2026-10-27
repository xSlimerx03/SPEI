{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         panel.blade.php
 * @path         resources/views/prestamos/panel.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-27
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Panel de detalle (400 px, sticky): folio, estado, solicitante,
 *               datos, recursos y acciones según el estado.
 *               Variables: prestamo, usuario (operador).
 *
 * @requisitos   RF-028, RF-029, RF-031, RF-034, RF-042
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Panel de detalle); DT-028
 * ============================================================================
--}}
@php
    $estado = $prestamo->obtenerEstado();
    $solicitante = $prestamo->usuario;
    $tolerancia = max(15, min(20, (int) config('spei.prestamos.tolerancia_recogida')));
    $puedeCancelar = $estado === \App\Enums\EstadoPrestamo::Pendiente && $usuario->can('cancelar', $prestamo);
    $puedeCerrar = $estado === \App\Enums\EstadoPrestamo::Activo && $prestamo->fecha_hora_fin->isPast()
        && $usuario->can('cerrarNoDevuelto', $prestamo);
    $marcas = (int) $solicitante->marcas_acumuladas;
@endphp
<aside class="tarjeta panel-detalle" aria-labelledby="titulo-detalle">
    <div class="panel-detalle__cabecera">
        <div class="encabezado-pagina encabezado-pagina--centro">
            <a href="{{ route('prestamos.show', $prestamo) }}" id="titulo-detalle"
               class="folio texto-13">{{ $prestamo->obtenerFolio() }}</a>
            <span class="insignia {{ $estado->obtenerClaseInsignia() }}">
                {{ $estado->obtenerEtiqueta() }} · {{ $prestamo->obtenerSubestado() }}
            </span>
        </div>
        <div class="en-linea">
            <span class="avatar avatar--48 avatar--azul"
                  aria-hidden="true">{{ \App\Support\Iniciales::de($solicitante->nombre_completo) }}</span>
            <div class="titulos crece">
                <span class="subtitulo-pagina">{{ $solicitante->nombre_completo }}</span>
                <span class="texto-13 texto-sec">
                    {{ $solicitante->rol->obtenerEtiqueta() }} ·
                    {{ $marcas }} {{ $marcas === 1 ? 'marca' : 'marcas' }}@if ($solicitante->bloqueado) · bloqueado
                    hasta {{ $solicitante->fecha_fin_bloqueo?->format('d/m H:i') }}@endif
                </span>
            </div>
        </div>
    </div>

    @if ($estado === \App\Enums\EstadoPrestamo::Cancelado || $estado === \App\Enums\EstadoPrestamo::Vencido)
        <div class="alerta alerta--neutra panel-detalle__alerta">
            <span class="icono" aria-hidden="true">info</span>
            <span>{{ $prestamo->motivo_cancelacion ?: 'Sin justificación registrada.' }}</span>
        </div>
    @endif
    @if ($estado === \App\Enums\EstadoPrestamo::Activo && $prestamo->fecha_hora_fin->isPast())
        <div class="alerta alerta--advertencia panel-detalle__alerta">
            <span class="icono" aria-hidden="true">assignment_late</span>
            <div class="titulos">
                <span class="alerta__titulo">Devolución atrasada</span>
                <span>Debía devolverse {{ $prestamo->fecha_hora_fin->diffForHumans() }}.</span>
            </div>
        </div>
    @endif

    <div class="panel-detalle__bloque pila--16 titulos">
        <div class="dos-columnas">
            <div class="dato">
                <span class="dato__etiqueta">Fecha</span>
                <span class="dato__valor">{{ \App\Support\FormatoFecha::corta($prestamo->fecha_hora_inicio) }}</span>
            </div>
            <div class="dato">
                <span class="dato__etiqueta">Horario</span>
                <span class="dato__valor">
                    {{ $prestamo->fecha_hora_inicio->format('H:i') }} – {{ $prestamo->fecha_hora_fin->format('H:i') }}
                </span>
            </div>
            <div class="dato ocupa-todo">
                <span class="dato__etiqueta">Motivo</span>
                <span class="dato__valor">{{ $prestamo->motivo }}</span>
            </div>
        </div>
        <div class="pila--8 titulos">
            <span class="dato__etiqueta">Recursos solicitados</span>
            @foreach ($prestamo->detalles as $detalle)
                <div class="recurso-solicitado">
                    <span class="icono icono--azul"
                          aria-hidden="true">{{ $detalle->recurso->tipo_recurso === \App\Enums\TipoRecurso::Espacio
                              ? 'meeting_room' : 'devices' }}</span>
                    <span class="crece">{{ $detalle->recurso->nombre_recurso }}</span>
                    @if ($detalle->condicion_devolucion
                        && in_array($detalle->condicion_devolucion, ['malo', 'danado'], true))
                        <span class="icono icono--18 icono--rojo" aria-label="Devuelto con daño">report</span>
                    @else
                        <span class="icono icono--18 icono--verde" aria-hidden="true">check_circle</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="panel-detalle__bloque pila--12 titulos">
        @switch($estado)
            @case(\App\Enums\EstadoPrestamo::Pendiente)
                <p class="texto-13 texto-apoyo en-linea en-linea--8 sin-envolver">
                    <span class="icono icono--18 icono--verde" aria-hidden="true">verified</span>
                    Recursos bloqueados para este horario. Tolerancia de recogida: {{ $tolerancia }} min.
                </p>
                <a href="{{ route('mostrador.index') }}" class="boton boton--primario boton--grande">
                    <span class="icono" aria-hidden="true">qr_code_scanner</span>Abrir mostrador
                </a>
                @if ($puedeCancelar)
                    <button type="button" class="boton boton--peligro"
                            data-abrir-modal="modal-cancelar-{{ $prestamo->id_prestamo }}">
                        <span class="icono" aria-hidden="true">priority_high</span>Cancelar reserva
                    </button>
                @endif
                @break
            @case(\App\Enums\EstadoPrestamo::Activo)
                <p class="texto-13 texto-apoyo en-linea en-linea--8 sin-envolver">
                    <span class="icono icono--18 icono--gris" aria-hidden="true">block</span>
                    No se permiten prórrogas. Si necesita más tiempo, el usuario debe crear una nueva solicitud.
                </p>
                <a href="{{ route('prestamos.show', $prestamo) }}" class="boton boton--secundario">
                    <span class="icono" aria-hidden="true">receipt_long</span>Ver comprobante
                </a>
                @if ($puedeCerrar)
                    <button type="button" class="boton boton--peligro"
                            data-abrir-modal="modal-no-devuelto-{{ $prestamo->id_prestamo }}">
                        <span class="icono" aria-hidden="true">report</span>Reportar pérdida
                    </button>
                @endif
                @break
            @default
                <a href="{{ route('prestamos.show', $prestamo) }}" class="boton boton--secundario">
                    <span class="icono" aria-hidden="true">receipt_long</span>Ver comprobante
                </a>
        @endswitch
    </div>
</aside>

@if ($puedeCancelar)
    @push('modales')
        @include('prestamos.modal-cancelar', ['prestamo' => $prestamo, 'minimo' => 20, 'operador' => true])
    @endpush
@endif
@if ($puedeCerrar)
    @push('modales')
        @include('prestamos.modal-no-devuelto', ['prestamo' => $prestamo])
    @endpush
@endif
