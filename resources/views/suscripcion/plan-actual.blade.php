{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         plan-actual.blade.php
 * @path         resources/views/suscripcion/plan-actual.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-25
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Tarjeta oscura del plan actual con estado y vigencia, y
 *               tarjeta de uso del plan. Variables: institucion, uso,
 *               administradores (número).
 *
 * @requisitos   RF-003, RF-004, RN-010
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Suscripción); DT-028
 * ============================================================================
--}}
@php
    $institucion->loadMissing('plan');
    $inicio = $institucion->fecha_inicio;
    $corte = $institucion->fecha_corte;
    $totalDias = max(1, (int) $inicio->diffInDays($corte));
    $transcurridos = min($totalDias, max(0, (int) $inicio->diffInDays(today())));
    $claseEstado = match ($institucion->estado_suscripcion) {
        \App\Enums\EstadoSuscripcion::Activa => 'insignia--exito',
        \App\Enums\EstadoSuscripcion::SoloLectura => 'insignia--pendiente',
        default => 'insignia--peligro',
    };
    $barras = [
        ['Áreas', $uso['areas'], $institucion->limite_areas],
        ['Recursos', $uso['recursos'], $institucion->limite_recursos],
    ];
@endphp
<div class="cuadricula-360">
    <section class="tarjeta tarjeta--oscura tarjeta--relleno-24 pila--20 titulos" aria-labelledby="titulo-plan-actual">
        <div class="encabezado-pagina encabezado-pagina--centro">
            <div class="titulos">
                <span class="texto-13 texto-claro">Plan actual</span>
                <h2 id="titulo-plan-actual"
                    class="plan-actual__nombre">{{ $institucion->plan?->nombre
                        ?? $institucion->plan_suscripcion->obtenerEtiqueta() }}</h2>
            </div>
            <span class="insignia {{ $claseEstado }}">{{ $institucion->estado_suscripcion->obtenerEtiqueta() }}</span>
        </div>
        <div class="pila--8 titulos">
            <div class="encabezado-pagina texto-13 texto-claro-2">
                <span>{{ \App\Support\FormatoFecha::conAnio($inicio) }}</span>
                <span>Corte: {{ \App\Support\FormatoFecha::conAnio($corte) }}</span>
            </div>
            <div class="progreso progreso--oscuro" role="img"
                 aria-label="{{ $transcurridos }} de {{ $totalDias }} días transcurridos">
                <div class="progreso__valor progreso__valor--claro"
                     style="width: {{ round($transcurridos * 100 / $totalDias, 1) }}%"></div>
            </div>
            <span class="texto-13 texto-claro-2">{{ $transcurridos }} de {{ $totalDias }} días transcurridos</span>
        </div>
    </section>

    <section class="tarjeta tarjeta--relleno-24 pila--20 titulos" aria-labelledby="titulo-uso-plan">
        <h2 id="titulo-uso-plan" class="tarjeta__titulo">Uso del plan</h2>
        @foreach ($barras as [$etiqueta, $usado, $limite])
            @php($porcentaje = min(100, round($usado * 100 / max(1, $limite))))
            <div class="pila--8 titulos">
                <div class="encabezado-pagina">
                    <span>{{ $etiqueta }}</span><span><strong>{{ $usado }}</strong> de
                    {{ number_format($limite) }}</span>
                </div>
                <div class="progreso" role="img" aria-label="{{ $porcentaje }} % usado">
                    <div class="progreso__valor {{ $porcentaje >= 90 ? 'progreso__valor--ambar' : '' }}"
                         style="width: {{ $porcentaje }}%"></div>
                </div>
            </div>
        @endforeach
        <div class="encabezado-pagina">
            <span>Administradores del sistema</span>
            <span><strong>{{ $administradores }}</strong> registrados</span>
        </div>
    </section>
</div>
