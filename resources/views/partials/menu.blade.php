{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         menu.blade.php
 * @path         resources/views/partials/menu.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Elementos del sidebar según el rol (máximo 7, icono con texto
 *               y elemento activo resaltado, Tabla 65).
 *
 * @requisitos   RNF-008, RS-006
 *
 * @see          Manual de Programación v1.2, Tablas 62 y 65
 * ============================================================================
--}}
@php
    $opciones = match (auth()->user()->rol) {
        \App\Enums\Rol::Superadmin => [
            ['panel', 'dashboard', 'Panel', 'panel'],
            ['instituciones.index', 'domain', 'Instituciones', 'instituciones.*'],
            ['planes.index', 'sell', 'Planes', 'planes.*'],
        ],
        \App\Enums\Rol::AdminInstitucional => [
            ['panel', 'dashboard', 'Panel', 'panel'],
            ['suscripcion.show', 'card_membership', 'Suscripción', 'suscripcion.*'],
            ['administradores.index', 'manage_accounts', 'Administradores', 'administradores.*'],
            ['notificaciones.index', 'notifications', 'Notificaciones', 'notificaciones.*'],
        ],
        \App\Enums\Rol::AdminSistema => [
            ['panel', 'dashboard', 'Panel', 'panel'],
            ['areas.index', 'apartment', 'Áreas', 'areas.*'],
            ['recursos.index', 'inventory_2', 'Inventario', 'recursos.index|recursos.create|recursos.edit'],
            ['recursos.importar', 'upload_file', 'Importar CSV', 'recursos.importar'],
            ['notificaciones.index', 'notifications', 'Notificaciones', 'notificaciones.*'],
        ],
        \App\Enums\Rol::Asistente => [
            ['notificaciones.index', 'notifications', 'Notificaciones', 'notificaciones.*'],
        ],
        default => [
            ['catalogo.index', 'grid_view', 'Catálogo', 'catalogo.*'],
            ['notificaciones.index', 'notifications', 'Notificaciones', 'notificaciones.*'],
        ],
    };
@endphp
<ul class="menu">
    @foreach ($opciones as [$ruta, $icono, $texto, $patron])
        @php($activo = request()->routeIs(...explode('|', $patron)))
        <li>
            <a href="{{ route($ruta) }}" class="menu__enlace {{ $activo ? 'menu__enlace--activo' : '' }}"
               @if ($activo) aria-current="page" @endif>
                <span class="icono" aria-hidden="true">{{ $icono }}</span>
                <span>{{ $texto }}</span>
            </a>
        </li>
    @endforeach
    <li>
        <form method="POST" action="{{ route('sesion.destroy') }}">
            @csrf
            <button type="submit" class="menu__enlace menu__enlace--boton">
                <span class="icono" aria-hidden="true">logout</span>
                <span>Cerrar sesión</span>
            </button>
        </form>
    </li>
</ul>
