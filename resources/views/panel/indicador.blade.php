{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Panel principal
 * @file         indicador.blade.php
 * @path         resources/views/panel/indicador.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Tarjeta de indicador (KPI). Variables: etiqueta, valor,
 *               icono, tono (clase del tile), detalle, claseDetalle.
 *
 * @requisitos   RF-054
 *
 * @see          DT-028
 * ============================================================================
--}}
<div class="tarjeta kpi">
    <div class="kpi__cabecera">
        <span class="kpi__etiqueta">{{ $etiqueta }}</span>
        <span class="icono-tile icono-tile--40 {{ $tono }}" aria-hidden="true"><span class="icono">{{ $icono }}</span></span>
    </div>
    <span class="kpi__valor">{{ $valor }}</span>
    <span class="kpi__detalle {{ $claseDetalle ?? '' }}">{{ $detalle }}</span>
</div>
