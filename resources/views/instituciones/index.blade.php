{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         index.blade.php
 * @path         resources/views/instituciones/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Listado de instituciones para el superadmin.
 *
 * @requisitos   RF-004, RF-006
 *
 * @see          docs/requisitos/BBB_CU002_AdministrarSuscripcion_v1.0.md
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Instituciones')

@section('contenido')
    <div class="tarjeta">
        @if ($instituciones->isEmpty())
            <div class="estado-vacio">
                <span class="icono icono--vacio" aria-hidden="true">inbox</span>
                <p>No hay instituciones registradas.</p>
            </div>
        @else
            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th scope="col">Institución</th>
                            <th scope="col">Dominio</th>
                            <th scope="col">Plan</th>
                            <th scope="col">Fecha de corte</th>
                            <th scope="col">Estado</th>
                            <th scope="col"><span class="visualmente-oculto">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($instituciones as $institucion)
                            <tr>
                                <td>{{ $institucion->nombre_institucion }}</td>
                                <td>{{ $institucion->dominio_correo }}</td>
                                <td>{{ ucfirst($institucion->plan_suscripcion->value) }}</td>
                                <td>{{ $institucion->fecha_corte->format('d/m/Y') }}</td>
                                <td>{{ $institucion->estado_suscripcion->obtenerEtiqueta() }}</td>
                                <td>
                                    <a href="{{ route('instituciones.show', $institucion) }}" class="boton boton--pequeno">
                                        <span class="icono icono--boton" aria-hidden="true">open_in_new</span>Abrir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $instituciones->links('partials.paginacion') }}
        @endif
    </div>
@endsection
