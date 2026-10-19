{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         index.blade.php
 * @path         resources/views/administradores/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Administradores del sistema de la institución con su estado
 *               de registro.
 *
 * @requisitos   RF-008
 *
 * @see          docs/requisitos/BBB_CU004_InvitarAdministrador_v1.0.md
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Administradores del sistema')

@section('contenido')
    <div class="acciones" style="margin-bottom: 24px;">
        <a href="{{ route('administradores.create') }}" class="boton">
            <span class="icono icono--boton" aria-hidden="true">add</span>Invitar administrador
        </a>
    </div>
    <div class="tarjeta">
        @if ($administradores->isEmpty())
            <div class="estado-vacio">
                <span class="icono icono--vacio" aria-hidden="true">inbox</span>
                <p>Aún no hay administradores del sistema.</p>
            </div>
        @else
            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Correo</th>
                            <th scope="col">Área a cargo</th>
                            <th scope="col">Registro</th>
                            <th scope="col"><span class="visualmente-oculto">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($administradores as $administrador)
                            <tr>
                                <td>{{ $administrador->nombre_completo }}</td>
                                <td>{{ $administrador->correo_institucional }}</td>
                                <td>{{ $administrador->areaAsignada?->nombre_area ?? 'Todas' }}</td>
                                <td>
                                    <span class="etiqueta {{ $administrador->activo ? 'etiqueta--exito' : 'etiqueta--advertencia' }}">
                                        {{ $administrador->activo ? 'Completo' : 'Pendiente' }}
                                    </span>
                                </td>
                                <td>
                                    @unless ($administrador->activo)
                                        <form method="POST" class="formulario-en-linea"
                                              action="{{ route('administradores.reenviar', $administrador) }}">
                                            @csrf
                                            <button type="submit" class="boton boton--pequeno">
                                                <span class="icono icono--boton" aria-hidden="true">send</span>Reenviar
                                            </button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $administradores->links('partials.paginacion') }}
        @endif
    </div>
@endsection
