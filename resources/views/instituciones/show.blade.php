{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         show.blade.php
 * @path         resources/views/instituciones/show.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Detalle de una institución para el superadmin: resumen,
 *               cambio de plan, renovación y cambio de estado.
 *
 * @requisitos   RF-003, RF-004, RF-006
 *
 * @see          docs/requisitos/BBB_CU002_AdministrarSuscripcion_v1.0.md; MBD Tabla 28
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', $institucion->nombre_institucion)

@section('contenido')
    @include('suscripcion.resumen', ['institucion' => $institucion, 'uso' => $uso])

    <div class="columnas" style="margin-top: 24px;">
        <section class="tarjeta" aria-labelledby="titulo-renovar">
            <h2 id="titulo-renovar" class="tarjeta__titulo">Renovar periodo</h2>
            <p class="texto-secundario">
                Extiende la fecha de corte por la duración del plan y reactiva una suscripción en solo lectura.
            </p>
            <form method="POST" action="{{ route('instituciones.renovar', $institucion) }}">
                @csrf
                <button type="submit" class="boton">
                    <span class="icono icono--boton" aria-hidden="true">check</span>Renovar
                </button>
            </form>
        </section>

        <section class="tarjeta" aria-labelledby="titulo-estado">
            <h2 id="titulo-estado" class="tarjeta__titulo">Cambiar estado</h2>
            @if (count($estados) === 0)
                <p class="texto-secundario">La suscripción cancelada no admite cambios.</p>
            @else
                <form method="POST" action="{{ route('instituciones.estado', $institucion) }}">
                    @csrf
                    @method('PUT')
                    <div class="campo">
                        <label for="estado_suscripcion" class="campo__etiqueta">Nuevo estado</label>
                        <select id="estado_suscripcion" name="estado_suscripcion" class="control">
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->value }}">{{ $estado->obtenerEtiqueta() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="boton boton--secundario">Confirmar</button>
                </form>
            @endif
        </section>
    </div>

    @include('suscripcion.cambiar-plan', [
        'accion' => route('instituciones.plan', $institucion),
        'planes' => $planes,
        'institucion' => $institucion,
    ])
@endsection
