{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         paginacion.blade.php
 * @path         resources/views/partials/paginacion.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Controles de paginación con botones secundarios.
 *
 * @requisitos   RF-015, RNF-009
 *
 * @see          Manual de Programación v1.2, Tabla 66
 * ============================================================================
--}}
@if ($paginator->hasPages())
    <nav class="paginacion" aria-label="Paginación">
        <span class="texto-secundario">
            Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }} · {{ $paginator->total() }} registros
        </span>
        <div class="paginacion__enlaces">
            @if (! $paginator->onFirstPage())
                <a href="{{ $paginator->previousPageUrl() }}" class="boton boton--secundario" rel="prev">
                    <span class="icono icono--boton" aria-hidden="true">chevron_left</span>Ver anterior
                </a>
            @endif
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="boton boton--secundario" rel="next">
                    Ver siguiente<span class="icono icono--boton" aria-hidden="true">chevron_right</span>
                </a>
            @endif
        </div>
    </nav>
@endif
