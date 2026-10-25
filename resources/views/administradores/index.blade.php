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
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Administradores del sistema de la institución con su estado
 *               de registro y reenvío de invitación.
 *
 * @requisitos   RF-008
 *
 * @see          docs/requisitos/BBB_CU004_InvitarAdministrador_v1.0.md; DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Administradores')
@section('ruta', 'Suscripción')

@section('contenido')
    <section class="tarjeta" aria-labelledby="titulo-administradores">
        <div class="tarjeta__cabecera">
            <div class="titulos">
                <h2 id="titulo-administradores" class="tarjeta__titulo">Administradores del sistema</h2>
                <span class="tarjeta__subtitulo">Sin área asignada administran todo el inventario; con área, solo esa área.</span>
            </div>
            <a href="{{ route('administradores.create') }}" class="boton boton--primario">
                <span class="icono" aria-hidden="true">person_add</span>Crear
            </a>
        </div>
        @forelse ($administradores as $administrador)
            @include('administradores.fila', ['administrador' => $administrador])
        @empty
            <div class="vacio">
                <span class="icono" aria-hidden="true">manage_accounts</span>
                <span class="vacio__titulo">Aún no hay administradores del sistema</span>
                <span class="vacio__texto">Invita al primero para que registre áreas y recursos.</span>
            </div>
        @endforelse
        {{ $administradores->links('partials.paginacion') }}
    </section>
@endsection
