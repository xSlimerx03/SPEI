{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         lista.blade.php
 * @path         resources/views/recursos/lista.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Vista de lista de recursos (alternativa a la cuadrícula).
 *               Variables: recursos, disponibilidad, rutaDetalle.
 *
 * @requisitos   RF-013, RF-015
 *
 * @see          DT-028
 * ============================================================================
--}}
<div class="tarjeta tarjeta--recortada">
    <div class="tabla-contenedor">
        <table class="tabla tabla--clicable tabla--820">
            <thead>
                <tr>
                    <th scope="col">Recurso</th>
                    <th scope="col">Área</th>
                    <th scope="col">Inventario</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Disponibilidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recursos as $recurso)
                    @php($enlace = route($rutaDetalle, $recurso->id_recurso))
                    <tr data-href="{{ $enlace }}">
                        <td>
                            <div class="celda-persona">
                                <span class="imagen-pendiente imagen-pendiente--miniatura" aria-hidden="true">
                                    <span class="icono">{{ $recurso->tipo_recurso === \App\Enums\TipoRecurso::Espacio ? 'meeting_room' : 'devices' }}</span>
                                </span>
                                <div class="celda-doble">
                                    <a href="{{ $enlace }}" class="enlace-fila truncar">{{ $recurso->nombre_recurso }}</a>
                                    <span class="celda-doble__secundaria">{{ $recurso->tipo_recurso->obtenerEtiqueta() }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $recurso->area->nombre_area }}</td>
                        <td class="mono">{{ $recurso->numero_inventario }}</td>
                        <td><span class="insignia {{ $recurso->estado->obtenerClaseInsignia() }}">{{ $recurso->estado->obtenerEtiqueta() }}</span></td>
                        <td class="texto-13 texto-apoyo">{{ $disponibilidad[$recurso->id_recurso] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
