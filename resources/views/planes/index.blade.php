{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         index.blade.php
 * @path         resources/views/planes/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Listado de planes con precio, límites e instituciones.
 *
 * @requisitos   RF-007
 *
 * @see          docs/requisitos/BBB_CU003_ModificarPlanes_v1.0.md
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Planes')

@section('contenido')
    <div class="tarjeta">
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th scope="col">Plan</th>
                        <th scope="col">Monto mensual</th>
                        <th scope="col">Áreas</th>
                        <th scope="col">Recursos</th>
                        <th scope="col">Días</th>
                        <th scope="col">Instituciones</th>
                        <th scope="col">Estado</th>
                        <th scope="col"><span class="visualmente-oculto">Acciones</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($planes as $plan)
                        <tr>
                            <td>{{ $plan->nombre }}</td>
                            <td>${{ number_format((float) $plan->monto_mensual, 2) }}</td>
                            <td>{{ $plan->limite_areas }}</td>
                            <td>{{ number_format($plan->limite_recursos) }}</td>
                            <td>{{ $plan->duracion_dias }}</td>
                            <td>{{ $plan->instituciones_count }}</td>
                            <td>{{ $plan->activo ? 'Activo' : 'Inactivo' }}</td>
                            <td>
                                <a href="{{ route('planes.edit', $plan) }}" class="boton boton--pequeno">
                                    <span class="icono icono--boton" aria-hidden="true">edit</span>Editar
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
