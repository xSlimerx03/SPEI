{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.8: Historias, Reportes e Inteligencia Operativa
 * @file         index.blade.php
 * @path         resources/views/auditoria/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Auditoría (prototipo hi-fi): aviso de registro inalterable,
 *               filtros (texto, acción, tabla, fecha) y tabla monoespaciada de
 *               eventos con exportación CSV.
 *
 * @requisitos   RF-052, RF-053, RNF-005
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Auditoría); DT-010, DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Auditoría')

@section('contenido')
    <div class="pila--20 titulos">
        <div class="alerta alerta--blanca alerta--grande">
            <span class="icono" aria-hidden="true">shield_lock</span>
            <span class="crece"><strong>Registro inalterable.</strong> Los eventos no se pueden modificar ni borrar; la base de datos rechaza cualquier cambio.</span>
            <a href="{{ route('auditoria.exportar', array_filter($filtros)) }}" class="boton boton--secundario">
                <span class="icono" aria-hidden="true">download</span>Exportar
            </a>
        </div>

        <section class="tarjeta tarjeta--recortada" aria-label="Eventos de auditoría">
            <form method="GET" action="{{ route('auditoria.index') }}" class="barra-filtros barra-filtros--tabla">
                <div class="control-icono control-icono--con-icono control-icono--compacto barra-filtros__busqueda">
                    <span class="icono" aria-hidden="true">search</span>
                    <label for="filtro-texto" class="visualmente-oculto">Buscar</label>
                    <input type="search" id="filtro-texto" name="texto" class="control control--compacto" maxlength="100"
                           placeholder="Buscar por usuario, acción o registro" value="{{ $filtros['texto'] ?? '' }}">
                </div>
                <div class="selector selector--150">
                    <label for="filtro-accion" class="visualmente-oculto">Acción</label>
                    <select id="filtro-accion" name="accion" class="control control--compacto" data-autoenvio>
                        <option value="">Todas las acciones</option>
                        @foreach ($acciones as $accion)
                            <option value="{{ $accion }}" @selected(($filtros['accion'] ?? '') === $accion)>{{ $accion }}</option>
                        @endforeach
                    </select>
                    <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
                </div>
                <div class="selector selector--150">
                    <label for="filtro-tabla" class="visualmente-oculto">Tabla</label>
                    <select id="filtro-tabla" name="tabla" class="control control--compacto" data-autoenvio>
                        <option value="">Todas las tablas</option>
                        @foreach ($tablas as $tabla)
                            <option value="{{ $tabla }}" @selected(($filtros['tabla'] ?? '') === $tabla)>{{ $tabla }}</option>
                        @endforeach
                    </select>
                    <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
                </div>
                <div class="control-icono control-icono--con-icono control-icono--compacto">
                    <span class="icono" aria-hidden="true">calendar_month</span>
                    <label for="filtro-fecha" class="visualmente-oculto">Fecha</label>
                    <input type="date" id="filtro-fecha" name="fecha" class="control control--compacto"
                           value="{{ $filtros['fecha'] ?? '' }}" data-autoenvio>
                </div>
                <button type="submit" class="boton boton--secundario">
                    <span class="icono" aria-hidden="true">filter_list</span>Filtrar
                </button>
            </form>

            @if ($eventos->isEmpty())
                <div class="vacio">
                    <span class="icono" aria-hidden="true">manage_search</span>
                    <span class="vacio__titulo">Sin eventos</span>
                    <span class="vacio__texto">No hay eventos que coincidan con los filtros.</span>
                </div>
            @else
                <div class="tabla-contenedor">
                    <table class="tabla tabla--compacta tabla--1040">
                        <thead>
                            <tr>
                                <th scope="col">Fecha y hora</th>
                                <th scope="col">Usuario</th>
                                <th scope="col">Acción</th>
                                <th scope="col">Tabla</th>
                                <th scope="col">Registro</th>
                                <th scope="col">Detalle</th>
                                <th scope="col">Resultado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($eventos as $evento)
                                <tr>
                                    <td class="mono texto-apoyo">{{ $evento->fecha_hora?->format('Y-m-d H:i:s') }}</td>
                                    <td class="negrita">{{ $evento->usuario?->nombre_completo ?? 'Sistema · tarea' }}</td>
                                    <td><span class="insignia insignia--accion">{{ $evento->accion }}</span></td>
                                    <td class="mono">{{ $evento->tabla_afectada }}</td>
                                    <td class="folio">{{ \App\Support\ResumenAuditoria::registro($evento) }}</td>
                                    <td class="texto-apoyo-2">{{ \App\Support\ResumenAuditoria::detalle($evento) }}</td>
                                    <td><span class="insignia insignia--exito">Éxito</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $eventos->links('partials.paginacion') }}
            @endif
        </section>
    </div>
@endsection
