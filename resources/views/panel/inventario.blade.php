{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Panel principal
 * @file         inventario.blade.php
 * @path         resources/views/panel/inventario.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Barra apilada y leyenda del estado del inventario.
 *               Variables: inventario (estado => total), total.
 *
 * @requisitos   RF-013, RF-054
 *
 * @see          DT-028
 * ============================================================================
--}}
<div class="barra-apilada" role="img"
     aria-label="{{ collect(\App\Enums\EstadoRecurso::cases())
         ->map(fn (\App\Enums\EstadoRecurso $e): string => $e->obtenerEtiqueta() . ' ' . $inventario[$e->value])
         ->implode(', ') }}">
    @foreach (\App\Enums\EstadoRecurso::cases() as $estado)
        @if ($inventario[$estado->value] > 0)
            <div class="color-{{ $estado->value }}"
                 style="width: {{ round($inventario[$estado->value] * 100 / $total, 2) }}%"></div>
        @endif
    @endforeach
</div>
<div class="titulos">
    @foreach (\App\Enums\EstadoRecurso::cases() as $estado)
        <div class="leyenda-lista__fila">
            <span class="leyenda-lista__punto color-{{ $estado->value }}" aria-hidden="true"></span>
            <span class="crece">{{ $estado->obtenerEtiqueta() }}</span>
            <span class="negrita">{{ $inventario[$estado->value] }}</span>
            <span class="leyenda-lista__porcentaje">{{ round($inventario[$estado->value] * 100 / $total) }} %</span>
        </div>
    @endforeach
</div>
