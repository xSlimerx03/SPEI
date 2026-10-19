{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         show.blade.php
 * @path         resources/views/suscripcion/show.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Suscripción de la institución y cambio de plan por el
 *               administrador institucional.
 *
 * @requisitos   RF-003, RF-004
 *
 * @see          docs/requisitos/BBB_CU002_AdministrarSuscripcion_v1.0.md
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Suscripción')

@section('contenido')
    @include('suscripcion.resumen', ['institucion' => $institucion, 'uso' => $uso])
    @include('suscripcion.cambiar-plan', ['accion' => route('suscripcion.plan'), 'planes' => $planes])
@endsection
