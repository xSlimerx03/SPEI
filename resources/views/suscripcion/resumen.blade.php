{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         resumen.blade.php
 * @path         resources/views/suscripcion/resumen.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Tarjeta con plan, límites y uso, vigencia, días restantes y
 *               estado de la suscripción. Variables: institucion, uso.
 *
 * @requisitos   RF-003, RF-004
 *
 * @see          docs/requisitos/BBB_CU002_AdministrarSuscripcion_v1.0.md (paso 1)
 * ============================================================================
--}}
@php
    $dias = $institucion->obtenerDiasParaCorte();
    $claseEstado = match ($institucion->estado_suscripcion) {
        \App\Enums\EstadoSuscripcion::Activa => 'etiqueta--exito',
        \App\Enums\EstadoSuscripcion::SoloLectura => 'etiqueta--advertencia',
        default => 'etiqueta--error',
    };
@endphp
<section class="tarjeta" aria-labelledby="titulo-resumen">
    <h2 id="titulo-resumen" class="tarjeta__titulo">Suscripción</h2>
    <dl class="lista-datos">
        <dt>Estado</dt>
        <dd><span class="etiqueta {{ $claseEstado }}">{{ $institucion->estado_suscripcion->obtenerEtiqueta() }}</span></dd>
        <dt>Plan</dt>
        <dd>{{ ucfirst($institucion->plan_suscripcion->value) }}</dd>
        <dt>Dominio</dt>
        <dd>{{ $institucion->dominio_correo }}</dd>
        <dt>Vigencia</dt>
        <dd>{{ $institucion->fecha_inicio->format('d/m/Y') }} – {{ $institucion->fecha_corte->format('d/m/Y') }}</dd>
        <dt>Días restantes</dt>
        <dd>{{ $dias >= 0 ? $dias : 'Vencida' }}</dd>
        <dt>Áreas</dt>
        <dd>{{ $uso['areas'] }} de {{ $institucion->limite_areas }}</dd>
        <dt>Recursos</dt>
        <dd>{{ $uso['recursos'] }} de {{ number_format($institucion->limite_recursos) }}</dd>
    </dl>
</section>
