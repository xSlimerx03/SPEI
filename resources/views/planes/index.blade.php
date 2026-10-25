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
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Planes de suscripción en tarjetas con precio, límites,
 *               instituciones suscritas y edición (superadmin).
 *
 * @requisitos   RF-007
 *
 * @see          docs/requisitos/BBB_CU003_ModificarPlanes_v1.0.md; DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Planes')
@section('ruta', 'Plataforma')

@section('contenido')
    <div class="pila--20 titulos">
        <p class="texto-sec">Los cambios de límites se aplican a todas las instituciones con el plan. Un plan inactivo no se ofrece en la contratación.</p>
        <div class="cuadricula-220">
            @foreach ($planes as $plan)
                <section class="tarjeta plan-opcion" aria-labelledby="plan-{{ $plan->id_plan }}">
                    <div class="encabezado-pagina encabezado-pagina--centro">
                        <h2 id="plan-{{ $plan->id_plan }}" class="plan-opcion__nombre">{{ $plan->nombre }}</h2>
                        <span class="insignia {{ $plan->activo ? 'insignia--exito' : 'insignia--neutra' }}">{{ $plan->activo ? 'Activo' : 'Inactivo' }}</span>
                    </div>
                    <span><span class="plan-opcion__precio">${{ number_format((float) $plan->monto_mensual, 2) }}</span>
                        <span class="texto-13 texto-sec">MXN / mes</span></span>
                    <div class="pila--8 titulos">
                        <span class="plan-opcion__caracteristica"><span class="icono" aria-hidden="true">check</span>{{ $plan->limite_areas }} áreas</span>
                        <span class="plan-opcion__caracteristica"><span class="icono" aria-hidden="true">check</span>{{ number_format($plan->limite_recursos) }} recursos</span>
                        <span class="plan-opcion__caracteristica"><span class="icono" aria-hidden="true">check</span>{{ $plan->duracion_dias }} días por periodo</span>
                    </div>
                    <span class="texto-13 texto-apoyo en-linea en-linea--8">
                        <span class="icono icono--18 icono--gris" aria-hidden="true">domain</span>{{ $plan->instituciones_count }} {{ $plan->instituciones_count === 1 ? 'institución' : 'instituciones' }}
                    </span>
                    <a href="{{ route('planes.edit', $plan) }}" class="boton boton--secundario boton--bloque">
                        <span class="icono" aria-hidden="true">edit</span>Editar
                    </a>
                </section>
            @endforeach
        </div>
    </div>
@endsection
