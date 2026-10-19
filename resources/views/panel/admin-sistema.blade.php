{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Panel principal
 * @file         admin-sistema.blade.php
 * @path         resources/views/panel/admin-sistema.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Panel del administrador del sistema: recursos por estado y
 *               búsqueda rápida por código de barras.
 *
 * @requisitos   RF-011, RF-013
 *
 * @see          Manual de Programación v1.2, sección 2.4.5 (dashboard admin)
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Panel del inventario')

@section('contenido')
    <div class="metricas">
        @foreach ($metricas as $estado => $total)
            <div class="tarjeta metrica">
                <span class="icono icono--tarjeta" aria-hidden="true">inventory_2</span>
                <div>
                    <div class="metrica__valor">{{ $total }}</div>
                    <div class="texto-secundario">{{ $estado }}</div>
                </div>
            </div>
        @endforeach
    </div>
    @include('recursos.buscar-codigo')
@endsection
