{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Panel principal
 * @file         superadmin.blade.php
 * @path         resources/views/panel/superadmin.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Panel del superadmin: instituciones por estado de suscripción.
 *
 * @requisitos   RF-004, RF-006
 *
 * @see          Manual de Programación v1.2, sección 2.4.5 (dashboard admin)
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Panel de la plataforma')

@section('contenido')
    <div class="metricas">
        @foreach ($metricas as $estado => $total)
            <div class="tarjeta metrica">
                <span class="icono icono--tarjeta" aria-hidden="true">domain</span>
                <div>
                    <div class="metrica__valor">{{ $total }}</div>
                    <div class="texto-secundario">{{ $estado }}</div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="acciones">
        <a href="{{ route('instituciones.index') }}" class="boton">
            <span class="icono icono--boton" aria-hidden="true">domain</span>Ver instituciones
        </a>
        <a href="{{ route('planes.index') }}" class="boton boton--secundario">Ver planes</a>
    </div>
@endsection
