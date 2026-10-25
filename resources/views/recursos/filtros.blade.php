{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         filtros.blade.php
 * @path         resources/views/recursos/filtros.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Barra de filtros de recursos: búsqueda, área, tipo y
 *               conmutador cuadrícula/lista. Variables: ruta, filtros, areas,
 *               tipos, vistaLista.
 *
 * @requisitos   RF-015
 *
 * @see          DT-028
 * ============================================================================
--}}
@php
    $consulta = array_filter($filtros, fn (mixed $v): bool => $v !== null && $v !== '');
    unset($consulta['page']);
@endphp
<form method="GET" action="{{ route($ruta) }}" class="barra-filtros barra-filtros--tarjeta" role="search">
    @if (! empty($filtros['estado']))
        <input type="hidden" name="estado" value="{{ $filtros['estado'] }}">
    @endif
    @if ($vistaLista)
        <input type="hidden" name="vista" value="lista">
    @endif
    <div class="control-icono control-icono--con-icono barra-filtros__busqueda--260">
        <span class="icono" aria-hidden="true">search</span>
        <label for="filtro-texto" class="visualmente-oculto">Buscar recurso</label>
        <input type="search" id="filtro-texto" name="texto" class="control" maxlength="100"
               placeholder="Buscar por nombre, inventario o marca" value="{{ $filtros['texto'] ?? '' }}">
    </div>
    <div class="selector selector--icono selector--170">
        <span class="icono icono-inicio" aria-hidden="true">apartment</span>
        <label for="filtro-area" class="visualmente-oculto">Área</label>
        <select id="filtro-area" name="id_area" class="control" data-autoenvio>
            <option value="">Todas las áreas</option>
            @foreach ($areas as $area)
                <option value="{{ $area->id_area }}" @selected((int) ($filtros['id_area'] ?? 0) === $area->id_area)>{{ $area->nombre_area }}</option>
            @endforeach
        </select>
        <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
    </div>
    <div class="selector selector--icono selector--170">
        <span class="icono icono-inicio" aria-hidden="true">category</span>
        <label for="filtro-tipo" class="visualmente-oculto">Tipo</label>
        <select id="filtro-tipo" name="tipo_recurso" class="control" data-autoenvio>
            <option value="">Equipos y espacios</option>
            @foreach ($tipos as $tipo)
                <option value="{{ $tipo->value }}" @selected(($filtros['tipo_recurso'] ?? '') === $tipo->value)>{{ $tipo->obtenerEtiqueta() }}</option>
            @endforeach
        </select>
        <span class="icono icono-flecha" aria-hidden="true">expand_more</span>
    </div>
    <button type="submit" class="visualmente-oculto">Buscar</button>
    <div class="segmentado" role="group" aria-label="Vista">
        <a href="{{ route($ruta, \Illuminate\Support\Arr::except($consulta, ['vista'])) }}"
           class="segmentado__opcion segmentado__opcion--icono {{ $vistaLista ? '' : 'segmentado__opcion--activa' }}"
           title="Tarjetas" aria-label="Ver como tarjetas" @unless ($vistaLista) aria-current="true" @endunless>
            <span class="icono" aria-hidden="true">grid_view</span>
        </a>
        <a href="{{ route($ruta, $consulta + ['vista' => 'lista']) }}"
           class="segmentado__opcion segmentado__opcion--icono {{ $vistaLista ? 'segmentado__opcion--activa' : '' }}"
           title="Lista" aria-label="Ver como lista" @if ($vistaLista) aria-current="true" @endif>
            <span class="icono" aria-hidden="true">view_list</span>
        </a>
    </div>
</form>
