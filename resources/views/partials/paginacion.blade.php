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
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Paginación del prototipo: "Mostrando N – M de T" y botones
 *               numerados de 32 px con ventana de cinco páginas. Variable
 *               opcional: suelta (fuera de una tarjeta).
 *
 * @requisitos   RF-015, RNF-009
 *
 * @see          DT-028
 * ============================================================================
--}}
@if ($paginator->total() > 0)
    @php
        $actual = $paginator->currentPage();
        $ultima = $paginator->lastPage();
        $desde = max(1, min($actual - 2, $ultima - 4));
        $hasta = min($ultima, $desde + 4);
    @endphp
    <nav class="paginacion {{ ($suelta ?? false) ? 'paginacion--suelta' : '' }}" aria-label="Paginación">
        <span>Mostrando {{ $paginator->firstItem() }} – {{ $paginator->lastItem() }} de {{ $paginator->total() }}</span>
        @if ($paginator->hasPages())
            <div class="paginacion__botones">
                @if ($paginator->onFirstPage())
                    <span class="paginacion__boton paginacion__boton--inactivo"
                          aria-hidden="true"><span class="icono">chevron_left</span></span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="paginacion__boton" rel="prev"
                       aria-label="Página anterior">
                        <span class="icono" aria-hidden="true">chevron_left</span>
                    </a>
                @endif
                @for ($pagina = $desde; $pagina <= $hasta; $pagina++)
                    @if ($pagina === $actual)
                        <span class="paginacion__boton paginacion__boton--actual"
                              aria-current="page">{{ $pagina }}</span>
                    @else
                        <a href="{{ $paginator->url($pagina) }}" class="paginacion__boton"
                           aria-label="Página {{ $pagina }}">{{ $pagina }}</a>
                    @endif
                @endfor
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="paginacion__boton" rel="next"
                       aria-label="Página siguiente">
                        <span class="icono" aria-hidden="true">chevron_right</span>
                    </a>
                @else
                    <span class="paginacion__boton paginacion__boton--inactivo"
                          aria-hidden="true"><span class="icono">chevron_right</span></span>
                @endif
            </div>
        @endif
    </nav>
@endif
