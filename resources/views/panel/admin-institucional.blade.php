{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Panel principal
 * @file         admin-institucional.blade.php
 * @path         resources/views/panel/admin-institucional.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Panel del administrador institucional: vigencia, uso del plan
 *               y accesos a suscripción y administradores.
 *
 * @requisitos   RF-003, RF-004, RF-008
 *
 * @see          docs/requisitos/BBB_CU002_AdministrarSuscripcion_v1.0.md
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', $institucion->nombre_institucion)

@section('contenido')
    @include('suscripcion.resumen', ['institucion' => $institucion, 'uso' => $uso])
    <div class="acciones">
        <a href="{{ route('suscripcion.show') }}" class="boton">
            <span class="icono icono--boton" aria-hidden="true">card_membership</span>Ver suscripción
        </a>
        <a href="{{ route('administradores.create') }}" class="boton boton--secundario">Invitar administrador</a>
    </div>
@endsection
